<?php
/**
 * „Signal empfangen“ – einfache Drop-Benachrichtigung.
 *
 * Speichert E-Mail-Adressen mit Einwilligung in WordPress und zeigt sie unter
 * Werkzeuge → Drop-Signal (inkl. CSV-Export). Für echte Mailings später an
 * Brevo, Mailchimp o. Ä. anbinden.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function polinis_signup_form() {
	$status = isset( $_GET['signal'] ) ? sanitize_key( wp_unslash( $_GET['signal'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	ob_start();
	?>
	<form class="signup__form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="signal">
		<input type="hidden" name="action" value="polinis_signup">
		<?php wp_nonce_field( 'polinis_signup', 'polinis_nonce' ); ?>
		<div class="signup__row">
			<label class="screen-reader-text" for="signup-email"><?php esc_html_e( 'E-Mail-Adresse', 'polinis' ); ?></label>
			<input id="signup-email" type="email" name="email" required autocomplete="email" placeholder="<?php esc_attr_e( 'deine@email.de', 'polinis' ); ?>">
			<button type="submit" class="btn btn--primary"><?php esc_html_e( 'Signal holen', 'polinis' ); ?></button>
		</div>
		<label class="signup__consent">
			<input type="checkbox" name="consent" value="1" required>
			<span><?php esc_html_e( 'Ich möchte per E-Mail über neue Drops informiert werden. Abmeldung jederzeit möglich.', 'polinis' ); ?></span>
		</label>
		<p class="signup__status" role="status">
			<?php
			if ( 'ok' === $status ) {
				esc_html_e( 'Du bist drin. Beim nächsten Drop meldet sich Polinis bei dir.', 'polinis' );
			} elseif ( 'invalid' === $status ) {
				esc_html_e( 'Das sieht nicht nach einer E-Mail-Adresse aus. Bitte prüfen und nochmal senden.', 'polinis' );
			}
			?>
		</p>
	</form>
	<?php
	return ob_get_clean();
}

function polinis_handle_signup() {
	$back = wp_get_referer() ?: home_url( '/' );
	$back = remove_query_arg( 'signal', $back );
	if ( ! isset( $_POST['polinis_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['polinis_nonce'] ) ), 'polinis_signup' ) ) {
		wp_safe_redirect( add_query_arg( 'signal', 'invalid', $back ) . '#signal' );
		exit;
	}
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$consent = ! empty( $_POST['consent'] );
	if ( ! is_email( $email ) || ! $consent ) {
		wp_safe_redirect( add_query_arg( 'signal', 'invalid', $back ) . '#signal' );
		exit;
	}
	$list           = get_option( 'polinis_signups', array() );
	$list[ $email ] = array( 'time' => current_time( 'mysql' ), 'consent' => 'Drop-Benachrichtigung per E-Mail' );
	update_option( 'polinis_signups', $list, false );
	wp_safe_redirect( add_query_arg( 'signal', 'ok', $back ) . '#signal' );
	exit;
}
add_action( 'admin_post_polinis_signup', 'polinis_handle_signup' );
add_action( 'admin_post_nopriv_polinis_signup', 'polinis_handle_signup' );

add_action(
	'admin_menu',
	function () {
		add_management_page(
			__( 'Drop-Signal', 'polinis' ),
			__( 'Drop-Signal', 'polinis' ),
			'manage_options',
			'polinis-signups',
			'polinis_signups_page'
		);
	}
);

add_action(
	'admin_init',
	function () {
		if ( ! isset( $_GET['page'], $_GET['csv'] ) || 'polinis-signups' !== $_GET['page'] || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		check_admin_referer( 'polinis_csv' );
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=drop-signal.csv' );
		echo "email;datum;einwilligung\n";
		foreach ( get_option( 'polinis_signups', array() ) as $email => $row ) {
			echo str_replace( array( ';', "\n" ), ' ', $email . ';' . $row['time'] . ';' . $row['consent'] ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		exit;
	}
);

function polinis_signups_page() {
	$list = get_option( 'polinis_signups', array() );
	echo '<div class="wrap"><h1>' . esc_html__( 'Drop-Signal', 'polinis' ) . '</h1>';
	printf( '<p>%s</p>', esc_html( sprintf( _n( '%d Anmeldung', '%d Anmeldungen', count( $list ), 'polinis' ), count( $list ) ) ) );
	printf( '<p><a class="button" href="%s">CSV herunterladen</a></p>', esc_url( wp_nonce_url( admin_url( 'tools.php?page=polinis-signups&csv=1' ), 'polinis_csv' ) ) );
	echo '<table class="widefat striped"><thead><tr><th>E-Mail</th><th>Datum</th></tr></thead><tbody>';
	foreach ( array_reverse( $list, true ) as $email => $row ) {
		printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html( $email ), esc_html( $row['time'] ) );
	}
	echo '</tbody></table></div>';
}
