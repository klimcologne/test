<?php
/**
 * Fußbereich mit Drop-Signal.
 */
?>
<section class="signup" aria-labelledby="signup-title">
	<div class="signup__inner">
		<h2 id="signup-title" class="signup__title"><?php esc_html_e( 'Kein Drop ohne dich.', 'polinis' ); ?></h2>
		<p class="signup__text"><?php esc_html_e( 'Limitierte Serien sind schnell weg. Trag dich ein und du bekommst das Signal, bevor der nächste Drop online geht.', 'polinis' ); ?></p>
		<?php echo polinis_signup_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
</section>

<footer class="site-footer">
	<div class="site-footer__inner">
		<div class="site-footer__brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo polinis_logo( 'logo--footer' ); ?></a>
			<p class="site-footer__claim">Create your world.<br>Wear your story.</p>
		</div>

		<nav class="site-footer__col" aria-label="<?php esc_attr_e( 'Shop', 'polinis' ); ?>">
			<h2 class="site-footer__head"><?php esc_html_e( 'Shop', 'polinis' ); ?></h2>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'depth' => 1 ) );
			} else {
				polinis_primary_nav();
			}
			?>
		</nav>

		<div class="site-footer__col">
			<h2 class="site-footer__head"><?php esc_html_e( 'Service', 'polinis' ); ?></h2>
			<?php polinis_legal_nav(); ?>
		</div>

		<?php if ( function_exists( 'polinis_payment_labels' ) && polinis_payment_labels() ) : ?>
		<div class="site-footer__col">
			<h2 class="site-footer__head"><?php esc_html_e( 'Bezahlen', 'polinis' ); ?></h2>
			<p class="site-footer__pay"><?php echo esc_html( implode( ' · ', polinis_payment_labels() ) ); ?></p>
		</div>
		<?php endif; ?>
	</div>

	<div class="site-footer__base">
		<span class="mono"><?php echo esc_html( polinis_mod( 'polinis_coordinates' ) ); ?></span>
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Polinis®</span>
		<span class="mono">MMXXVI</span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
