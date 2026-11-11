<?php
/**
 * Shop, Serien und Kategorien.
 *
 * @package Polinis
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$polinis_term  = is_product_taxonomy() ? get_queried_object() : null;
$polinis_title = $polinis_term ? $polinis_term->name : __( 'Alle Teile', 'polinis' );
$polinis_desc  = $polinis_term ? term_description( $polinis_term ) : '<p>' . esc_html__( 'Drei Linien, ein Label: S-01 No Signal, S-02 Light Series und die Essentials.', 'polinis' ) . '</p>';
$polinis_art   = array( 's-01' => 'art-s01.webp', 's-02' => 'art-s02.webp' );
$polinis_chips = array(
	array( polinis_shop_url(), __( 'Alle', 'polinis' ), is_shop() ),
	array( polinis_term_link( 's-01' ), 'S-01 No Signal', is_product_tag( 's-01' ) ),
	array( polinis_term_link( 's-02' ), 'S-02 Light Series', is_product_tag( 's-02' ) ),
	array( polinis_term_link( 'essentials' ), 'Essentials', is_product_tag( 'essentials' ) ),
	array( polinis_term_link( 'hoodies', 'product_cat' ), 'Hoodies', is_product_category( 'hoodies' ) ),
	array( polinis_term_link( 't-shirts', 'product_cat' ), 'T-Shirts', is_product_category( 't-shirts' ) ),
	array( polinis_term_link( 'sweatshirts', 'product_cat' ), 'Sweatshirts', is_product_category( 'sweatshirts' ) ),
	array( polinis_term_link( 'caps', 'product_cat' ), 'Caps', is_product_category( 'caps' ) ),
	array( polinis_term_link( 'polos-jacken', 'product_cat' ), 'Polos & Jacken', is_product_category( 'polos-jacken' ) ),
);

do_action( 'woocommerce_before_main_content' );
?>
<header class="shop-head<?php echo ( $polinis_term && isset( $polinis_art[ $polinis_term->slug ] ) ) ? ' shop-head--art' : ''; ?>">
	<div class="shop-head__copy">
		<h1 class="shop-head__title"><?php echo polinis_nobr( $polinis_title ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h1>
		<div class="shop-head__desc"><?php echo wp_kses_post( $polinis_desc ); ?></div>
	</div>
	<?php if ( $polinis_term && isset( $polinis_art[ $polinis_term->slug ] ) ) : ?>
		<figure class="shop-head__art frame">
			<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/' . $polinis_art[ $polinis_term->slug ] ); ?>" alt="" width="730" height="495">
		</figure>
	<?php endif; ?>
	<nav class="chips" aria-label="<?php esc_attr_e( 'Filter', 'polinis' ); ?>">
		<?php foreach ( $polinis_chips as $polinis_chip ) : ?>
			<a class="chip" href="<?php echo esc_url( $polinis_chip[0] ); ?>"<?php echo $polinis_chip[2] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $polinis_chip[1] ); ?></a>
		<?php endforeach; ?>
	</nav>
</header>

<?php if ( woocommerce_product_loop() ) : ?>
	<div class="shop-bar">
		<?php do_action( 'woocommerce_before_shop_loop' ); ?>
	</div>
	<?php
	woocommerce_product_loop_start();
	if ( wc_get_loop_prop( 'total' ) ) {
		while ( have_posts() ) {
			the_post();
			do_action( 'woocommerce_shop_loop' );
			wc_get_template_part( 'content', 'product' );
		}
	}
	woocommerce_product_loop_end();
	do_action( 'woocommerce_after_shop_loop' );
else :
	do_action( 'woocommerce_no_products_found' );
endif;

do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
