<?php
/**
 * Core functions
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
 * Display data in table extracted from the given query
 *
 * @param string  $type type to manage.
 * @param string  $fld field.
 * @param string  $fldtitle title of field.
 * @param int     $limit quantity of elements to extract.
 * @param boolean $print TRUE if the table is to print in page.
 * @return return the HTML output accoding to the sprint state
 */
function nsp_get_data_query2( $type, $fld, $fldtitle, $limit = 0, $print = true ) {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	$text = "<div class='wrap'>
            <table class='widefat'>
             <thead>
               <tr>
                <th scope='col' class='keytab-head'><h2>$fldtitle</h2></th>
                <th scope='col' style='width:10%;text-align:center;'>" . __( 'Visits', 'newstatpress' ) . "</th>
               </tr>
             </thead>\n";

	switch ( $type ) {
		case 'DATE1':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(date) as rks
        FROM `$table_name`
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'OS':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(os) as rks
        FROM `$table_name`
        WHERE feed='' AND spider='' AND os<>''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'BROWSER':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(browser) as rks
        FROM `$table_name`
        WHERE feed='' AND spider='' AND browser<>''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'FEED':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(feed) as rks
        FROM `$table_name`
        WHERE feed<>''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'SEARCHENGINE':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(searchengine) as rks
        FROM `$table_name`
        WHERE searchengine<>''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'SEARCH':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(search) as rks
        FROM `$table_name`
        WHERE search<>''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'REFFERER':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT count(referrer) as rks
        FROM `$table_name`
        WHERE referrer<>'' AND referrer NOT LIKE %s
        ",
					'%' . get_bloginfo( 'url' ) . '%'
				)
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'NATION':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(nation) as rks
        FROM `$table_name`
        WHERE nation<>'' AND spider=''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'SPIDER':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(spider) as rks
        FROM `$table_name`
        WHERE spider<>''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'URLREQUESTED':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(urlrequested) as rks
        FROM `$table_name`
        WHERE feed='' and spider=''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'DATE2':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(distinct ip) as rks
        FROM `$table_name`
        WHERE feed='' and spider=''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'DATE3':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(urlrequested) as rks
        FROM `$table_name`
        WHERE feed='' and spider=''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
		case 'IP':
			// phpcs:ignore -- db call ok; no-cache ok.
			$rks = $wpdb->get_var(
				"SELECT count(urlrequested) as rks
        FROM `$table_name`
        WHERE feed='' and spider=''
        "
			); // phpcs:ignore: unprepared SQL OK.
			break;
	}

	if ( $rks > 0 ) {
		if ( $limit > 0 ) {
			switch ( $type ) {
				case 'DATE1':
					// use prepare.
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(date) as pageview, date
              FROM `$table_name`             
              GROUP BY date
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'OS':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(os) as pageview, os
              FROM `$table_name`  
              WHERE feed='' AND spider='' AND os<>''
              GROUP BY os
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'BROWSER':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(browser) as pageview, browser
              FROM `$table_name`        
							WHERE feed='' AND spider='' AND browser<>''
              GROUP BY browser
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'FEED':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(feed) as pageview, feed
              FROM `$table_name`        
							WHERE feed<>''
              GROUP BY feed
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'SEARCHENGINE':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(searchengine) as pageview, searchengine
              FROM `$table_name`        
							WHERE searchengine<>''
              GROUP BY searchengine
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'SEARCH':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(search) as pageview, search
              FROM `$table_name`        
							WHERE search<>''
              GROUP BY search
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'REFFERER':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(referrer) as pageview, referrer
              FROM `$table_name`        
            	WHERE referrer<>'' AND referrer NOT LIKE %s
              GROUP BY referrer
              ORDER BY pageview DESC
              LIMIT %d",
							'%' . get_bloginfo( 'url' ) . '%',
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'NATION':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(nation) as pageview, nation
              FROM `$table_name`        
            	WHERE nation<>'' AND spider=''
              GROUP BY nation
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'SPIDER':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(spider) as pageview, spider
              FROM `$table_name`        
            	WHERE spider<>''
              GROUP BY spider
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'URLREQUESTED':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(urlrequested) as pageview, urlrequested
              FROM `$table_name`        
            	WHERE feed='' and spider=''
              GROUP BY urlrequested
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'DATE2':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(distinct ip) as pageview, date
              FROM `$table_name`        
            	WHERE feed='' and spider=''
              GROUP BY date
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'DATE3':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(urlrequested) as pageview, date
              FROM `$table_name`        
            	WHERE feed='' and spider=''
              GROUP BY date
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'IP':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(urlrequested) as pageview, ip
              FROM `$table_name`        
            	WHERE feed='' and spider=''
              GROUP BY ip
              ORDER BY pageview DESC
              LIMIT %d",
							$limit
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
			}
		} else {
			switch ( $type ) {
				case 'DATE1':
					// use prepare.
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(date) as pageview, date
              FROM `$table_name`             
              GROUP BY date
              ORDER BY pageview DESC
              LIMIT %d"
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'OS':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(os) as pageview, os
              FROM `$table_name`  
              WHERE feed='' AND spider='' AND os<>''
              GROUP BY os
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'BROWSER':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(browser) as pageview, browser
              FROM `$table_name`        
							WHERE feed='' AND spider='' AND browser<>''
              GROUP BY browser
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'FEED':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(feed) as pageview, feed
              FROM `$table_name`        
							WHERE feed<>''
              GROUP BY feed
              ORDER BY pageview DESC
              LIMIT %d"
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'SEARCHENGINE':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(searchengine) as pageview, searchengine
              FROM `$table_name`        
							WHERE searchengine<>''
              GROUP BY searchengine
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'SEARCH':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(search) as pageview, search
              FROM `$table_name`        
							WHERE search<>''
              GROUP BY search
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'REFFERER':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT count(referrer) as pageview, referrer
              FROM `$table_name`        
            	WHERE referrer<>'' AND referrer NOT LIKE %s
              GROUP BY referrer
              ORDER BY pageview DESC
              ",
							'%' . get_bloginfo( 'url' ) . '%'
						)
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'NATION':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(nation) as pageview, nation
              FROM `$table_name`        
            	WHERE nation<>'' AND spider=''
              GROUP BY nation
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'SPIDER':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(spider) as pageview, spider
              FROM `$table_name`        
            	WHERE spider<>''
              GROUP BY spider
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'URLREQUESTED':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(urlrequested) as pageview, urlrequested
              FROM `$table_name`        
            	WHERE feed='' and spider=''
              GROUP BY urlrequested
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'DATE2':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(distinct ip) as pageview, date
              FROM `$table_name`        
            	WHERE feed='' and spider=''
              GROUP BY date
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'DATE3':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(urlrequested) as pageview, date
              FROM `$table_name`        
            	WHERE feed='' and spider=''
              GROUP BY date
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
				case 'IP':
					// phpcs:ignore -- db call ok; no-cache ok.
					$qry = $wpdb->get_results(
						"SELECT count(urlrequested) as pageview, ip
              FROM `$table_name`        
            	WHERE feed='' and spider=''
              GROUP BY ip
              ORDER BY pageview DESC
              "
					); // phpcs:ignore: unprepared SQL OK.
					break;
			}
		}

		$tdwidth = 450;

		// Collects data.
		$data = array();
		foreach ( $qry as $rk ) {
			$pc = round( ( $rk->pageview * 100 / $rks ), 1 );
			if ( 'nation' === $fld ) {
				$rk->$fld = strtoupper( $rk->$fld ); }
			if ( 'date' === $fld ) {
				$rk->$fld = nsp_hdate( $rk->$fld ); }
			if ( 'urlrequested' === $fld ) {
				$rk->$fld = nsp_decode_url( $rk->$fld ); }
			$data[ substr( $rk->$fld, 0, 250 ) ] = $rk->pageview;
		}
	}

	// Draw table body.
	$text .= "<tbody id='the-list'>";
	if ( $rks > 0 ) {  // Chart!

		if ( 'NATION' === $type ) { // Nation chart.
			$charts = plugins_url( './geocharts.html', __FILE__ ) . nsp_get_google_geo( $data );
		} else { // Pie chart.
			$charts = plugins_url( './piecharts.html', __FILE__ ) . nsp_get_google_pie( $fldtitle, $data );
		}

		foreach ( $data as $key => $value ) {
			$text .= "<tr><td class='keytab'>" . $key . "</td><td class='valuetab'>" . $value . "</td></tr>\n";
		}

		$text .= "<tr><td colspan=2 style='width:50%;'>
              <iframe src='" . $charts . "' class='framebox'></iframe></td></tr>";
	}
	$text .= "</tbody>
	</table>
	</div>
	<br />\n";

	if ( $print ) {
		print wp_kses(
			$text,
			array(
				'a'      => array(
					'href'   => array(),
					'target' => array(),
					'class'  => array(),
				),
				'tr'     => array(),
				'thead'  => array(),
				'h2'     => array(),
				'p'      => array(),

				'iframe' => array(
					'src'   => array(),
					'class' => array(),
				),
				'th'     => array(
					'scope' => array(),
					'style' => array(),
					'class' => array(),
				),
				'td'     => array(
					'colspan' => array(),
					'style'   => array(),
					'class'   => array(),
				),
				'tbody'  => array(
					'id' => array(),
				),
				'table'  => array(
					'class' => array(),
				),
				'div'    => array(
					'class' => array(),
				),
			)
		);
	} else {
		return $text;
	}
}

/**
 * Get google url query for geo data
 *
 * @param string $data_array the array of data_array.
 * @return the url with data
 */
function nsp_get_google_geo( $data_array ) {
	if ( empty( $data_array ) ) {
		return ''; }
	// get hash.
	foreach ( $data_array as $key => $value ) {
		$values[] = $value;
		$labels[] = $key;
	}
	return '?cht=Country&chd=' . ( implode( ',', $values ) ) . '&chlt=Popularity&chld=' . ( implode( ',', $labels ) );
}

/**
 * Get google url query for pie data
 *
 * @param string $title the title to use.
 * @param string $data_array the array of data_array.
 * @return the url with data
 */
function nsp_get_google_pie( $title, $data_array ) {
	if ( empty( $data_array ) ) {
		return ''; }
	// get hash.
	foreach ( $data_array as $key => $value ) {
		$values[] = $value;
		$labels[] = $key;
	}

	return '?title=' . $title . '&chd=' . ( implode( ',', $values ) ) . '&chl=' . urlencode( implode( '|', $labels ) );
}

/**
 * Replace a content in page with NewStatPress output
 * Used format is: [NewStatPress: type]
 * Type can be:
 *  [NewStatPress: Overview]
 *  [NewStatPress: Top days]
 *  [NewStatPress: O.S.]
 *  [NewStatPress: Browser]
 *  [NewStatPress: Feeds]
 *  [NewStatPress: Search Engine]
 *  [NewStatPress: Search terms]
 *  [NewStatPress: Top referrer]
 *  [NewStatPress: Languages]
 *  [NewStatPress: Spider]
 *  [NewStatPress: Top Pages]
 *  [NewStatPress: Top Days - Unique visitors]
 *  [NewStatPress: Top Days - Pageviews]
 *  [NewStatPress: Top IPs - Pageviews]
 *
 * @param string $content the content of page.
 ******************************************************/
function nsp_shortcode( $content = '' ) {
	ob_start();
	$types = array();
	$type  = preg_match_all( '/\[NewStatPress: (.*)\]/Ui', $content, $types );

	foreach ( $types[1] as $k => $type ) {
		echo esc_html( $type );
		switch ( $type ) {
			case 'Overview':
				require_once 'api/nsp-api-dashboard.php';
				$replacement = nsp_api_dashboard( 'HTML' );
				break;
			case 'Top days':
				$replacement = nsp_get_data_query2( 'DATE1', 'date', __( 'Top days', 'newstatpress' ), ( get_option( 'newstatpress_el_top_days' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_top_days' ) ), false );
				break;
			case 'O.S.':
				$replacement = nsp_get_data_query2( 'OS', 'os', __( 'OSes', 'newstatpress' ), ( get_option( 'newstatpress_el_os' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_os' ) ), false );
				break;
			case 'Browser':
				$replacement = nsp_get_data_query2( 'BROWSER', 'browser', __( 'Browsers', 'newstatpress' ), ( get_option( 'newstatpress_el_browser' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_browser' ) ), false );
				break;
			case 'Feeds':
				$replacement = nsp_get_data_query2( 'FEED', 'feed', __( 'Feeds', 'newstatpress' ), ( get_option( 'newstatpress_el_feed' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_feed' ) ), false );
				break;
			case 'Search Engine':
				$replacement = nsp_get_data_query2( 'SEARCHENGINE', 'searchengine', __( 'Search engines', 'newstatpress' ), ( get_option( 'newstatpress_el_searchengine' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_searchengine' ) ), false );
				break;
			case 'Search terms':
				$replacement = nsp_get_data_query2( 'SEARCH', 'search', __( 'Top search terms', 'newstatpress' ), ( get_option( 'newstatpress_el_search' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_search' ) ), false );
				break;
			case 'Top referrer':
				$replacement = nsp_get_data_query2( 'REFFERER', 'referrer', __( 'Top referrers', 'newstatpress' ), ( get_option( 'newstatpress_el_referrer' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_referrer' ) ), false );
				break;
			case 'Languages':
				$replacement = nsp_get_data_query2( 'NATION', 'nation', __( 'Countries', 'newstatpress' ) . '/' . __( 'Languages', 'newstatpress' ), ( get_option( 'newstatpress_el_languages' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_languages' ) ), false );
				break;
			case 'Spider':
				$replacement = nsp_get_data_query2( 'SPIDER', 'spider', __( 'Spiders', 'newstatpress' ), ( get_option( 'newstatpress_el_spiders' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_spiders' ) ), false );
				break;
			case 'Top Pages':
				$replacement = nsp_get_data_query2( 'URLREQUESTED', 'urlrequested', __( 'Top pages', 'newstatpress' ), ( get_option( 'newstatpress_el_pages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_pages' ) ), false );
				break;
			case 'Top Days - Unique visitors':
				$replacement = nsp_get_data_query2( 'DATE2', 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Unique visitors', 'newstatpress' ), ( get_option( 'newstatpress_el_visitors' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_visitors' ) ), false );
				break;
			case 'Top Days - Pageviews':
				$replacement = nsp_get_data_query2( 'DATE3', 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_daypages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_daypages' ) ), false );
				break;
			case 'Top IPs - Pageviews':
				$replacement = nsp_get_data_query2( 'IP', 'ip', __( 'Top IPs', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_ippages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_ippages' ) ), '', 'urlrequested', false );
				break;
			default:
				$replacement = '';
		}
		$content = str_replace( $types[0][ $k ], $replacement, $content );
	}
	ob_get_clean();
	return $content;
}
add_filter( 'the_content', 'nsp_shortcode' );


