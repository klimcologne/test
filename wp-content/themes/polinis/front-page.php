<?php
/**
 * Startseite: ein Schalter, zwei Serien.
 */
get_header();

$polinis_img     = get_template_directory_uri() . '/assets/img/';
$polinis_s01     = polinis_series_products( 's-01', 12 );
$polinis_s02     = polinis_series_products( 's-02', 12 );
$polinis_ess     = polinis_series_products( 'essentials', 8 );
$polinis_tracks  = array(
	'off' => array( 'slug' => 's-01', 'name' => 'S-01 No Signal', 'products' => $polinis_s01, 'line' => __( 'Der Schalter, das Transmission-Artwork, das Kreis-Logo.', 'polinis' ) ),
	'on'  => array( 'slug' => 's-02', 'name' => 'S-02 Light Series', 'products' => $polinis_s02, 'line' => __( 'Fadenkreuz, Wortmarke und die Light-Series-Collage.', 'polinis' ) ),
);
$polinis_cats    = array( 'hoodies' => 'Hoodies', 't-shirts' => 'T-Shirts', 'sweatshirts' => 'Sweatshirts', 'caps' => 'Caps', 'polos-jacken' => 'Polos & Jacken' );
?>
<main id="main" class="home">

	<section class="hero" aria-labelledby="hero-title">
		<div class="hero__copy">
			<h1 id="hero-title" class="hero__title">
				<span data-when="off"><?php echo esc_html( polinis_mod( 'polinis_off_title' ) ); ?></span>
				<span data-when="on"><?php echo esc_html( polinis_mod( 'polinis_on_title' ) ); ?></span>
			</h1>
			<p class="hero__line">
				<span data-when="off"><?php echo esc_html( polinis_mod( 'polinis_off_line' ) ); ?></span>
				<span data-when="on"><?php echo esc_html( polinis_mod( 'polinis_on_line' ) ); ?></span>
			</p>
			<div class="hero__actions">
				<a class="btn btn--primary" data-when="off" href="<?php echo esc_url( polinis_term_link( 's-01' ) ); ?>"><?php esc_html_e( 'S-01 shoppen', 'polinis' ); ?><?php echo polinis_icon( 'arrow' ); ?></a>
				<a class="btn btn--primary" data-when="on" href="<?php echo esc_url( polinis_term_link( 's-02' ) ); ?>"><?php esc_html_e( 'S-02 shoppen', 'polinis' ); ?><?php echo polinis_icon( 'arrow' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( polinis_shop_url() ); ?>"><?php esc_html_e( 'Alle Teile', 'polinis' ); ?></a>
			</div>
		</div>

		<div class="hero__stage">
			<figure class="hero__screen">
				<div class="frame">
				<img data-when="off" src="<?php echo esc_url( $polinis_img . 'art-s01.webp' ); ?>" width="730" height="495" alt="<?php esc_attr_e( 'S-01 Artwork: Silhouette vor einer Stadt im Gewitter, Schriftzug Polinis – Find your freedom', 'polinis' ); ?>" fetchpriority="high">
				<img data-when="on" src="<?php echo esc_url( $polinis_img . 'art-s02.webp' ); ?>" width="760" height="515" alt="<?php esc_attr_e( 'S-02 Artwork: Collage mit Kapuzenfigur, Skyline und dem Satz Create your world. Wear your story.', 'polinis' ); ?>" loading="lazy">
				</div>
				<figcaption class="hero__caption mono">
					<span data-when="off">PIL S-01 · Rückendruck Transmission</span>
					<span data-when="on">PIL S-02 · Rückendruck Light Series</span>
				</figcaption>
			</figure>

			<div class="hero__switch">
				<?php echo polinis_switch( 'hero' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>

		<p class="hero__data mono">
			<span><?php echo esc_html( polinis_mod( 'polinis_coordinates' ) ); ?></span>
			<span>MMXXVI</span>
		</p>
	</section>

	<section class="rail" aria-labelledby="rail-title">
		<?php foreach ( $polinis_tracks as $polinis_state => $polinis_track ) : ?>
			<div class="rail__set" data-when="<?php echo esc_attr( $polinis_state ); ?>">
				<div class="rail__head">
					<h2 class="rail__title" <?php echo 'off' === $polinis_state ? 'id="rail-title"' : ''; ?>><?php echo polinis_nobr( $polinis_track['name'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
					<p class="rail__line"><?php echo esc_html( $polinis_track['line'] ); ?> <span class="mono"><?php echo esc_html( sprintf( _n( '%d Teil', '%d Teile', count( $polinis_track['products'] ), 'polinis' ), count( $polinis_track['products'] ) ) ); ?></span></p>
					<a class="link-arrow" href="<?php echo esc_url( polinis_term_link( $polinis_track['slug'] ) ); ?>"><?php esc_html_e( 'Ganze Serie', 'polinis' ); ?><?php echo polinis_icon( 'arrow' ); ?></a>
				</div>
				<ul class="rail__track" tabindex="0" aria-label="<?php echo esc_attr( $polinis_track['name'] ); ?>">
					<?php foreach ( $polinis_track['products'] as $polinis_product ) : ?>
						<li class="rail__item"><?php get_template_part( 'template-parts/product-card', null, array( 'product' => $polinis_product ) ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</section>

	<section class="manifesto" aria-labelledby="manifesto-title">
		<div class="manifesto__inner">
			<h2 id="manifesto-title" class="manifesto__quote">You don’t need permission to dream. You just need to start.</h2>
			<p class="manifesto__text"><?php esc_html_e( 'Der Satz steht klein auf dem S-01-Artwork. Jede Polinis-Serie trägt ihre eigene Geschichte: Projektnummer, Jahr, Koordinaten, ein Motiv. Kein Logo-Shirt von der Stange, sondern ein Kapitel, das du anziehst.', 'polinis' ); ?></p>
			<p class="manifesto__sign mono">Break the noise · Build the future</p>
		</div>
		<figure class="manifesto__photo">
			<img src="<?php echo esc_url( $polinis_img . 'life-night.webp' ); ?>" width="1122" height="1402" alt="<?php esc_attr_e( 'Person im schwarzen Polinis Crosshair Hoodie vor Betonwand', 'polinis' ); ?>" loading="lazy">
		</figure>
	</section>

	<section class="index" aria-labelledby="index-title">
		<h2 id="index-title" class="index__title section-head__title"><?php esc_html_e( 'Nach Teil', 'polinis' ); ?></h2>
		<ul class="index__list">
			<?php
			foreach ( $polinis_cats as $polinis_slug => $polinis_label ) :
				$polinis_first = function_exists( 'wc_get_products' ) ? wc_get_products( array( 'category' => array( $polinis_slug ), 'limit' => 1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) : array();
				$polinis_thumb = $polinis_first ? wp_get_attachment_image_url( $polinis_first[0]->get_image_id(), 'woocommerce_thumbnail' ) : '';
				?>
				<li>
					<a class="index__row" href="<?php echo esc_url( polinis_term_link( $polinis_slug, 'product_cat' ) ); ?>">
						<?php if ( $polinis_thumb ) : ?>
							<img class="index__thumb" src="<?php echo esc_url( $polinis_thumb ); ?>" alt="" width="240" height="240" loading="lazy">
						<?php endif; ?>
						<span class="index__name"><?php echo esc_html( $polinis_label ); ?></span>
						<span class="index__count mono"><?php echo esc_html( polinis_cat_count( $polinis_slug ) ); ?></span>
						<?php echo polinis_icon( 'arrow' ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="flip" aria-label="<?php esc_attr_e( 'Andere Serie', 'polinis' ); ?>">
		<div class="flip__set" data-when="off">
			<img class="flip__art" src="<?php echo esc_url( $polinis_img . 'art-s02.webp' ); ?>" alt="" width="760" height="515" loading="lazy">
			<div class="flip__copy">
				<h2 class="flip__title"><?php esc_html_e( 'Licht an?', 'polinis' ); ?></h2>
				<p><?php esc_html_e( 'S-02 Light Series: gleiche Haltung, anderes Licht. Ein Klick und der ganze Shop dreht.', 'polinis' ); ?></p>
				<button class="btn btn--primary" type="button" data-signal-set="on"><?php esc_html_e( 'Auf ON schalten', 'polinis' ); ?></button>
			</div>
		</div>
		<div class="flip__set" data-when="on">
			<img class="flip__art" src="<?php echo esc_url( $polinis_img . 'art-s01.webp' ); ?>" alt="" width="730" height="495" loading="lazy">
			<div class="flip__copy">
				<h2 class="flip__title"><?php esc_html_e( 'Licht aus?', 'polinis' ); ?></h2>
				<p><?php esc_html_e( 'S-01 No Signal: der Schalter, das Gewitter, die Nacht. Ein Klick und der ganze Shop dreht.', 'polinis' ); ?></p>
				<button class="btn btn--primary" type="button" data-signal-set="off"><?php esc_html_e( 'Auf OFF schalten', 'polinis' ); ?></button>
			</div>
		</div>
	</section>

	<section class="next" aria-labelledby="next-title">
		<div class="section-head">
			<h2 id="next-title" class="section-head__title"><?php esc_html_e( 'Nächste Signale', 'polinis' ); ?></h2>
			<p class="section-head__text"><?php esc_html_e( 'Drei Serien in Arbeit. Noch nicht bestellbar: Trag dich unten ein, dann bekommst du das Signal zuerst.', 'polinis' ); ?></p>
			<a class="link-arrow" href="<?php echo esc_url( polinis_series_page_url() ); ?>"><?php esc_html_e( 'Alle Serien', 'polinis' ); ?><?php echo polinis_icon( 'arrow' ); ?></a>
		</div>
		<div class="next__list">
			<?php
			foreach ( polinis_series_concepts() as $polinis_slug => $polinis_s ) {
				polinis_concept_item( $polinis_slug, $polinis_s );
			}
			?>
		</div>
	</section>

	<?php if ( $polinis_ess ) : ?>
	<section class="essentials" aria-labelledby="ess-title">
		<div class="section-head">
			<h2 id="ess-title" class="section-head__title"><?php esc_html_e( 'Essentials', 'polinis' ); ?></h2>
			<p class="section-head__text"><?php esc_html_e( 'Kleines Logo, schwere Stoffe. Die Basis unter jedem Drop.', 'polinis' ); ?></p>
			<a class="link-arrow" href="<?php echo esc_url( polinis_term_link( 'essentials' ) ); ?>"><?php esc_html_e( 'Alle Essentials', 'polinis' ); ?><?php echo polinis_icon( 'arrow' ); ?></a>
		</div>
		<ul class="grid">
			<?php foreach ( $polinis_ess as $polinis_product ) : ?>
				<li><?php get_template_part( 'template-parts/product-card', null, array( 'product' => $polinis_product ) ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php endif; ?>

	<section class="facts" aria-label="<?php esc_attr_e( 'Gut zu wissen', 'polinis' ); ?>">
		<ul class="facts__list">
			<li><?php echo polinis_icon( 'truck' ); ?><strong><?php esc_html_e( 'Versandkostenfrei', 'polinis' ); ?></strong><span><?php esc_html_e( 'innerhalb Deutschlands', 'polinis' ); ?></span></li>
			<li><?php echo polinis_icon( 'leaf' ); ?><strong><?php esc_html_e( 'Bio-Baumwolle', 'polinis' ); ?></strong><span><?php esc_html_e( 'bei vielen Tees, Hoodies und Sweats', 'polinis' ); ?></span></li>
			<li><?php echo polinis_icon( 'ruler' ); ?><strong><?php esc_html_e( 'Unisex S bis 5XL', 'polinis' ); ?></strong><span><?php esc_html_e( 'Größentabelle an jedem Teil', 'polinis' ); ?></span></li>
			<?php if ( function_exists( 'polinis_payment_labels' ) && polinis_payment_labels() ) : ?>
				<li><?php echo polinis_icon( 'card' ); ?><strong><?php esc_html_e( 'Sicher zahlen', 'polinis' ); ?></strong><span><?php echo esc_html( implode( ' · ', polinis_payment_labels() ) ); ?></span></li>
			<?php else : ?>
				<li><?php echo polinis_icon( 'target' ); ?><strong><?php esc_html_e( 'Drei Linien', 'polinis' ); ?></strong><span><?php esc_html_e( 'S-01, S-02 und Essentials', 'polinis' ); ?></span></li>
			<?php endif; ?>
		</ul>
	</section>

</main>
<?php
get_footer();
