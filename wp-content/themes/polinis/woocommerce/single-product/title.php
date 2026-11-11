<?php
/**
 * Produkttitel – Seriennummern wie „S-01“ brechen nicht um.
 *
 * @package Polinis
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

echo '<h1 class="product_title entry-title">' . polinis_nobr( get_the_title() ) . '</h1>'; // phpcs:ignore WordPress.Security.EscapeOutput
