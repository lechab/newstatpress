<?php
/**
 * Api Overview creation
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
 * API: Overview
 *
 * Return the overview according to the passed parameters as json encoded
 *
 * @param string $typ the type of result (Json/Html).
 * @param string $par the number of days for the graph (20 default, if 0 use the one in NewStatPress option).
 * @return the result
 */
function nsp_api_overview( $typ, $par ) {
	global $wpdb;
	global $nsp_option_vars;

	$offsets = get_option( $nsp_option_vars['stats_offsets']['name'] );

	$table_name = NSP_TABLENAME;

	$since     = nsp_expand_vars_inside_code( '%since%' );
	$lastmonth = nsp_lastmonth();
	$thisyear  = gmdate( 'Y', current_time( 'timestamp' ) );
	$thismonth = gmdate( 'Ym', current_time( 'timestamp' ) );
	$yesterday = gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 );
	$today     = gmdate( 'Ymd', current_time( 'timestamp' ) );

	$tlm[0]           = substr( $lastmonth, 0, 4 );
	$tlm[1]           = substr( $lastmonth, 4, 2 );
	$thisyear_header  = gmdate( 'Y', current_time( 'timestamp' ) );
	$lastmonth_header = gmdate( 'M, Y', gmmktime( 0, 0, 0, $tlm[1], 1, $tlm[0] ) );
	$thismonth_header = gmdate( 'M, Y', current_time( 'timestamp' ) );
	$yesterday_header = gmdate( 'd M', current_time( 'timestamp' ) - 86400 );
	$today_header     = gmdate( 'd M', current_time( 'timestamp' ) );

	// get the days of the graph.
	$gdays = intval( $par );
	if ( 0 === $gdays ) {
		$gdays = get_option( 'newstatpress_daysinoverviewgraph' ); }
	if ( 0 === $gdays ) {
		$gdays = 20; }

	// get result of dashboard as some date is shared with this.
	$result_j = nsp_api_dashboard( 'JSON' );

	$result_j['days'] = $gdays;  // export.

	$overview_rows = array( 'visitors', 'visitors_feeds', 'pageview', 'feeds', 'spiders' );

	foreach ( $overview_rows as $row ) {
		switch ( $row ) {
			case 'visitors':
				$row_title = __( 'Visitors', 'newstatpress' );
				// db call ok; no-cache ok; unprepared SQL OK.
				$result_j[ $row . '_total' ] = $wpdb->get_row( "SELECT count(DISTINCT ip) AS visitors FROM `$table_name` WHERE feed='' AND spider=''" )->$row; // phpcs:ignore
				$result_j[ $row . '_tyear' ]  = $wpdb->get_row( $wpdb->prepare( "SELECT count(DISTINCT ip) AS visitors FROM `$table_name` WHERE feed='' AND spider='' AND date LIKE %s", $thisyear . '%' ) )->$row; // phpcs:ignore
				$result_j[ $row . '_total' ] += $offsets['alltotalvisits'];
				break;
			case 'visitors_feeds':
				$row_title = __( 'Visitors through Feeds', 'newstatpress' );
				// db call ok; no-cache ok; unprepared SQL OK.
				$result_j[ $row . '_total' ] = $wpdb->get_row( "SELECT count(DISTINCT ip) AS visitors_feeds FROM `$table_name` WHERE feed<>'' AND spider='' AND agent<>''" )->$row; // phpcs:ignore
				$result_j[ $row . '_tyear' ]  = $wpdb->get_row( $wpdb->prepare( "SELECT count(DISTINCT ip) AS visitors_feeds FROM `$table_name` WHERE feed<>'' AND spider='' AND agent<>'' AND date LIKE %s", $thisyear . '%' ) )->$row; // phpcs:ignore
				$result_j[ $row . '_total' ] += $offsets['visitorsfeeds'];
				break;
			case 'pageview':
				$row_title = __( 'Pageviews', 'newstatpress' );
				// db call ok; no-cache ok; unprepared SQL OK.
				$result_j[ $row . '_total' ] = $wpdb->get_row( "SELECT count(date) AS pageview FROM `$table_name` WHERE feed='' AND spider=''" )->$row; // phpcs:ignore
				$result_j[ $row . '_tyear' ] = $wpdb->get_row( $wpdb->prepare( "SELECT count(date) AS pageview FROM `$table_name` WHERE feed='' AND spider='' AND date LIKE %s", $thisyear . '%' ) )->$row; // phpcs:ignore
				$result_j[ $row . '_total' ] += $offsets['pageviews'];
				break;
			case 'spiders':
				$row_title = __( 'Spiders', 'newstatpress' );
				// db call ok; no-cache ok; unprepared SQL OK.
				$result_j[ $row . '_total' ] = $wpdb->get_row( "SELECT count(date) AS spiders FROM `$table_name` WHERE feed='' AND spider<>''" )->$row; // phpcs:ignore
				$result_j[ $row . '_tyear' ] = $wpdb->get_row( $wpdb->prepare( "SELECT count(date) AS spiders FROM `$table_name` WHERE feed='' AND spider<>'' AND date LIKE %s", $thisyear . '%' ) )->$row; // phpcs:ignore
				$result_j[ $row . '_total' ] += $offsets['spy'];
				break;
			case 'feeds':
				$row_title = __( 'Pageviews through Feeds', 'newstatpress' );
				// db call ok; no-cache ok; unprepared SQL OK.
				$result_j[ $row . '_total' ] = $wpdb->get_row( "SELECT count(date) AS feeds FROM `$table_name` WHERE feed<>'' AND spider=''" )->$row;  // phpcs:ignore
				$result_j[ $row . '_tyear' ] = $wpdb->get_row( $wpdb->prepare( "SELECT count(date) AS feeds FROM `$table_name` WHERE feed<>'' AND spider='' AND date LIKE %s", $thisyear . '%' ) )->$row;  // phpcs:ignore
				$result_j[ $row . '_total' ] += $offsets['pageviewfeeds'];
				break;
		}
	}

	// make graph.

	$maxxday = 0;
	for ( $gg = $gdays - 1;$gg >= 0;$gg-- ) {

		$date = gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 * $gg );

		// db call ok; no-cache ok; unprepared SQL OK.
		$qry_visitors    = $wpdb->get_row( $wpdb->prepare( "SELECT count(DISTINCT ip) AS total FROM `$table_name` WHERE feed='' AND spider='' AND date = %s", $date ) ); // phpcs:ignore
		$visitors[ $gg ] = $qry_visitors->total;

		// db call ok; no-cache ok; unprepared SQL OK.
		$qry_pageviews   = $wpdb->get_row( $wpdb->prepare( "SELECT count(date) AS total FROM `$table_name` WHERE feed='' AND spider='' AND date = %s", $date ) ); // phpcs:ignore
		$pageviews[ $gg ] = $qry_pageviews->total;

		// db call ok; no-cache ok; unprepared SQL OK.
		$qry_spiders    = $wpdb->get_row( $wpdb->prepare( "SELECT count(date) AS total FROM `$table_name` WHERE feed='' AND spider<>'' AND date = %s", $date ) ); // phpcs:ignore
		$spiders[ $gg ] = $qry_spiders->total;

		// db call ok; no-cache ok; unprepared SQL OK.
		$qry_feeds    = $wpdb->get_row( $wpdb->prepare( "SELECT count(date) AS total FROM `$table_name` WHERE feed<>'' AND spider='' AND date = %s", $date ) ); // phpcs:ignore
		$feeds[ $gg ] = $qry_feeds->total;

		$total = $visitors[ $gg ] + $pageviews[ $gg ] + $spiders[ $gg ] + $feeds[ $gg ];
		if ( $total > $maxxday ) {
			$maxxday = $total;
		}
	}
	if ( 0 === $maxxday ) {
		$maxxday = 1; }

	$result_j['visitors']  = $visitors;  // export.
	$result_j['pageviews'] = $pageviews; // export.
	$result_j['spiders']   = $spiders;   // export.
	$result_j['feeds']     = $feeds;     // export.
	$result_j['max']       = $maxxday;   // export.

	// output an HTML representation of the collected data.

	$overview_table = '';

	// dashboard.
	$overview_table .= "<table class='widefat center nsp'>
                     <thead>
                      <tr class='sup'>
                       <th></th>
                       <th>" . __( 'Total since', 'newstatpress' ) . "</th>
                       <th scope='col'>" . __( 'This year', 'newstatpress' ) . "</th>
                       <th scope='col'>" . __( 'Last month', 'newstatpress' ) . "</th>
                       <th scope='col' colspan='2'>" . __( 'This month', 'newstatpress' ) . "</th>
                       <th scope='col' colspan='2'>" . __( 'Target This month', 'newstatpress' ) . "</th>
                       <th scope='col'>" . __( 'Yesterday', 'newstatpress' ) . "</th>
                       <th scope='col'>" . __( 'Today', 'newstatpress' ) . "</th>
                      </tr>
                      <tr class='inf'>
                       <th></th>
                       <th><span>$since</span></th>
                       <th><span>$thisyear_header</span></th>
                       <th><span>$lastmonth_header</span></th>
                       <th colspan='2'><span > $thismonth_header </span></th>
                       <th colspan='2'><span > $thismonth_header </span></th>
                       <th><span>$yesterday_header</span></th>
                       <th><span>$today_header</span></th>
                      </tr>
                     </thead>
                    <tbody class='overview-list'>";

	// build body table overview.
	$overview_rows = array( 'visitors', 'visitors_feeds', 'pageview', 'feeds', 'spiders' );

	foreach ( $overview_rows as $row ) {
		$result = nsp_calculate_variation( $result_j[ $row . '_tmonth' ], $result_j[ $row . '_lmonth' ] );

		// build full current row.
		$overview_table .= "<tr><td class='row_title $row'>" . $result_j[ $row . '_title' ] . '</td>';
		$overview_table .= "<td class='colc'>" . $result_j[ $row . '_total' ] . "</td>\n";
		$overview_table .= "<td class='colc'>" . $result_j[ $row . '_tyear' ] . "</td>\n";
		$overview_table .= "<td class='colc'>" . $result_j[ $row . '_lmonth' ] . "</td>\n";
		$overview_table .= "<td class='colr'>" . $result_j[ $row . '_tmonth' ] . $result[0] . "</td>\n";
		$overview_table .= "<td class='colr'> $result[1] $result[2] </td>\n";
		$overview_table .= "<td class='colc'>" . $result_j[ $row . '_qry_y' ] . "</td>\n";
		$overview_table .= "<td class='colc'>" . $result_j[ $row . '_qry_t' ] . "</td>\n";
		$overview_table .= '</tr>';
	}

	$overview_table .= '</tr></table>';

	$start_of_week = get_option( 'start_of_week' );
	$gd            = ( 90 / $gdays ) . '%';

	$overview_graph = "<table class='graph'><tr>";

	for ( $gg = $gdays - 1;$gg >= 0;$gg-- ) {

		$scale_factor = 2; // 2 : 200px in CSS

		$date = gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 * $gg );

		$px_visitors  = $scale_factor * ( round( $result_j['visitors'][ $gg ] * 100 / $maxxday ) );
		$px_pageviews = $scale_factor * ( round( $result_j['pageviews'][ $gg ] * 100 / $maxxday ) );
		$px_spiders   = $scale_factor * ( round( $result_j['spiders'][ $gg ] * 100 / $maxxday ) );
		$px_feeds     = $scale_factor * ( round( $result_j['feeds'][ $gg ] * 100 / $maxxday ) );

		$px_white = $scale_factor * 100 - $px_feeds - $px_spiders - $px_pageviews - $px_visitors;

		$overview_graph .= "<td width='$gd' valign='bottom'>";

		$overview_graph .= "<div class='overview-graph'>
      <div style='border-left:1px; background:#ffffff;width:100%;height:" . $px_white . "px;'></div>
        <div class='visitors_bar' style='height:" . $px_visitors . "px;' title='" . $result_j['visitors'][ $gg ] . ' ' . __( 'Visitors', 'newstatpress' ) . "'></div>
        <div class='web_bar' style='height:" . $px_pageviews . "px;' title='" . $result_j['pageviews'][ $gg ] . ' ' . __( 'Pageviews', 'newstatpress' ) . "'></div>
        <div class='spiders_bar' style='height:" . $px_spiders . "px;' title='" . $result_j['spiders'][ $gg ] . ' ' . __( 'Spiders', 'newstatpress' ) . "'></div>
        <div class='feeds_bar' style='height:" . $px_feeds . "px;' title='" . $result_j['feeds'][ $gg ] . ' ' . __( 'Feeds', 'newstatpress' ) . "'></div>
        <div style='background:gray;width:100%;height:1px;'></div>";
		if ( gmdate( 'w', current_time( 'timestamp' ) - 86400 * $gg === $start_of_week ) ) {
			$overview_graph .= "<div class='legend-W'>";
		} else {
			$overview_graph .= "<div class='legend'>";
		}
		$overview_graph .= gmdate( 'd', current_time( 'timestamp' ) - 86400 * $gg ) . ' ' . gmdate( 'M', current_time( 'timestamp' ) - 86400 * $gg ) . "</div></div></td>\n";
	}
	$overview_graph .= '</tr></table>';

	$overview_table = $overview_table . $overview_graph;

	$result_h = $overview_table;
	return $result_h;

}
