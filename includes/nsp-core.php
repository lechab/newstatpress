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
function newstatpress_get_data_query2( $type, $fld, $fldtitle, $limit = 0, $print = true ) {
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
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
			SELECT count(date) AS rks
			FROM {$table_literal}
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'OS':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(os) AS rks
				FROM {$table_literal}
				WHERE feed='' AND spider='' AND os<>''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'BROWSER':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(browser) AS rks
				FROM {$table_literal}
				WHERE feed='' AND spider='' AND browser<>''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'FEED':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(feed) AS rks
				FROM {$table_literal}
				WHERE feed<>''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'SEARCHENGINE':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(searchengine) AS rks
				FROM {$table_literal}
				WHERE searchengine<>''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'SEARCH':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(search) AS rks
				FROM {$table_literal}
				WHERE search<>''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'REFFERER':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			$sql = sprintf(
				"
				SELECT count(referrer) AS rks
				FROM %s
				WHERE referrer<>'' AND referrer NOT LIKE %%s
				",
				$table_literal
			);

			$prepared = $wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$sql,
				'%' . get_bloginfo( 'url' ) . '%'
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $prepared );
			break;


		case 'NATION':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(nation) AS rks
				FROM {$table_literal}
				WHERE nation<>'' AND spider=''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'SPIDER':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(spider) AS rks
				FROM {$table_literal}
				WHERE spider<>''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'URLREQUESTED':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(urlrequested) AS rks
				FROM {$table_literal}
				WHERE feed='' AND spider=''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'DATE2':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(DISTINCT ip) AS rks
				FROM {$table_literal}
				WHERE feed='' AND spider=''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'DATE3':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(urlrequested) AS rks
				FROM {$table_literal}
				WHERE feed='' AND spider=''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

		case 'IP':
			$table_literal = '`' . esc_sql( $table_name ) . '`';

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$sql = "
				SELECT count(urlrequested) AS rks
				FROM {$table_literal}
				WHERE feed='' AND spider=''
			";

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$rks = $wpdb->get_var( $sql );
			break;

	}

	if ( $rks > 0 ) {
		if ( $limit > 0 ) {
			switch ( $type ) {
				case 'DATE1':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(date) AS pageview, date
						FROM %s
						GROUP BY date
						ORDER BY pageview DESC
						LIMIT %%d
						",
						$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'OS':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					// Costruisco la query con sprintf (PHPCS non la analizza come SQL)
					$sql = sprintf(
						"
						SELECT count(os) AS pageview, os
						FROM %s
						WHERE feed='' AND spider='' AND os<>''
						GROUP BY os
						ORDER BY pageview DESC
						LIMIT %%d
						",
						$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'BROWSER':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(browser) AS pageview, browser
						FROM %s
						WHERE feed='' AND spider='' AND browser<>''
						GROUP BY browser
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'FEED':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(feed) AS pageview, feed
						FROM %s
						WHERE feed<>''
						GROUP BY feed
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'SEARCHENGINE':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(searchengine) AS pageview, searchengine
						FROM %s
						WHERE searchengine<>''
						GROUP BY searchengine
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'SEARCH':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(search) AS pageview, search
						FROM %s
						WHERE search<>''
						GROUP BY search
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'REFFERER':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(referrer) AS pageview, referrer
						FROM %s
						WHERE referrer<>'' AND referrer NOT LIKE %%s
						GROUP BY referrer
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						'%' . get_bloginfo( 'url' ) . '%',
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'NATION':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(nation) AS pageview, nation
						FROM %s
						WHERE nation<>'' AND spider=''
						GROUP BY nation
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'SPIDER':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(spider) AS pageview, spider
						FROM %s
						WHERE spider<>''
						GROUP BY spider
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'URLREQUESTED':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(urlrequested) AS pageview, urlrequested
						FROM %s
						WHERE feed='' AND spider=''
						GROUP BY urlrequested
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'DATE2':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(DISTINCT ip) AS pageview, date
						FROM %s
						WHERE feed='' AND spider=''
						GROUP BY date
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'DATE3':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(urlrequested) AS pageview, date
						FROM %s
						WHERE feed='' AND spider=''
						GROUP BY date
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'IP':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(urlrequested) AS pageview, ip
						FROM %s
						WHERE feed='' AND spider=''
						GROUP BY ip
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

			}
		} else {
			switch ( $type ) {
				case 'DATE1':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(date) AS pageview, date
						FROM %s
						GROUP BY date
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'OS':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(os) AS pageview, os
						FROM %s
						WHERE feed='' AND spider='' AND os<>''
						GROUP BY os
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'BROWSER':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(browser) AS pageview, browser
						FROM %s
						WHERE feed='' AND spider='' AND browser<>''
						GROUP BY browser
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'FEED':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(feed) AS pageview, feed
						FROM %s
						WHERE feed<>''
						GROUP BY feed
						ORDER BY pageview DESC
						LIMIT %%d
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						$limit
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'SEARCHENGINE':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(searchengine) AS pageview, searchengine
						FROM %s
						WHERE searchengine<>''
						GROUP BY searchengine
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'SEARCH':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(search) AS pageview, search
						FROM %s
						WHERE search<>''
						GROUP BY search
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'REFFERER':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(referrer) AS pageview, referrer
						FROM %s
						WHERE referrer<>'' AND referrer NOT LIKE %%s
						GROUP BY referrer
						ORDER BY pageview DESC
						",
					$table_literal
					);

					$prepared = $wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$sql,
						'%' . get_bloginfo( 'url' ) . '%'
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'NATION':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(nation) AS pageview, nation
						FROM %s
						WHERE nation<>'' AND spider=''
						GROUP BY nation
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'SPIDER':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(spider) AS pageview, spider
						FROM %s
						WHERE spider<>''
						GROUP BY spider
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'URLREQUESTED':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(urlrequested) AS pageview, urlrequested
						FROM %s
						WHERE feed='' AND spider=''
						GROUP BY urlrequested
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'DATE2':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(DISTINCT ip) AS pageview, date
						FROM %s
						WHERE feed='' AND spider=''
						GROUP BY date
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'DATE3':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(urlrequested) AS pageview, date
						FROM %s
						WHERE feed='' AND spider=''
						GROUP BY date
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
					break;

				case 'IP':
					$table_literal = '`' . esc_sql( $table_name ) . '`';

					$sql = sprintf(
						"
						SELECT count(urlrequested) AS pageview, ip
						FROM %s
						WHERE feed='' AND spider=''
						GROUP BY ip
						ORDER BY pageview DESC
						",
					$table_literal
					);

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$prepared = $wpdb->prepare( $sql );

					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$qry = $wpdb->get_results( $prepared );
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
				$rk->$fld = newstatpress_hdate2( $rk->$fld ); }
			if ( 'urlrequested' === $fld ) {
				$rk->$fld = newstatpress_decode_url( $rk->$fld ); }
			$data[ substr( $rk->$fld, 0, 250 ) ] = $rk->pageview;
		}
	}

	// Draw table body.
	$text .= "<tbody id='the-list'>";
	if ( $rks > 0 ) {  // Chart!

		if ( 'NATION' === $type ) { // Nation chart.
			$charts = plugins_url( './geocharts.html', __FILE__ ) . newstatpress_get_google_geo( $data );
		} else { // Pie chart.
			$charts = plugins_url( './piecharts.html', __FILE__ ) . newstatpress_get_google_pie( $fldtitle, $data );
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
function newstatpress_get_google_geo( $data_array ) {
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
function newstatpress_get_google_pie( $title, $data_array ) {
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
 * NewStatPress shortcode handler using WordPress Shortcode API.
 *
 * Usage:
 *  [NewStatPress type="Overview"]
 *  [NewStatPress type="Top days"]
 *  [NewStatPress type="O.S."]
 *  [NewStatPress type="Browser"]
 *  [NewStatPress type="Feeds"]
 *  [NewStatPress type="Search Engine"]
 *  [NewStatPress type="Search terms"]
 *  [NewStatPress type="Top referrer"]
 *  [NewStatPress type="Languages"]
 *  [NewStatPress type="Spider"]
 *  [NewStatPress type="Top Pages"]
 *  [NewStatPress type="Top Days - Unique visitors"]
 *  [NewStatPress type="Top Days - Pageviews"]
 *  [NewStatPress type="Top IPs - Pageviews"]
 *
 * @param array $attrs attributes.
 * @param string $content the content of tag.
 ******************************************************/
function newstatpress_shortcode_handler( $atts = array(), $content = null ) {
	$allowed_types = array(
		'Overview',
		'Top days',
		'O.S.',
		'Browser',
		'Feeds',
		'Search Engine',
		'Search terms',
		'Top referrer',
		'Languages',
		'Spider',
		'Top Pages',
		'Top Days - Unique visitors',
		'Top Days - Pageviews',
		'Top IPs - Pageviews',
	);

	// Read the "type" attribute.
	$atts = shortcode_atts(
		array(
			'type' => '',
		),
		$atts,
		'NewStatPress'
	);

	// Sanificate the input.
	$type = sanitize_text_field( $atts['type'] );

	// Do nothing if the type is not valid
	if ( ! in_array( $type, $allowed_types, true ) ) {
		return '';
	}

	switch ( $type ) {
		case 'Overview':
			require_once 'api/nsp-api-dashboard.php';
			return newstatpress_api_dashboard( 'HTML' );
		case 'Top days':
			return newstatpress_get_data_query2( 'DATE1', 'date', __( 'Top days', 'newstatpress' ), ( get_option( 'newstatpress_el_top_days' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_top_days' ) ), false );
		case 'O.S.':
			return newstatpress_get_data_query2( 'OS', 'os', __( 'OSes', 'newstatpress' ), ( get_option( 'newstatpress_el_os' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_os' ) ), false );
		case 'Browser':
			return newstatpress_get_data_query2( 'BROWSER', 'browser', __( 'Browsers', 'newstatpress' ), ( get_option( 'newstatpress_el_browser' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_browser' ) ), false );
		case 'Feeds':
			return newstatpress_get_data_query2( 'FEED', 'feed', __( 'Feeds', 'newstatpress' ), ( get_option( 'newstatpress_el_feed' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_feed' ) ), false );
		case 'Search Engine':
			return newstatpress_get_data_query2( 'SEARCHENGINE', 'searchengine', __( 'Search engines', 'newstatpress' ), ( get_option( 'newstatpress_el_searchengine' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_searchengine' ) ), false );
		case 'Search terms':
			return newstatpress_get_data_query2( 'SEARCH', 'search', __( 'Top search terms', 'newstatpress' ), ( get_option( 'newstatpress_el_search' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_search' ) ), false );
		case 'Top referrer':
			return newstatpress_get_data_query2( 'REFFERER', 'referrer', __( 'Top referrers', 'newstatpress' ), ( get_option( 'newstatpress_el_referrer' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_referrer' ) ), false );
		case 'Languages':
			return newstatpress_get_data_query2( 'NATION', 'nation', __( 'Countries', 'newstatpress' ) . '/' . __( 'Languages', 'newstatpress' ), ( get_option( 'newstatpress_el_languages' ) === '' ) ? 20 : intval( get_option( 'newstatpress_el_languages' ) ), false );
		case 'Spider':
			return newstatpress_get_data_query2( 'SPIDER', 'spider', __( 'Spiders', 'newstatpress' ), ( get_option( 'newstatpress_el_spiders' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_spiders' ) ), false );
		case 'Top Pages':
			return newstatpress_get_data_query2( 'URLREQUESTED', 'urlrequested', __( 'Top pages', 'newstatpress' ), ( get_option( 'newstatpress_el_pages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_pages' ) ), false );
		case 'Top Days - Unique visitors':
			return newstatpress_get_data_query2( 'DATE2', 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Unique visitors', 'newstatpress' ), ( get_option( 'newstatpress_el_visitors' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_visitors' ) ), false );
		case 'Top Days - Pageviews':
			return newstatpress_get_data_query2( 'DATE3', 'date', __( 'Top days', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_daypages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_daypages' ) ), false );
		case 'Top IPs - Pageviews':
			return newstatpress_get_data_query2( 'IP', 'ip', __( 'Top IPs', 'newstatpress' ) . ' - ' . __( 'Pageviews', 'newstatpress' ), ( get_option( 'newstatpress_el_ippages' ) === '' ) ? 5 : intval( get_option( 'newstatpress_el_ippages' ) ), '', 'urlrequested', false );
	}

	return '';
}
add_shortcode( 'NewStatPress', 'newstatpress_shortcode_handler' );


