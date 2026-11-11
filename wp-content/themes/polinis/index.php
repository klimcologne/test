<?php
/**
 * Standard-Template für Seiten, Beiträge und Suchergebnisse.
 */
get_header();
?>
<main id="main" class="site-page">
	<div class="site-page__inner">
		<?php if ( is_search() ) : ?>
			<h1 class="site-page__title"><?php printf( esc_html__( 'Suche: %s', 'polinis' ), esc_html( get_search_query() ) ); ?></h1>
		<?php elseif ( is_archive() ) : ?>
			<h1 class="site-page__title"><?php the_archive_title(); ?></h1>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'entry' ); ?>>
					<?php if ( is_singular() ) : ?>
						<h1 class="site-page__title"><?php the_title(); ?></h1>
						<div class="entry__content"><?php the_content(); ?></div>
					<?php else : ?>
						<h2 class="entry__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<p><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
				</article>
			<?php endwhile; ?>
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<h1 class="site-page__title"><?php esc_html_e( 'Kein Signal.', 'polinis' ); ?></h1>
			<p><?php esc_html_e( 'Hier ist nichts. Versuch es im Shop.', 'polinis' ); ?></p>
			<p><a class="btn btn--primary" href="<?php echo esc_url( polinis_shop_url() ); ?>"><?php esc_html_e( 'Zum Shop', 'polinis' ); ?></a></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
