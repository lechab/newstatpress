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
	$table_name = $wpdb->prefix . 'statpress';

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
		$qry = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT count(distinct urlrequested, ip) AS pageview
				FROM %s AS t1
				WHERE
				spider='' AND
				feed='' AND
				urlrequested!='';
				",
				$table_name
			)
		); // db call ok; no-cache ok.
		if ( null !== $qry ) {
			echo wp_json_encode( $qry[0]->pageview + $offsets['alltotalvisits'] );
		}
	} elseif ( 'visits' === $var ) {
			// no need prepare.
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(DISTINCT(ip)) AS pageview
					FROM %s
					WHERE
					date = %s AND
					spider='' and feed='';
					",
					$table_name,
					gmdate( 'Ymd', current_time( 'timestamp' ) )
				)
			); // db call ok; no-cache ok.
		if ( null !== $qry ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'yvisits' === $var ) {
			// no need prepare.
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(DISTINCT(ip)) AS pageview
					FROM %s
					WHERE
					date = %s AND
					spider='' and feed='';
					",
					$table_name,
					gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 )
				)
			); // db call ok; no-cache ok.
		if ( null !== $qry ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'mvisits' === $var ) {
		if ( get_option( $nsp_option_vars['calculation']['name'] ) === 'sum' ) {
			// no need prepare.
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT SUM(pagv) AS pageview FROM (
					SELECT count(DISTINCT(ip)) AS pagv
					FROM %s
					WHERE
						DATE >= DATE_FORMAT(CURDATE(), %s) AND
						spider='' and feed=''
					GROUP BY DATE
					) AS pageview;
					",
					$table_name,
					'%Y%m01'
				)
			); // db call ok; no-cache ok.
		} else {
				// no need prepare.
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT count(DISTINCT(ip)) AS pageview
						FROM %s
						WHERE
							DATE >= DATE_FORMAT(CURDATE(), %s) AND
							spider='' and feed='';
						",
						$table_name,
						'%Y%m01'
					)
				); // db call ok; no-cache ok.
		}
		if ( null !== $qry ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'wvisits' === $var ) {
		if ( get_option( $nsp_option_vars['calculation']['name'] ) === 'sum' ) {
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT SUM(pagv) AS pageview FROM (
						SELECT count(DISTINCT(ip)) AS pagv
						FROM %s
						WHERE
							YEARWEEK (date) = YEARWEEK( CURDATE()) AND
							spider='' and feed=''
						GROUP BY DATE
						) AS pageview;
						",
						$table_name
					)
				); // db call ok; no-cache ok.
		} else {
				// no need prepare.
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT count(DISTINCT(ip)) AS pageview
						FROM %s
						WHERE
							YEARWEEK (date) = YEARWEEK( CURDATE()) AND
							spider='' and feed='';
						",
						$table_name
					)
				); // db call ok; no-cache ok.
		}
		if ( null !== $qry ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'totalvisits' === $var ) {
		if ( get_option( $nsp_option_vars['calculation']['name'] ) === 'sum' ) {
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT SUM(pagv) AS pageview FROM (
						SELECT count(DISTINCT(ip)) AS pagv
						FROM %s
						WHERE
							spider='' AND
							feed=''
						GROUP BY DATE
						) AS pageview;
						",
						$table_name
					)
				); // db call ok; no-cache ok.
		} else {
				// no need prepare.
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT count(DISTINCT(ip)) AS pageview
						FROM %s
						WHERE
							spider='' AND
							feed='';
						",
						$table_name
					)
				); // db call ok; no-cache ok.
		}
		if ( null !== $qry ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'totalpageviews' === $var ) {
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(id) AS pageview
					FROM %s
					WHERE
						spider='' AND
						feed='';
					",
					$table_name
				)
			); // db call ok; no-cache ok.
		if ( null !== $qry ) {
			echo wp_json_encode( $qry[0]->pageview + $offsets['pageviews'] );
		}
	} elseif ( 'todaytotalpageviews' === $var ) {
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(id) AS pageview
					FROM %s
					WHERE
						date=%s AND
						spider='' AND
						feed='';
					",
					$table_name,
					gmdate( 'Ymd', current_time( 'timestamp' ) )
				)
			); // db call ok; no-cache ok.
		if ( null !== $qry ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'thistotalvisits' === $var ) {
		if ( isset( $_REQUEST['URL'] ) ) {
			$url = esc_url_raw( wp_unslash( $_REQUEST['URL'] ) );
		}

		// use prepare.
		$qry = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT count(DISTINCT(ip)) AS pageview
				FROM %s
				WHERE
					spider='' AND
					feed='' AND
					urlrequested=%s';
				",
				$table_name,
				$url
			)
		); // db call ok; no-cache ok.
		if ( null !== $qry ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'monthtotalpageviews' === $var ) {
			$qry = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT count(id) AS pageview
					FROM %s
					WHERE
					DATE >= DATE_FORMAT(CURDATE(), %s) AND
						spider='' and feed='';
					",
					$table_name,
					'%Y%m01'
				)
			); // db call ok; no-cache ok.
		if ( null !== $qry ) {
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
		$qry = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT urlrequested,count(*) as totale
				FROM %s
				WHERE
					spider='' AND
					feed='' AND
					urlrequested LIKE %s
				GROUP BY urlrequested
				ORDER BY totale DESC LIMIT %d;
				",
				$table_name,
				'%p=%',
				$limit
			)
		); // db call ok; no-cache ok.
		foreach ( $qry as $rk ) {
			$res .= "<li><a href='?" . $rk->urlrequested . "' target='_blank'>" . nsp_DecodeURL( $rk->urlrequested ) . "</a></li>\n";
			if ( 'checked' === strtolower( $showcounts ) ) {
				$res .= ' (' . $rk->totale . ')';
			}
		}
		echo wp_json_encode( "$res</ul>\n" );
	}

	wp_die();
}
