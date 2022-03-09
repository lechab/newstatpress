<?php
/**
 * Dashboard functions
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
 * Show statistics in dashboard
 *******************************/
function nsp_build_dashboard_widget() {
	global $newstatpress_dir;
	global $_newstatpress;

	$api_key          = get_option( 'newstatpress_apikey' );
	$newstatpress_url = nsp_plugin_url();

	wp_enqueue_script( 'wp_ajax_nsp_js_dashbord', plugins_url( './js/nsp_dashboard.js', __FILE__ ), array( 'jquery' ), $_newstatpress['version'], true );
	wp_localize_script(
		'wp_ajax_nsp_js_dashbord',
		'nsp_externalAjax_dashboard',
		array(
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'Key'              => md5( gmdate( 'm-d-y H i' ) . $api_key ),
			'postCommentNonce' => wp_create_nonce( 'newstatpress-nsp_external-nonce' ),
		)
	);

	echo '<div id="nsp_result-dashboard"><img id="nsp_loader-dashboard" src="' . esc_url( $newstatpress_url ) . '/images/ajax-loader.gif"></div>';
	?>
  <ul class='nsp_dashboard'>
	<li>
	  <a href='admin.php?page=nsp-details'><?php esc_html_e( 'Details', 'newstatpress' ); ?></a> |
	</li>
	<li>
	  <a href='admin.php?page=nsp-visits'><?php esc_html_e( 'Visits', 'newstatpress' ); ?></a> |
	</li>
	<li>
	  <a href='admin.php?page=nsp-options'><?php esc_html_e( 'Options', 'newstatpress' ); ?>
	  </li>
  </ul>
	<?php
}

/**
 * Create the function use in the action hook.
 */
function nsp_add_dashboard_widget() {

	global $wp_meta_boxes;
	$title = __( 'NewStatPress Overview', 'newstatpress' );

	// Add the dashboard widget if user option is 'yes'.
	if ( get_option( 'newstatpress_dashboard' ) === 'checked' ) {
		wp_add_dashboard_widget( 'dashboard_NewsStatPress_overview', $title, 'nsp_build_dashboard_widget' );
	} else {
		unset( $wp_meta_boxes['dashboard']['side']['core']['wp_dashboard_setup'] );
	}
}
?>
