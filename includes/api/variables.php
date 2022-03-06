<?php
/**
 * Get variables with the nsp_variables_ajax
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
 * Ajax routine for getting variables values
 */
function nsp_variables_ajax() {
	global $wpdb;
	global $nsp_option_vars;
	$table_name = "{$wpdb->prefix}statpress";

	// response output.
	header( 'Content-Type: application/json' );

	// check to see if the submitted nonce matches with the
	// generated nonce we created earlier.
	if ( ! ( isset( $_POST['postCommentNonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['postCommentNonce'] ) ), 'newstatpress-nsp_variables-nonce' ) ) ) {
		die( 'Busted!' );
	}

	// get the submitted parameters.
	if ( isset( $_POST['VAR'] ) ) {
		$var = sanitize_text_field( wp_unslash( $_POST['VAR'] ) );
	} else {
		die( 'no var' );
	}

	$offsets = get_option( $nsp_option_vars['stats_offsets']['name'] );

	// test all vars.
	if ( 'alltotalvisits' === $var ) {
		// no need prepare.
		// phpcs:ignore -- db call ok; no-cache ok
		$qry = $wpdb->get_results(
			"SELECT count(distinct urlrequested, ip) AS pageview
				FROM `$table_name` AS t1
				WHERE
				spider='' AND
				feed='' AND
				urlrequested!=''
				"
		); // phpcs:ignore: unprepared SQL OK.
		if ( isset( $offsets['pageviews'] ) ) {
			$num = $offsets['pageviews'];
		} else {
			$num = 0;
		}
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview + $num );
		} else {
			echo wp_json_encode( $num );
		}
	} elseif ( 'visits' === $var ) {
			// no need prepare.
			// phpcs:ignore -- db call ok; no-cache ok
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(DISTINCT(ip)) AS pageview
					FROM `$table_name`
					WHERE
					date = %s AND
					spider='' and feed=''
					",
					gmdate( 'Ymd', current_time( 'timestamp' ) )
				)
			); // phpcs:ignore: unprepared SQL OK.
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'yvisits' === $var ) {
			// no need prepare.
			// phpcs:ignore -- db call ok; no-cache ok
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(DISTINCT(ip)) AS pageview
					FROM `$table_name`
					WHERE
					date = %s AND
					spider='' and feed=''
					",
					gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 )
				)
			); // phpcs:ignore: unprepared SQL OK.
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'mvisits' === $var ) {
		if ( get_option( $nsp_option_vars['calculation']['name'] ) === 'sum' ) {
			// no need prepare.
			// phpcs:ignore -- db call ok; no-cache ok
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT SUM(pagv) AS pageview FROM (
					SELECT count(DISTINCT(ip)) AS pagv
					FROM `$table_name`
					WHERE
						DATE >= DATE_FORMAT(CURDATE(), %s) AND
						spider='' and feed=''
					GROUP BY DATE
					) AS pageview
					",
					'%Y%m01'
				)
			); // phpcs:ignore: unprepared SQL OK.
		} else {
				// no need prepare.
				// phpcs:ignore -- db call ok; no-cache ok
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT count(DISTINCT(ip)) AS pageview
						FROM `$table_name`
						WHERE
							DATE >= DATE_FORMAT(CURDATE(), %s) AND
							spider='' and feed=''
						",
						'%Y%m01'
					)
				); // phpcs:ignore: unprepared SQL OK.
		}
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'wvisits' === $var ) {
		if ( get_option( $nsp_option_vars['calculation']['name'] ) === 'sum' ) {
				// phpcs:ignore -- db call ok; no-cache ok
				$qry = $wpdb->get_results(
					"SELECT SUM(pagv) AS pageview FROM (
						SELECT count(DISTINCT(ip)) AS pagv
						FROM `$table_name`
						WHERE
							YEARWEEK (date) = YEARWEEK( CURDATE()) AND
							spider='' and feed=''
						GROUP BY DATE
						) AS pageview
						"
				); // phpcs:ignore: unprepared SQL OK.
		} else {
				// no need prepare.
				// phpcs:ignore -- db call ok; no-cache ok
				$qry = $wpdb->get_results(
					"SELECT count(DISTINCT(ip)) AS pageview
						FROM `$table_name`
						WHERE
							YEARWEEK (date) = YEARWEEK( CURDATE()) AND
							spider='' and feed=''
						"
				); // phpcs:ignore: unprepared SQL OK. 
		}
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'totalvisits' === $var ) {
		if ( get_option( $nsp_option_vars['calculation']['name'] ) === 'sum' ) {
				// phpcs:ignore -- db call ok; no-cache ok
				$qry = $wpdb->get_results(
					"SELECT SUM(pagv) AS pageview FROM (
						SELECT count(DISTINCT(ip)) AS pagv
						FROM `$table_name`
						WHERE
							spider='' AND
							feed=''
						GROUP BY DATE
						) AS pageview
						"
				);  // phpcs:ignore: unprepared SQL OK. 
		} else {
				// no need prepare.
				// phpcs:ignore -- db call ok; no-cache ok
				$qry = $wpdb->get_results(
					"SELECT count(DISTINCT(ip)) AS pageview
						FROM `$table_name`
						WHERE
							spider='' AND
							feed=''
						"
				); // phpcs:ignore: unprepared SQL OK. 
		}
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'totalpageviews' === $var ) {
			// phpcs:ignore -- db call ok; no-cache ok
			$qry = $wpdb->get_results(
				"SELECT count(id) AS pageview
					FROM `$table_name`
					WHERE
						spider='' AND
						feed=''
					"
			); // phpcs:ignore: unprepared SQL OK. 
		if ( isset( $offsets['pageviews'] ) ) {
			$num = $offsets['pageviews'];
		} else {
			$num = 0;
		}
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview + $num );
		} else {
			wp_json_encode( $num );
		}
	} elseif ( 'todaytotalpageviews' === $var ) {
			// phpcs:ignore -- db call ok; no-cache ok.
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(id) AS pageview
					FROM `$table_name`
					WHERE
						date=%s AND
						spider='' AND
						feed=''
					",
					gmdate( 'Ymd', current_time( 'timestamp' ) )
				)
			); // phpcs:ignore: unprepared SQL OK. 
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'thistotalvisits' === $var ) {
		if ( isset( $_REQUEST['URL'] ) ) {
			$url = esc_url_raw( wp_unslash( $_REQUEST['URL'] ) );
		}

		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT count(DISTINCT(ip)) AS pageview
				FROM `$table_name`
				WHERE
					spider='' AND
					feed='' AND
					urlrequested=%s
				",
				$url
			)
		); // phpcs:ignore: unprepared SQL OK. 
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'monthtotalpageviews' === $var ) {
			// phpcs:ignore -- db call ok; no-cache ok.
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(id) AS pageview
					FROM `$table_name`
					WHERE
					DATE >= DATE_FORMAT(CURDATE(), %s) AND
						spider='' and feed=''
					",
					'%Y%m01'
				)
			); // phpcs:ignore: unprepared SQL OK.
		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'widget_topposts' === $var ) {
		if ( isset( $_REQUEST['LIMIT'] ) ) {
			$limit = intval( $_REQUEST['LIMIT'] );
		}

		if ( isset( $_REQUEST['FLAG'] ) ) {
			$showcounts = preg_replace( '/[^a-zA-Z]+/', '', sanitize_text_field( wp_unslash( $_REQUEST['FLAG'] ) ) );
		}

		$res = "\n<ul>\n";
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$qry = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT urlrequested,count(*) as totale
				FROM `$table_name`
				WHERE
					spider='' AND
					feed='' AND
					urlrequested LIKE %s
				GROUP BY urlrequested
				ORDER BY totale DESC LIMIT %d
				",
				'%p=%',
				$limit
			)
		); // phpcs:ignore: unprepared SQL OK.
		foreach ( $qry as $rk ) {
			$res .= "<li><a href='?" . $rk->urlrequested . "' target='_blank'>" . nsp_decode_url( $rk->urlrequested ) . "</a></li>\n";
			if ( 'checked' === strtolower( $showcounts ) ) {
				$res .= ' (' . $rk->totale . ')';
			}
		}
		echo wp_json_encode( "$res</ul>\n" );
	}

	wp_die();
}
