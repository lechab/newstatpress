<?php
/**
 * Get the version of Newstatpress with the newstatpress_api_version
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
 * API: Version
 *
 * Return the current version of newstatpress as json/html
 *
 * @param string $typ the type of result (Json/Html).
 * @return the result
 */
function newstatpress_api_version( $typ ) {
	$result_j = array(
		'version' => NEWSTATPRESS_VERSION,
	);

	// avoid to calculte HTML if not necessary.
	if ( 'JSON' === $typ ) {
		return $result_j;
	}

	$result_h = '<div>' . esc_html( $result_j[ 'version' ] ) . '</div>';
	return $result_h;
}
