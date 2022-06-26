<?php
/**********************************************************
 * Plugin Name: NewStatPress
 * Plugin URI: http://newstatpress.altervista.org
 * Text Domain: newstatpress
 * Description: Real time stats for your WordPress blog
 * Version: 1.4.3
 * Author: Stefano Tognon and cHab (from Daniele Lippi works)
 * Author URI: http://newstatpress.altervista.org
 *
 * @package NewStatpress
 ************************************************************/

// Make sure plugin remains secure if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	if ( ! headers_sent() ) {
		header( 'HTTP/1.1 403 Forbidden' ); }
	die( esc_html__( 'ERROR: This plugin requires WordPress and will not function if called directly.', 'newstatpress' ) );
}

$_newstatpress['version']  = '1.4.3';
$_newstatpress['feedtype'] = '';

global  $newstatpress_dir,
				$wpdb,
				$nsp_option_vars,
				$nsp_overview_screen,
				$nsp_widget_vars;

define( 'NSP_TEXTDOMAIN', 'newstatpress' );
define( 'NSP_PLUGINNAME', 'NewStatPress' );
define( 'NSP_REQUIRED_WP_VERSION', '3.5' );
define( 'NSP_NOTICENEWS', true );
define( 'NSP_TABLENAME', $wpdb->prefix . 'statpress' );
define( 'NSP_BASENAME', dirname( plugin_basename( __FILE__ ) ) );
define( 'NSP_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'NSP_SERVER_NAME', nsp_get_server_name() );
define( 'NSP_RATING_URL', 'https://wordpress.org/support/view/plugin-reviews/' . NSP_TEXTDOMAIN );
define( 'NSP_PLUGIN_URL', 'http://newstatpress.altervista.org' );
define( 'NSP_DONATE_URL', 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F5S5PF4QBWU7E' );
define( 'NSP_SUPPORT_URL', 'https://wordpress.org/support/plugin/' . NSP_TEXTDOMAIN );

// user compatibility way to get dir.
$newstatpress_dir = plugin_dir_path( __FILE__ );


$nsp_option_vars = array( // list of option variable name, with default value associated.
	'overview'                  => array(
		'name'  => 'newstatpress_el_overview',
		'value' => '10',
	),
	'top_days'                  => array(
		'name'  => 'newstatpress_el_top_days',
		'value' => '5',
	),
	'os'                        => array(
		'name'  => 'newstatpress_el_os',
		'value' => '10',
	),
	'browser'                   => array(
		'name'  => 'newstatpress_el_browser',
		'value' => '10',
	),
	'feed'                      => array(
		'name'  => 'newstatpress_el_feed',
		'value' => '5',
	),
	'searchengine'              => array(
		'name'  => 'newstatpress_el_searchengine',
		'value' => '10',
	),
	'search'                    => array(
		'name'  => 'newstatpress_el_search',
		'value' => '20',
	),
	'referrer'                  => array(
		'name'  => 'newstatpress_el_referrer',
		'value' => '10',
	),
	'languages'                 => array(
		'name'  => 'newstatpress_el_languages',
		'value' => '20',
	),
	'spiders'                   => array(
		'name'  => 'newstatpress_el_spiders',
		'value' => '10',
	),
	'pages'                     => array(
		'name'  => 'newstatpress_el_pages',
		'value' => '5',
	),
	'visitors'                  => array(
		'name'  => 'newstatpress_el_visitors',
		'value' => '5',
	),
	'daypages'                  => array(
		'name'  => 'newstatpress_el_daypages',
		'value' => '5',
	),
	'ippages'                   => array(
		'name'  => 'newstatpress_el_ippages',
		'value' => '5',
	),
	'ip_per_page_newspy'        => array(
		'name'  => 'newstatpress_ip_per_page_newspy',
		'value' => '',
	),
	'visits_per_ip_newspy'      => array(
		'name'  => 'newstatpress_visits_per_ip_newspy',
		'value' => '',
	),
	'bot_per_page_spybot'       => array(
		'name'  => 'newstatpress_bot_per_page_spybot',
		'value' => '',
	),
	'visits_per_bot_spybot'     => array(
		'name'  => 'newstatpress_visits_per_bot_spybot',
		'value' => '',
	),
	'autodelete'                => array(
		'name'  => 'newstatpress_autodelete',
		'value' => '',
	),
	'autodelete_spiders'        => array(
		'name'  => 'newstatpress_autodelete_spiders',
		'value' => '',
	),
	'daysinoverviewgraph'       => array(
		'name'  => 'newstatpress_daysinoverviewgraph',
		'value' => '',
	),
	'ignore_users'              => array(
		'name'  => 'newstatpress_ignore_users',
		'value' => '',
	),
	'ignore_ip'                 => array(
		'name'  => 'newstatpress_ignore_ip',
		'value' => '',
	),
	'ignore_permalink'          => array(
		'name'  => 'newstatpress_ignore_permalink',
		'value' => '',
	),
	'updateint'                 => array(
		'name'  => 'newstatpress_updateint',
		'value' => '',
	),
	'calculation'               => array(
		'name'  => 'newstatpress_calculation_method',
		'value' => 'classic',
	),
	'menu_cap'                  => array(
		'name'  => 'newstatpress_mincap',
		'value' => 'read',
	),
	'menuoverview_cap'          => array(
		'name'  => 'newstatpress_menuoverview_cap',
		'value' => 'switch_themes',
	),
	'menudetails_cap'           => array(
		'name'  => 'newstatpress_menudetails_cap',
		'value' => 'switch_themes',
	),
	'menuvisits_cap'            => array(
		'name'  => 'newstatpress_menuvisits_cap',
		'value' => 'switch_themes',
	),
	'menusearch_cap'            => array(
		'name'  => 'newstatpress_menusearch_cap',
		'value' => 'switch_themes',
	),
	'menuoptions_cap'           => array(
		'name'  => 'newstatpress_menuoptions_cap',
		'value' => 'edit_users',
	),
	'menutools_cap'             => array(
		'name'  => 'newstatpress_menutools_cap',
		'value' => 'switch_themes',
	),
	'menucredits_cap'           => array(
		'name'  => 'newstatpress_menucredits_cap',
		'value' => 'read',
	),
	'apikey'                    => array(
		'name'  => 'newstatpress_apikey',
		'value' => 'read',
	),
	'ip2nation'                 => array(
		'name'  => 'newstatpress_ip2nation',
		'value' => 'none',
	),
	'mail_notification'         => array(
		'name'  => 'newstatpress_mail_notification',
		'value' => 'disabled',
	),
	'mail_notification_freq'    => array(
		'name'  => 'newstatpress_mail_notification_freq',
		'value' => 'daily',
	),
	'mail_notification_address' => array(
		'name'  => 'newstatpress_mail_notification_emailaddress',
		'value' => '',
	),
	'mail_notification_time'    => array(
		'name'  => 'newstatpress_mail_notification_time',
		'value' => '',
	),
	'mail_notification_info'    => array(
		'name'  => 'newstatpress_mail_notification_info',
		'value' => '',
	),
	'mail_notification_sender'  => array(
		'name'  => 'newstatpress_mail_notification_sender',
		'value' => 'NewsStatPress',
	),
	'settings'                  => array(
		'name'  => 'newstatpress_settings',
		'value' => '',
	),
	'stats_offsets'             => array(
		'name'  => 'newstatpress_stats_offsets',
		'value' => '0',
	),
);

$nsp_widget_vars = array( // list of widget variables name, with description associated.
	array( 'visits', __( 'Today visits', 'newstatpress' ) ),
	array( 'yvisits', __( 'Yesterday visits', 'newstatpress' ) ),
	array( 'mvisits', __( 'Month visits', 'newstatpress' ) ),
	array( 'wvisits', __( 'Week visits', 'newstatpress' ) ),
	array( 'totalvisits', __( 'Total visits', 'newstatpress' ) ),
	array( 'totalpageviews', __( 'Total pages view', 'newstatpress' ) ),
	array( 'todaytotalpageviews', __( 'Total pages view today', 'newstatpress' ) ),
	array( 'thistotalvisits', __( 'This page, total visits', 'newstatpress' ) ),
	array( 'alltotalvisits', __( 'All page, total visits', 'newstatpress' ) ),
	array( 'os', __( 'Visitor Operative System', 'newstatpress' ) ),
	array( 'browser', __( 'Visitor Browser', 'newstatpress' ) ),
	array( 'ip', __( 'Visitor IP address', 'newstatpress' ) ),
	array( 'since', __( 'Date of the first hit', 'newstatpress' ) ),
	array( 'visitorsonline', __( 'Counts all online visitors', 'newstatpress' ) ),
	array( 'usersonline', __( 'Counts logged online visitors', 'newstatpress' ) ),
	array( 'monthtotalpageviews', __( 'Total page view in the month', 'newstatpress' ) ),
	array( 'toppost', __( 'The most viewed Post', 'newstatpress' ) ),
);

/**
 * Check to update of the plugin
 * Added by cHab
 *******************************/
function nsp_update_check() {

	global $_newstatpress;
	$active_version = get_option( 'newstatpress_version', '0' );
	$admin_notices  = get_option( 'newstatpress_admin_notices' );

	if ( ! empty( $admin_notices ) ) {
		add_action( 'admin_notices', 'nsp_admin_notices' );
	}

	// check version, update installation date and update notice status.
	if ( version_compare( $active_version, $_newstatpress['version'], '<' ) ) {
		if ( version_compare( $active_version, '1.1.0', '<' ) ) {
			nsp_activation( 'old' ); // for old installation > 14 days since nsp 1.1.4.
		}
		if ( NSP_NOTICENEWS ) {
			global $current_user;
			$status         = get_user_meta( $current_user->ID, 'newstatpress_nag_status', true );
			$status['news'] = false;
			update_user_meta( $current_user->ID, 'newstatpress_nag_status', $status );
		}
		update_option( 'newstatpress_version', $_newstatpress['version'] );
	}

	// check if is compatible with WP Version.
	global $wp_version;
	if ( version_compare( $wp_version, NSP_REQUIRED_WP_VERSION, '<' ) ) {
		deactivate_plugins( NSP_PLUGIN_BASENAME );
		// translators: placeholder for versions.
		$notice_text      = sprintf( __( 'Plugin %1$s deactivated. WordPress Version %2$s required. Please upgrade WordPress to the latest version.', 'newstatpress' ), NSP_PLUGINNAME, NSP_REQUIRED_WP_VERSION );
		$new_admin_notice = array(
			'style'  => 'error',
			'notice' => $notice_text,
		);
		update_option( 'newstatpress_admin_notices', $new_admin_notice );
		add_action( 'admin_notices', 'nsp_admin_notices' );
		return false;
	}

	nsp_check_nag_notices();

}
add_action( 'admin_init', 'nsp_update_check' );

/**
 * Check and Export if capability of user allow that
 * need here due to header change
 * Updated by cHab
 ***************************************************/
function nsp_check_export() {
	global $nsp_option_vars;
	global $current_user;
	wp_get_current_user();

	// phpcs:ignore -- nonce is verified inside nsp_export_now, so warning can be suppressed.
	if ( isset( $_GET['newstatpress_action'] ) && 'exportnow' === $_GET['newstatpress_action'] ) {
		$tools_capability = get_option( 'newstatpress_menutools_cap' );
		if ( ! $tools_capability ) { // default value.
			$tools_capability = $nsp_option_vars['menutools_cap']['value'];
		}
		if ( user_can( $current_user, $tools_capability ) ) {
			require 'includes/nsp-tools.php';
			nsp_export_now();
		}
	}
}
add_action( 'init', 'nsp_check_export' );

/**
 * Installation time update of the plugin
 * Added by cHab
 */
register_activation_hook( __FILE__, 'nsp_activation' );

/**
 * Activate plugin
 *
 * @param string $arg eventual argument.
 */
function nsp_activation( $arg = '' ) {
	global $nsp_option_vars;
	$nsp_settings = get_option( $nsp_option_vars['settings']['name'] );
	if ( empty( $nsp_settings['install_time'] ) ) {
		$nsp_settings['install_time'] = time();
		if ( 'old' === $arg ) {
			$nsp_settings['install_time'] = time() - 7776000;
		}
		update_option( 'newstatpress_settings', $nsp_settings );
	}
}

/**
 * Load CSS style, languages files, extra files
 * Added by cHab
 ***********************************************/
function nsp_register_plugin_styles_and_scripts() {
	global $_newstatpress;

	// CSS.
	$style_path = plugins_url( './css/style.css', __FILE__ );

	wp_register_style( 'NewStatPressStyles', $style_path, array(), $_newstatpress['version'] );
	wp_enqueue_style( 'NewStatPressStyles' );

	wp_enqueue_script( 'jquery-ui-core' );// enqueue jQuery UI Core.
	wp_enqueue_script( 'jquery-ui-tabs' );// enqueue jQuery UI Tabs.

	$style_path2 = plugins_url( './css/pikaday.css', __FILE__ );

	wp_register_style( 'pikaday', $style_path2, array(), $_newstatpress['version'] );
	wp_enqueue_style( 'pikaday' );

	wp_enqueue_style( 'NewStatPressStyles', get_stylesheet_uri(), array( 'dashicons' ), '1.0' );

	// Load the postbox script that provides the widget style boxes.
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' ); // meta box.

	// JS and jQuery.
	$scripts = array(
		'moment'         => plugins_url( './js/moment.min.js', __FILE__ ),
		'pikaday'        => plugins_url( './js/pikaday.js', __FILE__ ),
		'NewStatPressJs' => plugins_url( './js/nsp_general.js', __FILE__ ),
	);
	foreach ( $scripts as $key => $sc ) {
		wp_register_script( $key, $sc, array(), $_newstatpress['version'], true );

		if ( 'NewStatPressJs' === $key ) {
			wp_localize_script(
				'NewStatPressJs',
				'ExtData',
				array(
					'Credit'    => plugins_url( './includes/json/credit.json', __FILE__ ),
					'Lang'      => plugins_url( './includes/json/lang.json', __FILE__ ),
					'Resources' => plugins_url( './includes/json/ressources.json', __FILE__ ),
					'Donation'  => plugins_url( './includes/json/donation.json', __FILE__ ),
					'Domain'    => plugins_url( './images/domain', __FILE__ ),
				)
			);
		}

		wp_enqueue_script( $key );
	}

}
add_action( 'admin_enqueue_scripts', 'nsp_register_plugin_styles_and_scripts' );

/**
 * Load text domain
 */
function nsp_load_textdomain() {
	load_plugin_textdomain( 'newstatpress', false, NSP_BASENAME . '/langs' );
}
add_action( 'plugins_loaded', 'nsp_load_textdomain' );

if ( is_admin() ) { // load dashboard and extra functions.
	require 'includes/nsp-functions-extra.php';
	require 'includes/nsp-dashboard.php';

	add_action( 'wp_dashboard_setup', 'nsp_add_dashboard_widget' );
}
	require 'includes/api/variables.php';
	require 'includes/api/external.php';
	require 'includes/nsp-core.php';

	// register actions for ajax variables API.
	add_action( 'wp_ajax_nsp_variables', 'nsp_variables_ajax' );
	add_action( 'wp_ajax_nopriv_nsp_variables', 'nsp_variables_ajax' ); // need this to serve non logged in users.

	// register actions for ajax external API.
	add_action( 'wp_ajax_nsp_external', 'nsp_external_api_ajax_n' );
	add_action( 'wp_ajax_nopriv_nsp_external', 'nsp_external_api_ajax' ); // need this to serve non logged in users.


/*************************************
 * Add pages for NewStatPress plugin *
 *************************************/
function nsp_build_plugin_menu() {

	global $nsp_option_vars;
	global $current_user;
	global $nsp_overview_screen;
	wp_get_current_user();

	// Fix capability if it's not defined.
	$capability = $nsp_option_vars['menu_cap']['value'];

	$overview_capability = get_option( 'newstatpress_menuoverview_cap' );
	if ( ! $overview_capability ) { // default value.
		$overview_capability = $nsp_option_vars['menuoverview_cap']['value'];
	}

	$details_capability = get_option( 'newstatpress_menudetails_cap' );
	if ( ! $details_capability ) { // default value.
		$details_capability = $nsp_option_vars['menudetails_cap']['value'];
	}

	$visits_capability = get_option( 'newstatpress_menuvisits_cap' );
	if ( ! $visits_capability ) { // default value.
		$visits_capability = $nsp_option_vars['menuvisits_cap']['value'];
	}

	$search_capability = get_option( 'newstatpress_menusearch_cap' );
	if ( ! $search_capability ) { // default value.
		$search_capability = $nsp_option_vars['menusearch_cap']['value'];
	}

	$tools_capability = get_option( 'newstatpress_menutools_cap' );
	if ( ! $tools_capability ) { // default value.
		$tools_capability = $nsp_option_vars['menutools_cap']['value'];
	}

	$options_capability = get_option( 'newstatpress_menuoptions_cap' );
	if ( ! $options_capability ) { // default value.
		$options_capability = $nsp_option_vars['menuoptions_cap']['value'];
	}

	$credits_capability = $nsp_option_vars['menucredits_cap']['value'];

	// Display menu with personalized capabilities if user IS NOT "subscriber".
	if ( user_can( $current_user, 'edit_posts' ) ) {
		add_menu_page( 'NewStatPres', 'NewStatPress', $capability, 'nsp-main', 'nsp_newstatpress_main_c', plugins_url( 'newstatpress/images/stat.png', NSP_BASENAME ) );
		$nsp_overview_screen = add_submenu_page( 'nsp-main', __( 'Overview', 'newstatpress' ), __( 'Overview', 'newstatpress' ), $overview_capability, 'nsp-main', 'nsp_newstatpress_main_c' );
		add_submenu_page( 'nsp-main', __( 'Details', 'newstatpress' ), __( 'Details', 'newstatpress' ), $details_capability, 'nsp-details', 'nsp_display_details_c' );
		add_submenu_page( 'nsp-main', __( 'Visits', 'newstatpress' ), __( 'Visits', 'newstatpress' ), $visits_capability, 'nsp-visits', 'nsp_display_visits_page_c' );
		add_submenu_page( 'nsp-main', __( 'Search', 'newstatpress' ), __( 'Search', 'newstatpress' ), $search_capability, 'nsp-search', 'nsp_database_search_c' );
		add_submenu_page( 'nsp-main', __( 'Tools', 'newstatpress' ), __( 'Tools', 'newstatpress' ), $tools_capability, 'nsp-tools', 'nsp_display_tools_page_c' );
		add_submenu_page( 'nsp-main', __( 'Options', 'newstatpress' ), __( 'Options', 'newstatpress' ), $options_capability, 'nsp-options', 'nsp_options_c' );
		add_submenu_page( 'nsp-main', __( 'Credits', 'newstatpress' ), __( 'Credits', 'newstatpress' ), $credits_capability, 'nsp-credits', 'nsp_display_credits_page_c' );

		// Add action to load the meta boxes to the overview page.
		add_action( 'load-' . $nsp_overview_screen, 'nsp_statistics_load_overview_page' );
		add_action( 'admin_footer-' . $nsp_overview_screen, 'wptuts_print_script_in_footer' );
	}
}
add_action( 'admin_menu', 'nsp_build_plugin_menu' );

/**
 * Prints script in footer to 'initialises' the meta boxes
 */
function wptuts_print_script_in_footer() {
	?>
	<script>jQuery(document).ready(function(){ postboxes.add_postbox_toggles(pagenow);jQuery('.postbox h3').prepend('<a class="togbox">+</a> '); });</script>
	<?php
}


/**
 *  Load overview page
 */
function nsp_statistics_load_overview_page() {
	global $nsp_overview_screen;
	add_meta_box( 'nsp_lasthits_postbox', __( 'Last hits', 'newstatpress' ), 'nsp_generate_overview_lasthits', $nsp_overview_screen, 'normal', null, array( 'widget' => 'lasthits' ) );
	add_meta_box( 'nsp_lastsearchterms_postbox', __( 'Last search terms', 'newstatpress' ), 'nsp_generate_overview_lastsearchterms', $nsp_overview_screen, 'normal', null, array( 'widget' => 'lastsearchterms' ) );
	add_meta_box( 'nsp_lastreferrers_postbox', __( 'Last referrers', 'newstatpress' ), 'nsp_generate_overview_lastreferrers', $nsp_overview_screen, 'normal', null, array( 'widget' => 'lastreferrers' ) );
	add_meta_box( 'nsp_agents_postbox', __( 'Last agents', 'newstatpress' ), 'nsp_generate_overview_agents', $nsp_overview_screen, 'normal', null, array( 'widget' => 'agents' ) );
	add_meta_box( 'nsp_pages_postbox', __( 'Last pages', 'newstatpress' ), 'nsp_generate_overview_pages', $nsp_overview_screen, 'normal', null, array( 'widget' => 'pages' ) );
	add_meta_box( 'nsp_spiders_postbox', __( 'Last spiders', 'newstatpress' ), 'nsp_generate_overview_spiders', $nsp_overview_screen, 'normal', null, array( 'widget' => 'spiders' ) );
}

/**
 * Newstatpress main
 */
function nsp_newstatpress_main_c() {
	require 'includes/nsp-overview.php';
	nsp_new_stat_press_main();
}

/**
 * Display details
 */
function nsp_display_details_c() {
	require 'includes/nsp-details.php';
	nsp_display_details();
}

/**
 * Display credits page
 */
function nsp_display_credits_page_c() {
	require 'includes/nsp-credits.php';
	nsp_display_credits_page();
}

/**
 * Options
 */
function nsp_options_c() {
	require 'includes/nsp-options.php';
	nsp_options();
}

/**
 * Display tool page
 */
function nsp_display_tools_page_c() {
	require 'includes/nsp-tools.php';
	nsp_display_tools_page();
}

/**
 * Display visits page
 */
function nsp_display_visits_page_c() {
	require 'includes/nsp-visits.php';
	nsp_display_visits_page();
}

/**
 * Database search
 */
function nsp_database_search_c() {
	require 'includes/nsp-search.php';
	nsp_database_search();
}


/**
 * Get the url of the plugin
 *
 * @return the url of the plugin.
 ********************************/
function nsp_plugin_url() {
	// use only modern way to get it.
	return plugin_dir_url( __FILE__ );
}

/**
 * Get server name
 *
 * @return the server name.
 */
function nsp_get_server_name() {
	$server_name = '';
	if ( ! empty( $_SERVER['HTTP_HOST'] ) ) {
		$server_name = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
	} elseif ( ! empty( $_newstatpress_env['HTTP_HOST'] ) ) {
		$server_name = $_newstatpress_env['HTTP_HOST'];
	} elseif ( ! empty( $_SERVER['SERVER_NAME'] ) ) {
			$server_name = sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) );
	} elseif ( ! empty( $_newstatpress_env['SERVER_NAME'] ) ) {
			$server_name = $_newstatpress_env['SERVER_NAME']; }
			return esc_html( nsp_case_trans( 'lower', $server_name ) );
}

/** TODO rsfb_strlen
 * Convert case using multibyte version if available, if not, use defaults
 *
 * @param string $type type tp apply.
 * @param string $string string to modify.
 * @return modified string.
 ***/
function nsp_case_trans( $type, $string ) {

	switch ( $type ) {
		case 'upper':
			return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $string, 'UTF-8' ) : strtoupper( $string );
		case 'lower':
			return function_exists( 'mb_strtolower' ) ? mb_strtolower( $string, 'UTF-8' ) : strtolower( $string );
		case 'ucfirst':
			if ( function_exists( 'mb_strtoupper' ) && function_exists( 'mb_substr' ) ) {
				$strtmp = mb_strtoupper( mb_substr( $string, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $string, 1, null, 'UTF-8' );
				/* Added workaround for strange PHP bug in mb_substr() on some servers */
				return rsfb_strlen( $string ) === rsfb_strlen( $strtmp ) ? $strtmp : ucfirst( $string );
			} else {
				return ucfirst( $string );
			}
		case 'ucwords':
			return function_exists( 'mb_convert_case' ) ? mb_convert_case( $string, MB_CASE_TITLE, 'UTF-8' ) : ucwords( $string );
		/***
		 * Note differences in results between ucwords() and this.
		 * ucwords() will capitalize first characters without altering other characters, whereas this will lowercase everything, but capitalize the first character of each word.
		 * This works better for our purposes, but be aware of differences.
		 */
		default:
			return $string;
	}
}


/**
 * Calculate offset_time in second to add to epoch format
 * added by cHab
 *
 * @param int $t from time.
 * @param int $tu to time.
 * @return int $offset_time.
 ***********************************************************/
function nsp_calculation_offset_time( $t, $tu ) {

	list($current_hour, $current_minute)        = explode( ':', gmdate( 'H:i', $t ) );
	list($publishing_hour, $publishing_minutes) = explode( ':', $tu );

	if ( $current_hour > $publishing_hour ) {
		$plus_hour = 24 - $current_hour + $publishing_hour;
	} else {
		$plus_hour = $publishing_hour - $current_hour;
	}

	if ( $current_minute > $publishing_minutes ) {
		$plus_minute = 60 - $current_minute + $publishing_minutes;
		if ( 0 == $plus_hour ) {
			$plus_hour = 23;
		} else {
			--$plus_hour;
		}
	} else {
		$plus_minute = $publishing_minutes - $current_minute;
	}

	$offset_time = $plus_hour * 60 * 60 + $plus_minute * 60;
	return $offset_time;
}

/**
 * Parameters for newstatpress email notification
 * added by cHab
 *
 * @param string $content_type not used.
 * @retrun string the type of content.
 ***************************************************/
function nsp_set_mail_content_type( $content_type ) {
	return 'text/html';
}

/**
 * Send an email notification with the overview statistics
 * added by cHab
 *
 * @param string $arg type of mail ('' or 'test').
 * @return string $email_confirmation.
 *************************************/
function nsp_stat_by_email( $arg = '' ) {
	global $nsp_option_vars, $support_pluginpage, $author_linkpage;
	$date = gmdate( 'm/d/Y h:i:s a', time() );

	add_filter( 'wp_mail_content_type', 'nsp_set_mail_content_type' );

	$name   = $nsp_option_vars['mail_notification']['name'];
	$status = get_option( $name );
	$name   = $nsp_option_vars['mail_notification_freq']['name'];
	$freq   = get_option( $name );

	$userna = esc_html( get_option( 'newstatpress_mail_notification_info' ) );

	$blog_title = esc_html( get_bloginfo( 'name' ) );
	// translators: placeholder for title.
	$subject = sprintf( __( '[%s] Visits statistics', 'newstatpress' ), $blog_title );
	if ( 'test' === $arg ) {
		// translators: placeholder for title.
		$subject = sprintf( __( '[%s] Visits statistics : test of email address', 'newstatpress' ), $blog_title );
	}

	require_once 'includes/api/nsp-api-dashboard.php';
	$result_h = nsp_api_dashboard( 'HTML' );

	$name          = $nsp_option_vars['mail_notification_address']['name'];
	$email_address = sanitize_email( get_option( $name ) );

	$name   = $nsp_option_vars['mail_notification_sender']['name'];
	$sender = esc_html( get_option( $name ) );

	if ( '' === $sender ) {
		$sender = esc_html( $nsp_option_vars['mail_notification_sender']['value'] );
	}

	$support_pluginpage = "<a href='" . NSP_SUPPORT_URL . "' target='_blank'>" . __( 'support page', 'newstatpress' ) . '</a>';
	$author_linkpage    = "<a href='" . NSP_PLUGIN_URL . "/?page_id=2' target='_blank'>" . __( 'the author', 'newstatpress' ) . '</a>';

	$credits_introduction = __( 'If you have found this plugin useful and you like it, thank you to take a moment to rate it.', 'newstatpress' );
	// translators: placeholders for link.
	$credits_introduction .= ' ' . sprintf( __( 'You can help to the plugin development by reporting bugs on the %1$s or by adding/updating translation by contacting directly %2$s.', 'newstatpress' ), $support_pluginpage, $author_linkpage );
	$credits_introduction .= '<br />';
	$credits_introduction .= __( 'NewStatPress is provided for free and is maintained only on free time, you can also consider a donation to support further work, directly on the plugin website or through the plugin (Credits Page).', 'newstatpress' );

	$warning            = __( 'This option is yet experimental, please report bugs or improvement (see link on the bottom)', 'newstatpress' );
	$advising           = __( 'You receive this email because you have enabled the statistics notification in the NewStatpress plugin (option menu) from your WP website ', 'newstatpress' );
	$message            = __( 'Dear', 'newstatpress' ) . " $userna, <br /> <br />
             <i>$advising<STRONG>$blog_title</STRONG>.</i>
             <mark>$warning.</mark> <br />
             <br />" .
				__( 'Statistics at', 'newstatpress' ) . " $date (" . __( 'server time', 'newstatpress' ) . ") from  $blog_title: <br />
             $result_h <br /> <br />"
				. __( 'Best Regards from', 'newstatpress' ) . " <i>NewStatPress Team</i>. <br />
             <br />
             <br />
             -- <br />
             $credits_introduction";
	$headers            = 'From: ' . $sender . " <newstatpress@altervista.org> \r\n";
	$email_confirmation = wp_mail( $email_address, $subject, $message, $headers );

	remove_filter( 'wp_mail_content_type', 'nsp_set_mail_content_type' );

	return $email_confirmation;
}

/**
 * Mail notificatiom deactivate
 */
function nsp_mail_notification_deactivate() {
	wp_clear_scheduled_hook( 'nsp_mail_notification' );
}

// Hook mail publi.
add_action( 'nsp_mail_notification', 'nsp_stat_by_email' );




/**
 * Add Settings link to plugins page
 * added by cHab
 *
 * @param string $links the links.
 * @param string $file the file.
 * @return the link.
 */
function nsp_add_settings_link( $links, $file ) {
	if ( plugin_basename( __FILE__ ) !== $file ) {
		return $links;
	}

	$settings_link = '<a href="admin.php?page=nsp_options">' . __( 'Settings', 'newstatpress' ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'nsp_add_settings_link', 10, 2 );



/**TODO useful or not????
 * PHP 4 compatible mb_substr function
 * (taken in statpress-visitors)
 *
 * @param string $str the string.
 * @param int    $x x value.
 * @param int    $y y value.
 * @return the substring.
 */
function nsp_my_substr( $str, $x, $y = 0 ) {
	if ( 0 == $y ) {
		$y = strlen( $str ) - $x;
	}

	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $str, $x, $y );
	} else {
		return substr( $str, $x, $y );
	}
}


/**
 * Decode the given url
 *
 * @param string $out_url the given url to decode.
 * @return the decoded url.
 ****************************************/
function nsp_decode_url( $out_url ) {
	$out_url = filter_var( $out_url, FILTER_SANITIZE_URL );

	if ( '' === $out_url ) {
		$out_url = __( 'Page', 'newstatpress' ) . ': Home'; }
	if ( substr( $out_url, 0, 4 ) === 'cat=' ) {
		$out_url = __( 'Category', 'newstatpress' ) . ': ' . get_cat_name( substr( $out_url, 4 ) ); }
	if ( substr( $out_url, 0, 2 ) === 'm=' ) {
		$out_url = __( 'Calendar', 'newstatpress' ) . ': ' . substr( $out_url, 6, 2 ) . '/' . substr( $out_url, 2, 4 ); }
	if ( substr( $out_url, 0, 2 ) === 's=' ) {
		$out_url = __( 'Search', 'newstatpress' ) . ': ' . substr( $out_url, 2 ); }
	if ( substr( $out_url, 0, 2 ) === 'p=' ) {
		$sub_out   = substr( $out_url, 2 );
		$post_id_7 = get_post( $sub_out, ARRAY_A );
		$out_url   = $post_id_7['post_title'];
	}
	if ( substr( $out_url, 0, 8 ) === 'page_id=' ) {
		$sub_out   = substr( $out_url, 8 );
		$post_id_7 = get_page( $sub_out, ARRAY_A );
		$out_url   = __( 'Page', 'newstatpress' ) . ': ' . $post_id_7['post_title'];
	}
	return $out_url;
}


/**
 * Get url
 *
 * @retrun the url.
 */
function nsp_url() {
	$url_requested = ( isset( $_SERVER['QUERY_STRING'] ) ? filter_var( wp_unslash( $_SERVER['QUERY_STRING'] ), FILTER_SANITIZE_URL ) : '' );
	if ( '' === $url_requested ) { // SEO problem!
		$url_requested = ( isset( $_SERVER['REQUEST_URI'] ) ? filter_var( wp_unslash( $_SERVER['REQUEST_URI'] ), FILTER_SANITIZE_URL ) : '' );
	}
	if ( substr( $url_requested, 0, 2 ) === '/?' ) {
		$url_requested = substr( $url_requested, 2 ); }
	if ( '/' === $url_requested ) {
		$url_requested = ''; }

	// sanitize urldecode.
	$url_requested = filter_var( $url_requested, FILTER_SANITIZE_URL );

	return $url_requested;
}


/**
 * Convert data us to default format di WordPress
 *
 * @param string $dt date to convert.
 * @return converted data.
 ****************************************************/
function nsp_hdate( $dt = '00000000' ) {
	return mysql2date( get_option( 'date_format' ), substr( $dt, 0, 4 ) . '-' . substr( $dt, 4, 2 ) . '-' . substr( $dt, 6, 2 ) );
}


/**
 * Get newstatpress_hdate
 *
 * @param string $dt the date.
 * @return the hdate.
 */
function newstatpress_hdate( $dt = '00000000' ) {
	return mysql2date( get_option( 'date_format' ), nsp_my_substr( $dt, 0, 4 ) . '-' . nsp_my_substr( $dt, 4, 2 ) . '-' . nsp_my_substr( $dt, 6, 2 ) );
}



// ---------------------------------------------------------------------------
// GET DATA from visitors Functions
// ---------------------------------------------------------------------------


/**TODO clean $accepted
 * Extracts the accepted language from browser headers
 *
 * @param string $accepted not used.
 */
function nsp_get_language( $accepted ) {
	if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {

		// Capture up to the first delimiter (, found in Safari).
		preg_match( '/([^,;]*)/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ), $array_languages );

		// Fix some codes, the correct syntax is with minus (-) not underscore (_).
		return str_replace( '_', '-', strtolower( $array_languages[0] ) );
	}
	return 'xx';  // Indeterminable language.
}

/**
 * Get query pairs
 *
 * @param string $url the url.
 * @return the pairs.
 */
function nsp_get_query_pairs( $url ) {
	$parsed_url = wp_parse_url( $url );
	$tab        = wp_parse_url( $url );
	$host       = $tab['host'];
	if ( key_exists( 'query', $tab ) ) {
		$query = $tab['query'];
		return explode( '&', $query );
	} else {
		return null;}
}


/**
 * Get OS from the given argument
 *
 * @param string $arg the argument to parse for OS.
 * @return the OS find in configuration file.
 *******************************************/
function nsp_get_os( $arg ) {
	global $newstatpress_dir;

	$arg   = str_replace( ' ', '', $arg );
	$lines = file( $newstatpress_dir . '/def/os.dat' );
	foreach ( $lines as $line_num => $os ) {
		list($nome_os,$id_os) = explode( '|', $os );
		if ( strpos( $arg, $id_os ) === false ) {
			continue;
		}
		return $nome_os;     // fount.
	}
	return '';
}

/**
 * Get OS logo from the given argument
 *
 * @param string $arg the argument to parse for OS.
 * @return the OS find in configuration file.
 *******************************************/
function nsp_get_os_img( $arg ) {
	global $newstatpress_dir;
	$lines = file( $newstatpress_dir . '/def/os.dat' );
	foreach ( $lines as $line_num => $os ) {
		list($name_os,$id_os,$img_os) = explode( '|', $os );
		if ( strcmp( $name_os, $arg ) == 0 ) {
			return $img_os;
		}
	}
	return '';
}

/**
 * Get Browser from the given argument
 *
 * @param string $arg the argument to parse for Brower.
 * @return the Browser find in configuration file.
 ************************************************/
function nsp_get_browser( $arg ) {
	global $newstatpress_dir;

	$arg   = str_replace( ' ', '', $arg );
	$lines = file( $newstatpress_dir . '/def/browser.dat' );
	foreach ( $lines as $line_num => $browser ) {
		list($nome,$id) = explode( '|', $browser );
		if ( strpos( $arg, $id ) === false ) {
			continue;
		}
		return $nome;     // fount.
	}
	return '';
}

/**
 * Get Browser from the given argument
 *
 * @param string $arg the argument to parse for Brower.
 * @return the Browser find in configuration file.
 ************************************************/
function nsp_get_browser_img( $arg ) {
	global $newstatpress_dir;
	$lines = file( $newstatpress_dir . '/def/browser.dat' );
	foreach ( $lines as $line_num => $browser ) {
		list($name_browser,$id,$img_browser) = explode( '|', $browser );
		if ( strcmp( $name_browser, $arg ) == 0 ) {
			return $img_browser;
		}
	}
	return '';
}

/**
 * Check if the given ip is to ban
 *
 * @param string $arg the ip to check.
 * @return '' id the address is banned.
 */
function nsp_check_ban_ip( $arg ) {
	global $newstatpress_dir;

	$lines = file( $newstatpress_dir . '/def/banips.dat' );
	foreach ( $lines as $line_num => $banip ) {
		if ( strpos( $arg, rtrim( $banip, "\n" ) ) === false ) {
			continue;
		}
		return ''; // this is banned.
	}
	return $arg;
}

/**
 * Get the search engines
 *
 * @param string $referrer the url to test.
 * @return the search engine present in the url.
 */
function nsp_get_se( $referrer = null ) {
	global $newstatpress_dir;

	$key   = null;
	$lines = file( $newstatpress_dir . '/def/searchengines.dat' );
	foreach ( $lines as $line_num => $se ) {
		list($nome,$url,$key) = explode( '|', $se );
		if ( strpos( $referrer, $url ) === false ) {
			continue;
		}

		// find if.
		$variables               = nsp_get_query_pairs( html_entity_decode( $referrer ) );
		null === $variables ? $i = 0 : $i = count( $variables );
		while ( $i-- ) {
			$tab = explode( '=', $variables[ $i ] );
			if ( $tab[0] === $key ) {
				return ( $nome . '|' . urldecode( $tab[1] ) );}
		}
	}
	return null;
}

/**
 * Get the spider from the given agent
 *
 * @param string $agent the agent string.
 * @return agent the fount agent.
 *************************************/
function nsp_get_spider( $agent = null ) {
	global $newstatpress_dir;

	$agent = str_replace( ' ', '', $agent );
	$key   = null;
	$lines = file( $newstatpress_dir . '/def/spider.dat' );
	foreach ( $lines as $line_num => $spider ) {
		list($nome,$key) = explode( '|', $spider );
		if ( strpos( $agent, $key ) === false ) {
			continue;
		}
		// fount.
		return $nome;
	}
	return null;
}

/**
 * Get the previous month in 'YYYYMM' format
 *
 * @return the previous month.
 */
function nsp_lastmonth() {
	$ta = getdate( current_time( 'timestamp' ) );

	$year  = $ta['year'];
	$month = $ta['mon'];

	--$month; // go back 1 month.

	if ( 0 == $month ) : // if this month is Jan.
		--$year; // go back a year.
		$month = 12; // last month is Dec.
	endif;

	// return in format 'YYYYMM'.
	return sprintf( $year . '%02d', $month );
}

/**
 * Create or update the table
 *
 * @param string $action to do: update, create.
 *************************************/
function nsp_build_plugin_sql_table( $action ) {

	global $wpdb;
	global $wp_db_version;
	$table_name      = NSP_TABLENAME;
	$charset_collate = $wpdb->get_charset_collate();
	$index_list      = array(
		array(
			'Key_name'    => 'spider_nation',
			'Column_name' => '(spider, nation)',
		),
		array(
			'Key_name'    => 'ip_date',
			'Column_name' => '(ip, date)',
		),
		array(
			'Key_name'    => 'agent',
			'Column_name' => '(agent)',
		),
		array(
			'Key_name'    => 'search',
			'Column_name' => '(search)',
		),
		array(
			'Key_name'    => 'referrer',
			'Column_name' => '(referrer)',
		),
		array(
			'Key_name'    => 'feed_spider_os',
			'Column_name' => '(feed, spider, os)',
		),
		array(
			'Key_name'    => 'os',
			'Column_name' => '(os)',
		),
		array(
			'Key_name'    => 'date_feed_spider',
			'Column_name' => '(date, feed, spider)',
		),
		array(
			'Key_name'    => 'feed_spider_browser',
			'Column_name' => '(feed, spider, browser)',
		),
		array(
			'Key_name'    => 'browser',
			'Column_name' => '(browser)',
		),
	);
	// Add by chab
	// IF the table is already created then DROP INDEX for update.
	if ( '' === $action ) {
		$action = 'create';
	}

	$sql_createtable = '
    CREATE TABLE ' . $table_name . ' (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      date int(8),
      time time,
      ip varchar(39),
      urlrequested varchar(250),
      agent varchar(250),
      referrer varchar(512),
      search varchar(250),
      nation varchar(2),
      os varchar(30),
      browser varchar(32),
      searchengine varchar(16),
      spider varchar(32),
      feed varchar(8),
      user varchar(16),
      timestamp timestamp DEFAULT 0,
      UNIQUE KEY id (id)';

	if ( 'create' === $action ) {
		foreach ( $index_list as $index ) {
			$key_name         = $index['Key_name'];
			$column_name      = $index['Column_name'];
			$sql_createtable .= ", INDEX $key_name $column_name";
		}
	} elseif ( 'update' === $action ) {
		foreach ( $index_list as $index ) {
			$key_name    = $index['Key_name'];
			$column_name = $index['Column_name'];
			// db call ok; no-cache ok. unprepared SQL OK.
			// phpcs:ignore 
			if ( $wpdb->query( 
				     // phpcs:ignore
				     $wpdb->prepare( "SHOW INDEXES FROM `$table_name` WHERE Key_name = %s", $key_name ) // phpcs:ignore
			) === '' ) {
				$sql_createtable .= ",\n INDEX $key_name $column_name";
			}
		}
	}
	$sql_createtable .= ") $charset_collate;";

	if ( $wp_db_version >= 5540 ) {
		$page = 'wp-admin/includes/upgrade.php';
	} else {
		$page = 'wp-admin/upgrade-functions.php';
	}

	require_once ABSPATH . $page;
	dbDelta( $sql_createtable );
}

/**
 * Get if this is a feed
 *
 * @param string $url the url to test.
 * @return the kind of feed that is found.
 *****************************************/
function nsp_is_feed( $url ) {
	$tmp = get_bloginfo( 'rdf_url' );
	if ( $tmp ) {
		if ( stristr( $url, $tmp ) !== false ) {
			return 'RDF'; }
	}

	$tmp = get_bloginfo( 'rss2_url' );
	if ( $tmp ) {
		if ( stristr( $url, $tmp ) !== false ) {
			return 'RSS2'; }
	}

	$tmp = get_bloginfo( 'rss_url' );
	if ( $tmp ) {
		if ( stristr( $url, $tmp ) !== false ) {
			return 'RSS'; }
	}

	$tmp = get_bloginfo( 'atom_url' );
	if ( $tmp ) {
		if ( stristr( $url, $tmp ) !== false ) {
			return 'ATOM'; }
	}

	$tmp = get_bloginfo( 'comments_rss2_url' );
	if ( $tmp ) {
		if ( stristr( $url, $tmp ) !== false ) {
			return 'COMMENT'; }
	}

	$tmp = get_bloginfo( 'comments_atom_url' );
	if ( $tmp ) {
		if ( stristr( $url, $tmp ) !== false ) {
			return 'COMMENT'; }
	}

	if ( stristr( $url, 'wp-feed.php' ) !== false ) {
		return 'RSS2'; }
	if ( stristr( $url, '/feed/' ) !== false ) {
		return 'RSS2'; }
	return '';
}

/**
 * Insert statistic into the database
 ************************************/
function nsp_stat_append() {

	global $wpdb;
	$table_name = NSP_TABLENAME;
	global $userdata;

	wp_get_current_user();
	$feed = '';

	// Time.
	$timestamp = current_time( 'timestamp' );
	$vdate     = gmdate( 'Ymd', $timestamp );
	$vtime     = gmdate( 'H:i:s', $timestamp );
	$timestamp = gmdate( 'Y-m-d H:i:s', $timestamp );

	// IP.
	if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ); // BASIC detection -> to delete if it works.
	}

	// Is this IP blacklisted from file?
	if ( nsp_check_ban_ip( $ip_address ) === '' ) {
		return ''; }

	// Is this IP blacklisted from user?
	$to_ignore = get_option( 'newstatpress_ignore_ip', array() );
	foreach ( $to_ignore as $a_ip_range ) {
		list ($ip_to_ignore, $mask) = @explode( '/', trim( $a_ip_range ) );
		if ( empty( $mask ) ) {
			$mask = 32;
		}
		$long_ip_to_ignore        = ip2long( $ip_to_ignore );
		$long_mask                = bindec( str_pad( '', $mask, '1' ) . str_pad( '', 32 - $mask, '0' ) );
		$long_masked_user_ip      = ip2long( $ip_address ) & $long_mask;
		$long_masked_ip_to_ignore = $long_ip_to_ignore & $long_mask;
		if ( $long_masked_user_ip === $long_masked_ip_to_ignore ) {
			return ''; }
	}

	if ( get_option( 'newstatpress_cryptip' ) === 'checked' ) {
		$ip_address = crypt( $ip_address, NSP_TEXTDOMAIN );
	}

	// URL (requested).
	$url_requested = nsp_url();
	if ( preg_match( '/.ico$/i', $url_requested ) ) {
		return ''; }
	if ( preg_match( '/favicon.ico/i', $url_requested ) ) {
		return ''; }
	if ( preg_match( '/.css$/i', $url_requested ) ) {
		return ''; }
	if ( preg_match( '/.js$/i', $url_requested ) ) {
		return ''; }
	if ( stristr( $url_requested, content_url() ) !== false ) {
		return ''; }
	if ( stristr( $url_requested, admin_url() ) !== false ) {
		return ''; }
	$url_requested = esc_sql( $url_requested );

	// Is a given permalink blacklisted?
	$to_ignore = get_option( 'newstatpress_ignore_permalink', array() );
	foreach ( $to_ignore as $a_filter ) {
		if ( ! empty( $url_requested ) && strpos( $url_requested, $a_filter ) === 0 ) {
			return ''; }
	}

	$referrer = ( isset( $_SERVER['HTTP_REFERER'] ) ? htmlentities( sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) ) : '' );
	$referrer = esc_url( $referrer );
	$referrer = esc_sql( $referrer );

	$user_agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? htmlentities( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '' );
	$user_agent = sanitize_text_field( $user_agent );
	$user_agent = esc_sql( $user_agent );

	$spider = nsp_get_spider( $user_agent );

	if ( ( '' != $spider ) && ( get_option( 'newstatpress_donotcollectspider' ) === 'checked' ) ) {
		return ''; }

	// ininitalize to empty.
	$searchengine  = '';
	$search_phrase = '';

	if ( '' != $spider ) {
		$os      = '';
		$browser = '';
	} else {
		// Trap feeds.
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$feed = nsp_is_feed( get_bloginfo( 'url' ) . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		}
		// Get OS and browser.
		$os      = nsp_get_os( $user_agent );
		$browser = nsp_get_browser( $user_agent );

		$exp_referrer = nsp_get_se( $referrer );
		if ( isset( $exp_referrer ) ) {
			list($searchengine,$search_phrase) = explode( '|', $exp_referrer );
		}
	}

	// Country (ip2nation table) or language.
	$countrylang = '';
	if ( $wpdb->get_var( "SHOW TABLES LIKE 'ip2nation'" ) === 'ip2nation' ) {
		$qry = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT *
          FROM ip2nation
          WHERE ip < INET_ATON( %s )
          ORDER BY ip DESC
          LIMIT 0,1',
				$ip_address
			)
		); // db call ok; no-cache ok.
		if ( isset( $qry->country ) ) {
			$countrylang = $qry->country;
		}
	}

	if ( '' == $countrylang ) {
		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$countrylang = nsp_get_language( sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) );
		}
	}

	// Auto-delete visits if...
	if ( get_option( 'newstatpress_autodelete' ) !== '' ) {
		$int = filter_var( get_option( 'newstatpress_autodelete' ), FILTER_SANITIZE_NUMBER_INT );
		// secure action.
		if ( $int >= 1 ) {
			$t = gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 * $int * 30 );
			// phpcs:ignore -- db call ok; no-cache ok.
			$results = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM `$table_name`
          WHERE date < %s
          ",
					$t
				)
			); // phpcs:ignore: unprepared SQL OK.
		}
	}

	// Auto-delete spiders visits if...
	if ( get_option( 'newstatpress_autodelete_spiders' ) !== '' ) {
		$int = filter_var( get_option( 'newstatpress_autodelete_spiders' ), FILTER_SANITIZE_NUMBER_INT );

		// secure action.
		if ( $int >= 1 ) {
			$t = gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 * $int * 30 );
			// phpcs:ignore -- db call ok; no-cache ok.
			$results = $wpdb->query(
				$wpdb->prepare(
					"DELETE FROM `$table_name`
          WHERE date < %s and
                feed='' and
                spider<>''
         ",
					$t
				)
			); // phpcs:ignore: unprepared SQL OK.
		}
	}

	if ( ( ! is_user_logged_in() ) || ( get_option( 'newstatpress_collectloggeduser' ) === 'checked' ) ) {
		if ( is_user_logged_in() && ( get_option( 'newstatpress_collectloggeduser' ) === 'checked' ) ) {
			$current_user = wp_get_current_user();

			// Is a given name to ignore?
			$to_ignore = get_option( 'newstatpress_ignore_users', array() );
			foreach ( $to_ignore as $a_filter ) {
				if ( $current_user->user_login === $a_filter ) {
					return ''; }
			}
		}

		// phpcs:ignore -- db call ok; no-cache ok.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) !== $table_name ) {
			nsp_build_plugin_sql_table( 'create' );
		}

		$login = $userdata ? $userdata->user_login : null;

		$results = $wpdb->insert(
			$table_name,
			array(
				'date'         => $vdate,
				'time'         => $vtime,
				'ip'           => substr( $ip_address, 0, 39 ),
				'urlrequested' => substr( $url_requested, 0, 250 ),
				'agent'        => substr( wp_strip_all_tags( $user_agent ), 0, 250 ),
				'referrer'     => substr( $referrer, 0, 512 ),
				'search'       => substr( wp_strip_all_tags( $search_phrase ), 0, 250 ),
				'nation'       => substr( $countrylang, 0, 2 ),
				'os'           => substr( $os, 0, 30 ),
				'browser'      => substr( $browser, 0, 32 ),
				'searchengine' => substr( $searchengine, 0, 16 ),
				'spider'       => substr( $spider, 0, 32 ),
				'feed'         => substr( $feed, 0, 8 ),
				'user'         => substr( $login, 0, 16 ),
				'timestamp'    => $timestamp,
			),
			array( '%s' )
		);
	}
}
add_action( 'send_headers', 'nsp_stat_append' );

/**
 * Generate the Ajax code for the given variable
 *
 * @param string $var variable to get.
 * @param int    $limit optional limit value for query.
 * @param string $flag optional flag value for checked.
 * @param string $url optional url address.
 ************************************************/
function nsp_generate_ajax_var( $var, $limit = 0, $flag = '', $url = '' ) {
	global $newstatpress_dir;
	global $_newstatpress;

	wp_enqueue_script( 'wp_ajax_nsp_variables_' . $var, plugins_url( './includes/js/nsp_variables_' . $var . '.js', __FILE__ ), array( 'jquery' ), $_newstatpress['version'], true );
	wp_localize_script(
		'wp_ajax_nsp_variables_' . $var,
		'nsp_variablesAjax_' . $var,
		array(
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'postCommentNonce' => wp_create_nonce( 'newstatpress-nsp_variables-nonce' ),
			'VAR'              => $var,
			'URL'              => $url,
			'FLAG'             => $flag,
			'LIMIT'            => $limit,
		)
	);

	$res = '<span id="' . $var . '">_</span>';
	return $res;
}

/**
 * Return the expanded vars into the give code. API to use for users.
 *
 * @param string $body the body.
 */
function newstatpress_print( $body = '' ) {
	return nsp_expand_vars_inside_code( $body );
}

/**
 * Expand vars into the give code
 *
 * @param string $body the code where to look for variables to expand.
 * @return the modified code.
 ************************************************************/
function nsp_expand_vars_inside_code( $body ) {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	$vars_list = array(
		'visits',
		'yvisits',
		'mvisits',
		'wvisits',
		'totalvisits',
		'totalpageviews',
		'todaytotalpageviews',
		'alltotalvisits',
		'monthtotalpageviews',
	);

	// look for $vars_list.
	foreach ( $vars_list as $var ) {
		if ( strpos( strtolower( $body ), "%$var%" ) !== false ) {
			$body = str_replace( "%$var%", nsp_generate_ajax_var( $var ), $body );
		}
	}

	// look for %thistotalvisits%.
	if ( strpos( strtolower( $body ), '%thistotalvisits%' ) !== false ) {
		$body = str_replace( '%thistotalvisits%', nsp_generate_ajax_var( 'thistotalvisits', 0, '', nsp_url() ), $body );
	}

	// look for %since%.
	if ( strpos( strtolower( $body ), '%since%' ) !== false ) {
		// not needs prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry  = $wpdb->get_var(
			"SELECT date
       FROM `$table_name`
       ORDER BY date
       LIMIT 1
      "
		); // phpcs:ignore: unprepared SQL OK.
		$body = str_replace( '%since%', nsp_hdate( $qry ), $body );
	}

	// look for %os%.
	if ( strpos( strtolower( $body ), '%os%' ) !== false ) {
		$user_agent = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' );
		$os         = nsp_get_os( $user_agent );
		$body       = str_replace( '%os%', $os, $body );
	}

	// look for %browser%.
	if ( strpos( strtolower( $body ), '%browser%' ) !== false ) {
		$browser = nsp_get_browser( $user_agent );
		$body    = str_replace( '%browser%', $browser, $body );
	}

	// look for %ip%.
	if ( strpos( strtolower( $body ), '%ip%' ) !== false ) {
		$ip_address = ( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		$body       = str_replace( '%ip%', $ip_address, $body );
	}

	// look for %visitorsonline%.
	if ( strpos( strtolower( $body ), '%visitorsonline%' ) !== false ) {
		$act_time  = current_time( 'timestamp' );
		$from_time = gmdate( 'Y-m-d H:i:s', strtotime( '-4 minutes', $act_time ) );
		$to_time   = gmdate( 'Y-m-d H:i:s', $act_time );
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry  = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT count(DISTINCT(ip)) AS visitors
       FROM `$table_name`
       WHERE
         spider='' AND
         feed='' AND
         date = %s AND
         timestamp BETWEEN %s AND %s
      ",
				gmdate( 'Ymd', $act_time ),
				$from_time,
				$to_time
			)
		); // phpcs:ignore: unprepared SQL OK.
		$body = str_replace( '%visitorsonline%', $qry, $body );
	}

	// look for %usersonline%.
	if ( strpos( strtolower( $body ), '%usersonline%' ) !== false ) {
		$act_time  = current_time( 'timestamp' );
		$from_time = gmdate( 'Y-m-d H:i:s', strtotime( '-4 minutes', $act_time ) );
		$to_time   = gmdate( 'Y-m-d H:i:s', $act_time );
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry  = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT count(DISTINCT(ip)) AS users
       FROM `$table_name`
       WHERE
         spider='' AND
         feed='' AND
         date = %s AND
         user<>'' AND
         timestamp BETWEEN %s AND %s
      ",
				gmdate( 'Ymd', $act_time ),
				$from_time,
				$to_time
			)
		); // phpcs:ignore: unprepared SQL OK.
		$body = str_replace( '%usersonline%', $qry, $body );
	}

	// look for %toppost%.
	if ( strpos( strtolower( $body ), '%toppost%' ) !== false ) {
		// not needs prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry  = $wpdb->get_row(
			"SELECT urlrequested,count(*) AS totale
       FROM `$table_name`
       WHERE
         spider='' AND
         feed='' AND
         urlrequested LIKE '%p=%'
       GROUP BY urlrequested
       ORDER BY totale DESC
       LIMIT 1
      "
		); // phpcs:ignore: unprepared SQL OK.
		$body = str_replace( '%toppost%', nsp_decode_url( $qry->urlrequested ), $body );
	}

	// look for %topbrowser%.
	if ( strpos( strtolower( $body ), '%topbrowser%' ) !== false ) {
		// not needs prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry  = $wpdb->get_row(
			"SELECT browser,count(*) AS totale
        FROM `$table_name`
        WHERE
          spider='' AND
          feed=''
        GROUP BY browser
        ORDER BY totale DESC
        LIMIT 1
       "
		); // phpcs:ignore: unprepared SQL OK.
		$body = str_replace( '%topbrowser%', nsp_decode_url( $qry->browser ), $body );
	}

	// look for %topos%.
	if ( strpos( strtolower( $body ), '%topos%' ) !== false ) {
		// not needs prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry  = $wpdb->get_row(
			"SELECT os,count(*) AS totale
       FROM `$table_name`
       WHERE
         spider='' AND
         feed=''
       GROUP BY os
       ORDER BY totale DESC
       LIMIT 1
      "
		); // phpcs:ignore: unprepared SQL OK.
		$body = str_replace( '%topos%', nsp_decode_url( $qry->os ), $body );
	}

	// look for %topsearch%.
	if ( strpos( strtolower( $body ), '%topsearch%' ) !== false ) {
		// not needs prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry = $wpdb->get_row(
			"SELECT search, count(*) AS csearch
       FROM `$table_name`
       WHERE
         search<>''
       GROUP BY search
       ORDER BY csearch DESC
       LIMIT 1
      "
		); // phpcs:ignore: unprepared SQL OK.
		if ( is_object( $qry ) ) {
			$body = str_replace( '%topsearch%', nsp_decode_url( $qry->search ), $body );
		} else {
			$body = str_replace( '%topsearch%', '', $body );
		}
	}

	// look for %br%.
	if ( strpos( strtolower( $body ), '%br%' ) !== false ) {
		$body = str_replace( '%br%', '<br>', $body );
	}
	// look for %ul%.
	if ( strpos( strtolower( $body ), '%ul%' ) !== false ) {
		$body = str_replace( '%ul%', '<ul>', $body );
	}
	// look for %li%.
	if ( strpos( strtolower( $body ), '%li%' ) !== false ) {
		$body = str_replace( '%li%', '<li>', $body );
	}
	// look for %/ul%.
	if ( strpos( strtolower( $body ), '%/ul%' ) !== false ) {
		$body = str_replace( '%/ul%', '</ul>', $body );
	}
	// look for %/li%.
	if ( strpos( strtolower( $body ), '%/li%' ) !== false ) {
		$body = str_replace( '%/li%', '</li>', $body );
	}

	return $body;
}

// TODO : if working, move the contents into the caller instead of this function.
/**
 * Get top posts
 *
 * @param int    $limit the number of post to show.
 * @param string $showcounts if checked show totals.
 * @return result of extraction
 *******************************************/
function nsp_top_posts( $limit = 5, $showcounts = 'checked' ) {
	return nsp_generate_ajax_var( 'widget_topposts', $limit, $showcounts );
}


/**
 * Build NewsStatPress Widgets: Stat and TopPosts
 *
 * @param string $args arguments.
 ************************************************/
function nsp_widget_init( $args ) {
	if ( ! function_exists( 'wp_register_sidebar_widget' ) || ! function_exists( 'wp_register_widget_control' ) ) {
		return;
	}

	/**
	 * Statistics Widget control.
	 */
	function nsp_widget_stats_control() {
		global $nsp_widget_vars;
		$options = get_option( 'widget_newstatpress' );
		if ( ! is_array( $options ) ) {
			$options = array(
				'title' => 'NewStatPress Stats',
				'body'  => 'Visits today: %visits%',
			);
		}
		if ( isset( $_POST['newstatpress-submit'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['newstatpress-submit'] ) ), 'nsp_widget_stats_control' ) ) {
			if ( isset( $_POST['newstatpress-title'] ) ) {
				$options['title'] = sanitize_text_field( wp_unslash( $_POST['newstatpress-title'] ) );
			}
			if ( isset( $_POST['newstatpress-body'] ) ) {
				$options['body'] = stripslashes( sanitize_text_field( wp_unslash( $_POST['newstatpress-body'] ) ) );
			}
			update_option( 'widget_newstatpress', $options );
		}
		$title = htmlspecialchars( $options['title'], ENT_QUOTES );
		$body  = htmlspecialchars( $options['body'], ENT_QUOTES );

		// the form.
		echo "<p>
            <label for='newstatpress-title'>" . esc_html__( 'Title:', 'newstatpress' ) . "</label>
            <input class='widget-title' id='newstatpress-title' name='newstatpress-title' type='text' value='" . esc_attr( $title ) . "' />
          </p>
          <p>
            <label for='newstatpress-body'>" . esc_html_e( 'Body:', 'newstatpress' ) . "</label>
            <textarea class='widget-body' id='newstatpress-body' name='newstatpress-body' type='textarea' placeholder='Example: Month visits: %mvisits%...'>" . esc_html( $body ) . '</textarea>
          </p>
          ' . "
          <input type='hidden' id='newstatpress-submit' name='newstatpress-submit' value='" . esc_html( wp_create_nonce( 'nsp_widget_stats_control' ) ) . "' />
          <p>" . esc_html__( 'Stats available: ', 'newstatpress' ) . "<br/ >
          <span class='widget_varslist'>";
		foreach ( $nsp_widget_vars as $var ) {
			echo "<a href='#'>%" . esc_html( $var[0] ) . '%  <span>';
			esc_html( $var[1] );
			echo '</span></a> | ';
		}
		echo '</span></p>';
	}

	/**
	 * Widget stats
	 *
	 * @param string $args arguments.
	 */
	function nsp_widget_stats( $args ) {
		$options = get_option( 'widget_newstatpress' );
		$title   = esc_js( $options['title'] );
		$body    = esc_js( $options['body'] );
		echo wp_kses_post( $args['before_widget'] );
		print( wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] ) );
		print wp_kses_post( nsp_expand_vars_inside_code( $body ) );
		echo wp_kses_post( $args['after_widget'] );
	}
	wp_register_sidebar_widget( 'NewStatPress', 'NewStatPress Stats', 'nsp_widget_stats' );
	wp_register_widget_control( 'NewStatPress', array( 'NewStatPress', 'widgets' ), 'nsp_widget_stats_control', 300, 210 );

	/**
	 * Top posts Widget control.
	 */
	function nsp_widget_top_posts_control() {
		$options = get_option( 'widget_newstatpresstopposts' );
		if ( ! is_array( $options ) ) {
			$options = array(
				'title'      => 'NewStatPress TopPosts',
				'howmany'    => '5',
				'showcounts' => 'checked',
			);
		}
		if ( isset( $_POST['newstatpresstopposts-submit'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['newstatpresstopposts-submit'] ) ), 'nsp_widget_top_posts_control' ) ) {
			if ( isset( $_POST['newstatpresstopposts-title'] ) ) {
				$options['title'] = sanitize_text_field( wp_unslash( $_POST['newstatpresstopposts-title'] ) );
			}
			if ( isset( $_POST['newstatpresstopposts-howmany'] ) ) {
				$options['howmany'] = filter_var( wp_unslash( $_POST['newstatpresstopposts-howmany'] ), FILTER_SANITIZE_NUMBER_INT );
			}
			if ( isset( $_POST['newstatpresstopposts-showcounts'] ) ) {
				$options['showcounts'] = sanitize_text_field( wp_unslash( $_POST['newstatpresstopposts-showcounts'] ) );
			}
			if ( '1' === $options['showcounts'] ) {
				$options['showcounts'] = 'checked';
			}
			update_option( 'widget_newstatpresstopposts', $options );
		}
		$title      = htmlspecialchars( $options['title'], ENT_QUOTES );
		$howmany    = htmlspecialchars( $options['howmany'], ENT_QUOTES );
		$showcounts = htmlspecialchars( $options['showcounts'], ENT_QUOTES );
		// the form.
		echo "<p style='text-align:right;'>
            <label for='newstatpresstopposts-title'>" . esc_html__( 'Title', 'newstatpress' ) . "
            <input style='width: 250px;' id='newstatpress-title' name='newstatpresstopposts-title' type='text' value='" . esc_attr( $title ) . "' />
            </label>
          </p>
          <p style='text-align:right;'>
            <label for='newstatpresstopposts-howmany'>" . esc_html__( 'Limit results to', 'newstatpress' ) . "
            <input style='width: 100px;' id='newstatpresstopposts-howmany' name='newstatpresstopposts-howmany' type='text' value='" . esc_attr( $howmany ) . "' />
            </label>
          </p>";
		echo '<p style="text-align:right;"><label for="newstatpresstopposts-showcounts">' . esc_html__( 'Visits', 'newstatpress' ) . ' <input id="newstatpresstopposts-showcounts" name="newstatpresstopposts-showcounts" type=checkbox value="checked" ' . esc_attr( $showcounts ) . ' /></label></p>';
		echo '<input type="hidden" id="newstatpress-submitTopPosts" name="newstatpresstopposts-submit" value="' . esc_html( wp_create_nonce( 'nsp_widget_top_posts_control' ) ) . '" />';
	}

	/**
	 * Widget top posts
	 *
	 * @param string $args args to use.
	 */
	function nsp_widget_top_posts( $args ) {
		$options    = get_option( 'widget_newstatpresstopposts' );
		$title      = htmlspecialchars( $options['title'], ENT_QUOTES );
		$howmany    = htmlspecialchars( $options['howmany'], ENT_QUOTES );
		$showcounts = htmlspecialchars( $options['showcounts'], ENT_QUOTES );
		echo wp_kses_post( $args['before_widget'] );
		print( wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] ) );
		print wp_kses_post( nsp_top_posts( $howmany, $showcounts ) );
		echo wp_kses_post( $args['after_widget'] );
	}
	wp_register_sidebar_widget( 'NewStatPressTopPosts', 'NewStatPress TopPosts', 'nsp_widget_top_posts' );
	wp_register_widget_control( 'NewStatPressTopPosts', array( 'NewStatPressTopPosts', 'widgets' ), 'nsp_widget_top_posts_control', 300, 110 );
}
add_action( 'plugins_loaded', 'nsp_widget_init' );


/**
 * Calculate variations
 *
 * @param int $month month.
 * @param int $lmonth lmonth.
 */
function nsp_calculate_variation( $month, $lmonth ) {

	$target = round(
		$month / (
		( gmdate( 'd', current_time( 'timestamp' ) ) - 1 +
		( gmdate( 'H', current_time( 'timestamp' ) ) +
		( gmdate( 'i', current_time( 'timestamp' ) ) + 1 ) / 60.0 ) / 24.0 ) ) * gmdate( 't', current_time( 'timestamp' ) )
	);

	$monthchange = null;
	$added       = null;

	if ( 0 <> $lmonth ) {
		$percent_change = round( 100 * ( $month / $lmonth ) - 100, 1 );
		$percent_target = round( 100 * ( $target / $lmonth ) - 100, 1 );

		if ( $percent_change >= 0 ) {
			$percent_change = sprintf( "+%'04.1f", $percent_change );
			$monthchange    = "<td class='coll'><code style='color:green'>($percent_change%)</code></td>";
		} else {
			$percent_change = sprintf( "%'05.1f", $percent_change );
			$monthchange    = "<td class='coll'><code style='color:red'>($percent_change%)</code></td>";
		}

		if ( $percent_target >= 0 ) {
			$percent_target = sprintf( "+%'04.1f", $percent_target );
			$added          = "<td class='coll'><code style='color:green'>($percent_target%)</code></td>";
		} else {
			$percent_target = sprintf( "%'05.1f", $percent_target );
			$added          = "<td class='coll'><code style='color:red'>($percent_target%)</code></td>";
		}
	} else {
		$monthchange = '<td></td>';
		$added       = "<td class='coll'></td>";
	}

	$calculated_result = array( $monthchange, $target, $added );
	return $calculated_result;
}

register_activation_hook( __FILE__, 'nsp_build_plugin_sql_table' );
?>
