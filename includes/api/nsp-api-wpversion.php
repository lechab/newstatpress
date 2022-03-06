<?php
/**
 * Get the version of WordPress with the nsp_api_wp_version
 *
 * @package NewStatpress
 */

// Make sure plugin remains secure if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	if ( ! headers_sent() ) {
		header( 'HTTP/1.1 403 Forbidden' );
	}
	die( esc_html( __( 'ERROR: This plugin requires WordPress and will not function if called directly.', 'newstatpress' ) ) );
}

/**
 * API: WP Version
 *
 * Return the current version of WordPress as json/html.
 *
 * @param string $typ the type of result (Json/Html).
 * @return the result
 */
function nsp_api_wp_version( $typ ) {
	global $_newstatpress;

	$result_j = array(
		'wpversion' => get_bloginfo( 'version' ),
	);

	if ( 'JSON' === $typ ) {
		return $result_j;         // avoid to calculte HTML if not necessary.
	}

	$result_h = '<div>' . esc_html( $result_j[ $var ] ) . '</div>';
	return $result_h;
}
