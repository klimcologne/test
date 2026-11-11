<?php
/**
 * Produkt im Shop-Raster.
 *
 * @package Polinis
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) ); ?>
</li>
