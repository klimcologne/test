<?php
/**
 * Einstellungen unter Design → Customizer → Polinis.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function polinis_mod( $key ) {
	$defaults = array(
		'polinis_ticker'      => 'Versandkostenfrei in Deutschland — Break the noise — Build the future — Unisex S bis 5XL — Project S-01 limited to 999',
		'polinis_coordinates' => '50.8760° N · 6.5858° E',
		'polinis_off_title'   => 'No Signal.',
		'polinis_off_line'    => 'The world is loud. Stay true.',
		'polinis_on_title'    => 'Light Series.',
		'polinis_on_line'     => 'Create your world. Wear your story.',
		'polinis_drop_name'   => 'S-03 Static',
		'polinis_drop_date'   => '',
		'polinis_drop_link'   => '',
	);
	return get_theme_mod( $key, $defaults[ $key ] ?? '' );
}

add_action(
	'customize_register',
	function ( WP_Customize_Manager $c ) {
		$c->add_section( 'polinis', array( 'title' => 'Polinis', 'priority' => 30 ) );

		$c->add_setting( 'polinis_default_signal', array( 'default' => 'off', 'sanitize_callback' => fn( $v ) => 'on' === $v ? 'on' : 'off' ) );
		$c->add_control(
			'polinis_default_signal',
			array(
				'section' => 'polinis',
				'label'   => 'Startzustand des Schalters',
				'type'    => 'radio',
				'choices' => array( 'off' => 'OFF – S-01 No Signal (dunkel)', 'on' => 'ON – S-02 Light Series (hell)' ),
			)
		);

		$fields = array(
			'polinis_ticker'      => 'Laufband oben (Teile mit — trennen)',
			'polinis_off_title'   => 'OFF: Headline',
			'polinis_off_line'    => 'OFF: Zeile darunter',
			'polinis_on_title'    => 'ON: Headline',
			'polinis_on_line'     => 'ON: Zeile darunter',
			'polinis_coordinates' => 'Koordinaten',
			'polinis_drop_name'   => 'Nächster Drop: Name (leer = Leiste aus)',
			'polinis_drop_date'   => 'Nächster Drop: Datum + Uhrzeit, z. B. 2026-12-05 18:00 (leer = „Datum folgt“)',
			'polinis_drop_link'   => 'Nächster Drop: Link (leer = Serien-Seite)',
		);
		foreach ( $fields as $key => $label ) {
			$c->add_setting( $key, array( 'default' => polinis_mod( $key ), 'sanitize_callback' => 'sanitize_text_field' ) );
			$c->add_control( $key, array( 'section' => 'polinis', 'label' => $label, 'type' => 'text' ) );
		}
	}
);
