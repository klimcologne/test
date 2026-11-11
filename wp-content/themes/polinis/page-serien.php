<?php
/**
 * Seite „Serien“: alle Linien von Polinis, laufende und geplante.
 * WordPress nutzt dieses Template automatisch für die Seite mit der Adresse /serien/.
 */
get_header();
?>
<main id="main" class="series-page">
	<header class="shop-head">
		<div class="shop-head__copy">
			<h1 class="shop-head__title"><?php esc_html_e( 'Alle Serien', 'polinis' ); ?></h1>
			<div class="shop-head__desc">
				<p><?php esc_html_e( 'Jede Polinis-Serie ist ein eigenes Signal mit Nummer, Motiv und Satz. S-01 und S-02 sind im Shop. S-03 bis S-05 sind in Arbeit.', 'polinis' ); ?></p>
			</div>
		</div>
	</header>

	<div class="series-list">
		<?php foreach ( polinis_series() as $polinis_slug => $polinis_s ) : ?>
			<?php if ( 'live' === $polinis_s['status'] ) : ?>
				<article class="series-row" id="<?php echo esc_attr( $polinis_slug ); ?>">
					<figure class="series-row__media frame">
						<img src="<?php echo esc_url( $polinis_s['image'] ); ?>" alt="" width="760" height="515" loading="lazy">
					</figure>
					<div class="series-row__body">
						<h2 class="next__name"><span class="next__code"><?php echo esc_html( $polinis_s['code'] ); ?></span> <?php echo esc_html( $polinis_s['name'] ); ?></h2>
						<p class="next__motto"><?php echo esc_html( $polinis_s['motto'] ); ?></p>
						<p class="next__text"><?php echo esc_html( $polinis_s['text'] ); ?></p>
						<p class="series-row__meta mono"><?php echo esc_html( sprintf( _n( '%d Teil im Shop', '%d Teile im Shop', count( polinis_series_products( $polinis_slug, 50 ) ), 'polinis' ), count( polinis_series_products( $polinis_slug, 50 ) ) ) ); ?> · <?php echo 'off' === $polinis_s['world'] ? 'OFF' : 'ON'; ?></p>
						<a class="btn btn--primary" href="<?php echo esc_url( polinis_term_link( $polinis_slug ) ); ?>"><?php echo esc_html( sprintf( __( '%s shoppen', 'polinis' ), $polinis_s['code'] ) ); ?><?php echo polinis_icon( 'arrow' ); ?></a>
					</div>
				</article>
			<?php endif; ?>
		<?php endforeach; ?>

		<article class="series-row series-row--plain" id="essentials">
			<div class="series-row__body">
				<h2 class="next__name"><?php esc_html_e( 'Essentials', 'polinis' ); ?></h2>
				<p class="next__text"><?php esc_html_e( 'Kleines Logo, schwere Stoffe. Die Basis unter jedem Drop, in beiden Welten zu Hause.', 'polinis' ); ?></p>
				<a class="link-arrow" href="<?php echo esc_url( polinis_term_link( 'essentials' ) ); ?>"><?php esc_html_e( 'Alle Essentials', 'polinis' ); ?><?php echo polinis_icon( 'arrow' ); ?></a>
			</div>
		</article>
	</div>

	<section class="next next--page" aria-labelledby="next-title">
		<div class="section-head">
			<h2 id="next-title" class="section-head__title"><?php esc_html_e( 'In Arbeit', 'polinis' ); ?></h2>
			<p class="section-head__text"><?php esc_html_e( 'Entwürfe für die nächsten Drops. Noch nicht bestellbar.', 'polinis' ); ?></p>
		</div>
		<div class="next__list">
			<?php
			foreach ( polinis_series_concepts() as $polinis_slug => $polinis_s ) {
				polinis_concept_item( $polinis_slug, $polinis_s );
			}
			?>
		</div>
	</section>
</main>
<?php
get_footer();
