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
	nsp_get_data_query2( 'date', __( 'Top days', 'newstatpress' ), ( get_option( 'newstatpress_el_top_days' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_top_days' ) ), false );
	// O.S.
	nsp_get_data_query2( 'os', __( 'OSes', 'newstatpress' ), ( get_option( 'newstatpress_el_os' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_os' ) ), '', '', "AND feed='' AND spider='' AND os<>''" );

	// Browser.
	nsp_get_data_query2( 'browser', __( 'Browsers', 'newstatpress' ), ( get_option( 'newstatpress_el_browser' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_browser' ) ), '', '', "AND feed='' AND spider='' AND browser<>''" );

	// Feeds.
	nsp_get_data_query2( 'feed', __( 'Feeds', 'newstatpress' ), ( get_option( 'newstatpress_el_feed' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_feed' ) ), '', '', "AND feed<>''" );

	// SE.
	nsp_get_data_query2( 'searchengine', __( 'Search engines', 'newstatpress' ), ( get_option( 'newstatpress_el_searchengine' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_searchengine' ) ), '', '', "AND searchengine<>''" );

	// Search terms.
	nsp_get_data_query2( 'search', __( 'Top search terms', 'newstatpress' ), ( get_option( 'newstatpress_el_search' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_search' ) ), '', '', "AND search<>''" );

	// Top referrer.
	nsp_get_data_query2( 'referrer', __( 'Top referrers', 'newstatpress' ), ( get_option( 'newstatpress_el_referrer' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_referrer' ) ), '', '', "AND referrer<>'' AND referrer NOT LIKE '%" . get_bloginfo( 'url' ) . "%'" );

	// Languages.
	nsp_get_data_query2( 'nation', __( 'Countries', 'newstatpress' ) . '/' . __( 'Languages', 'newstatpress' ), ( get_option( 'newstatpress_el_languages' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_languages' ) ), '', '', "AND nation<>'' AND spider=''" );

	// Spider.
	nsp_get_data_query2( 'spider', __( 'Spiders', 'newstatpress' ), ( get_option( 'newstatpress_el_spiders' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_spiders' ) ), '', '', "AND spider<>''" );

	// Top Pages.
	nsp_get_data_query2( 'urlrequested', __( 'Top pages', 'newstatpress' ), ( get_option( 'newstatpress_el_pages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_pages' ) ), '', 'urlrequested', "AND feed='' and spider=''" );

	// Top Days - Unique visitors.
	nsp_get_data_query2( 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Unique visitors', 'newstatpress' ), ( get_option( 'newstatpress_el_visitors' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_visitors' ) ), 'distinct', 'ip', "AND feed='' and spider=''" ); /* Maddler 04112007: required patching iriValueTable */

	// Top Days - Pageviews.
	nsp_get_data_query2( 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_daypages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_daypages' ) ), '', 'urlrequested', "AND feed='' and spider=''" ); /* Maddler 04112007: required patching iriValueTable */

	// Top IPs - Pageviews.
	nsp_get_data_query2( 'ip', __( 'Top IPs', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_ippages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_ippages' ) ), '', 'urlrequested', "AND feed='' and spider=''" ); /* Maddler 04112007: required patching iriValueTable */
}

