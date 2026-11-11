<?php
/**
 * Kleine Bausteine für die Templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline-SVG-Icons in einer Strichstärke.
 */
function polinis_icon( $name, $label = '' ) {
	$paths = array(
		'bag'      => '<path d="M5 8h14l-1.2 12.1a1 1 0 0 1-1 .9H7.2a1 1 0 0 1-1-.9L5 8Z"/><path d="M9 10V6.5a3 3 0 0 1 6 0V10"/>',
		'search'   => '<circle cx="10.5" cy="10.5" r="6"/><path d="m15 15 5 5"/>',
		'user'     => '<circle cx="12" cy="8.5" r="3.5"/><path d="M5 20c.8-3.6 3.6-5.5 7-5.5s6.2 1.9 7 5.5"/>',
		'arrow'    => '<path d="M4 12h15"/><path d="m13 6 6 6-6 6"/>',
		'menu'     => '<path d="M4 8h16"/><path d="M4 16h16"/>',
		'close'    => '<path d="m6 6 12 12"/><path d="M18 6 6 18"/>',
		'plus'     => '<path d="M12 5v14"/><path d="M5 12h14"/>',
		'target'   => '<circle cx="12" cy="12" r="6.5"/><path d="M12 2v5M12 17v5M2 12h5M17 12h5"/><circle cx="12" cy="12" r="1" fill="currentColor"/>',
		'truck'    => '<path d="M3 6.5h11v9H3z"/><path d="M14 9.5h3.6l3.4 3.3v2.7h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
		'leaf'     => '<path d="M5 19c0-8 5-13 14-14 0 9-5 14-13 14"/><path d="M5 19c3-4 6-6.5 9-8"/>',
		'ruler'    => '<path d="m3.5 15.5 12-12 5 5-12 12z"/><path d="m7.5 11.5 2 2M10.5 8.5l2 2M13.5 5.5l2 2"/>',
		'card'     => '<rect x="3" y="5.5" width="18" height="13" rx="1.5"/><path d="M3 10h18"/><path d="M7 15h3"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	$aria = $label ? 'role="img" aria-label="' . esc_attr( $label ) . '"' : 'aria-hidden="true" focusable="false"';
	return '<svg class="icon icon--' . esc_attr( $name ) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" ' . $aria . '>' . $paths[ $name ] . '</svg>';
}

/**
 * Das Logo bleibt unverändert; im dunklen Zustand wird es per CSS invertiert.
 */
function polinis_logo( $class = '' ) {
	$src = get_template_directory_uri() . '/assets/img/logo.png';
	return sprintf(
		'<img class="logo %s" src="%s" width="900" height="655" alt="%s" decoding="async">',
		esc_attr( $class ),
		esc_url( $src ),
		esc_attr__( 'Polinis®', 'polinis' )
	);
}

/**
 * Der ON/OFF-Kippschalter – das Motiv von den S-01-Teilen.
 *
 * @param string $size 'hero' oder 'mini'.
 */
function polinis_switch( $size = 'mini' ) {
	ob_start();
	?>
	<button type="button" class="switch switch--<?php echo esc_attr( $size ); ?>" data-signal-toggle aria-pressed="false">
		<span class="switch__plate" aria-hidden="true">
			<span class="switch__rocker">
				<span class="switch__on">ON</span>
				<span class="switch__off">OFF</span>
			</span>
		</span>
		<span class="switch__caption">
			<span class="switch__state switch__state--off"><?php esc_html_e( 'No Signal', 'polinis' ); ?></span>
			<span class="switch__state switch__state--on"><?php esc_html_e( 'Signal', 'polinis' ); ?></span>
		</span>
		<span class="screen-reader-text"><?php esc_html_e( 'Zwischen S-01 No Signal und S-02 Light Series umschalten', 'polinis' ); ?></span>
	</button>
	<?php
	return ob_get_clean();
}

/**
 * Produkte einer Serie (Produkt-Schlagwort s-01, s-02, essentials).
 */
function polinis_series_products( $serie, $limit = 12 ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}
	return wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => $limit,
			'tag'     => array( $serie ),
			'orderby' => 'menu_order',
			'order'   => 'ASC',
		)
	);
}

/**
 * Link zu einer Serie oder Kategorie, ohne Fehler wenn sie noch nicht existiert.
 */
function polinis_term_link( $slug, $taxonomy = 'product_tag' ) {
	$link = get_term_link( $slug, $taxonomy );
	return is_wp_error( $link ) ? home_url( '/shop/' ) : $link;
}

function polinis_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/**
 * Hauptnavigation – falls kein Menü zugewiesen ist, aus Serien und Kategorien.
 */
function polinis_primary_nav() {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'nav__list',
				'depth'          => 1,
			)
		);
		return;
	}
	$items = array(
		array( polinis_term_link( 's-01' ), 'S-01 No Signal' ),
		array( polinis_term_link( 's-02' ), 'S-02 Light Series' ),
		array( polinis_term_link( 'essentials' ), 'Essentials' ),
		array( polinis_term_link( 'hoodies', 'product_cat' ), 'Hoodies' ),
		array( polinis_term_link( 't-shirts', 'product_cat' ), 'T-Shirts' ),
		array( polinis_term_link( 'sweatshirts', 'product_cat' ), 'Sweats' ),
		array( polinis_term_link( 'caps', 'product_cat' ), 'Caps' ),
	);
	echo '<ul class="nav__list">';
	foreach ( $items as $item ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $item[0] ), esc_html( $item[1] ) );
	}
	echo '</ul>';
}

function polinis_legal_nav() {
	if ( has_nav_menu( 'legal' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'legal',
				'container'      => false,
				'menu_class'     => 'footer__legal',
				'depth'          => 1,
			)
		);
		return;
	}
	$pages = array( 'impressum' => 'Impressum', 'datenschutz' => 'Datenschutz', 'agb' => 'AGB', 'widerrufsrecht' => 'Widerrufsrecht', 'versand' => 'Versand', 'kontakt' => 'Kontakt' );
	echo '<ul class="footer__legal">';
	foreach ( $pages as $slug => $label ) {
		$page = get_page_by_path( $slug );
		$url  = $page ? get_permalink( $page ) : home_url( '/' . $slug . '/' );
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Text escapen und Seriennummern (S-01, S-02) zusammenhalten.
 */
function polinis_nobr( $text ) {
	return preg_replace( '/\bS-0\d\b/', '<span class="nobr">$0</span>', esc_html( $text ) );
}

/**
 * Anzahl Produkte in einer Kategorie.
 */
function polinis_cat_count( $slug ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	return $term ? (int) $term->count : 0;
}
