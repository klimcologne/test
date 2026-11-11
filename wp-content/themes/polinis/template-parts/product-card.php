<?php
/**
 * Produktkarte: Vorderseite, beim Hovern der Rückendruck.
 *
 * @var array $args { product: WC_Product }
 */

$product = $args['product'] ?? null;
if ( ! $product instanceof WC_Product ) {
	return;
}

$link     = $product->get_permalink();
$serie    = polinis_product_serie( $product );
$hover_id = (int) get_post_meta( $product->get_id(), '_polinis_hover_image', true );
$colors   = polinis_product_colors( $product );
$sizes    = polinis_product_sizes_label( $product );
$labels   = array( 's-01' => 'S-01', 's-02' => 'S-02', 'essentials' => 'ESS' );
?>
<article class="card<?php echo $hover_id ? ' card--has-alt' : ''; ?>">
	<a class="card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, array( 'class' => 'card__img', 'loading' => 'lazy', 'sizes' => '(max-width: 700px) 70vw, 25vw' ) );
		if ( $hover_id ) {
			echo wp_get_attachment_image( $hover_id, 'woocommerce_thumbnail', false, array( 'class' => 'card__img card__img--alt', 'loading' => 'lazy', 'alt' => '', 'sizes' => '(max-width: 700px) 70vw, 25vw' ) );
		}
		?>
		<?php if ( $serie ) : ?>
			<span class="card__tag mono"><?php echo esc_html( $labels[ $serie->slug ] ?? $serie->name ); ?></span>
		<?php endif; ?>
	</a>
	<div class="card__body">
		<h3 class="card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo polinis_nobr( $product->get_name() ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></h3>
		<p class="card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
		<?php if ( $colors || $sizes ) : ?>
			<p class="card__meta">
				<?php if ( $colors ) : ?>
					<span class="dots" aria-label="<?php echo esc_attr( sprintf( _n( '%d Farbe', '%d Farben', count( $colors ), 'polinis' ), count( $colors ) ) ); ?>">
						<?php foreach ( array_slice( $colors, 0, 5 ) as $c ) : ?>
							<span class="dot" style="--dot: <?php echo esc_attr( polinis_color_hex( $c ) ); ?>" title="<?php echo esc_attr( $c ); ?>"></span>
						<?php endforeach; ?>
						<?php if ( count( $colors ) > 5 ) : ?>
							<span class="dots__more">+<?php echo esc_html( count( $colors ) - 5 ); ?></span>
						<?php endif; ?>
					</span>
				<?php endif; ?>
				<?php if ( $sizes ) : ?>
					<span class="mono card__sizes"><?php echo esc_html( $sizes ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</div>
</article>
