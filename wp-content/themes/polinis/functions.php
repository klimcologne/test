<?php
/**
 * Polinis ON/OFF – Theme-Funktionen.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'POLINIS_VERSION', '1.0.0' );

require_once __DIR__ . '/inc/template-tags.php';
require_once __DIR__ . '/inc/woocommerce.php';
require_once __DIR__ . '/inc/newsletter.php';
require_once __DIR__ . '/inc/customizer.php';
require_once __DIR__ . '/inc/series.php';

/* ---------- Setup ---------- */

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'polinis', __DIR__ . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
		add_theme_support( 'responsive-embeds' );
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 720,
				'single_image_width'    => 1200,
				'product_grid'          => array( 'default_columns' => 4, 'default_rows' => 6 ),
			)
		);

		register_nav_menus(
			array(
				'primary' => __( 'Hauptmenü', 'polinis' ),
				'footer'  => __( 'Footer: Shop', 'polinis' ),
				'legal'   => __( 'Footer: Rechtliches', 'polinis' ),
			)
		);
	}
);

/* Große Zwischengrößen braucht der Shop nicht – spart Speicher beim Import. */
add_filter(
	'intermediate_image_sizes_advanced',
	function ( $sizes ) {
		unset( $sizes['1536x1536'], $sizes['2048x2048'], $sizes['medium_large'] );
		return $sizes;
	}
);

/* ---------- Assets ---------- */

add_action(
	'wp_enqueue_scripts',
	function () {
		$uri = get_template_directory_uri() . '/assets';
		wp_enqueue_style( 'polinis', $uri . '/css/main.css', array(), POLINIS_VERSION );
		wp_enqueue_script( 'polinis', $uri . '/js/main.js', array(), POLINIS_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script(
			'polinis',
			'POLINIS',
			array(
				'storageKey' => 'polinis-signal',
				'labels'     => array(
					'on'  => __( 'Signal an: S-02 Light Series', 'polinis' ),
					'off' => __( 'Signal aus: S-01 No Signal', 'polinis' ),
				),
			)
		);
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'classic-theme-styles' );
	},
	20
);

/**
 * Serienseiten und Serienprodukte haben ihre feste Welt:
 * S-01 ist immer OFF (dunkel), S-02 immer ON (hell). Sonst entscheidet der Schalter.
 */
function polinis_forced_signal() {
	$serie = '';
	if ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
		$serie = get_queried_object()->slug ?? '';
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$term  = polinis_product_serie( wc_get_product( get_queried_object_id() ) );
		$serie = $term ? $term->slug : '';
	}
	return array( 's-01' => 'off', 's-02' => 'on' )[ $serie ] ?? '';
}

function polinis_default_signal() {
	return get_theme_mod( 'polinis_default_signal', 'off' ) === 'on' ? 'on' : 'off';
}

add_action(
	'wp_head',
	function () {
		$uri = get_template_directory_uri() . '/assets/fonts/';
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( $uri . 'archivo-latin-wdth-normal.woff2' ) );
		$forced = polinis_forced_signal();
		// Zustand vor dem ersten Rendern setzen, damit nichts aufblitzt.
		echo "<script>(function(){var f='" . esc_js( $forced ) . "',s='" . esc_js( polinis_default_signal() ) . "';try{var v=localStorage.getItem('polinis-signal');if(v==='on'||v==='off'){s=v;}}catch(e){}document.documentElement.dataset.signal=f||s;})();</script>\n";
		echo '<meta name="theme-color" content="#0d0c11">' . "\n";
	},
	1
);

add_filter(
	'language_attributes',
	function ( $output ) {
		return $output . ' data-signal="' . esc_attr( polinis_forced_signal() ?: polinis_default_signal() ) . '"';
	}
);

/* ---------- Sonstiges ---------- */

add_filter( 'excerpt_length', fn() => 24 );

add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_front_page() ) {
			$classes[] = 'is-home';
		}
		return $classes;
	}
);
