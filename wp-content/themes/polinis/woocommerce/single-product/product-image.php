<?php
/**
 * Produktbilder als ruhige Strecke: auf dem Desktop untereinander,
 * auf dem Handy zum Wischen. Das erste Bild tauscht WooCommerce bei der Farbwahl.
 *
 * @package Polinis
 * @version 9.7.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$polinis_ids = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
$polinis_n   = count( $polinis_ids );
?>
<div class="woocommerce-product-gallery pdp-gallery" data-columns="1" style="opacity: 1">
	<div class="woocommerce-product-gallery__wrapper pdp-gallery__track" tabindex="0" aria-label="<?php esc_attr_e( 'Produktbilder', 'polinis' ); ?>">
		<?php if ( ! $polinis_ids ) : ?>
			<div class="woocommerce-product-gallery__image--placeholder pdp-gallery__item">
				<?php echo wc_placeholder_img( 'woocommerce_single' ); // phpcs:ignore ?>
			</div>
		<?php endif; ?>
		<?php foreach ( $polinis_ids as $polinis_i => $polinis_id ) : ?>
			<?php $polinis_full = wp_get_attachment_image_src( $polinis_id, 'full' ); ?>
			<figure class="pdp-gallery__item<?php echo 0 === $polinis_i ? ' woocommerce-product-gallery__image' : ''; ?>" data-thumb="<?php echo esc_url( wp_get_attachment_image_url( $polinis_id, 'woocommerce_gallery_thumbnail' ) ); ?>">
				<a href="<?php echo esc_url( $polinis_full ? $polinis_full[0] : '' ); ?>" target="_blank" rel="noopener">
					<?php
					echo wp_get_attachment_image(
						$polinis_id,
						'woocommerce_single',
						false,
						array(
							'class'         => 0 === $polinis_i ? 'wp-post-image' : '',
							'loading'       => $polinis_i < 2 ? 'eager' : 'lazy',
							'fetchpriority' => 0 === $polinis_i ? 'high' : 'auto',
							'sizes'         => '(max-width: 900px) 88vw, 50vw',
							'title'         => '',
						)
					);
					?>
				</a>
				<figcaption class="pdp-gallery__count mono"><?php echo esc_html( sprintf( '%02d / %02d', $polinis_i + 1, $polinis_n ) ); ?></figcaption>
			</figure>
		<?php endforeach; ?>
	</div>
</div>
