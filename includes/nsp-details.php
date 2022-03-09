<?php
/**
 * Details
 *
 * @package NewStatpress
 */

// Make sure plugin remains secure if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	if ( ! headers_sent() ) {
		header( 'HTTP/1.1 403 Forbidden' ); }
	die( esc_html__( 'ERROR: This plugin requires WordPress and will not function if called directly.', 'newstatpress' ) );
}

/**
 * Display details page
 */
function nsp_display_details() {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	// Top days.
	nsp_get_data_query2( 'DATE1', 'date', __( 'Top days', 'newstatpress' ), ( get_option( 'newstatpress_el_top_days' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_top_days' ) ), false );
	// O.S.
	nsp_get_data_query2( 'OS', 'os', __( 'OSes', 'newstatpress' ), ( get_option( 'newstatpress_el_os' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_os' ) ) );

	// Browser.
	nsp_get_data_query2( 'BROWSER', 'browser', __( 'Browsers', 'newstatpress' ), ( get_option( 'newstatpress_el_browser' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_browser' ) ) );

	// Feeds.
	nsp_get_data_query2( 'FEED', 'feed', __( 'Feeds', 'newstatpress' ), ( get_option( 'newstatpress_el_feed' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_feed' ) ) );

	// SE.
	nsp_get_data_query2( 'SEARCHENGINE', 'searchengine', __( 'Search engines', 'newstatpress' ), ( get_option( 'newstatpress_el_searchengine' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_searchengine' ) ) );

	// Search terms.
	nsp_get_data_query2( 'SEARCH', 'search', __( 'Top search terms', 'newstatpress' ), ( get_option( 'newstatpress_el_search' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_search' ) ) );

	// Top referrer.
	nsp_get_data_query2( 'REFFERER', 'referrer', __( 'Top referrers', 'newstatpress' ), ( get_option( 'newstatpress_el_referrer' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_referrer' ) ) );

	// Languages.
	nsp_get_data_query2( 'NATION', 'nation', __( 'Countries', 'newstatpress' ) . '/' . __( 'Languages', 'newstatpress' ), ( get_option( 'newstatpress_el_languages' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_languages' ) ) );

	// Spider.
	nsp_get_data_query2( 'SPIDER', 'spider', __( 'Spiders', 'newstatpress' ), ( get_option( 'newstatpress_el_spiders' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_spiders' ) ) );

	// Top Pages.
	nsp_get_data_query2( 'URLREQUESTED', 'urlrequested', __( 'Top pages', 'newstatpress' ), ( get_option( 'newstatpress_el_pages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_pages' ) ) );

	// Top Days - Unique visitors.
	nsp_get_data_query2( 'DATE2', 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Unique visitors', 'newstatpress' ), ( get_option( 'newstatpress_el_visitors' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_visitors' ) ) ); /* Maddler 04112007: required patching iriValueTable */

	// Top Days - Pageviews.
	nsp_get_data_query2( 'DATE3', 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_daypages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_daypages' ) ) ); /* Maddler 04112007: required patching iriValueTable */

	// Top IPs - Pageviews.
	nsp_get_data_query2( 'IP', 'ip', __( 'Top IPs', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_ippages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_ippages' ) ) ); /* Maddler 04112007: required patching iriValueTable */
}

