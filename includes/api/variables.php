<?php
/**
 * Get variables with the newstatpress_variables_ajax
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
function newstatpress_variables_ajax() {
	global $wpdb;
	global $newstatpress_option_vars;
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

	$offsets = get_option( $newstatpress_option_vars['stats_offsets']['name'] );

	$table_literal = '`' . esc_sql( $table_name ) . '`';

	// test all vars.
	if ( 'alltotalvisits' === $var ) {
		$sql = sprintf(	"
			SELECT COUNT(DISTINCT CONCAT(urlrequested, ip)) AS pageview
			FROM %s AS t1
			WHERE
			spider = '' AND
			feed = '' AND
			urlrequested != ''",
			$table_literal
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$qry = $wpdb->get_results(
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$sql
				);

		$num = isset( $offsets['pageviews'] ) ? $offsets['pageviews'] : 0;

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview + $num );
		} else {
			echo wp_json_encode( $num );
		}
	} elseif ( 'visits' === $var ) {
		$sql = sprintf(	"
			SELECT COUNT(DISTINCT(ip)) AS pageview
			FROM %s
			WHERE
			date = %%s AND
			spider = '' AND
			feed = ''",
			$table_literal
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$prepared = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql,
			gmdate( 'Ymd', current_time( 'timestamp' ) )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$qry = $wpdb->get_results(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$prepared
				);

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'yvisits' === $var ) {
		$sql = sprintf("
			SELECT COUNT(DISTINCT(ip)) AS pageview
			FROM %s
			WHERE
				date = %%s AND
				spider = '' AND
				feed = ''",
			$table_literal
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$prepared = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql,
			gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$qry = $wpdb->get_results(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$prepared
					);

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'mvisits' === $var ) {
		if ( get_option( $newstatpress_option_vars['calculation']['name'] ) === 'sum' ) {
			$sql = sprintf(	"
				SELECT SUM(pagv) AS pageview FROM (
					SELECT COUNT(DISTINCT(ip)) AS pagv
					FROM %s
					WHERE
						DATE >= DATE_FORMAT(CURDATE(), %%s) AND
						spider = '' AND feed = ''
					GROUP BY DATE
				) AS pageview",
			$table_literal
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$prepared = $wpdb->prepare( $sql, '%Y%m01' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$qry = $wpdb->get_results(
							// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							$prepared
						);
		} else {
			$sql = sprintf(	"
				SELECT COUNT(DISTINCT(ip)) AS pageview
				FROM %s
				WHERE
				DATE >= DATE_FORMAT(CURDATE(), %%s) AND
				spider = '' AND feed = ''",
				$table_literal
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$prepared = $wpdb->prepare( $sql, '%Y%m01' );

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$qry = $wpdb->get_results(
							// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
							$prepared
						);
		}

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'wvisits' === $var ) {
		if ( get_option( $newstatpress_option_vars['calculation']['name'] ) === 'sum' ) {
			$sql = sprintf(	"
				SELECT SUM(pagv) AS pageview FROM (
					SELECT COUNT(DISTINCT(ip)) AS pagv
					FROM %s
					WHERE
						YEARWEEK(date) = YEARWEEK(CURDATE()) AND
						spider = '' AND feed = ''
					GROUP BY DATE
				) AS pageview",
				$table_literal
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$qry = $wpdb->get_results( $sql );
		} else {
			$sql = sprintf(	"
				SELECT COUNT(DISTINCT(ip)) AS pageview
				FROM %s
				WHERE
					YEARWEEK(date) = YEARWEEK(CURDATE()) AND
					spider = '' AND feed = ''",
				$table_literal
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$qry = $wpdb->get_results( $sql );
		}

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'totalvisits' === $var ) {
		if ( get_option( $newstatpress_option_vars['calculation']['name'] ) === 'sum' ) {
			$sql = sprintf(	"
				SELECT SUM(pagv) AS pageview FROM (
					SELECT COUNT(DISTINCT(ip)) AS pagv
					FROM %s
					WHERE
						spider = '' AND
						feed = ''
					GROUP BY DATE
				) AS pageview",
				$table_literal
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$qry = $wpdb->get_results( $sql );
		} else {
			$sql = sprintf(	"
				SELECT COUNT(DISTINCT(ip)) AS pageview
				FROM %s
				WHERE
					spider = '' AND
					feed = ''",
				$table_literal
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$qry = $wpdb->get_results( $sql );
		}

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'totalpageviews' === $var ) {
		$sql = sprintf(	"
			SELECT COUNT(id) AS pageview
			FROM %s
			WHERE
				spider = '' AND
				feed = ''",
			$table_literal
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$qry = $wpdb->get_results( $sql );

		$num = isset( $offsets['pageviews'] ) ? $offsets['pageviews'] : 0;

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview + $num );
		} else {
			echo wp_json_encode( $num );
		}
	} elseif ( 'todaytotalpageviews' === $var ) {
		$sql = sprintf("
			SELECT COUNT(id) AS pageview
			FROM %s
			WHERE
				date = %%s AND
				spider = '' AND
				feed = ''",
			$table_literal
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$prepared = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$sql,
			gmdate( 'Ymd', current_time( 'timestamp' ) )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$qry = $wpdb->get_results(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$prepared
				);

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'thistotalvisits' === $var ) {
		if ( isset( $_REQUEST['URL'] ) ) {
			$url = esc_url_raw( wp_unslash( $_REQUEST['URL'] ) );
		}

		$sql = sprintf(	"
			SELECT COUNT(DISTINCT(ip)) AS pageview
			FROM %s
			WHERE
				spider = '' AND
				feed = '' AND
				urlrequested = %%s",
			$table_literal
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$prepared = $wpdb->prepare( $sql, $url );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$qry = $wpdb->get_results(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$prepared
					);

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'monthtotalpageviews' === $var ) {
		$sql = sprintf(	"
			SELECT COUNT(id) AS pageview
			FROM %s
			WHERE
			DATE >= DATE_FORMAT(CURDATE(), %%s) AND
				spider = '' AND
				feed = ''",
			$table_literal
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$prepared = $wpdb->prepare( $sql, '%Y%m01' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$qry = $wpdb->get_results(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$prepared
					);

		if ( isset( $qry[0]->pageview ) ) {
			echo wp_json_encode( $qry[0]->pageview );
		}
	} elseif ( 'widget_topposts' === $var ) {
		if ( isset( $_REQUEST['LIMIT'] ) ) {
			$limit = intval( $_REQUEST['LIMIT'] );
		}

		if ( isset( $_REQUEST['FLAG'] ) ) {
			$showcounts = preg_replace(
				'/[^a-zA-Z]+/',
				'',
				sanitize_text_field( wp_unslash( $_REQUEST['FLAG'] ) )
			);
		}

		$res = "\n<ul>\n";

		$sql = sprintf(	"
			SELECT urlrequested, COUNT(*) AS totale
			FROM %s
			WHERE
				spider = '' AND
				feed = '' AND
				urlrequested LIKE %%s
			GROUP BY urlrequested
			ORDER BY totale DESC
			LIMIT %%d",
			$table_literal
		);

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$prepared = $wpdb->prepare( $sql, '%p=%', $limit );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$qry = $wpdb->get_results(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$prepared
					);

		foreach ( $qry as $rk ) {
			$res .= "<li><a href='" . esc_attr( '?' . $rk->urlrequested ) . "' target='_blank'>" .
			esc_html( newstatpress_decode_url( $rk->urlrequested ) ) .
			"</a></li>\n";

			if ( 'checked' === strtolower( $showcounts ) ) {
				$res .= ' (' . $rk->totale . ')';
			}
		}

		echo wp_json_encode( "$res</ul>\n" );
	}

	wp_die();
}
