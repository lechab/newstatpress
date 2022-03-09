<?php
/**
 * Visits functions
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
 * Visits Page to finish
 */
function nsp_display_visits_page() {
	global $pagenow;
	$visits_page_tabs = array(
		'lastvisitors' => __( 'Last visitors', 'newstatpress' ),
		'visitors'     => __( 'Visitors', 'newstatpress' ),
		'spybot'       => __( 'Spy Bot', 'newstatpress' ),
	);
	$page             = 'nsp-visits';

	print "<div class='wrap'><h2>" . esc_html__( 'Visits', 'newstatpress' ) . '</h2>';

	if ( isset( $_GET['tab'] ) ) {
		nsp_display_tabs_navbar_for_menu_page( $visits_page_tabs, sanitize_text_field( wp_unslash( $_GET['tab'] ) ), $page );
	} else {
		nsp_display_tabs_navbar_for_menu_page( $visits_page_tabs, 'lastvisitors', $page );
	}

	if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && $_GET['page'] === $page ) {

		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		} else {
			$tab = 'lastvisitors';
		}

		switch ( $tab ) {

			case 'lastvisitors':
				nsp_spy();
				break;

			case 'visitors':
				nsp_new_spy();
				break;

			case 'spybot':
				nsp_spy_bot();
				break;
		}
	}
}

/**
 * Get page period taken in statpress-visitors
 */
function newstatpress_page_periode() {
	// pp is the display page periode.
	if ( isset( $_GET['pp'] ) ) {
		// Get Current page periode from URL.
		$periode = intval( $_GET['pp'] );
		if ( $periode <= 0 ) {
			// Periode is less than 0 then set it to 1.
			$periode = 1;
		}
	} else {      // URL does not show the page set it to 1.
		$periode = 1;
	}
	return $periode;
}

/**
 * Get page post taken in statpress-visitors
 *
 * @return page
 ******************************************/
function newstatpress_page_posts() {
	global $wpdb;
	// pa is the display pages Articles.
	if ( isset( $_GET['pa'] ) ) {
		// Get Current page Articles from URL.
		$page_a = intval( $_GET['pa'] );
		if ( $page_a <= 0 ) {
			// Article is less than 0 then set it to 1.
			$page_a = 1;
		}
	} else {      // URL does not show the Article set it to 1.
		$page_a = 1;
	}
	return $page_a;
}


/**
 * New spy bot function taken in statpress-visitors
 */
function nsp_spy_bot() {
	global $wpdb;
	global $newstatpress_dir;

	$action     = 'spybot';
	$table_name = NSP_TABLENAME;

	$limit       = get_option( 'newstatpress_bot_per_page_spybot' );
	$limit_proof = get_option( 'newstatpress_visits_per_bot_spybot' );

	if ( 0 === $limit ) {
		$limit = 10;
	}
	if ( 0 === $limit_proof ) {
		$limit_proof = 30;
	}

	$pa          = newstatpress_page_posts();
	$limit_value = ( $pa * $limit ) - $limit;

	// limit the search 7 days ago.
	$day_ago = gmdate( 'Ymd', current_time( 'timestamp' ) - 7 * 86400 );
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$min_id = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT min(id) as MinId
       FROM `$table_name`
       WHERE date > %s
      ",
			$day_ago
		)
	); // phpcs:ignore: unprepared SQL OK.

	// Number of distinct spiders after $day_ago
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$num = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT count(distinct spider)
      FROM `$table_name`
      WHERE
       spider<>'' AND
       id > %d
   ",
			$min_id
		)
	); // phpcs:ignore: unprepared SQL OK.
	$na  = ceil( $num / $limit );

	echo '<br />';

	// selection of spider, group by spider, order by most recently visit (last id in the table)
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$qry = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
    FROM `$table_name` as T1
    JOIN
    (SELECT spider,max(id) as MaxId
     FROM `$table_name`
     WHERE spider<>''
     GROUP BY spider
     ORDER BY MaxId
     DESC LIMIT %d, %d
    ) as T2
    ON T1.spider = T2.spider
    WHERE T1.id > %d
    ORDER BY MaxId DESC, id DESC
  ",
			$limit_value,
			$limit,
			$min_id
		)
	); // phpcs:ignore: unprepared SQL OK.

	echo '<div align="center">';
	newstatpress_print_pp_pa_link( 0, 0, $action, $na, $pa );
	echo '</div><div align="left">';
	?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
<table id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4"><div align='left'>
	<?php
	$spider  = 'robot';
	$num_row = 0;
	$robot   = '';
	foreach ( $qry as $rk ) {  // Bot Spy.
		if ( $robot !== $rk->spider ) {
			echo "<div align='left'>
            <tr>
            <td colspan='2' bgcolor='#dedede'>";
			$img   = str_replace( ' ', '_', strtolower( $rk->spider ) );
			$img   = str_replace( '.', '', $img ) . '.png';
			$lines = file( $newstatpress_dir . '/def/spider.dat' );
			foreach ( $lines as $line_num => $spider ) { // seeks the tooltip corresponding to the photo.
				list($title,$id) = explode( '|', $spider );
				if ( $title === $rk->spider ) {
					break; // break, the tooltip ($title) is found.
				}
			}
			echo "<IMG class='img_os' style='align:left;' alt='" . esc_attr( $title ) . "' title='" . esc_attr( $title ) . "' SRC='" . esc_attr( plugins_url( '../images/spider/' . $img, __FILE__ ) ) . "'>
            <span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;' onClick=ttogle('" . esc_attr( $img ) . "');>http more info</span>
            <div id='" . esc_attr( $img ) . "' name='" . esc_attr( $img ) . "'><br /><small>" . esc_attr( $rk->ip ) . '</small><br><small>' . esc_attr( $rk->agent ) . "<br /></small></div>
            <script>document.getElementById('" . esc_html( $img ) . "').style.display='none';</script>
            </tr>
            <tr><td valign='top' width='170'><div><font size='1' color='#3B3B3B'><strong>" . esc_html( newstatpress_hdate( $rk->date ) ) . ' ' . esc_html( $rk->time ) . '</strong></font></div></td>
            <td><div>' . esc_html( newstatpress_decode( $rk->urlrequested ) ) . '</div></td></tr>';
			$robot   = $rk->spider;
			$num_row = 1;
		} elseif ( $num_row < $limit_proof ) {
			echo "<tr>
              <td valign='top' width='170'><div><font size='1' color='#3B3B3B'><strong>" . esc_html( newstatpress_hdate( $rk->date ) ) . ' ' . esc_html( $rk->time ) . '</strong></font></div></td>
              <td><div>' . esc_html( newstatpress_decode( $rk->urlrequested ) ) . '</div></td></tr>';
			++$num_row;
		}
		echo "</div></td></tr>\n";
	}
	echo '</table>';
	newstatpress_print_pp_pa_link( 0, 0, $action, $na, $pa );
	echo '</div>';
}


/**
 * Newstatpress spy function
 */
function nsp_spy() {
	global $wpdb;
	global $newstatpress_dir;

	$table_name = NSP_TABLENAME;

	// Spy.
	$today     = gmdate( 'Ymd', current_time( 'timestamp' ) );
	$yesterday = gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 );
	echo '<br />';
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$qry = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ip,nation,os,browser,agent
      FROM `$table_name`
      WHERE
        spider='' AND
        feed='' AND
        date BETWEEN %s AND %s
      GROUP BY ip ORDER BY id DESC LIMIT 20
      ",
			$yesterday,
			$today
		)
	); // phpcs:ignore: unprepared SQL OK.

	?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
<div>
<table id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4">
	<?php
	foreach ( $qry as $rk ) {
		print "<tr><td colspan='2' bgcolor='#dedede'><div align='left'>";

		if ( '' !== $rk->nation ) {
			// the nation exist.
			$img   = strtolower( $rk->nation ) . '.png';
			$lines = file( $newstatpress_dir . '/def/domain.dat' );
			foreach ( $lines as $line_num => $nation ) {
				list($title,$id) = explode( '|', $nation );
				if ( $id === $rk->nation ) {
					break;
				}
			}
			echo "<IMG style='border:0px;height:16px;' alt='" . esc_attr( $title ) . "' title='" . esc_attr( $title ) . "' SRC='" . esc_attr( plugins_url( '../images/domain/' . $img, __FILE__ ) ) . "'>  ";
		} else {
			$response = wp_remote_request( 'https://api.hostip.info/country.php?ip=' . $rk->ip );
			$output   = wp_remote_retrieve_body( $response );
			$output  .= '.png';
			$output   = strtolower( $output );
			echo "<IMG style='border:0px;width:18;height:12px;' alt='hostip' title='hostip' SRC='" . esc_attr( plugins_url( '../images/domain/' . $output, __FILE__ ) ) . "'>  ";
		}

		print "<strong><span><font size='2' color='#7b7b7b'>" . esc_attr( $rk->ip ) . '</font></span></strong> ';
		print "<span class='visits-details' onClick=ttogle('" . esc_attr( $rk->ip ) . "');>" . esc_html__( 'more info', 'newstatpress' ) . '</span></div>';
		print "<div id='" . esc_attr( $rk->ip ) . "' name='" . esc_attr( $rk->ip ) . "'>";
		if ( 'checked' !== get_option( 'newstatpress_cryptip' ) ) {
			print "<br><iframe class='visit-iframe' scrolling='no' marginwidth=0 marginheight=0 src=http://api.hostip.info/get_html.php?ip=" . esc_html( $rk->ip ) . '></iframe>';
		}
		print '<br><small><span>OS or device:</span> ' . esc_html( $rk->os ) . '</small>';
		if ( filter_var( $rk->ip, FILTER_VALIDATE_IP ) ) {
			print '<br><small><span>DNS Name:</span> ' . esc_html( gethostbyaddr( $rk->ip ) ) . '</small>';
		}
		print '<br><small><span>Browser:</span> ' . esc_html( $rk->browser ) . '</small>';
		print '<br><small><span>Browser Detail:</span> ' . esc_html( $rk->agent ) . '</small>';
		print '<br><br></div>';
		print "<script>document.getElementById('" . esc_html( $rk->ip ) . "').style.display='none';</script>";
		print '</td></tr>';
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry2 = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
      FROM `$table_name`
      WHERE
        ip= %s AND
        (date BETWEEN %s AND %s)
      ORDER BY id
      LIMIT 10
     ",
				$rk->ip,
				$yesterday,
				$today
			)
		); // phpcs:ignore: unprepared SQL OK.
		foreach ( $qry2 as $details ) {
			print '<tr>';
			print "<td valign='top' width='151'><div><font size='1' color='#3B3B3B'><strong>" . esc_html( nsp_hdate( $details->date ) ) . ' ' . esc_html( $details->time ) . '</strong></font></div></td>';
			print "<td><div><a href='" . esc_attr( get_bloginfo( 'url' ) ) . '/?' . esc_attr( filter_var( $details->urlrequested, FILTER_SANITIZE_URL ) ) . "' target='_blank'>" . esc_html( nsp_decode_url( $details->urlrequested ) ) . '</a>';

			$details->referrer = filter_var( $details->referrer, FILTER_SANITIZE_URL );

			if ( '' !== $details->searchengine ) {
				print '<br><small>' . esc_html__( 'arrived from', 'newstatpress' ) . ' <b>' . esc_html( $details->searchengine ) . '</b> ' . esc_html__( 'searching', 'newstatpress' ) . " <a href='" . esc_html( $details->referrer ) . "' target='_blank'>" . esc_html( $details->search ) . '</a></small>';
			} elseif ( '' !== $details->referrer && strpos( $details->referrer, get_option( 'home' ) ) === false ) {
				print '<br><small>' . esc_html__( 'arrived from', 'newstatpress' ) . " <a href='" . esc_attr( $details->referrer ) . "' target='_blank'>" . esc_html( $details->referrer ) . '</a></small>';
			}
			print '</div></td>';
			print "</tr>\n";
		}
	}
	?>
</table>
</div>
	<?php
}

/**
 * New spy function taken in statpress-visitors
 */
function nsp_new_spy() {
	global $wpdb;
	global $newstatpress_dir;
	$action     = 'newspy';
	$table_name = NSP_TABLENAME;

	// number of IP or bot by page.
	$limit       = get_option( 'newstatpress_ip_per_page_newspy' );
	$limit_proof = get_option( 'newstatpress_visits_per_ip_newspy' );
	if ( 0 === $limit ) {
		$limit = 20;
	}
	if ( 0 === $limit_proof ) {
		$limit_proof = 20;
	}

	$pp = newstatpress_page_periode();

	// Number of distinct ip (unique visitors)
	// no need prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$num_ip      = $wpdb->get_var(
		"SELECT count(distinct ip)
       FROM `$table_name`
       WHERE spider=''"
	); // phpcs:ignore: unprepared SQL OK.
	$np          = ceil( $num_ip / $limit );
	$limit_value = ( $pp * $limit ) - $limit;

	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$qry = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
    FROM `$table_name` as T1
    JOIN
      (SELECT max(id) as MaxId,min(id) as MinId,ip, nation
       FROM `$table_name`
       WHERE spider=''
       GROUP BY ip
       ORDER BY MaxId
       DESC LIMIT %d, %d ) as T2
    ON T1.ip = T2.ip
    WHERE id BETWEEN MinId AND MaxId
    ORDER BY MaxId DESC, id DESC
   ",
			$limit_value,
			$limit
		)
	); // phpcs:ignore: unprepared SQL OK. 

	?>
<script>
function ttogle(thediv){
if (document.getElementById(thediv).style.display=="inline") {
document.getElementById(thediv).style.display="none"
} else {document.getElementById(thediv).style.display="inline"}
}
</script>
	<?php
	$ip      = 0;
	$num_row = 0;
	echo "<div id='paginating' align='center' class='pagination'>";
	newstatpress_print_pp_link( $np, $pp, $action );
	echo '</div><table id="mainspytab" name="mainspytab" width="99%" border="0" cellspacing="0" cellpadding="4">';
	foreach ( $qry as $rk ) {
		// Visitors.
		if ( $ip !== $rk->ip ) {
			// this is the first time these ip appear, print informations.
			echo "<tr><td colspan='2' bgcolor='#dedede'><div align='left'>";

			$title = '';
			$id    = '';
			if ( '' !== $rk->nation ) {
				// the nation exist.
				$img = strtolower( $rk->nation ) . '.png';

				$lines = file( $newstatpress_dir . '/def/domain.dat' );
				foreach ( $lines as $line_num => $nation ) {
					list($title,$id) = explode( '|', $nation );
					if ( $id === $rk->nation ) {
						break;
					}
				}
				print '' . esc_html__( 'Http domain', 'newstatpress' ) . " <IMG class='img_os' alt='" . esc_attr( $title ) . "' title='" . esc_attr( $title ) . "' SRC='" . esc_attr( plugins_url( '../images/domain/' . $img, __FILE__ ) ) . "'>  ";

			} else {
				$response = wp_remote_request( 'https://api.hostip.info/country.php?ip=' . $rk->ip );
				$output   = wp_remote_retrieve_body( $response );
				$output  .= '.png';
				$output   = strtolower( $output );

				print '' . esc_html__( 'Hostip country', 'newstatpress' ) . "<IMG style='border:0px;width:18;height:12px;' alt='" . esc_attr( $title ) . "' title='" . esc_attr( $title ) . "' SRC='" . esc_attr( plugins_url( '../images/domain/' . $output, __FILE__ ) ) . "'>  ";
			}

			print "<strong><span><font size='2' color='#7b7b7b'>" . esc_html( $rk->ip ) . '</font></span></strong> ';
			print "<span style='color:#006dca;cursor:pointer;border-bottom:1px dotted #AFD5F9;font-size:8pt;' onClick=ttogle('" . esc_html( $rk->ip ) . "');>" . esc_html__( 'more info', 'newstatpress' ) . '</span></div>';
			print "<div id='" . esc_attr( $rk->ip ) . "' name='" . esc_attr( $rk->ip ) . "'>";

			if ( get_option( 'newstatpress_cryptip' ) !== 'checked' ) {
				print "<br><iframe style='overflow:hidden;border:0px;width:100%;height:60px;font-family:helvetica;padding:0;' scrolling='no' marginwidth=0 marginheight=0 src=http://api.hostip.info/get_html.php?ip=" . esc_attr( $rk->ip ) . '></iframe>';
			}
			print "<br><small><span style='font-weight:700;'>OS or device:</span> " . esc_html( $rk->os ) . '</small>';
			print "<br><small><span style='font-weight:700;'>DNS Name:</span> " . esc_html( gethostbyaddr( $rk->ip ) ) . '</small>';
			print "<br><small><span style='font-weight:700;'>Browser:</span> " . esc_html( $rk->browser ) . '</small>';
			print "<br><small><span style='font-weight:700;'>Browser Detail:</span> " . esc_html( $rk->agent ) . '</small>';
			print '<br><br></div>';
			print "<script>document.getElementById('" . esc_html( $rk->ip ) . "').style.display='none';</script>";
			print '</td></tr>';

			// sanitize if present javascript in DB.
			$rk->referrer = filter_var( $rk->referrer, FILTER_SANITIZE_URL );

			echo "<td valign='top' width='151'><div><font size='1' color='#3B3B3B'><strong>" . esc_html( newstatpress_hdate( $rk->date ) ) . ' ' . esc_html( $rk->time ) . '</strong></font></div></td>
              <td>' . esc_html( newstatpress_decode( $rk->urlrequested ) ) . '';
			if ( '' !== $rk->searchengine ) {
				print '<br><small>' . esc_html__( 'arrived from', 'newstatpress' ) . ' <b>' . esc_html( $rk->searchengine ) . '</b> ' . esc_html__( 'searching', 'newstatpress' ) . " <a href='" . esc_attr( $rk->referrer ) . "' target=_blank>" . esc_html( urldecode( $rk->search ) ) . '</a></small>';
			} elseif ( '' !== $rk->referrer && strpos( $rk->referrer, get_option( 'home' ) ) === false ) {
				print '<br><small>' . esc_html__( 'arrived from', 'newstatpress' ) . " <a href='" . esc_attr( $rk->referrer ) . "' target=_blank>" . esc_html( $rk->referrer ) . '</a></small>';
			}
			echo "</div></td></tr>\n";
			$ip      = $rk->ip;
			$num_row = 1;
		} elseif ( $num_row < $limit_proof ) {

			// sanitize if present javascript in DB.
			$rk->referrer = filter_var( $rk->referrer, FILTER_SANITIZE_URL );

			echo "<tr><td valign='top' width='151'><div><font size='1' color='#3B3B3B'><strong>" . esc_html( newstatpress_hdate( $rk->date ) ) . ' ' . esc_html( $rk->time ) . '</strong></font></div></td>
              <td><div>' . esc_html( newstatpress_decode( $rk->urlrequested ) ) . '';
			if ( '' !== $rk->searchengine ) {
				print '<br><small>' . esc_html__( 'arrived from', 'newstatpress' ) . ' <b>' . esc_attr( $rk->searchengine ) . '</b> ' . esc_html__( 'searching', 'newstatpress' ) . " <a href='" . esc_attr( $rk->referrer ) . "' target=_blank>" . esc_html( urldecode( $rk->search ) ) . '</a></small>';
			} elseif ( '' !== $rk->referrer && strpos( $rk->referrer, get_option( 'home' ) ) === false ) {
				print '<br><small>' . esc_html__( 'arrived from', 'newstatpress' ) . " <a href='" . esc_attr( $rk->referrer ) . "' target=_blank>" . esc_attr( $rk->referrer ) . '</a></small>';
			}
			++$num_row;
			echo "</div></td></tr>\n";
		}
	}
	echo "</div></td></tr>\n</table>";
	echo "<div id='paginating' align='center' class='pagination'>";
	newstatpress_print_pp_link( $np, $pp, $action );
	echo '</div></div>';
}

/**
 * Get true if permalink is enabled in WordPress
 * (taken in statpress-visitors)
 *
 * @return true if permalink is enabled in WordPress
 ***************************************************/
function nsp_permalinks_enabled() {
	global $wpdb;

	// no needs prepare.
	$result = $wpdb->get_row( 'SELECT `option_value` FROM `' . $wpdb->prefix . 'options` WHERE `option_name` = "permalink_structure"' ); // db call ok; no-cache ok.
	if ( '' !== $result->option_value ) {
		return true;
	} else {
		return false;
	}
}


/**
 * Decode the url in a better manner
 *
 * @param string $out_url the url.
 * @return url decoded
 ************************************/
function newstatpress_decode( $out_url ) {
	$out_url = filter_var( $out_url, FILTER_SANITIZE_URL );

	if ( ! nsp_permalinks_enabled() ) {
		if ( '' === $out_url ) {
			$out_url = __( 'Page', 'newstatpress' ) . ': Home';
		}
		if ( nsp_my_substr( $out_url, 0, 4 ) === 'cat=' ) {
			$out_url = __( 'Category', 'newstatpress' ) . ': ' . get_cat_name( nsp_my_substr( $out_url, 4 ) );
		}
		if ( nsp_my_substr( $out_url, 0, 2 ) === 'm=' ) {
			$out_url = __( 'Calendar', 'newstatpress' ) . ': ' . nsp_my_substr( $out_url, 6, 2 ) . '/' . nsp_my_substr( $out_url, 2, 4 );
		}
		if ( nsp_my_substr( $out_url, 0, 2 ) === 's=' ) {
			$out_url = __( 'Search', 'newstatpress' ) . ': ' . nsp_my_substr( $out_url, 2 );
		}
		if ( nsp_my_substr( $out_url, 0, 2 ) === 'p=' ) {
			$sub_out   = nsp_my_substr( $out_url, 2 );
			$post_id_7 = get_post( $sub_out, ARRAY_A );
			$out_url   = $post_id_7['post_title'];
		}
		if ( nsp_my_substr( $out_url, 0, 8 ) === 'page_id=' ) {
			$sub_out   = nsp_my_substr( $out_url, 8 );
			$post_id_7 = get_page( $sub_out, ARRAY_A );
			$out_url   = __( 'Page', 'newstatpress' ) . ': ' . $post_id_7['post_title'];
		}
	} else {
		if ( '' === $out_url ) {
			$out_url = __( 'Page', 'newstatpress' ) . ': Home';
		} elseif ( nsp_my_substr( $out_url, 0, 9 ) === 'category/' ) {
			$out_url = __( 'Category', 'newstatpress' ) . ': ' . get_cat_name( nsp_my_substr( $out_url, 9 ) );
		} elseif ( nsp_my_substr( $out_url, 0, 2 ) === 's=' ) {
			$out_url = __( 'Search', 'newstatpress' ) . ': ' . nsp_my_substr( $out_url, 2 );
		} elseif ( nsp_my_substr( $out_url, 0, 2 ) === 'p=' ) {
				// not working yet.
				$sub_out   = nsp_my_substr( $out_url, 2 );
				$post_id_7 = get_post( $sub_out, ARRAY_A );
				$out_url   = $post_id_7['post_title'];
		} elseif ( nsp_my_substr( $out_url, 0, 8 ) === 'page_id=' ) {
				// not working yet.
				$sub_out   = nsp_my_substr( $out_url, 8 );
				$post_id_7 = get_page( $sub_out, ARRAY_A );
				$out_url   = __( 'Page', 'newstatpress' ) . ': ' . $post_id_7['post_title'];
		}
	}
	return $out_url;
}

/**
 * Display links for group of pages
 *
 * @param int    $np the group of pages.
 * @param int    $pp the page to show.
 * @param string $action the action.
 *
 * TODO change print into return $result.
 */
function newstatpress_print_pp_link( $np, $pp, $action ) {
	// For all pages ($np) Display first 3 pages, 3 pages before current page($pp), 3 pages after current page , each 25 pages and the 3 last pages for($action).
	$guil1 = false;
	$guil2 = false;// suspension points  not writed  style='border:0px;width:16px;height:16px;   style="border:0px;width:16px;height:16px;".
	if ( $np > 1 ) {
		for ( $i = 1; $i <= $np; $i++ ) {
			if ( $i <= $np ) {
				// $page is not the last page.
				if ( $i === $pp ) {
					echo " <span class='current'>" . esc_html( "{$i}" ) . ' </span> '; // $page is current page.
				} else {
					// Not the current page Hyperlink them.
					if ( ( $i <= 3 ) || ( ( $i >= $pp - 3 ) && ( $i <= $pp + 3 ) ) || ( $i >= $np - 3 ) || is_int( $i / 100 ) ) {
						echo '<a href="?page=nsp-visits&tab=visitors&newstatpress_action=' . esc_attr( $action ) . '&pp=' . esc_attr( $i ) . '">' . esc_attr( $i ) . '</a> ';
					} else {

						if ( ( false === $guil1 ) || ( $i === $pp + 4 ) ) {
							echo '...';
							$guil1 = true;
						}
						if ( $i === $pp - 4 ) {
							echo '..';
						}
						if ( is_int( ( $i - 1 ) / 100 ) ) {
							echo '.';
						}
						if ( $i === $np - 4 ) {
							echo '..';
						}
						// suspension points writed.

					}
				}
			}
		}
	}
}
/**
 * Display links for group of pages
 *
 * @param int    $np the group of pages.
 * @param int    $pp the page to show.
 * @param string $action the action.
 * @param int    $na group.
 * @param int    $pa current page.
 *
 *    TODO change print into return $result.
 */
function newstatpress_print_pp_pa_link( $np, $pp, $action, $na, $pa ) {
	if ( 0 !== $np ) {
		newstatpress_print_pp_link( $np, $pp, $action );
	}

	// For all pages ($np) display first 5 pages, 3 pages before current page($pa), 3 pages after current page , 3 last pages.
	$guil1 = false;// suspension points not writed.
	$guil2 = false;

	echo '<table width="100%" border="0"><tr></tr></table>';
	if ( $na > 1 ) {
		echo "<font size='1'>" . esc_html__( 'Pages', 'newstatpress' ) . ' : </font>';
		for ( $j = 1; $j <= $na; $j++ ) {
			if ( $j <= $na ) {  // $i is not the last Articles page.
				if ( $j === $pa ) {  // $i is current page.
					echo esc_html( " [{$j}] " );
				} else { // Not the current page Hyperlink them.
					if ( ( $j <= 5 ) || ( ( $j >= $pa - 2 ) && ( $j <= $pa + 2 ) ) || ( $j >= $na - 2 ) ) {
						echo '<a href="?page=newstatpress/newstatpress.php&newstatpress_action=' . esc_attr( $action ) . '&pp=' . esc_attr( $pp ) . '&pa=' . esc_attr( $j ) . '">' . esc_attr( $j ) . '</a> ';
					} else {
						if ( false === $guil1 ) {
							echo '... ';
						} $guil1 = true;
						if ( ( $j === $pa + 4 ) && ( false === $guil2 ) ) {
							echo ' ... ';
							$guil2 = true;
						}
						// suspension points writed.
					}
				}
			}
		}
	}
}


?>
