<?php
/**
 * External API
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

require 'nsp-api-version.php';
require 'nsp-api-wpversion.php';
require 'nsp-api-dashboard.php';
require 'nsp-api-overview.php';

/**
 * Body function of external API Nonce
 */
function nsp_external_api_ajax_n() {
	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier.
	if ( ! ( isset( $_POST['postCommentNonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['postCommentNonce'] ) ), 'newstatpress-nsp_external-nonce' ) ) ) {
		die( 'Busted!' );
	}

	nsp_external_api_ajax();
}

/**
 * Body function of external API
 */
function nsp_external_api_ajax() {
	global $_newstatpress;
	global $wpdb;
	header( 'HTTP/1.0 200 Ok' );
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
		header( 'HTTP/1.0 403 Forbidden' );
		die( 'Invalid use of API' );
	}

	if ( get_option( 'newstatpress_externalapi' ) !== 'checked' ) {
		header( 'HTTP/1.0 403 Forbidden' );
		die( 'API not activated' );
	}

	// read key from WordPress option.
	$api_key = get_option( 'newstatpress_apikey' );
	$api_key = md5( gmdate( 'm-d-y H i' ) . $api_key );

	// get the parameter from URL.
	if ( isset( $_REQUEST['VAR'] ) ) {
		$var = substr( preg_replace( '/[^a-z]+/', '', sanitize_text_field( wp_unslash( $_REQUEST['VAR'] ) ) ), 0, 9 );
	}

	if ( isset( $_REQUEST['KEY'] ) ) {
		$key = preg_replace( '/[^a-z0-9]+/', '', sanitize_text_field( wp_unslash( $_REQUEST['KEY'] ) ) );     // key readed is md5(date('m-d-y H i').'Key').
	}

	if ( isset( $_REQUEST['PAR'] ) ) {
		$par = intval( $_REQUEST['PAR'] ); // can be empty.
	}

	if ( isset( $_REQUEST['TYP'] ) ) {
		$typ = substr( preg_replace( '/[^A-Z]+/', '', sanitize_text_field( wp_unslash( $_REQUEST['TYP'] ) ) ), 0, 4 ); // can be empty.
	}

	if ( null === $typ ) {
		$typ = 'JSON';
	}

	if ( 'JSON' !== $typ && 'HTML' !== $typ ) {
		header( 'HTTP/1.0 403 Forbidden' );
		die( 'Return type not available' );
	}

	if ( ! preg_match( '/^[a-f0-9]{32}$/', $key ) ) {
		header( 'HTTP/1.0 403 Forbidden' );
		die( 'Invalid key' );
	}

	if ( null === $var && null === $key ) {
		header( 'HTTP/1.0 403 Forbidden' );
		die( 'API needs parameters' );
	}

	// test if can use API.
	if ( $key !== $api_key ) {
		header( 'HTTP/1.0 403 Forbidden' );
		die( 'Not authorized API access' );
	}

	switch ( $var ) {
		case 'version':
			$result = nsp_api_version( $typ );
			break;
		case 'wpversion':
			$result = nsp_api_wp_version( $typ );
			break;
		case 'dashboard':
			$result = nsp_api_dashboard( $typ );
			break;
		case 'overview':
			$result = nsp_api_overview( $typ, $par );
			break;
		default:
			header( 'HTTP/1.0 403 Forbidden' );
			die( 'Not recognized API.' );
	}

	if ( 'JSON' === $typ ) {
		// response output.
		header( 'Content-Type: application/json' );
		// gives the complete output according to $resultJ.
		echo wp_json_encode(
			$result
		);
	}

	if ( 'HTML' === $typ ) {
		// response output.
		header( 'Content-Type: application/html' );
		// gives the complete output according to $resultH.
		echo wp_kses(
			$result,
			array(
				'table' => array(
					'class' => array(),
				),
				'tbody' => array(
					'class' => array(),
				),
				'tr'    => array(
					'class' => array(),
				),
				'div'   => array(
					'class' => array(),
					'style' => array(),
					'title' => array(),
				),
				'td'    => array(
					'class'  => array(),
					'width'  => array(),
					'valign' => array(),
				),
				'th'    => array(
					'scope'   => array(),
					'colspan' => array(),
				),
				'thead' => array(),
				'span'  => array(),
				'br'    => array(),
				'p'     => array(),
				'i'     => array(),
			)
		);
	}
	wp_die();
}

