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
	'off' => array( 'slug' => 's-01', 'name' => 'S-01 No Signal', 'products' => $polinis_s01, 'line' => __( 'Der Schalter, das Transmission-Motiv, das Kreis-Logo.', 'polinis' ) ),
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
			<?php echo polinis_switch( 'hero' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<p class="hero__hint mono">
				<span data-when="off"><?php esc_html_e( 'Umlegen für S-02', 'polinis' ); ?></span>
				<span data-when="on"><?php esc_html_e( 'Umlegen für S-01', 'polinis' ); ?></span>
			</p>
		</div>

		<p class="hero__data mono">
			<span><?php echo esc_html( polinis_mod( 'polinis_coordinates' ) ); ?></span>
			<span>MMXXVI</span>
		</p>
	</section>

	<?php
	$polinis_drop_name = polinis_mod( 'polinis_drop_name' );
	$polinis_drop_date = polinis_mod( 'polinis_drop_date' );
	$polinis_drop_ts   = $polinis_drop_date ? strtotime( get_gmt_from_date( $polinis_drop_date ) . ' UTC' ) : 0;
	$polinis_drop_link = polinis_mod( 'polinis_drop_link' ) ?: polinis_series_page_url() . '#s-03';
	if ( $polinis_drop_name ) :
		?>
	<section class="drop" aria-label="<?php esc_attr_e( 'Nächster Drop', 'polinis' ); ?>">
		<div class="drop__inner">
			<p class="drop__what">
				<span class="drop__label"><?php esc_html_e( 'Nächster Drop', 'polinis' ); ?></span>
				<a class="drop__name" href="<?php echo esc_url( $polinis_drop_link ); ?>"><?php echo polinis_nobr( $polinis_drop_name ); // phpcs:ignore ?></a>
			</p>
			<?php if ( $polinis_drop_ts > time() ) : ?>
				<p class="drop__clock" data-countdown="<?php echo esc_attr( $polinis_drop_ts ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Drop am %s', 'polinis' ), wp_date( 'j. F Y, H:i', $polinis_drop_ts ) ) ); ?>">
					<span><b data-cd="d">00</b><small><?php esc_html_e( 'Tage', 'polinis' ); ?></small></span>
					<span><b data-cd="h">00</b><small><?php esc_html_e( 'Std', 'polinis' ); ?></small></span>
					<span><b data-cd="m">00</b><small><?php esc_html_e( 'Min', 'polinis' ); ?></small></span>
					<span><b data-cd="s">00</b><small><?php esc_html_e( 'Sek', 'polinis' ); ?></small></span>
				</p>
			<?php else : ?>
				<p class="drop__clock drop__clock--tbd"><?php esc_html_e( 'Datum folgt', 'polinis' ); ?></p>
			<?php endif; ?>
			<a class="btn btn--primary drop__cta" href="#signal"><?php esc_html_e( 'Erinnere mich', 'polinis' ); ?></a>
		</div>
	</section>
	<?php endif; ?>

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
			<p class="manifesto__text"><?php esc_html_e( 'Der Satz steht klein auf dem S-01-Druck. Jede Polinis-Serie trägt ihre eigene Geschichte: Projektnummer, Jahr, Koordinaten, ein Motiv. Kein Logo-Shirt von der Stange, sondern ein Kapitel, das du anziehst.', 'polinis' ); ?></p>
			<p class="manifesto__sign mono">Break the noise · Build the future</p>
		</div>
		<figure class="manifesto__photo">
			<img src="<?php echo esc_url( polinis_series_photo( 's-01', 0 ) ); ?>" width="1200" height="1200" alt="<?php esc_attr_e( 'No Signal Switch Hoodie, Rückseite mit ON/OFF-Schalter', 'polinis' ); ?>" loading="lazy">
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
			<div class="flip__pair">
				<img src="<?php echo esc_url( polinis_series_photo( 's-02', 0, 'woocommerce_thumbnail' ) ); ?>" alt="" width="720" height="720" loading="lazy">
				<img src="<?php echo esc_url( polinis_series_photo( 's-02', 1, 'woocommerce_thumbnail' ) ); ?>" alt="" width="720" height="720" loading="lazy">
			</div>
			<div class="flip__copy">
				<h2 class="flip__title"><?php esc_html_e( 'Licht an?', 'polinis' ); ?></h2>
				<p><?php esc_html_e( 'S-02 Light Series: Fadenkreuz, Wortmarke, helle Stoffe. Ein Klick und der ganze Shop dreht.', 'polinis' ); ?></p>
				<button class="btn btn--primary" type="button" data-signal-set="on"><?php esc_html_e( 'Auf ON schalten', 'polinis' ); ?></button>
			</div>
		</div>
		<div class="flip__set" data-when="on">
			<div class="flip__pair">
				<img src="<?php echo esc_url( polinis_series_photo( 's-01', 0, 'woocommerce_thumbnail' ) ); ?>" alt="" width="720" height="720" loading="lazy">
				<img src="<?php echo esc_url( polinis_series_photo( 's-01', 3, 'woocommerce_thumbnail' ) ); ?>" alt="" width="720" height="720" loading="lazy">
			</div>
			<div class="flip__copy">
				<h2 class="flip__title"><?php esc_html_e( 'Licht aus?', 'polinis' ); ?></h2>
				<p><?php esc_html_e( 'S-01 No Signal: der Schalter, das Kreis-Logo, die Nacht. Ein Klick und der ganze Shop dreht.', 'polinis' ); ?></p>
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
