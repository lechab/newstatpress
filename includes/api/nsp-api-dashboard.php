<?php
/**
 * Dashboard via API
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
 * API: Dashboard
 *
 * Return the overview according to the passed parameters as json encoded
 *
 * @param string $typ the type of result (Json/Html).
 * @return the result
 */
function nsp_api_dashboard( $typ ) {
	global $wpdb;
	global $nsp_option_vars;

	$table_name = nsp_TABLENAME;

	$lastmonth = nsp_Lastmonth();

	$thisyear  = gmdate( 'Y', current_time( 'timestamp' ) );
	$thismonth = gmdate( 'Ym', current_time( 'timestamp' ) );
	$yesterday = gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 );
	$today     = gmdate( 'Ymd', current_time( 'timestamp' ) );
	$tlm[0]    = substr( $lastmonth, 0, 4 );
	$tlm[1]    = substr( $lastmonth, 4, 2 );

	$thisyear_header  = gmdate( 'Y', current_time( 'timestamp' ) );
	$lastmonth_header = gmdate( 'M, Y', gmmktime( 0, 0, 0, $tlm[1], 1, $tlm[0] ) );
	$thismonth_header = gmdate( 'M, Y', current_time( 'timestamp' ) );
	$yesterday_header = gmdate( 'd M', current_time( 'timestamp' ) - 86400 );
	$today_header     = gmdate( 'd M', current_time( 'timestamp' ) );

	$result_j['lastmonth'] = $lastmonth;                       // export.
	$result_j['thisyear']  = $thisyear;                        // export.
	$result_j['thismonth'] = $thismonth;                       // export.
	$result_j['yesterday'] = $yesterday;                       // export.
	$result_j['today']     = $today;                           // export.

	$thismonth1  = gmdate( 'Ym', current_time( 'timestamp' ) ) . '01';
	$thismonth31 = gmdate( 'Ymt', current_time( 'timestamp' ) );
	$lastmonth1  = $lastmonth . '01';
	$lastmonth31 = gmdate( 'Ymt', strtotime( $lastmonth1 ) );

	$overview_rows = array( 'visitors', 'visitors_feeds', 'pageview', 'feeds', 'spiders' );

	foreach ( $overview_rows as $row ) {

		switch ( $row ) {
			case 'visitors':
				$row2            = 'DISTINCT ip';
				$row_title       = __( 'Visitors', 'newstatpress' );
				$sql_query_total = "SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider=''";
				break;
			case 'visitors_feeds':
				$row2            = 'DISTINCT ip';
				$row_title       = __( 'Visitors through Feeds', 'newstatpress' );
				$sql_query_total = "SELECT count($row2) AS $row FROM $table_name WHERE feed<>'' AND spider='' AND agent<>''";
				break;
			case 'pageview':
				$row2            = 'date';
				$row_title       = __( 'Pageviews', 'newstatpress' );
				$sql_query_total = "SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider=''";
				break;
			case 'spiders':
				$row2            = 'date';
				$row_title       = __( 'Spiders', 'newstatpress' );
				$sql_query_total = "SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider<>''";
				break;
			case 'feeds':
				$row2            = 'date';
				$row_title       = __( 'Pageviews through Feeds', 'newstatpress' );
				$sql_query_total = "SELECT count($row2) AS $row FROM $table_name WHERE feed<>'' AND spider=''";
				break;
		}

		if ( get_option( $nsp_option_vars['calculation']['name'] ) === 'sum' ) {

			// alternative calculation by mouth: sum of unique visitors of each day.
			$tot   = 0;
			$t     = getdate( current_time( 'timestamp' ) );
			$year  = $t['year'];
			$month = sprintf( '%02d', $t['mon'] );
			$day   = $t['mday'];
			$totlm = 0;

			/*
				Left out
				for ( $k = $t['mon']; $k > 0; $k-- ) {
					// current month.
				}
			*/

			for ( $i = 0; $i < $day; $i++ ) {
				// use prepare.
				$qry_daylmonth = $wpdb->get_row( $wpdb->prepare( '%s AND date LIKE %s', $sql_query_total, $lastmonth . $i . '%' ) ); // db call ok; no-cache ok.
				$qry_day       = $wpdb->get_row( $wpdb->prepare( '%s AND date LIKE %s', $sql_query_total, $year . $month . $i . '%' ) ); // db call ok; no-cache ok.
				$tot          += $qry_day->$row;
				$totlm        += $qry_daylmonth->$row;
			}

			$qry_tmonth       = new stdClass();
			$qry_lmonth       = new stdClass();
			$qry_tmonth->$row = $tot;
			$qry_lmonth->$row = $totlm;

		} else { // classic.
				// use prepare.
				$qry_tmonth = $wpdb->get_row( $wpdb->prepare( '%s AND date BETWEEN %s AND %s', $sql_query_total, $thismonth1, $thismonth31 ) ); // db call ok; no-cache ok.
				$qry_lmonth = $wpdb->get_row( $wpdb->prepare( '%s AND date BETWEEN %s AND %s', $sql_query_total, $lastmonth1, $lastmonth31 ) ); // db call ok; no-cache ok.
		}

			$result_j[ $row . '_tmonth' ] = $qry_tmonth->$row;  // export.
			$result_j[ $row . '_lmonth' ] = $qry_lmonth->$row;  // export.

			// use prepare.
			$qry_y = $wpdb->get_row( $wpdb->prepare( '%s AND date LIKE %s', $sql_query_total, $yesterday ) ); // db call ok; no-cache ok.
			$qry_t = $wpdb->get_row( $wpdb->prepare( '%s AND date LIKE %s', $sql_query_total, $today ) ); // db call ok; no-cache ok.

			$result_j[ $row . '_qry_y' ] = $qry_y->$row;  // export.
			$result_j[ $row . '_qry_t' ] = $qry_t->$row;  // export.

		if ( 0 !== $result_j[ $row . '_lmonth' ] ) {
			$result_j[ $row . '_perc_change' ] = round( 100 * ( $result_j[ $row . '_tmonth' ] / $result_j[ $row . '_lmonth' ] ) - 100, 1 ) . '%';  // export.
		} else {
					$result_j[ $row . '_perc_change' ] = '';
		}

		$result_j[ $row . '_title' ] = $row_title;       // export.
	}

	if ( 'JSON' === $typ ) {
		return $result_j;  // avoid to calculte HTML if not necessary.
	}

	// output a HTML representation of the collected data.

	$overview_table = '';

	// dashboard.
	$overview_table .= "<table class='widefat center nsp'>
                      <thead>
                      <tr class='sup dashboard'>
                      <th></th>
                          <th scope='col'>" . __( 'M-1', 'newstatpress' ) . "</th>
                          <th scope='col' colspan='2'>" . __( 'M', 'newstatpress' ) . "</th>
                          <th scope='col'>" . __( 'Y', 'newstatpress' ) . "</th>
                          <th scope='col'>" . __( 'T', 'newstatpress' ) . "</th>
                      </tr>
                      <tr class='inf dashboard'>
                      <th></th>
                          <th><span>$lastmonth_header</span></th>
                          <th colspan='2'><span > $thismonth_header </span></th>
                          <th><span>$yesterday_header</span></th>
                          <th><span>$today_header</span></th>
                      </tr></thead>
                      <tbody class='overview-list'>";

	foreach ( $overview_rows as $row ) {
		$result = nsp_CalculateVariation( $result_j[ $row . '_tmonth' ], $result_j[ $row . '_lmonth' ] );

		// build full current row.
		$overview_table .= "<tr><td class='row_title $row'>" . $result_j[ $row . '_title' ] . '</td>';
		$overview_table .= "<td class='colc'>" . $result_j[ $row . '_lmonth' ] . "</td>\n";
		$overview_table .= "<td class='colr'>" . $result_j[ $row . '_tmonth' ] . $result[0] . "</td>\n";
		$overview_table .= "<td class='colc'>" . $result_j[ $row . '_qry_y' ] . "</td>\n";
		$overview_table .= "<td class='colc'>" . $result_j[ $row . '_qry_t' ] . "</td>\n";
		$overview_table .= '</tr>';
	}

	$overview_table .= "</tr></table>\n";

	$result_h = $overview_table;
	return $result_h;
}
