<?php
/**
 * WooCommerce-Anpassungen.
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WooCommerce' ) ) {
	return;
}

/* Eigene Wrapper statt der Standard-Container, keine Sidebar, keine Standard-Brotkrumen. */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_action( 'woocommerce_before_main_content', fn() => print '<main id="main" class="wc-main">', 10 );
add_action( 'woocommerce_after_main_content', fn() => print '</main>', 10 );

/* Nur die Styles behalten, die Warenkorb, Kasse und Konto brauchen. */
add_filter(
	'woocommerce_enqueue_styles',
	function ( $styles ) {
		unset( $styles['woocommerce-smallscreen'] );
		return $styles;
	}
);

add_filter( 'loop_shop_per_page', fn() => 24 );
add_filter( 'loop_shop_columns', fn() => 4 );

/* ---------- Produktseite ---------- */

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
/* Details direkt unter den Kaufbereich statt unter die Bilder. */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 45 );

add_action( 'woocommerce_single_product_summary', 'polinis_single_meta_line', 6 );
function polinis_single_meta_line() {
	global $product;
	$serie  = polinis_product_serie( $product );
	$art_nr = get_post_meta( $product->get_id(), '_polinis_art_nr', true );
	echo '<p class="pdp__meta">';
	if ( $serie ) {
		printf( '<a href="%s">%s</a>', esc_url( get_term_link( $serie ) ), esc_html( $serie->name ) );
	}
	if ( $art_nr ) {
		printf( '<span class="mono">%s</span>', esc_html( $art_nr ) );
	}
	echo '</p>';
}

add_action( 'woocommerce_single_product_summary', 'polinis_single_promises', 35 );
function polinis_single_promises() {
	?>
	<ul class="pdp__promises">
		<li><?php echo polinis_icon( 'truck' ); ?><?php esc_html_e( 'Versandkostenfrei in Deutschland', 'polinis' ); ?></li>
		<li><?php echo polinis_icon( 'ruler' ); ?><?php esc_html_e( 'Unisex-Schnitt, Größentabelle unten', 'polinis' ); ?></li>
		<?php if ( polinis_payment_labels() ) : ?>
			<li><?php echo polinis_icon( 'card' ); ?><?php echo esc_html( implode( ', ', polinis_payment_labels() ) ); ?></li>
		<?php endif; ?>
	</ul>
	<?php
}

add_filter( 'woocommerce_product_related_products_heading', fn() => __( 'Passt dazu', 'polinis' ) );
add_filter(
	'woocommerce_output_related_products_args',
	function ( $args ) {
		$args['posts_per_page'] = 4;
		$args['columns']        = 4;
		return $args;
	}
);

/* Der frühere, lange Shopify-Titel bleibt als Seitentitel für Suchmaschinen. */
add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( is_product() ) {
			$seo = get_post_meta( get_the_ID(), '_polinis_seo_title', true );
			if ( $seo ) {
				$parts['title'] = get_the_title() . ' – ' . $seo;
			}
		}
		return $parts;
	}
);

add_action(
	'wp_head',
	function () {
		if ( is_product() && ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'RankMath' ) ) {
			$desc = wp_strip_all_tags( get_the_excerpt() );
			if ( $desc ) {
				printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_trim_words( $desc, 28 ) ) );
			}
		}
	}
);

/* ---------- Warenkorb-Zähler im Header ---------- */

function polinis_cart_count_html() {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return sprintf( '<span class="cart-count" data-count="%1$d">%1$d</span>', $count );
}
add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['.cart-count'] = polinis_cart_count_html();
		return $fragments;
	}
);

/* ---------- Zahlarten: nur zeigen, was wirklich eingerichtet ist ---------- */

function polinis_payment_labels() {
	if ( ! function_exists( 'WC' ) || ! WC()->payment_gateways() ) {
		return array();
	}
	$names = array(
		'ppcp-gateway'        => 'PayPal',
		'ppcp'                => 'PayPal',
		'paypal'              => 'PayPal',
		'klarna_payments'     => 'Klarna',
		'kco'                 => 'Klarna',
		'stripe'              => 'Karte',
		'stripe_applepay'     => 'Apple Pay',
		'stripe_googlepay'    => 'Google Pay',
		'woocommerce_payments' => 'Karte',
		'bacs'                => 'Überweisung',
		'cod'                 => 'Nachnahme',
	);
	$labels = array();
	foreach ( WC()->payment_gateways()->payment_gateways() as $id => $gateway ) {
		if ( 'yes' === $gateway->enabled ) {
			$labels[] = $names[ $id ] ?? wp_strip_all_tags( $gateway->get_title() );
		}
	}
	return array_values( array_unique( $labels ) );
}

/* ---------- Helfer für Karten ---------- */

function polinis_product_serie( $product ) {
	$terms = get_the_terms( $product->get_id(), 'product_tag' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return null;
	}
	foreach ( $terms as $t ) {
		if ( in_array( $t->slug, array( 's-01', 's-02', 'essentials' ), true ) ) {
			return $t;
		}
	}
	return null;
}

function polinis_product_colors( $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return array();
	}
	$attrs = $product->get_variation_attributes();
	if ( empty( $attrs['pa_farbe'] ) ) {
		return array();
	}
	$names = array();
	foreach ( $attrs['pa_farbe'] as $slug ) {
		$term    = get_term_by( 'slug', $slug, 'pa_farbe' );
		$names[] = $term ? $term->name : $slug;
	}
	return $names;
}

function polinis_product_sizes_label( $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return '';
	}
	$attrs = $product->get_variation_attributes();
	if ( empty( $attrs['pa_groesse'] ) ) {
		return '';
	}
	$terms = wc_get_product_terms( $product->get_id(), 'pa_groesse', array( 'orderby' => 'menu_order' ) );
	if ( ! $terms ) {
		return '';
	}
	$first = reset( $terms )->name;
	$last  = end( $terms )->name;
	return $first === $last ? $first : $first . '–' . $last;
}

/**
 * Bekannte Textilfarben als Farbwerte für die Farbpunkte.
 */
function polinis_color_hex( $name ) {
	$map = array(
		'schwarz'          => '#111014',
		'weiß'             => '#f4f3f1',
		'sand'             => '#c8a77a',
		'carolina-blau'    => '#9ec3e6',
		'athletisch heather' => '#a7a7a9',
		'white heather'    => '#e7e6e4',
		'heather grau'     => '#9d9ea0',
		'stein'            => '#d9cfb6',
		'french navy'      => '#1d2540',
		'navy'             => '#1d2540',
		'pine green'       => '#1d4a3c',
		'fichte'           => '#1d4a3c',
		'kobalt'           => '#2f45a8',
		'kastanie'         => '#6b3a26',
		'clay'             => '#a4552e',
		'cypress'          => '#6e7651',
		'desert dust'      => '#d8c6a8',
		'fraiche peche'    => '#f2c6ad',
		'cotton pink'      => '#f3c3cf',
		'lavendel'         => '#c9b9e6',
		'faded bone'       => '#e6e0d2',
		'faded eucalyptus' => '#b8c4a8',
		'faded khaki'      => '#cdbd97',
		'faded grey'       => '#8f8f8c',
		'faded black'      => '#2a292b',
		'faded coal'       => '#46484b',
		'faded indigo'     => '#4f6584',
		'dunkelgrau'       => '#46474b',
		'silber'           => '#a9abae',
		'natürlich/ schwarz' => '#e9e2cf',
	);
	$key = mb_strtolower( trim( $name ) );
	return $map[ $key ] ?? '#888888';
}
