<?php
/**
 * Search functions
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
 *  Search in database
 *
 *  @param string $what waht to search.
 */
function nsp_database_search( $what = '' ) {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	$f['urlrequested'] = __( 'URL Requested', 'newstatpress' );
	$f['agent']        = __( 'Agent', 'newstatpress' );
	$f['referrer']     = __( 'Referrer', 'newstatpress' );
	$f['search']       = __( 'Search terms', 'newstatpress' );
	$f['searchengine'] = __( 'Search engine', 'newstatpress' );
	$f['os']           = __( 'Operative system', 'newstatpress' );
	$f['browser']      = __( 'Browser', 'newstatpress' );
	$f['spider']       = __( 'Spider', 'newstatpress' );
	$f['ip']           = __( 'IP', 'newstatpress' );
	?>
	<div class='wrap'><h2><?php esc_html_e( 'Search', 'newstatpress' ); ?></h2>
	<form method=post><table>
	<?php
	for ( $i = 1;$i <= 3;$i++ ) {
		print '<tr>';
		print '<td>' . esc_html__( 'Field', 'newstatpress' ) . ' <select name=' . esc_attr( "where$i" ) . "><option value=''></option>";
		foreach ( array_keys( $f ) as $k ) {
			print "<option value='" . esc_html( $k ) . "'";
			if ( isset( $_POST[ "where$i" ] ) && $_POST[ "where$i" ] === $k ) {
				print ' SELECTED '; }
			print '>' . esc_html( $f[ $k ] ) . '</option>';
		}
		print '</select></td>';
		if ( isset( $_POST[ "groupby$i" ] ) ) {
			// must only be a "checked" value if this is set.
			print '<td><input type=checkbox name=' . esc_attr( "groupby$i" ) . " value='checked' checked" . '> ' . esc_html__( 'Group by', 'newstatpress' ) . '</td>';
		} else {
			print '<td> <input type=checkbox name=' . esc_attr( "groupby$i" ) . " value = 'checked' " . '> ' . esc_html__( 'Group by', 'newstatpress' ) . '</td>';
		}

		if ( isset( $_POST[ "sortby$i" ] ) ) {
			// must only be a 'checked" value if this is set.
			print '<td><input type=checkbox name=' . esc_attr( "sortby$i" ) . " value = 'checked' " . 'checked> ' . esc_html__( 'Sort by', 'newstatpress' ) . '</td>';
		} else {
			print '<td><input type=checkbox name=' . esc_attr( "sortby$i" ) . " value = 'checked' " . '> ' . esc_html__( 'Sort by', 'newstatpress' ) . '</td>';
		}

		$what = '';
		// we accept only chars, number, space and . (for ip) in search field.
		if ( isset( $_POST[ "what$i" ] ) ) {
			$what = preg_replace( '/[^A-Za-z0-9\.\s]/', '', sanitize_text_field( wp_unslash( $_POST[ "what$i" ] ) ) );
		}
		print '<td>, ' . esc_html__( 'if contains', 'newstatpress' ) . ' <input type=text name=' . esc_attr( "what$i" ) . " value = '" . esc_js( esc_html( $what ) ) . "' > </td> ";
		print '</tr>';
	}

	$orderby = '';
	if ( isset( $_POST['oderbycount'] ) ) {
		$orderby = sanitize_text_field( wp_unslash( $_POST['oderbycount'] ) );
	}

	$spider = '';
	if ( isset( $_POST['spider'] ) ) {
		$spider = sanitize_text_field( wp_unslash( $_POST['spider'] ) );
	}

	$feed = '';
	if ( isset( $_POST['feed'] ) ) {
		$feed = sanitize_text_field( wp_unslash( $_POST['feed'] ) );
	}
	?>
	</table>
	<br>
	<table>
		<tr>
		<td>
			<table>
			<tr><td><input type=checkbox name=oderbycount value=checked <?php print esc_html( $orderby ); ?>> <?php esc_html_e( 'sort by count if grouped', 'newstatpress' ); ?></td></tr>
			<tr><td><input type=checkbox name=spider value=checked <?php print esc_html( $spider ); ?>> <?php esc_html_e( 'include spiders/crawlers/bot', 'newstatpress' ); ?></td></tr>
			<tr><td><input type=checkbox name=feed value=checked <?php print esc_html( $feed ); ?>> <?php esc_html_e( 'include feed', 'newstatpress' ); ?></td></tr>
			</table>
		</td>
		<td width=15> </td>
		<td>
			<table>
			<tr>
				<td><?php esc_html_e( 'Limit results to', 'newstatpress' ); ?>
				<select name=limitquery>
				<?php
				if ( isset( $_POST['limitquery'] ) && $_POST['limitquery'] > 0 ) {
					print '<option>' . esc_html( sanitize_text_field( wp_unslash( $_POST['limitquery'] ) ) ) . '</option>';}
				?>
				<option>1</option><option>5</option><option>10</option><option>20</option><option>50</option></select>
				</td>
			</tr>
			<tr><td>&nbsp;</td></tr>
			<tr>
			<?php wp_nonce_field( 'nsp_search', 'nsp_search_post' ); ?>
			<td align=right><input class='button button-primary' type=submit value=<?php esc_html_e( 'Search', 'newstatpress' ); ?> name=searchsubmit></td>
			</tr>
			</table>
		</td>
	</tr>
		</table>
		<input type=hidden name=page value='nsp-search'>
		<input type=hidden name=newstatpress_action value=search>
	</form>

	<br>
	<?php

	if ( isset( $_POST['searchsubmit'] ) ) {
		check_admin_referer( 'nsp_search', 'nsp_search_post' );
		if ( ! current_user_can( 'administrator' ) ) {
			die( 'NO permission' );
		}

		if ( ! ( isset( $_REQUEST['nsp_search_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nsp_search_post'] ) ), 'nsp_search' ) ) ) {
			die( 'Failed security check' );
		}

		// query builder.
		$qry   = '';
		$array = array();

		$_urlrequested = '';
		$_agent        = '';
		$_referrer     = '';
		$_search       = '';
		$_searchengine = '';
		$_os           = '';
		$_browser      = '';
		$_spider       = '';
		$_ip           = '';

		// FIELDS.
		$fields = '';
		for ( $i = 1;$i <= 3;$i++ ) {
			if ( isset( $_POST[ "where$i" ] ) && '' !== $_POST[ "where$i" ] ) {
				$where_i = sanitize_text_field( wp_unslash( $_POST[ "where$i" ] ) );
				if ( ! array_key_exists( $where_i, $f ) ) {
					$where_i = ''; // prevent to use not valid values.
				}
				$fields .= $where_i . ',';
			}
		}
		$fields = rtrim( $fields, ',' );

		for ( $i = 1;$i <= 3;$i++ ) {
			if ( ( '' !== $_POST[ "what$i" ] ) && isset( $_POST[ "where$i" ] ) && ( '' !== $_POST[ "where$i" ] ) ) {
				$where_i = sanitize_text_field( wp_unslash( $_POST[ "where$i" ] ) );
				if ( array_key_exists( $where_i, $f ) ) {
							$what_i = preg_replace( '/[^A-Za-z0-9\.\s]/', '', sanitize_text_field( wp_unslash( $_POST[ "what$i" ] ) ) ); // sanitize with prepare, but before extract what we expected.
					switch ( $where_i ) {
						case 'agent':
							$_agent = $what_i;
							break;
						case 'referrer':
							$_referrer = $what_i;
							break;
						case 'search':
							$_search = $what_i;
							break;
						case 'searchengine':
							$_searchengine = $what_i;
							break;
						case 'os':
							$_os = $what_i;
							break;
						case 'browser':
							$_browser = $what_i;
							break;
						case 'spider':
							$_spider = $what_i;
							break;
						case 'ip':
							$_ip = $what_i;
							break;
					}
							$array[] = '%' . $what_i . '%';             // sanitize with prepare.
				}
			}
		}
				// ORDER BY.
				$orderby = '';
		for ( $i = 1;$i <= 3;$i++ ) {
			if ( isset( $_POST[ "sortby$i" ] ) && isset( $_POST[ "where$i" ] ) && ( 'checked' === $_POST[ "sortby$i" ] ) && ( '' !== $_POST[ "where$i" ] ) ) {
				$where_i = sanitize_text_field( wp_unslash( $_POST[ "where$i" ] ) );
				if ( array_key_exists( $where_i, $f ) ) {
					$orderby .= $where_i . ',';
				}
			}
		}

				// GROUP BY.
				$groupby = '';
		for ( $i = 1;$i <= 3;$i++ ) {
			if ( isset( $_POST[ "groupby$i" ] ) && isset( $_POST[ "where$i" ] ) && ( 'checked' === $_POST[ "groupby$i" ] ) && ( '' !== $_POST[ "where$i" ] ) ) {
				$where_i = sanitize_text_field( wp_unslash( $_POST[ "where$i" ] ) );
				if ( array_key_exists( $where_i, $f ) ) {
					$groupby .= $where_i . ',';
				}
			}
		}
		if ( '' !== $groupby ) {
			$groupby = rtrim( $groupby, ',' );
			$fields .= ',count(*) as totale';
			if ( isset( $_POST['oderbycount'] ) && 'checked' === $_POST['oderbycount'] ) {
				$orderby = 'totale DESC,' . $orderby; }
		}

		if ( '' !== $orderby ) {
			$orderby = rtrim( $orderby, ',' );
		} else {
			$orderby = 'id';
		}

		if ( isset( $_POST['limitquery'] ) ) {
			$limit_num = intval( $_POST['limitquery'] ); // force to use integer.
		}

				// Results.
				print '<h2>' . esc_html__( 'Results', 'newstatpress' ) . '</h2>';

				print "<table class='widefat'> <thead> <tr> ";
		for ( $i = 1;$i <= 3;$i++ ) {
			if ( isset( $_POST[ "where$i" ] ) ) {
				$where_i = htmlspecialchars( wp_strip_all_tags( wp_unslash( $_POST[ "where$i" ] ) ), ENT_COMPAT, 'UTF-8' );
			}
			if ( '' !== $where_i ) {
				print " <th scope='col'> " . esc_html( ucfirst( $where_i ) ) . '</th>'; }
		}
		if ( '' !== $groupby ) {
			print "<th scope='col'> " . esc_html__( 'Count', 'newstatpress' ) . '</th>'; }
				print " </tr> </thead > <tbody id='the-list'> ";

		if ( '' !== $groupby ) {
			if ( isset( $_POST['spider'] ) && 'checked' === $_POST['spider'] && isset( $_POST['feed'] ) && 'checked' === $_POST['feed'] ) {
				// phpcs:disable -- Use placeholders and $wpdb->prepare(); found interpolated variable $table_name at FROM `$table_name`
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
				       urlrequested,
				       agent,
			         referrer,
				       search,
				       searchengine,
				       os,
				       browser,
				       spider,
				       ip,
			         count(*) as totale
				      FROM `$table_name` 
				      WHERE 
				        urlrequested like %s AND
				        agent like %s AND
				        referrer like %s AND
				        search like %s AND
				        searchengine like %s AND
				        os like %s AND
				        browser like %s AND
				        spider like %s AND
				        ip like %s and
				        feed <> '' AND
				        spider <> ''
				       GROUP BY %s
				       ORDER BY %s                                              
				        LIMIT %d",
						'%' . $wpdb->esc_like( $_urlrequested ) . '%',
						'%' . $wpdb->esc_like( $_agent ) . '%',
						'%' . $wpdb->esc_like( $_refferer ) . '%',
						'%' . $wpdb->esc_like( $_search ) . '%',
						'%' . $wpdb->esc_like( $_searchengine ) . '%',
						'%' . $wpdb->esc_like( $_os ) . '%',
						'%' . $wpdb->esc_like( $_browser ) . '%',
						'%' . $wpdb->esc_like( $_spider ) . '%',
						'%' . $wpdb->esc_like( $_ip ) . '%',
						sanitize_sql_orderby( $groupby ),
						sanitize_sql_orderby( $orderby ),
						$limit_num
					),
					ARRAY_N
				); // phpcs:enable
			} elseif ( isset( $_POST['spider'] ) && 'checked' === $_POST['spider'] ) {
				// phpcs:disable -- Use placeholders and $wpdb->prepare(); found interpolated variable $table_name at FROM `$table_name`
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
				       urlrequested,
				       agent,
			         referrer,
				       search,
				       searchengine,
				       os,
				       browser,
				       spider,
				       ip,
			         count(*) as totale
				      FROM `$table_name` 
				      WHERE 
				        urlrequested like %s AND
				        agent like %s AND
				        referrer like %s AND
				        search like %s AND
				        searchengine like %s AND
				        os like %s AND
				        browser like %s AND
				        spider like %s AND
				        ip like %s and
				        feed = '' AND
				        spider <> ''
				       GROUP BY %s
				       ORDER BY %s                                              
				        LIMIT %d",
						'%' . $wpdb->esc_like( $_urlrequested ) . '%',
						'%' . $wpdb->esc_like( $_agent ) . '%',
						'%' . $wpdb->esc_like( $_refferer ) . '%',
						'%' . $wpdb->esc_like( $_search ) . '%',
						'%' . $wpdb->esc_like( $_searchengine ) . '%',
						'%' . $wpdb->esc_like( $_os ) . '%',
						'%' . $wpdb->esc_like( $_browser ) . '%',
						'%' . $wpdb->esc_like( $_spider ) . '%',
						'%' . $wpdb->esc_like( $_ip ) . '%',
						sanitize_sql_orderby( $groupby ),
						sanitize_sql_orderby( $orderby ),
						$limit_num
					),
					ARRAY_N
				); // phpcs:enable
			} elseif ( isset( $_POST['feed'] ) && 'checked' === $_POST['feed'] ) {
				// phpcs:disable -- Use placeholders and $wpdb->prepare(); found interpolated variable $table_name at FROM `$table_name`
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
				       urlrequested,
				       agent,
			         referrer,
				       search,
				       searchengine,
				       os,
				       browser,
				       spider,
				       ip,
			         count(*) as totale
				      FROM `$table_name` 
				      WHERE 
				        urlrequested like %s AND
				        agent like %s AND
				        referrer like %s AND
				        search like %s AND
				        searchengine like %s AND
				        os like %s AND
				        browser like %s AND
				        spider like %s AND
				        ip like %s and
				        feed <> '' AND
				        spider = ''
				       GROUP BY %s
				       ORDER BY %s                                              
				        LIMIT %d",
						'%' . $wpdb->esc_like( $_urlrequested ) . '%',
						'%' . $wpdb->esc_like( $_agent ) . '%',
						'%' . $wpdb->esc_like( $_refferer ) . '%',
						'%' . $wpdb->esc_like( $_search ) . '%',
						'%' . $wpdb->esc_like( $_searchengine ) . '%',
						'%' . $wpdb->esc_like( $_os ) . '%',
						'%' . $wpdb->esc_like( $_browser ) . '%',
						'%' . $wpdb->esc_like( $_spider ) . '%',
						'%' . $wpdb->esc_like( $_ip ) . '%',
						sanitize_sql_orderby( $groupby ),
						sanitize_sql_orderby( $orderby ),
						$limit_num
					),
					ARRAY_N
				); // phpcs:enable
			} else {
				// phpcs:disable -- Use placeholders and $wpdb->prepare(); found interpolated variable $table_name at FROM `$table_name`
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
				       urlrequested,
				       agent,
			         referrer,
				       search,
				       searchengine,
				       os,
				       browser,
				       spider,
				       ip,
			         count(*) as totale
				      FROM `$table_name` 
				      WHERE 
				        urlrequested like %s AND
				        agent like %s AND
				        referrer like %s AND
				        search like %s AND
				        searchengine like %s AND
				        os like %s AND
				        browser like %s AND
				        spider like %s AND
				        ip like %s AND
				        feed = '' AND
				        spider = ''
				       GROUP BY %s
				       ORDER BY %s                                              
				        LIMIT %d",
						'%' . $wpdb->esc_like( $_urlrequested ) . '%',
						'%' . $wpdb->esc_like( $_agent ) . '%',
						'%' . $wpdb->esc_like( $_refferer ) . '%',
						'%' . $wpdb->esc_like( $_search ) . '%',
						'%' . $wpdb->esc_like( $_searchengine ) . '%',
						'%' . $wpdb->esc_like( $_os ) . '%',
						'%' . $wpdb->esc_like( $_browser ) . '%',
						'%' . $wpdb->esc_like( $_spider ) . '%',
						'%' . $wpdb->esc_like( $_ip ) . '%',
						sanitize_sql_orderby( $groupby ),
						sanitize_sql_orderby( $orderby ),
						$limit_num
					),
					ARRAY_N
				); // phpcs:enable
			}
		} else {
			if ( isset( $_POST['spider'] ) && 'checked' === $_POST['spider'] && isset( $_POST['feed'] ) && 'checked' === $_POST['feed'] ) {
				// phpcs:disable -- Use placeholders and $wpdb->prepare(); found interpolated variable $table_name at FROM `$table_name`
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
				       urlrequested,
				       agent,
			         referrer,
				       search,
				       searchengine,
				       os,
				       browser,
				       spider,
				       ip
				      FROM `$table_name` 
				      WHERE 
				        urlrequested like %s AND
				        agent like %s AND
				        referrer like %s AND
				        search like %s AND
				        searchengine like %s AND
				        os like %s AND
				        browser like %s AND
				        spider like %s AND
				        ip like %s and
				        feed <> '' AND
				        spider <> ''        
				       ORDER BY %s
				        LIMIT %d",
						'%' . $wpdb->esc_like( $_urlrequested ) . '%',
						'%' . $wpdb->esc_like( $_agent ) . '%',
						'%' . $wpdb->esc_like( $_refferer ) . '%',
						'%' . $wpdb->esc_like( $_search ) . '%',
						'%' . $wpdb->esc_like( $_searchengine ) . '%',
						'%' . $wpdb->esc_like( $_os ) . '%',
						'%' . $wpdb->esc_like( $_browser ) . '%',
						'%' . $wpdb->esc_like( $_spider ) . '%',
						'%' . $wpdb->esc_like( $_ip ) . '%',
						sanitize_sql_orderby( $orderby ),
						$limit_num
					),
					ARRAY_N
				); // phpcs:enable

			} elseif ( isset( $_POST['spider'] ) && 'checked' === $_POST['spider'] ) {
				// phpcs:disable -- Use placeholders and $wpdb->prepare(); found interpolated variable $table_name at FROM `$table_name`
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
				       urlrequested,
				       agent,
			         referrer,
				       search,
				       searchengine,
				       os,
				       browser,
				       spider,
				       ip
				      FROM `$table_name` 
				      WHERE 
				        urlrequested like %s AND
				        agent like %s AND
				        referrer like %s AND
				        search like %s AND
				        searchengine like %s AND
				        os like %s AND
				        browser like %s AND
				        spider like %s AND
				        ip like %s and
				        feed = '' AND
				        spider <> ''
				       ORDER BY %s                                          
				        LIMIT %d",
						'%' . $wpdb->esc_like( $_urlrequested ) . '%',
						'%' . $wpdb->esc_like( $_agent ) . '%',
						'%' . $wpdb->esc_like( $_refferer ) . '%',
						'%' . $wpdb->esc_like( $_search ) . '%',
						'%' . $wpdb->esc_like( $_searchengine ) . '%',
						'%' . $wpdb->esc_like( $_os ) . '%',
						'%' . $wpdb->esc_like( $_browser ) . '%',
						'%' . $wpdb->esc_like( $_spider ) . '%',
						'%' . $wpdb->esc_like( $_ip ) . '%',
						sanitize_sql_orderby( $orderby ),
						$limit_num
					),
					ARRAY_N
				); // phpcs:enable
			} elseif ( isset( $_POST['feed'] ) && 'checked' === $_POST['feed'] ) {
				// phpcs:disable -- Use placeholders and $wpdb->prepare(); found interpolated variable $table_name at FROM `$table_name`
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
				       urlrequested,
				       agent,
			         referrer,
				       search,
				       searchengine,
				       os,
				       browser,
				       spider,
				       ip
				      FROM `$table_name` 
				      WHERE 
				        urlrequested like %s AND
				        agent like %s AND
				        referrer like %s AND
				        search like %s AND
				        searchengine like %s AND
				        os like %s AND
				        browser like %s AND
				        spider like %s AND
				        ip like %s and
				        feed <> '' AND
				        spider = ''
				       ORDER BY %s
				        LIMIT %d",
						'%' . $wpdb->esc_like( $_urlrequested ) . '%',
						'%' . $wpdb->esc_like( $_agent ) . '%',
						'%' . $wpdb->esc_like( $_refferer ) . '%',
						'%' . $wpdb->esc_like( $_search ) . '%',
						'%' . $wpdb->esc_like( $_searchengine ) . '%',
						'%' . $wpdb->esc_like( $_os ) . '%',
						'%' . $wpdb->esc_like( $_browser ) . '%',
						'%' . $wpdb->esc_like( $_spider ) . '%',
						'%' . $wpdb->esc_like( $_ip ) . '%',
						sanitize_sql_orderby( $orderby ),
						$limit_num
					),
					ARRAY_N
				); // phpcs:enable
			} else {
				// phpcs:disable -- Use placeholders and $wpdb->prepare(); found interpolated variable $table_name at FROM `$table_name`
				$qry = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT 
				       urlrequested,
				       agent,
			         referrer,
				       search,
				       searchengine,
				       os,
				       browser,
				       spider,
				       ip
				      FROM `$table_name` 
				      WHERE 
				        urlrequested like %s AND
				        agent like %s AND
				        referrer like %s AND
				        search like %s AND
				        searchengine like %s AND
				        os like %s AND
				        browser like %s AND
				        spider like %s AND
				        ip like %s AND
				        feed = '' AND
				        spider = ''
				       ORDER BY %s                                   
				        LIMIT %d",
						'%' . $wpdb->esc_like( $_urlrequested ) . '%',
						'%' . $wpdb->esc_like( $_agent ) . '%',
						'%' . $wpdb->esc_like( $_refferer ) . '%',
						'%' . $wpdb->esc_like( $_search ) . '%',
						'%' . $wpdb->esc_like( $_searchengine ) . '%',
						'%' . $wpdb->esc_like( $_os ) . '%',
						'%' . $wpdb->esc_like( $_browser ) . '%',
						'%' . $wpdb->esc_like( $_spider ) . '%',
						'%' . $wpdb->esc_like( $_ip ) . '%',
						sanitize_sql_orderby( $orderby ),
						$limit_num
					),
					ARRAY_N
				); // phpcs:enable
			}
		}

		foreach ( $qry as $rk ) {
			for ( $i = 1;$i <= 3;$i++ ) {
				print '<td>';
				if ( isset( $_POST[ "where$i" ] ) && 'urlrequested' === $_POST[ "where$i" ] ) {
					print esc_html( nsp_decode_url( $rk[0] ) );
				} else {
					switch ( $_POST[ "where$i" ] ) {
						case 'agent':
							if ( isset( $rk[1] ) ) {
								print esc_html( $rk[1] );
							}
							break;
						case 'referrer':
							if ( isset( $rk[2] ) ) {
								print esc_html( $rk[2] );
							}
							break;
						case 'search':
							if ( isset( $rk[3] ) ) {
								print esc_html( $rk[3] );
							}
							break;
						case 'searchengine':
							if ( isset( $rk[4] ) ) {
								print esc_html( $rk[4] );
							}
							break;
						case 'os':
							if ( isset( $rk[5] ) ) {
								print esc_html( $rk[5] );
							}
							break;
						case 'browser':
							if ( isset( $rk[6] ) ) {
								print esc_html( $rk[6] );
							}
							break;
						case 'spider':
							if ( isset( $rk[7] ) ) {
								print esc_html( $rk[7] );
							}
							break;
						case 'ip':
							if ( isset( $rk[8] ) ) {
								print esc_html( $rk[8] );
							}
							break;
					}
				}
				print esc_html( $rk[9] );
				print '</td>';
			}
			print '</tr>';
		}
				print '</table>';
				print '<br/><br/><font size=1 color=gray>sql: ' . esc_html( $wpdb->last_query ) . '</font></div>';
	}
}
?>
