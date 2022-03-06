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
 * @param string  $fld GROUP BY argument of query.
 * @param string  $fldtitle title of field.
 * @param int     $limit quantity of elements to extract.
 * @param string  $param extra arguemnt for query (like DISTINCT).
 * @param string  $queryfld field of query.
 * @param string  $exclude WHERE argument of query.
 * @param boolean $print TRUE if the table is to print in page.
 * @return return the HTML output accoding to the sprint state
 */
function nsp_get_data_query2( $fld, $fldtitle, $limit = 0, $param = '', $queryfld = '', $exclude = '', $print = true ) {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	if ( '' === $queryfld ) {
		$queryfld = $fld;
	}

	$text = "<div class='wrap'>
            <table class='widefat'>
             <thead>
               <tr>
                <th scope='col' class='keytab-head'><h2>$fldtitle</h2></th>
                <th scope='col' style='width:10%;text-align:center;'>" . __( 'Visits', 'newstatpress' ) . "</th>
               </tr>
             </thead>\n";

	$rks = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT count(".strtok($param, " ")." ".strtok($queryfld, " ").") as rks
       FROM `$table_name`
       WHERE 1=1 %s
       ",
			$exclude
		)
	); // db call ok; no-cache ok.

	if ( $rks > 0 ) {
		// in this form not needs prepare as $exclude nads $fld are fixed text.
		if ( $limit > 0 ) {
			// use prepare.
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(".strtok($param, " ")." ".strtok($queryfld, " ").") as pageview, %s
           FROM `$table_name`
           WHERE 1=1 %s
           GROUP BY %s
           ORDER BY pageview DESC
           LIMIT %d",
					$fld,
					$exclude,
					$fld,
					$limit
				)
			); // db call ok; no-cache ok.
		} else {
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(".strtok($param, " ")." ".strtok($queryfld, " ").") as pageview, %s
           FROM `$table_name`
           WHERE 1=1 %s
           GROUP BY %s
           ORDER BY pageview DESC",
					$fld,
					$exclude,
					$fld
				)
			); // db call ok; no-cache ok.
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

		if ( 'nation' === $fld ) { // Nation chart.
			$charts = plugins_url( './geocharts.html', __FILE__ ) . nsp_get_google_geo( $data );
		} else { // Pie chart.
			$charts = plugins_url( './piecharts.html', __FILE__ ) . nsp_get_google_pie( $fldtitle, $data );
		}

		foreach ( $data as $key => $value ) {
			$text .= "<tr><td class='keytab'>" . $key . "</td><td class='valuetab'>" . $value . "</td></tr>\n";
		}

		$text .= "<tr><td colspan=2 style='width:50%;'>
              <iframe src='" . $charts . "' class='framebox'>
          <p>[_e('This section requires a browser that supports iframes.]','newstatpress')</p>
        </iframe></td></tr>";
	}
	$text .= "</tbody>
	</table>
	</div>
	<br />\n";

	if ( $print ) {
		print wp_kses(
			$text,
			array(
				'a'     => array(
					'href'   => array(),
					'target' => array(),
					'class'  => array(),
				),
				'tr'    => array(),

				'p'     => array(
					'iframe' => array(),
					'src'    => array(),
					'class'  => array(),
				),
				'td'    => array(
					'colspan' => array(),
					'style'   => array(),
					'class'   => array(),
				),
				'tbody' => array(
					'id' => array(),
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
				$replacement = nsp_get_data_query2( 'date', __( 'Top days', 'newstatpress' ), ( get_option( 'newstatpress_el_top_days' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_top_days' ) ), false );
				break;
			case 'O.S.':
				$replacement = nsp_get_data_query2( 'os', __( 'OSes', 'newstatpress' ), ( get_option( 'newstatpress_el_os' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_os' ) ), '', '', "AND feed='' AND spider='' AND os<>''", false );
				break;
			case 'Browser':
				$replacement = nsp_get_data_query2( 'browser', __( 'Browsers', 'newstatpress' ), ( get_option( 'newstatpress_el_browser' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_browser' ) ), '', '', "AND feed='' AND spider='' AND browser<>''", false );
				break;
			case 'Feeds':
				$replacement = nsp_get_data_query2( 'feed', __( 'Feeds', 'newstatpress' ), ( get_option( 'newstatpress_el_feed' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_feed' ) ), '', '', "AND feed<>''", false );
				break;
			case 'Search Engine':
				$replacement = nsp_get_data_query2( 'searchengine', __( 'Search engines', 'newstatpress' ), ( get_option( 'newstatpress_el_searchengine' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_searchengine' ) ), '', '', "AND searchengine<>''", false );
				break;
			case 'Search terms':
				$replacement = nsp_get_data_query2( 'search', __( 'Top search terms', 'newstatpress' ), ( get_option( 'newstatpress_el_search' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_search' ) ), '', '', "AND search<>''", false );
				break;
			case 'Top referrer':
				$replacement = nsp_get_data_query2( 'referrer', __( 'Top referrers', 'newstatpress' ), ( get_option( 'newstatpress_el_referrer' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_referrer' ) ), '', '', "AND referrer<>'' AND referrer NOT LIKE '%" . get_bloginfo( 'url' ) . "%'", false );
				break;
			case 'Languages':
				$replacement = nsp_get_data_query2( 'nation', __( 'Countries', 'newstatpress' ) . '/' . __( 'Languages', 'newstatpress' ), ( get_option( 'newstatpress_el_languages' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_languages' ) ), '', '', "AND nation<>'' AND spider=''", false );
				break;
			case 'Spider':
				$replacement = nsp_get_data_query2( 'spider', __( 'Spiders', 'newstatpress' ), ( get_option( 'newstatpress_el_spiders' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_spiders' ) ), '', '', "AND spider<>''", false );
				break;
			case 'Top Pages':
				$replacement = nsp_get_data_query2( 'urlrequested', __( 'Top pages', 'newstatpress' ), ( get_option( 'newstatpress_el_pages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_pages' ) ), '', 'urlrequested', "AND feed='' and spider=''", false );
				break;
			case 'Top Days - Unique visitors':
				$replacement = nsp_get_data_query2( 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Unique visitors', 'newstatpress' ), ( get_option( 'newstatpress_el_visitors' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_visitors' ) ), 'distinct', 'ip', "AND feed='' and spider=''", false );
				break;
			case 'Top Days - Pageviews':
				$replacement = nsp_get_data_query2( 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_daypages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_daypages' ) ), '', 'urlrequested', "AND feed='' and spider=''", false );
				break;
			case 'Top IPs - Pageviews':
				$replacement = nsp_get_data_query2( 'ip', __( 'Top IPs', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_ippages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_ippages' ) ), '', 'urlrequested', "AND feed='' and spider=''", false );
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


