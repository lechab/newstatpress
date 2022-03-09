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
	<form method=get><table>
	<?php
	for ( $i = 1;$i <= 3;$i++ ) {
		print '<tr>';
		print '<td>' . esc_html__( 'Field', 'newstatpress' ) . ' <select name=' . esc_attr( "where$i" ) . "><option value=''></option>";
		foreach ( array_keys( $f ) as $k ) {
			print "<option value='" . esc_html( $k ) . "'";
			if ( isset( $_GET[ "where$i" ] ) && $_GET[ "where$i" ] === $k ) {
				print ' SELECTED '; }
			print '>' . esc_html( $f[ $k ] ) . '</option>';
		}
		print '</select></td>';
		if ( isset( $_GET[ "groupby$i" ] ) ) {
			// must only be a "checked" value if this is set.
			print '<td><input type=checkbox name=' . esc_attr( "groupby$i" ) . " value='checked' checked" . '> ' . esc_html__( 'Group by', 'newstatpress' ) . '</td>';
		} else {
			print '<td> <input type=checkbox name=' . esc_attr( "groupby$i" ) . " value = 'checked' " . '> ' . esc_html__( 'Group by', 'newstatpress' ) . '</td>';
		}

		if ( isset( $_GET[ "sortby$i" ] ) ) {
			// must only be a 'checked" value if this is set.
			print '<td><input type=checkbox name=' . esc_attr( "sortby$i" ) . " value = 'checked' " . 'checked> ' . esc_html__( 'Sort by', 'newstatpress' ) . '</td>';
		} else {
			print '<td><input type=checkbox name=' . esc_attr( "sortby$i" ) . " value = 'checked' " . '> ' . esc_html__( 'Sort by', 'newstatpress' ) . '</td>';
		}

		$what = '';
		// we accept only chars, number, space and . (for ip) in search field.
		if ( isset( $_GET[ "what$i" ] ) ) {
			$what = preg_replace( '/[^A-Za-z0-9\.\s]/', '', sanitize_text_field( wp_unslash( $_GET[ "what$i" ] ) ) );
		}
		print '<td>, ' . esc_html__( 'if contains', 'newstatpress' ) . ' <input type=text name=' . esc_attr( "what$i" ) . " value = '" . esc_js( esc_html( $what ) ) . "' > < / td > ";
		print '</tr>';
	}

	$orderby = '';
	if ( isset( $_GET['oderbycount'] ) ) {
		$orderby = sanitize_text_field( wp_unslash( $_GET['oderbycount'] ) );
	}

	$spider = '';
	if ( isset( $_GET['spider'] ) ) {
		$spider = sanitize_text_field( wp_unslash( $_GET['spider'] ) );
	}

	$feed = '';
	if ( isset( $_GET['feed'] ) ) {
		$feed = sanitize_text_field( wp_unslash( $_GET['feed'] ) );
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
				if ( isset( $_GET['limitquery'] ) && $_GET['limitquery'] > 0 ) {
					print '<option>' . esc_html( sanitize_text_field( wp_unslash( $_GET['limitquery'] ) ) ) . '</option>';}
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
		<input type=hidden name=page value='nsp_search'>
		<input type=hidden name=newstatpress_action value=search>
	</form>

	<br>
	<?php

	if ( isset( $_GET['searchsubmit'] ) ) {
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

		// FIELDS.
		$fields = '';
		for ( $i = 1;$i <= 3;$i++ ) {
			if ( isset( $_GET[ "where$i" ] ) && '' !== $_GET[ "where$i" ] ) {
				$where_i = sanitize_text_field( wp_unslash( $_GET[ "where$i" ] ) );
				if ( ! array_key_exists( $where_i, $f ) ) {
					$where_i = ''; // prevent to use not valid values.
				}
				$fields .= $where_i . ',';
			}
		}
		$fields = rtrim( $fields, ',' );

		// WHERE.
		$where = 'WHERE 1=1';

		if ( ! isset( $_GET['spider'] ) ) {
			$where .= " and spider                      = ''"; } elseif ( 'checked' !== $_GET['spider'] ) {
					$where .= " and spider              = ''"; }

			if ( ! isset( $_GET['feed'] ) ) {
				$where .= " and feed                    = ''"; } elseif ( 'checked' !== $_GET['feed'] ) {
					$where .= " and feed                = ''"; }

				for ( $i = 1;$i <= 3;$i++ ) {
					if ( ( '' !== $_GET[ "what$i" ] ) && isset( $_GET[ "where$i" ] ) && ( '' !== $_GET[ "where$i" ] ) ) {
						$where_i = sanitize_text_field( wp_unslash( $_GET[ "where$i" ] ) );
						if ( array_key_exists( $where_i, $f ) ) {
									$what_i  = preg_replace( '/[^A-Za-z0-9\.\s]/', '', sanitize_text_field( wp_unslash( $_GET[ "what$i" ] ) ) ); // sanitize with prepare, but before extract what we expected.
									$where  .= ' AND ' . $where_i . ' LIKE %s ';
									$array[] = '%' . $what_i . '%';             // sanitize with prepare.
						}
					}
				}
				// ORDER BY.
				$orderby = '';
				for ( $i = 1;$i <= 3;$i++ ) {
					if ( isset( $_GET[ "sortby$i" ] ) && isset( $_GET[ "where$i" ] ) && ( 'checked' === $_GET[ "sortby$i" ] ) && ( '' !== $_GET[ "where$i" ] ) ) {
						$where_i = sanitize_text_field( wp_unslash( $_GET[ "where$i" ] ) );
						if ( array_key_exists( $where_i, $f ) ) {
							$orderby .= $where_i . ',';
						}
					}
				}

				// GROUP BY.
				$groupby = '';
				for ( $i = 1;$i <= 3;$i++ ) {
					if ( isset( $_GET[ "groupby$i" ] ) && isset( $_GET[ "where$i" ] ) && ( 'checked' === $_GET[ "groupby$i" ] ) && ( '' !== $_GET[ "where$i" ] ) ) {
						$where_i = sanitize_text_field( wp_unslash( $_GET[ "where$i" ] ) );
						if ( array_key_exists( $where_i, $f ) ) {
							$groupby .= $where_i . ',';
						}
					}
				}
				if ( '' !== $groupby ) {
					$groupby = 'GROUP BY ' . rtrim( $groupby, ',' );
					$fields .= ',count(*) as totale';
					if ( isset( $_GET['oderbycount'] ) && 'checked' === $_GET['oderbycount'] ) {
						$orderby = 'totale DESC,' . $orderby; }
				}

				if ( '' !== $orderby ) {
					$orderby = 'ORDER BY ' . rtrim( $orderby, ',' ); }

				if ( isset( $_GET['limitquery'] ) ) {
					$limit_num = intval( $_GET['limitquery'] ); // force to use integer.
				}

				// Results.
				print '<h2>' . esc_html__( 'Results', 'newstatpress' ) . '</h2>';

				print " < table class                   = 'widefat' > < thead > < tr > ";
				for ( $i = 1;$i <= 3;$i++ ) {
					if ( isset( $_GET[ "where$i" ] ) ) {
						$where_i = htmlspecialchars( wp_strip_all_tags( wp_unslash( $_GET[ "where$i" ] ) ), ENT_COMPAT, 'UTF-8' );
					}
					if ( '' !== $where_i ) {
						print " < th scope              = 'col' > " . esc_html( ucfirst( $where_i ) ) . '</th>'; }
				}
				if ( '' !== $groupby ) {
					print " < th scope                  = 'col' > " . esc_html__( 'Count', 'newstatpress' ) . '</th>'; }
				print " < / tr > < / thead > < tbody id = 'the-list' > ";
				$qry = $wpdb->get_results( $wpdb->prepare( 'SELECT %s FROM %s %s %s %s LIMIT %d;', $fields, $table_name, $where, $groupby, $orderby, $limit_num ), ARRAY_N ); // db call ok; no-cache ok.
				foreach ( $qry as $rk ) {
					print '<tr>';
					for ( $i = 1;$i <= 3;$i++ ) {
						print '<td>';
						if ( isset( $_GET[ "where$i" ] ) && 'urlrequested' === $_GET[ "where$i" ] ) {
							print esc_html( nsp_decode_url( $rk[ $i - 1 ] ) ); } else {
							if ( isset( $rk[ $i - 1 ] ) ) {
								print esc_html( $rk[ $i - 1 ] );
							}
							}
							print '</td>';
					}
						print '</tr>';
				}
				print '</table>';
				print '<br /><br /><font size=1 color=gray>sql: ' . esc_html( $sql ) . '</font></div>';
	}
}
?>
