<?php
/**
 * Produktinfos als aufklappbare Abschnitte statt Reiter.
 *
 * @package Polinis
 * @version 9.8.0
 */

defined( 'ABSPATH' ) || exit;

$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( empty( $product_tabs ) ) {
	return;
}

$polinis_titles = array(
	'description'            => __( 'Beschreibung, Material & Größen', 'polinis' ),
	'additional_information' => __( 'Farben & Größen', 'polinis' ),
	'reviews'                => __( 'Bewertungen', 'polinis' ),
);
?>
<div class="pdp-details">
	<?php
	$polinis_first = true;
	foreach ( $product_tabs as $key => $product_tab ) :
		$polinis_title = $polinis_titles[ $key ] ?? wp_strip_all_tags( $product_tab['title'] );
		?>
		<details class="pdp-details__item" id="tab-<?php echo esc_attr( $key ); ?>"<?php echo $polinis_first ? ' open' : ''; ?>>
			<summary class="pdp-details__summary">
				<span><?php echo esc_html( $polinis_title ); ?></span>
				<?php echo polinis_icon( 'plus' ); // phpcs:ignore ?>
			</summary>
			<div class="pdp-details__body">
				<?php
				if ( isset( $product_tab['callback'] ) ) {
					call_user_func( $product_tab['callback'], $key, $product_tab );
				}
				?>
			</div>
		</details>
		<?php
		$polinis_first = false;
	endforeach;
	?>
	<details class="pdp-details__item">
		<summary class="pdp-details__summary">
			<span><?php esc_html_e( 'Versand & Rückgabe', 'polinis' ); ?></span>
			<?php echo polinis_icon( 'plus' ); // phpcs:ignore ?>
		</summary>
		<div class="pdp-details__body">
			<p><?php esc_html_e( 'Versandkostenfrei innerhalb Deutschlands. Jedes Teil wird für dich gedruckt, deshalb kann der Versand ein paar Werktage dauern.', 'polinis' ); ?></p>
			<p><?php esc_html_e( 'Alles zu Rückgabe und Widerruf steht im Widerrufsrecht.', 'polinis' ); ?></p>
		</div>
	</details>
	<?php do_action( 'woocommerce_product_after_tabs' ); ?>
</div>
