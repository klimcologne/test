<?php
/**
 * Alle Polinis-Serien an einer Stelle: laufende und geplante.
 * Geplante Serien haben noch keine Produkte; sie erscheinen als Konzept.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function polinis_series() {
	$img = get_template_directory_uri() . '/assets/img/';
	return array(
		's-01'       => array(
			'code'   => 'S-01',
			'name'   => 'No Signal',
			'status' => 'live',
			'motto'  => 'The world is loud. Stay true.',
			'text'   => __( 'Der ON/OFF-Schalter, das Transmission-Motiv, das Kreis-Logo. Die dunkle Hälfte des Labels.', 'polinis' ),
			'image'  => '',
			'world'  => 'off',
		),
		's-02'       => array(
			'code'   => 'S-02',
			'name'   => 'Light Series',
			'status' => 'live',
			'motto'  => 'Create your world. Wear your story.',
			'text'   => __( 'Fadenkreuz, Wortmarke und die Light-Series-Collage. Die helle Hälfte.', 'polinis' ),
			'image'  => '',
			'world'  => 'on',
		),
		's-03'       => array(
			'code'   => 'S-03',
			'name'   => 'Static',
			'status' => 'concept',
			'motto'  => 'Between the stations.',
			'text'   => __( 'Das Rauschen zwischen OFF und ON. Ein Feld aus Bildrauschen, das sich nach unten auflöst, quer der violette Abstimmbalken.', 'polinis' ),
			'basis'  => __( 'Relax Hoodie und Heavy Tee in Schwarz · Druck Weiß + Violett', 'polinis' ),
			'image'  => $img . 'series/s-03-static.webp',
		),
		's-04'       => array(
			'code'   => 'S-04',
			'name'   => 'Frequency',
			'status' => 'concept',
			'motto'  => 'Find your frequency.',
			'text'   => __( 'Das Signal kommt zurück. Eine Welle wie auf dem Oszilloskop, darunter eine Radioskala, die Nadel steht auf S-04.', 'polinis' ),
			'basis'  => __( 'Organic Crew und Hoodie in Schwarz · Druck Weiß + Violett', 'polinis' ),
			'image'  => $img . 'series/s-04-frequency.webp',
		),
		's-05'       => array(
			'code'   => 'S-05',
			'name'   => 'Homebase',
			'status' => 'concept',
			'motto'  => 'Know where you stand.',
			'text'   => __( 'Die Koordinaten vom S-01-Druck als Höhenlinien-Karte, das Fadenkreuz markiert den Punkt.', 'polinis' ),
			'basis'  => __( 'Heavy Tee in Stein und Weiß, Dad Cap mit gesticktem Fadenkreuz · Druck Schwarz + Violett', 'polinis' ),
			'image'  => $img . 'series/s-05-homebase.webp',
		),
	);
}

/**
 * Produktfoto aus einer Serie (n-tes Produkt nach Reihenfolge).
 */
function polinis_series_photo( $serie, $n = 0, $size = 'woocommerce_single' ) {
	$products = polinis_series_products( $serie, $n + 1 );
	if ( empty( $products[ $n ] ) ) {
		return '';
	}
	return (string) wp_get_attachment_image_url( $products[ $n ]->get_image_id(), $size );
}

function polinis_series_concepts() {
	return array_filter( polinis_series(), fn( $s ) => 'concept' === $s['status'] );
}

function polinis_series_page_url() {
	$page = get_page_by_path( 'serien' );
	return $page ? get_permalink( $page ) : home_url( '/serien/' );
}

/**
 * Eine geplante Serie als Bild + Text.
 */
function polinis_concept_item( $slug, $s, $heading = 'h3' ) {
	?>
	<article class="next__item" id="<?php echo esc_attr( $slug ); ?>">
		<figure class="next__media">
			<img src="<?php echo esc_url( $s['image'] ); ?>" width="1100" height="1100" alt="<?php echo esc_attr( sprintf( __( 'Entwurf %1$s %2$s auf der Rückseite', 'polinis' ), $s['code'], $s['name'] ) ); ?>" loading="lazy">
			<figcaption class="next__tag"><?php esc_html_e( 'Entwurf', 'polinis' ); ?></figcaption>
		</figure>
		<div class="next__body">
			<<?php echo tag_escape( $heading ); ?> class="next__name"><span class="next__code"><?php echo esc_html( $s['code'] ); ?></span> <?php echo esc_html( $s['name'] ); ?></<?php echo tag_escape( $heading ); ?>>
			<p class="next__motto"><?php echo esc_html( $s['motto'] ); ?></p>
			<p class="next__text"><?php echo esc_html( $s['text'] ); ?></p>
			<?php if ( ! empty( $s['basis'] ) ) : ?>
				<p class="next__basis"><?php echo esc_html( $s['basis'] ); ?></p>
			<?php endif; ?>
		</div>
	</article>
	<?php
}
