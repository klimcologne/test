<?php
/**
 * Kopfbereich.
 */
$polinis_ticker = array_filter( array_map( 'trim', explode( '—', polinis_mod( 'polinis_ticker' ) ) ) );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Zum Inhalt springen', 'polinis' ); ?></a>

<?php if ( $polinis_ticker ) : ?>
<div class="ticker" role="region" aria-label="<?php esc_attr_e( 'Hinweise', 'polinis' ); ?>">
	<div class="ticker__track">
		<?php for ( $polinis_i = 0; $polinis_i < 2; $polinis_i++ ) : ?>
			<ul class="ticker__group"<?php echo $polinis_i ? ' aria-hidden="true"' : ''; ?>>
				<?php foreach ( $polinis_ticker as $polinis_item ) : ?>
					<li><?php echo esc_html( $polinis_item ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endfor; ?>
	</div>
</div>
<?php endif; ?>

<header class="site-header" data-header>
	<div class="site-header__inner">
		<button class="icon-btn menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-menu-toggle>
			<?php echo polinis_icon( 'menu' ); ?>
			<?php echo polinis_icon( 'close' ); ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Menü', 'polinis' ); ?></span>
		</button>

		<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php echo polinis_logo(); ?>
		</a>

		<nav id="site-nav" class="nav" aria-label="<?php esc_attr_e( 'Hauptmenü', 'polinis' ); ?>" data-nav>
			<?php polinis_primary_nav(); ?>
		</nav>

		<div class="site-header__tools">
			<?php echo polinis_switch( 'mini' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<button class="icon-btn" type="button" aria-expanded="false" aria-controls="search-panel" data-search-toggle>
				<?php echo polinis_icon( 'search' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'Suche', 'polinis' ); ?></span>
			</button>
			<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
				<a class="icon-btn icon-btn--hide-sm" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
					<?php echo polinis_icon( 'user' ); ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Mein Konto', 'polinis' ); ?></span>
				</a>
				<a class="icon-btn cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>" data-drawer-open aria-controls="cart-drawer">
					<?php echo polinis_icon( 'bag' ); ?>
					<?php echo polinis_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Warenkorb', 'polinis' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<div class="search-panel" id="search-panel" hidden data-search-panel>
		<form role="search" method="get" class="search-panel__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="search-q"><?php esc_html_e( 'Produkte durchsuchen', 'polinis' ); ?></label>
			<?php echo polinis_icon( 'search' ); ?>
			<input id="search-q" type="search" name="s" placeholder="<?php esc_attr_e( 'Hoodie, Tee, S-01 …', 'polinis' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
			<input type="hidden" name="post_type" value="product">
			<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Suchen', 'polinis' ); ?></button>
		</form>
	</div>
</header>
