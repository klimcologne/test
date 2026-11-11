<?php
/**
 * Importiert die Produkte aus dem bisherigen Shopify-Shop nach WooCommerce.
 *
 * Aufruf (im WordPress-Verzeichnis):
 *   wp eval-file /pfad/zum/repo/tools/import-shopify.php
 *
 * Liest data/shopify-products.json (Export von /products.json) und
 * data/product-map.json (neue Namen, Serie, Kategorie). Mehrfach ausführbar:
 * bereits importierte Produkte werden aktualisiert, Bilder nicht doppelt geladen.
 *
 * Umgebungsvariablen (optional):
 *   POLINIS_IMAGE_BASE   Bildquelle, Standard: https://polinis-onlineshop.de/cdn/shop/files/
 *   POLINIS_MAX_GALLERY  Bilder pro Produktgalerie, Standard: 8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$polinis_root  = dirname( __DIR__ );
$polinis_data  = json_decode( file_get_contents( $polinis_root . '/data/shopify-products.json' ), true );
$polinis_map   = json_decode( file_get_contents( $polinis_root . '/data/product-map.json' ), true );
$polinis_max   = (int) ( getenv( 'POLINIS_MAX_GALLERY' ) ?: 8 );
$polinis_sizes = array( 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL' );
// Farben: Schwarz und Weiß vorne, dann dunkel nach hell.
$polinis_colors = array( 'Schwarz', 'Faded Black', 'Weiß', 'French Navy', 'Navy', 'Faded Coal', 'Dunkelgrau', 'Pine Green', 'Fichte', 'Kobalt', 'Faded Indigo', 'Kastanie', 'Clay', 'Cypress', 'Faded Grey', 'Athletisch Heather', 'Heather Grau', 'Silber', 'Sand', 'Faded Khaki', 'Desert Dust', 'Stein', 'Faded Bone', 'Faded Eucalyptus', 'Carolina-Blau', 'Lavendel', 'Cotton Pink', 'Fraiche Peche', 'White Heather', 'Natürlich/ Schwarz' );

if ( ! $polinis_data || ! $polinis_map ) {
	WP_CLI::error( 'Datendateien fehlen oder sind kein gültiges JSON.' );
}

/* ---------- Hilfsfunktionen ---------- */

function polinis_term( $name, $slug, $taxonomy, $description = '' ) {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( $term ) {
		wp_update_term( $term->term_id, $taxonomy, array( 'name' => $name, 'description' => $description ) );
		return (int) $term->term_id;
	}
	$res = wp_insert_term( $name, $taxonomy, array( 'slug' => $slug, 'description' => $description ) );
	if ( is_wp_error( $res ) ) {
		WP_CLI::error( $res->get_error_message() );
	}
	return (int) $res['term_id'];
}

function polinis_attribute( $label, $slug ) {
	$id = wc_attribute_taxonomy_id_by_name( $slug );
	if ( ! $id ) {
		$id = wc_create_attribute( array( 'name' => $label, 'slug' => $slug, 'type' => 'select', 'order_by' => 'menu_order' ) );
	}
	$tax = wc_attribute_taxonomy_name( $slug );
	if ( ! taxonomy_exists( $tax ) ) {
		register_taxonomy( $tax, array( 'product', 'product_variation' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
	}
	return array( (int) $id, $tax );
}

function polinis_value_term( $value, $tax, $order ) {
	$term = get_term_by( 'name', $value, $tax );
	if ( ! $term ) {
		$res  = wp_insert_term( $value, $tax );
		$term = get_term( $res['term_id'], $tax );
	}
	if ( null !== $order ) {
		update_term_meta( $term->term_id, 'order', $order );
	}
	return $term;
}

function polinis_file_from_src( $src ) {
	return basename( wp_parse_url( $src, PHP_URL_PATH ) );
}

function polinis_image_base() {
	return getenv( 'POLINIS_IMAGE_BASE' ) ?: 'https://polinis-onlineshop.de/cdn/shop/files/';
}

function polinis_image( $file, $post_id, $alt, $query = '?width=1400' ) {
	$found = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'meta_key' => '_polinis_src', 'meta_value' => $file, 'fields' => 'ids', 'numberposts' => 1 ) );
	if ( $found ) {
		return (int) $found[0];
	}
	$tmp = download_url( polinis_image_base() . rawurlencode( $file ) . $query, 60 );
	if ( is_wp_error( $tmp ) ) {
		WP_CLI::warning( "Bild nicht geladen: $file (" . $tmp->get_error_message() . ')' );
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), $post_id, $alt );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( "Bild nicht gespeichert: $file" );
		return 0;
	}
	update_post_meta( $id, '_polinis_src', $file );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	return (int) $id;
}

/**
 * Dateien, die in Beschreibungen beim Shopify-Server liegen (Pflegehinweise,
 * GPSR-Sicherheitsinfos, Hersteller-PDF, Gewährleistungslabel), in die
 * Mediathek holen und die Links umschreiben – sonst brechen sie, sobald Shopify gekündigt ist.
 */
function polinis_localize_files( $html, $post_id ) {
	return preg_replace_callback(
		'#https://cdn\.shopify\.com/s/files/[0-9/]+/files/([^"\'?\s]+)(\?[^"\'\s]*)?#',
		function ( $m ) use ( $post_id ) {
			$file = rawurldecode( $m[1] );
			$id   = polinis_image( $file, $post_id, '', '' );
			return $id ? wp_get_attachment_url( $id ) : $m[0];
		},
		$html
	);
}

function polinis_find_product( $handle, $type ) {
	$ids = get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'meta_key' => '_shopify_handle', 'meta_value' => $handle, 'fields' => 'ids', 'numberposts' => 1 ) );
	return $ids ? (int) $ids[0] : 0;
}

function polinis_sku_free( $sku, $own_id ) {
	$id = wc_get_product_id_by_sku( $sku );
	return ! $id || (int) $id === (int) $own_id;
}

function polinis_lead_text( $html ) {
	preg_match_all( '#<p[^>]*>(.*?)</p>#si', $html, $m );
	foreach ( $m[1] as $p ) {
		$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $p ) ) );
		if ( mb_strlen( $text ) > 60 && ! str_contains( $text, 'Artikelnummer' ) ) {
			return $text;
		}
	}
	return '';
}

/* ---------- Taxonomien ---------- */

$serie_ids = array();
foreach ( $polinis_map['series'] as $slug => $s ) {
	$serie_ids[ $slug ] = polinis_term( $s['name'], $slug, 'product_tag', $s['description'] );
}
$cat_ids = array();
$i       = 0;
foreach ( $polinis_map['categories'] as $slug => $name ) {
	$cat_ids[ $slug ] = polinis_term( $name, $slug, 'product_cat' );
	update_term_meta( $cat_ids[ $slug ], 'order', $i++ );
}

$attr_slugs = array( 'Farbe' => 'farbe', 'Größe' => 'groesse' );
$attrs      = array();
foreach ( $attr_slugs as $label => $slug ) {
	$attrs[ $label ] = polinis_attribute( $label, $slug );
}

$serie_offset = array( 's-01' => 0, 's-02' => 100, 'essentials' => 200 );

/* ---------- Produkte ---------- */

$count = 0;
foreach ( $polinis_data['products'] as $p ) {
	$handle = $p['handle'];
	$m      = $polinis_map['products'][ $handle ] ?? null;
	if ( ! $m ) {
		WP_CLI::warning( "Kein Mapping für $handle – übersprungen." );
		continue;
	}

	$options     = array_values( array_filter( $p['options'], fn( $o ) => 'Title' !== $o['name'] ) );
	$is_variable = count( $options ) > 0;
	$existing    = polinis_find_product( $handle, 'product' );
	$product     = $is_variable ? new WC_Product_Variable( $existing ) : new WC_Product_Simple( $existing );

	$art_nr = '';
	if ( preg_match( '/Artikelnummer:\s*(?:<[^>]+>\s*)*([A-Z]{2,4}-[A-Z]{2,4}-\d{2,4})/u', $p['body_html'], $mm ) ) {
		$art_nr = $mm[1];
	}

	$product->set_name( $m['name'] );
	$product->set_slug( sanitize_title( $m['name'] ) );
	$product->set_description( $p['body_html'] );
	$product->set_short_description( polinis_lead_text( $p['body_html'] ) );
	$product->set_status( 'publish' );
	$product->set_category_ids( array( $cat_ids[ $m['cat'] ] ) );
	$product->set_tag_ids( array( $serie_ids[ $m['serie'] ] ) );
	$product->set_menu_order( $serie_offset[ $m['serie'] ] + (int) $m['order'] );
	$product->set_date_created( $p['published_at'] ?? $p['created_at'] );
	if ( ! $is_variable ) {
		$product->set_regular_price( $p['variants'][0]['price'] );
		$product->set_stock_status( $p['variants'][0]['available'] ? 'instock' : 'outofstock' );
	}
	if ( $art_nr && polinis_sku_free( $art_nr, $existing ) ) {
		$product->set_sku( $art_nr );
	}

	if ( $is_variable ) {
		$wc_attrs = array();
		$pos      = 0;
		foreach ( $options as $opt ) {
			if ( ! isset( $attrs[ $opt['name'] ] ) ) {
				continue;
			}
			list( $attr_id, $tax ) = $attrs[ $opt['name'] ];
			$term_ids              = array();
			foreach ( $opt['values'] as $v ) {
				$order      = array_search( $v, 'Größe' === $opt['name'] ? $polinis_sizes : $polinis_colors, true );
				$term_ids[] = polinis_value_term( $v, $tax, false === $order ? 99 : $order )->term_id;
			}
			$a = new WC_Product_Attribute();
			$a->set_id( $attr_id );
			$a->set_name( $tax );
			$a->set_options( $term_ids );
			$a->set_position( $pos++ );
			$a->set_visible( true );
			$a->set_variation( true );
			$wc_attrs[] = $a;
		}
		$product->set_attributes( $wc_attrs );
	}

	$product_id = $product->save();
	$product->set_description( polinis_localize_files( $p['body_html'], $product_id ) );
	$product->save();
	update_post_meta( $product_id, '_shopify_handle', $handle );
	update_post_meta( $product_id, '_shopify_id', $p['id'] );
	update_post_meta( $product_id, '_polinis_seo_title', $p['title'] );
	update_post_meta( $product_id, '_polinis_art_nr', $art_nr );

	/* Bilder: Vorderseite, Rückendruck, Lifestyle, dann je Farbe ein Bild. */
	$files   = array_map( fn( $im ) => polinis_file_from_src( $im['src'] ), $p['images'] );
	$is_side = fn( $f ) => (bool) preg_match( '/-(left|right)(-front|-back)?-/', $f );
	$wanted  = array( $files[0] );
	foreach ( $files as $f ) {
		if ( str_contains( $f, '-back-' ) && ! $is_side( $f ) ) {
			$wanted[] = $f;
			break;
		}
	}
	foreach ( $files as $f ) {
		if ( ! preg_match( '/^(unisex|mens|womens|adidas|classic)-/', $f ) ) {
			$wanted[] = $f; // Lifestyle-Fotos.
		}
	}
	foreach ( $p['variants'] as $v ) {
		if ( ! empty( $v['featured_image']['src'] ) ) {
			$wanted[] = polinis_file_from_src( $v['featured_image']['src'] );
		}
	}
	foreach ( $files as $f ) {
		if ( ! $is_side( $f ) ) {
			$wanted[] = $f;
		}
	}
	$wanted = array_slice( array_values( array_unique( $wanted ) ), 0, max( 2, $polinis_max ) );

	$image_ids = array();
	foreach ( $wanted as $f ) {
		$id = polinis_image( $f, $product_id, $m['name'] );
		if ( $id ) {
			$image_ids[ $f ] = $id;
		}
	}
	$ids = array_values( $image_ids );
	$product = wc_get_product( $product_id );
	$product->set_image_id( $ids[0] ?? 0 );
	$product->set_gallery_image_ids( array_slice( $ids, 1 ) );
	$product->save();

	/* Hover-Bild für die Produktkarte: das Gegenstück zum Titelbild. */
	$first_is_back = str_contains( $files[0], '-back-' );
	$hover         = 0;
	foreach ( $image_ids as $f => $id ) {
		if ( $f !== $files[0] && ( $first_is_back ? str_contains( $f, '-front-' ) : str_contains( $f, '-back-' ) ) ) {
			$hover = $id;
			break;
		}
	}
	update_post_meta( $product_id, '_polinis_hover_image', $hover ?: ( $ids[1] ?? 0 ) );

	/* Varianten */
	if ( $is_variable ) {
		$defaults = array();
		foreach ( $p['variants'] as $idx => $v ) {
			$vid       = polinis_find_product( (string) $v['id'], 'product_variation' );
			$variation = new WC_Product_Variation( $vid );
			$variation->set_parent_id( $product_id );
			$va = array();
			foreach ( $options as $k => $opt ) {
				if ( ! isset( $attrs[ $opt['name'] ] ) ) {
					continue;
				}
				$tax          = $attrs[ $opt['name'] ][1];
				$val          = $v[ 'option' . ( $k + 1 ) ];
				$va[ $tax ]   = get_term_by( 'name', $val, $tax )->slug;
			}
			$variation->set_attributes( $va );
			$variation->set_regular_price( $v['price'] );
			$variation->set_menu_order( $idx );
			$variation->set_stock_status( $v['available'] ? 'instock' : 'outofstock' );
			if ( $v['sku'] && polinis_sku_free( $v['sku'], $vid ) ) {
				$variation->set_sku( $v['sku'] );
			}
			if ( ! empty( $v['featured_image']['src'] ) ) {
				$f = polinis_file_from_src( $v['featured_image']['src'] );
				$variation->set_image_id( $image_ids[ $f ] ?? polinis_image( $f, $product_id, $m['name'] ) );
			}
			$variation->save();
			update_post_meta( $variation->get_id(), '_shopify_handle', (string) $v['id'] );
			if ( 0 === $idx && isset( $va['pa_farbe'] ) ) {
				$defaults = array( 'pa_farbe' => $va['pa_farbe'] ); // Größe wählt der Kunde selbst.
			}
		}
		$product = wc_get_product( $product_id );
		$product->set_default_attributes( $defaults );
		$product->save();
		WC_Product_Variable::sync( $product_id );
	}

	++$count;
	WP_CLI::log( sprintf( '✓ %-34s %s · %d Bilder', $m['name'], $m['serie'], count( $image_ids ) ) );
}

wc_delete_product_transients();
WP_CLI::success( "$count Produkte importiert." );
