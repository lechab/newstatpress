<?php
/**
 * Options functions
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
 * Filter the given value for preventing XSS attacks
 *
 * @param string $_value the value to filter.
 * @return filtered value
 */
function nsp_filter_for_xss( $_value ) {
	$_value = trim( $_value );

	// Avoid XSS attacks.
	$clean_value = preg_replace( '/[^a-zA-Z0-9\,\.\/\ \-\_\?=&;]/', '', $_value );
	if ( 0 === strlen( $_value ) ) {
		return array();
	} else {
		$array_values = explode( ',', $clean_value );
		array_walk( $array_values, 'nsp_trim_value' );
		return $array_values;
	}
}


/**
 * Trim the given string
 *
 * @param string $value the reference to value to trim.
 */
function nsp_trim_value( &$value ) {
	$value = trim( $value );
}


/**
 * Print the options
 * added by cHab
 *
 * @param string $option_title the title for option.
 * @param string $option_var the variable for option.
 * @param string $var variables.
 */
function nsp_print_option( $option_title, $option_var, $var ) {
	if ( ( 'newstatpress_menuoverview_cap' !== $option_var ) &&
				( 'newstatpress_menudetails_cap' !== $option_var ) &&
				( 'newstatpress_menuvisits_cap' !== $option_var ) &&
				( 'newstatpress_menusearch_cap' !== $option_var ) &&
				( 'newstatpress_menutools_cap' !== $option_var ) &&
				( 'newstatpress_menuoptions_cap' !== $option_var ) ) {
		echo '<td>' . esc_html( $option_title ) . "</td>\n";
	}
	echo '<td><select name=' . esc_attr( $option_var ) . ">\n";
	if ( ( 'newstatpress_menuoverview_cap' === $option_var ) ||
				( 'newstatpress_menudetails_cap' === $option_var ) ||
				( 'newstatpress_menuvisits_cap' === $option_var ) ||
				( 'newstatpress_menusearch_cap' === $option_var ) ||
				( 'newstatpress_menutools_cap' === $option_var ) ||
				( 'newstatpress_menuoptions_cap' === $option_var ) ) {
		$role = get_role( 'administrator' );
		foreach ( $role->capabilities as $cap => $grant ) {
			print '<option ';
			if ( $var === $cap ) {
				print 'selected ';
			}
			print '>' . esc_html( $cap ) . '</option>';
		}
	} else {
		foreach ( $var as $option ) {
			echo '<option value="' . esc_attr( $option[0] ) . '"';
			if ( get_option( $option_var ) === $option[0] ) {
				echo ' selected';
			}
			echo '>' . esc_html( $option[0] );
			if ( '' !== $option[1] ) {
				echo ' ';
				echo esc_html( $option[1] );
			}
			echo "</option>\n";
		}
	}
	echo "</select></td>\n";
}


/**
 * Gets a sorted (according to interval) list of the cron schedules
 */
function nsp_get_schedules() {
	$schedules = wp_get_schedules();
	uasort(
		$schedules,
		function ( $a, $b ) {
			return $a['interval'] - $b['interval'];
		}
	);
	return $schedules;
}

/**
 * Calculate interval // took from wp-control
 * added by cHab
 *
 * @param int $since from when.
 */
function nsp_interval( $since ) {
	// array of time period chunks.
	$chunks = array(
		// translators: placeholders ok.
		array( 60 * 60 * 24 * 365, _n_noop( '%s year', '%s years', 'newstatpress' ) ),
		// translators: placeholders ok.
		array( 60 * 60 * 24 * 30, _n_noop( '%s month', '%s months', 'newstatpress' ) ),
		// translators: placeholders ok.
		array( 60 * 60 * 24 * 7, _n_noop( '%s week', '%s weeks', 'newstatpress' ) ),
		// translators: placeholders ok.
		array( 60 * 60 * 24, _n_noop( '%s day', '%s days', 'newstatpress' ) ),
		// translators: placeholders ok.
		array( 60 * 60, _n_noop( '%s hour', '%s hours', 'newstatpress' ) ),
		// translators: placeholders ok.
		array( 60, _n_noop( '%s minute', '%s minutes', 'newstatpress' ) ),
		// translators: placeholders ok.
		array( 1, _n_noop( '%s second', '%s seconds', 'newstatpress' ) ),
	);

	if ( $since <= 0 ) {
		return __( 'now', 'newstatpress' );
	}

	// we only want to output two chunks of time here, eg:
	// x years, xx months
	// x days, xx hours
	// so there's only two bits of calculation below:.

	// step one: the first chunk.
	for ( $i = 0, $j = count( $chunks ); $i < $j; $i++ ) {
		$seconds = $chunks[ $i ][0];
		$name    = $chunks[ $i ][1];

		// finding the biggest chunk (if the chunk fits, break).
		$count = floor( $since / $seconds );
		if ( 0 !== $count ) {
			break;
		}
	}

	// set output var.
	$output = sprintf( translate_nooped_plural( $name, $count, 'newstatpress' ), $count );

	// step two: the second chunk.
	if ( $i + 1 < $j ) {
		$seconds2 = $chunks[ $i + 1 ][0];
		$name2    = $chunks[ $i + 1 ][1];

		$count2 = floor( ( $since - ( $seconds * $count ) ) );
		if ( ( $count2 / $seconds2 ) !== 0 ) {
			// add to output var.
			$output .= ' ' . sprintf( translate_nooped_plural( $name2, $count2, 'newstatpress' ), $count2 );
		}
	}

	return $output;
}

/**
 * Print a dropdown filled with the possible schedules, including non-repeating.
 * added by cHab
 *
 * @param boolean $current The currently selected schedule.
 */
function nsp_print_schedules( $current ) {
	global $nsp_option_vars;
	$schedules = nsp_get_schedules();
	?>
	<select name="newstatpress_mail_notification_freq" id="mail_freq">
	<option <?php selected( $current, '_oneoff' ); ?> value="_oneoff"><?php esc_html_e( 'Non-repeating', 'newstatpress' ); ?></option>
	<?php foreach ( $schedules as $sched_name => $sched_data ) { ?>
		<option <?php selected( $current, $sched_name ); ?> value="<?php echo esc_attr( $sched_name ); ?>"><?php printf( '%s (%s)', esc_html( $sched_data['display'] ), esc_html( nsp_interval( $sched_data['interval'] ) ) ); ?></option>
	<?php } ?>
	</select>
	<?php
}

/**
 * Print a row of input
 * added by cHab
 *
 * @param strng  $option_title the title for options.
 * @param string $nsp_option_vars the variables for options.
 * @param int    $input_size the size of input.
 * @param int    $input_maxlength the max length of the input.
 ****************************************************/
function nsp_print_row_input( $option_title, $nsp_option_vars, $input_size, $input_maxlength ) {
	?>
	<tr>
	<td>
	<?php
	echo "<label for='" . esc_attr( $nsp_option_vars['name'] ) . "'>" . esc_html( $option_title ) . "</label></td>\n";
	echo "<td><input class='right' type='text' name='" . esc_attr( $nsp_option_vars['name'] ) . "' value=";
	echo ( esc_attr( get_option( $nsp_option_vars['name'] ) ) === '' ) ? esc_attr( $nsp_option_vars['value'] ) : esc_attr( get_option( $nsp_option_vars['name'] ) );
	echo ' size=' . esc_attr( $input_size ) . ' maxlength=' . esc_attr( $input_maxlength ) . " />\n";
	?>
	</td>
	</tr>
	<?php
}

/**
 * Get if the input is selected
 *
 * @param string $name the name to check.
 * @param string $value value to check.
 * @param strinf $default default value.
 */
function nsp_input_selected( $name, $value, $default ) {

	$status = get_option( $name );
	if ( '' === $status ) {
		$status = $default;
	}
	if ( $status === $value ) {
		echo ' checked';
	}
}

/**
 * Print a row with given title
 *
 * @param string $option_title the title for option.
 ******************************************/
function nsp_print_row( $option_title ) {
	echo "<tr>\n<td>" - esc_html( $option_title ) . "</td>\n</tr>\n";
}

/**
 * Print a checked row
 * added by cHab
 *
 * @param string $option_title the title for options.
 * @param string $option_var the variables for options.
 */
function nsp_print_checked( $option_title, $option_var ) {
	echo "<tr>\n<td><input type=checkbox name='" . esc_attr( $option_var ) . "' value='checked' " . esc_attr( get_option( $option_var ) ) . '>' . esc_html( $option_title ) . "</td>\n</tr>\n";
}


/**
 * Print a text area
 * added by chab
 *
 * @param string $option_title the title for options.
 * @param string $option_var the variables for options.
 * @param string $option_description the descriotion for options.
 */
function nsp_print_textaera( $option_title, $option_var, $option_description ) {
	echo "<tr><td>\n<p class='ign'><label for=" . esc_attr( $option_var ) . '>' . esc_html( $option_title ) . "</label></p>\n";
	echo '<p>' . esc_html( $option_description ) . "</p>\n";
	echo "<p><textarea class='large-text code' cols='40' rows='2' name=" . esc_attr( $option_var ) . ' id=' . esc_attr( $option_var ) . '>';
	echo esc_html( implode( ',', get_option( $option_var, array() ) ) );
	?>
	</textarea>
	</p>
	</td>
	</tr>
	<?php
}

/**
 * Manages the options that the user can choose
 * Update by cHab : integrate JS tabulation
 */
function nsp_options() {
	global $nsp_option_vars;
	?>

<div class='wrap'>
	<h2><?php esc_html_e( 'NewStatPress Settings', 'newstatpress' ); ?></h2>

	<?php

	if ( ! wp_next_scheduled( 'nsp_mail_notification' ) ) {
		$name   = $nsp_option_vars['mail_notification']['name'];
		$status = get_option( $name );

		if ( 'enabled' === $status ) {
			$name            = $nsp_option_vars['mail_notification_freq']['name'];
			$freq            = get_option( $name );
			$name            = $nsp_option_vars['mail_notification_time']['name'];
			$timeuser        = get_option( $name );
			$t               = time();
			$crontime_offest = nsp_calculation_offset_time( $t, $timeuser );
			$crontime        = time() + $crontime_offest;
			if ( '_oneoff' === $freq ) {
				wp_schedule_single_event( $crontime, 'nsp_mail_notification' );
			} else {
				wp_schedule_event( $crontime, $freq, 'nsp_mail_notification' );
			}
		}
	} else {
		$name   = $nsp_option_vars['mail_notification']['name'];
		$status = get_option( $name );

		if ( 'disabled' === $status ) {
			nsp_mail_notification_deactivate();
		} elseif ( 'enabled' === $status ) {
			if ( isset( $_POST['saveit'] ) && 'all' === $_POST['saveit'] ) {
				check_admin_referer( 'nsp_submit', 'nsp_option_post' );
				if ( ! current_user_can( 'administrator' ) ) {
					die( 'NO permission' );
				}

				if ( ! ( isset( $_POST['nsp_option_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nsp_option_post'] ) ), 'nsp_submit' ) ) ) {
					die( 'Failed security check' );
				}

				$name            = $nsp_option_vars['mail_notification_freq']['name'];
				$freq            = get_option( $name );
				$name            = $nsp_option_vars['mail_notification_time']['name'];
				$timeuser        = get_option( $name );
				$t               = time();
				$crontime_offest = nsp_calculation_offset_time( $t, $timeuser );
				$crontime        = time() + $crontime_offest;
				remove_action( 'nsp_mail_notification', 'nsp_stat_by_email' );
				$timestamp = wp_next_scheduled( 'nsp_mail_notification' );
				wp_unschedule_event( $timestamp, 'nsp_mail_notification' );
				if ( '_oneoff' === $freq ) {
					wp_schedule_single_event( $crontime, 'nsp_mail_notification' );
				} else {
					wp_schedule_event( $crontime, $freq, 'nsp_mail_notification' );
				}
			}
		}
	}

	if ( isset( $_POST['saveit'] ) && 'all' === $_POST['saveit'] ) { // option update request by user.
		check_admin_referer( 'nsp_submit', 'nsp_option_post' );
		if ( ! current_user_can( 'administrator' ) ) {
			die( 'NO permission' );
		}

		if ( ! ( isset( $_POST['nsp_option_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nsp_option_post'] ) ), 'nsp_submit' ) ) ) {
			die( 'Failed security check on option' );
		}

		$i = isset( $_POST['newstatpress_collectloggeduser'] ) ? ( 'checked' === $_POST['newstatpress_collectloggeduser'] ? 'checked' : '' ) : '';
		update_option( 'newstatpress_collectloggeduser', $i );

		$i = isset( $_POST['newstatpress_donotcollectspider'] ) ? ( 'checked' === $_POST['newstatpress_donotcollectspider'] ? 'checked' : '' ) : '';
		update_option( 'newstatpress_donotcollectspider', $i );

		$i = isset( $_POST['newstatpress_cryptip'] ) ? ( 'checked' === $_POST['newstatpress_cryptip'] ? 'checked' : '' ) : '';
		update_option( 'newstatpress_cryptip', $i );

		$i = isset( $_POST['newstatpress_dashboard'] ) ? ( 'checked' === $_POST['newstatpress_dashboard'] ? 'checked' : '' ) : '';
		update_option( 'newstatpress_dashboard', $i );

		$i = isset( $_POST['newstatpress_externalapi'] ) ? ( 'checked' === $_POST['newstatpress_externalapi'] ? 'checked' : '' ) : '';
		update_option( 'newstatpress_externalapi', $i );

		foreach ( $nsp_option_vars as $var ) {
			if ( isset( $_POST[ $var['name'] ] ) ) {

				if ( 'newstatpress_ignore_ip' === $var['name'] ) {
					if ( isset( $_POST['newstatpress_ignore_ip'] ) ) {
						update_option( 'newstatpress_ignore_ip', nsp_filter_for_xss( sanitize_text_field( wp_unslash( $_POST['newstatpress_ignore_ip'] ) ) ) );
					}
				} elseif ( 'newstatpress_ignore_users' === $var['name'] ) {
					if ( isset( $_POST['newstatpress_ignore_users'] ) ) {
						update_option( 'newstatpress_ignore_users', nsp_filter_for_xss( sanitize_text_field( wp_unslash( $_POST['newstatpress_ignore_users'] ) ) ) );
					}
				} elseif ( 'newstatpress_ignore_permalink' === $var['name'] ) {
					if ( isset( $_POST['newstatpress_ignore_permalink'] ) ) {
						if ( isset( $_POST['newstatpress_ignore_permalink'] ) ) {
							update_option( 'newstatpress_ignore_permalink', nsp_filter_for_xss( sanitize_text_field( wp_unslash( $_POST['newstatpress_ignore_permalink'] ) ) ) );
						}
					}
				} elseif ( 'newstatpress_stats_offsets' === $var['name'] ) {
					if ( isset( $_POST['newstatpress_stats_offsets'] ) ) {
						$temp = array();
						foreach ( array_map( 'sanitize_text_field', wp_unslash( $_POST['newstatpress_stats_offsets'] ) ) as $key => $id ) {
							$temp[ $key ] = intval( $id );
						}
						update_option( 'newstatpress_stats_offsets', $temp );
					}
				} else {
					update_option( $var['name'], sanitize_text_field( wp_unslash( $_POST[ $var['name'] ] ) ) );
				}
			}
		}

		// update database too and print message confirmation.
		nsp_build_plugin_sql_table( 'update' );
		print "<br /><div id='optionsupdated' class='updated'><p>" . esc_html__( 'Options saved!', 'newstatpress' ) . '</p></div>';

	} elseif ( isset( $_POST['saveit'] ) && 'mailme' === $_POST['saveit'] ) { // option mailme request by user.
		check_admin_referer( 'nsp_submit', 'nsp_option_post' );
		if ( ! current_user_can( 'administrator' ) ) {
			die( 'NO permission' );
		}

		if ( ! ( isset( $_POST['nsp_option_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nsp_option_post'] ) ), 'nsp_submit' ) ) ) {
			die( 'Failed security check on mail' );
		}

		if ( isset( $_POST['newstatpress_mail_notification_emailaddress'] ) ) {
			update_option( 'newstatpress_mail_notification_emailaddress', sanitize_email( wp_unslash( $_POST['newstatpress_mail_notification_emailaddress'] ) ) ); // save the.
		}
		$mail_confirmation = nsp_stat_by_email( 'test' );

		if ( $mail_confirmation ) {
			print "<br /><div id='mailsent' class='updated'><p>" . esc_html__( 'Email sent by the server!', 'newstatpress' ) . '</p></div>';
		} else {
			print "<br /><div id='mailsent' class='warning'><p>" . esc_html__( 'Problem: Email not sent by the server!', 'newstatpress' ) . '</p></div>';
		}
	}
	?>

	<form id="myoptions" method=post>

		<div id="usual1" class="usual">
		<ul>
		<?php
			$tools_page_tabs = array(
				'general'  => __( 'General', 'newstatpress' ),
				'data'     => __( 'Filters', 'newstatpress' ),
				'overview' => __( 'Overview Menu', 'newstatpress' ),
				'details'  => __( 'Details Menu', 'newstatpress' ),
				'visits'   => __( 'Visits Menu', 'newstatpress' ),
				'database' => __( 'Database', 'newstatpress' ),
				'mail'     => __( 'Email Notification', 'newstatpress' ),
				'api'      => __( 'API', 'newstatpress' ),
			);
			foreach ( $tools_page_tabs as $tab => $name ) {
				echo "<li><a href='#" . esc_attr( $tab ) . "' class='tab" . esc_attr( $tab ) . "'>" . esc_html( $name ) . "</a></li>\n";
			}
			?>
		</ul>

		<!-- tab 'general' -->
		<div id='general'>
		<table class='form-tableH'>
			<tr>

			<?php
			global $nsp_option_vars;

			// input parameters.
			$input_size      = '2';
			$input_maxlength = '3';

			echo "<th scope='row' rowspan='2'>";
			esc_html_e( 'Dashboard', 'newstatpress' );
			echo '</th></tr>';
			$option_title = __( 'Enable NewStatPress widget', 'newstatpress' );
			$option_var   = 'newstatpress_dashboard';
			nsp_print_checked( $option_title, $option_var );

			echo "<tr><th scope='row' rowspan='1'>" . esc_html__( 'Minimum capability to display each specific menu', 'newstatpress' ) . "(<a href='http://codex.wordpress.org/Roles_and_Capabilities' target='_blank'>" . esc_html__( 'more info', 'newstatpress' ) . "</a>)</th><td colspan='2'><span class=\"dashicons dashicons-editor-help\"></span>Reminder: manage_network = Super Admin, manage_options = Administrator, edit_others_posts = Editor, publish_posts = Author, edit_posts = Contributor, Read = Everybody.</td></tr>";

			$option_title = __( 'Overview menu', 'newstatpress' );
			echo "<tr><th scope='row' rowspan='1' class='tab tab2'>";
			echo esc_html( $option_title ) . '</th>';
			$option_var = 'newstatpress_menuoverview_cap';
			$val        = get_option( $option_var );
			nsp_print_option( '', $option_var, $val );

			echo '</tr>';
			echo '<tr>';
			$option_title = __( 'Detail menu', 'newstatpress' );
			echo "<tr><th scope='row' rowspan='2' class='tab tab2'>";
			echo esc_html( $option_title ) . '</th>';
			$option_var = 'newstatpress_menudetails_cap';
			$val        = get_option( $option_var );
			nsp_print_option( '', $option_var, $val );
			echo '</tr>';
			echo '<tr>';

			$option_title = __( 'Visits menu', 'newstatpress' );
			echo "<tr><th scope='row' rowspan='2' class='tab tab2'>";
			echo esc_html( $option_title ) . '</th>';
			$option_var = 'newstatpress_menuvisits_cap';
			$val        = get_option( $option_var );
			nsp_print_option( '', $option_var, $val );
			echo '</tr>';
			echo '<tr>';

			$option_title = __( 'Search menu', 'newstatpress' );
			echo "<tr><th scope='row' rowspan='2' class='tab tab2'>";
			echo esc_html( $option_title ) . '</th>';
			$option_var = 'newstatpress_menusearch_cap';
			$val        = get_option( $option_var );
			nsp_print_option( '', $option_var, $val );
			echo '</tr>';
			echo "<tr>\n";

			$option_title = __( 'Tools menu', 'newstatpress' );
			echo "<tr><th scope='row' rowspan='2' class='tab tab2'>";
			echo esc_html( $option_title ) . '</th>';
			$option_var = 'newstatpress_menutools_cap';
			$val        = get_option( $option_var );
			nsp_print_option( '', $option_var, $val );
			echo '</tr>';
			echo "<tr>\n";

			$option_title = __( 'Options menu', 'newstatpress' );
			echo "<tr><th scope='row' rowspan='2' class='tab tab2'>";
			echo esc_html( $option_title ) . '</th>';
			$option_var = 'newstatpress_menuvisits_cap';
			$val        = get_option( $option_var );
			nsp_print_option( '', $option_var, $val );
			?>
			</tr>
		</table>
		<br/>
		</div>

		<!-- tab 'overview' -->
		<div id='overview'>
		<table class='form-tableH'>
			<tr>
			<th scope='row' rowspan='2'> <?php esc_html_e( 'Visits calculation method', 'newstatpress' ); ?> </th>
			</tr>
			<tr>
			<td>
				<fieldset>
				<?php
				$name = $nsp_option_vars['calculation']['name'];
				$valu = $nsp_option_vars['calculation']['value'];
				echo "
                  <p><input type='radio' name='" . esc_attr( $name ) . "' value=";

				if ( ( get_option( $name ) === '' ) || ( get_option( $name ) === $valu ) ) {
					echo esc_html( $valu ) . ' checked';
				}
						echo ' />
                        <label>';
				esc_html_e( 'Simple sum of distinct IPs (Classic method)', 'newstatpress' ); echo "</label>
                    </p>
                    <p>
                        <input type='radio' name='" . esc_attr( $name ) . "' value=";

							echo 'sum';
				if ( 'sum' === get_option( $name ) ) {
					echo ' checked';
				}

						echo ' />
                      <label>';
						esc_html_e( 'Sum of the distinct IPs of each day', 'newstatpress' );
						echo '<br /> <span class="description">';
						esc_html_e( '(slower than classic method for big database)', 'newstatpress' );
				?>
			</span></label>
				</p>
				</fieldset>
			</td>
			</tr>

			<tr>
			<th scope='row' rowspan='2'> <?php esc_html_e( 'Graph', 'newstatpress' ); ?></th>
			</tr>
			<tr>
			<?php
			$val          = array( array( 7, '' ), array( 10, '' ), array( 20, '' ), array( 30, '' ), array( 50, '' ) );
			$option_title = __( 'Days number in Overview graph', 'newstatpress' );
			$option_var   = 'newstatpress_daysinoverviewgraph';
			nsp_print_option( $option_title, $option_var, $val );
			?>
			</tr>
			<tr>
			<th scope='row' rowspan='3'> <?php esc_html_e( 'Overview', 'newstatpress' ); ?></th>
			</tr>
			<tr>
			<?php
				// translators: add int value at placeholder.
				$option_title = sprintf( __( 'Elements in Overview (default %d)', 'newstatpress' ), intval( $nsp_option_vars['overview']['value'] ) );
				nsp_print_row_input( $option_title, $nsp_option_vars['overview'], $input_size, $input_maxlength );

				echo "<tr><th scope='row' rowspan='6'>" . esc_html__( 'Statistics offsets (Total visits)', 'newstatpress' ) . '</th></tr>';

				// input parameters.
				$input_size      = '10';
				$input_maxlength = '10';
				$name            = $nsp_option_vars['stats_offsets']['name'];
				$val             = get_option( $name );

				$alltotalvisits = empty( $val['alltotalvisits'] ) ? 0 : $val['alltotalvisits'];
				$visitorsfeeds  = empty( $val['visitorsfeeds'] ) ? 0 : $val['visitorsfeeds'];
				$pageviews      = empty( $val['pageviews'] ) ? 0 : $val['pageviews'];
				$pageviewfeeds  = empty( $val['pageviewfeeds'] ) ? 0 : $val['pageviewfeeds'];
				$spy            = empty( $val['spy'] ) ? 0 : $val['spy'];

				$option_title = __( 'Visitors', 'newstatpress' );
				echo '<tr><td class="tab2"><label for="' . esc_attr( $name ) . '[alltotalvisits]">' . esc_html( $option_title ) . "</label></td>\n";
				echo "<td><input class='right' type='number' required name=\"newstatpress_stats_offsets[alltotalvisits]\" value=\"" . esc_attr( $alltotalvisits );
				echo '" size="' . esc_attr( $input_size ) . '" maxlength="' . esc_attr( $input_maxlength ) . "\" />\n</td></tr>\n";

				$option_title = __( 'Visitors through Feeds', 'newstatpress' );
				echo '<tr><td class="tab2"><label for="' . esc_attr( $name ) . '[visitorsfeeds]">' . esc_html( $option_title ) . "</label></td>\n";
				echo "<td><input class='right' type='number' required name=\"newstatpress_stats_offsets[visitorsfeeds]\" value=\"" . esc_attr( $visitorsfeeds );
				echo '" size="' . esc_attr( $input_size ) . '" maxlength="' . esc_attr( $input_maxlength ) . "\" />\n</td></tr>\n";

				$option_title = __( 'Pageviews', 'newstatpress' );
				echo '<tr><td class="tab2"><label for="' . esc_attr( $name ) . '[pageviews]">' . esc_html( $option_title ) . "</label></td>\n";
				echo "<td><input class='right' type='number' required name=\"newstatpress_stats_offsets[pageviews]\" value=\"" . esc_attr( $pageviews );
				echo '" size="' . esc_attr( $input_size ) . '" maxlength="' . esc_attr( $input_maxlength ) . "\" />\n</td></tr>\n";

				$option_title = __( 'Pageviews through Feeds', 'newstatpress' );
				echo '<tr><td class="tab2"><label for="' . esc_attr( $name ) . '[pageviewfeeds]">' . esc_html( $option_title ) . "</label></td>\n";
				echo "<td><input class='right' type='number' required name=\"newstatpress_stats_offsets[pageviewfeeds]\" value=\"" . esc_attr( $pageviewfeeds );
				echo '" size="' . esc_attr( $input_size ) . '" maxlength="' . esc_attr( $input_maxlength ) . "\" />\n</td></tr>\n";

				$option_title = __( 'Spiders', 'newstatpress' );
				echo '<tr><td class="tab2"><label for="' . esc_attr( $name ) . '[spy]">' . esc_html( $option_title ) . "</label></td>\n";
				echo "<td><input class='right' type='number' required name=\"newstatpress_stats_offsets[spy]\" value=\"" . esc_attr( $spy );
				echo '" size="' . esc_attr( $input_size ) . '" maxlength="' . esc_attr( $input_maxlength ) . "\" />\n</td></tr>\n";
			?>
		</table>
		<br />
		</div>

		<!-- tab 'data' -->
		<div id='data'>
		<table class='form-tableH'>
			<tr>
			<?php
			// traduction $variable addition for Poedit parsing.
			__( 'Never', 'newstatpress' );
			__( 'All', 'newstatpress' );
			__( 'month', 'newstatpress' );
			__( 'months', 'newstatpress' );
			__( 'week', 'newstatpress' );
			__( 'weeks', 'newstatpress' );

			echo "<th scope='row' rowspan='4'>";
			esc_html_e( 'Data collection', 'newstatpress' );
			echo '</th></tr>';

			$option_title = __( 'Crypt IP addresses', 'newstatpress' );
			$option_var   = 'newstatpress_cryptip';
			nsp_print_checked( $option_title, $option_var );

			$option_title = __( 'Collect data about logged users, too.', 'newstatpress' );
			$option_var   = 'newstatpress_collectloggeduser';
			nsp_print_checked( $option_title, $option_var );

			$option_title = __( 'Do not collect spiders visits', 'newstatpress' );
			$option_var   = 'newstatpress_donotcollectspider';
			nsp_print_checked( $option_title, $option_var );
			?>
		</table>
		<table class='form-tableH'>
			<tr>
			<th class='padd' scope='row' rowspan='4'><?php esc_html_e( 'Data purge', 'newstatpress' ); ?></th>
			</tr>
			<tr>
			<?php
			$val          = array( array( '0', 'Never' ), array( 1, 'month' ), array( 3, 'months' ), array( 6, 'months' ), array( 12, 'months' ) );
			$option_title = __( 'Automatically delete all visits older than', 'newstatpress' );
			$option_var   = 'newstatpress_autodelete';
			nsp_print_option( $option_title, $option_var, $val );
			echo '</tr>';
			echo '<tr>';

			$option_title = __( 'Automatically delete only spiders visits older than', 'newstatpress' );
			$option_var   = 'newstatpress_autodelete_spiders';
			nsp_print_option( $option_title, $option_var, $val );
			?>
		</tr>
		</table>
		<table class='form-tableH'>
			<tr>
			<th class='padd' scope='row' rowspan='9'><?php esc_html_e( 'Parameters to ignore', 'newstatpress' ); ?></th>
			<?php

				$option_title       = __( 'Logged users', 'newstatpress' );
				$option_var         = 'newstatpress_ignore_users';
				$option_description = __( 'Enter a list of users you don\'t want to track, separated by commas, even if collect data about logged users is on', 'newstatpress' );
				nsp_print_textaera( $option_title, $option_var, $option_description );

				$option_title       = __( 'IP addresses', 'newstatpress' );
				$option_var         = 'newstatpress_ignore_ip';
				$option_description = __( 'Enter a list of networks you don\'t want to track, separated by commas. Each network <strong>must</strong> be defined using the CIDR notation (i.e. <em>192.168.1.1/24</em>). <br />If the format is incorrect, NewStatPress may not track pageviews properly.', 'newstatpress' );
				nsp_print_textaera( $option_title, $option_var, $option_description );

				$option_title       = __( 'Pages and posts', 'newstatpress' );
				$option_var         = 'newstatpress_ignore_permalink';
				$option_description = __( 'Enter a list of permalinks you don\'t want to track, separated by commas. You should omit the domain name from these resources: <em>/about, p=1</em>, etc. <br />NewStatPress will ignore all the pageviews whose permalink <strong>contains</strong> at least one of them.', 'newstatpress' );
				nsp_print_textaera( $option_title, $option_var, $option_description );
			?>
		</table>
		</div>

		<!-- tab 'visits' -->
		<div id='visits'>
		<table class='form-tableH'>
			<tr>
			<th scope='row' rowspan='2'>
				<?php esc_html_e( 'Visitors by Spy', 'newstatpress' ); ?>
			</th>
			<?php
				$val          = array( array( 20, '' ), array( 50, '' ), array( 100, '' ) );
				$option_title = __( 'number of IP per page', 'newstatpress' );
				$option_var   = 'newstatpress_ip_per_page_newspy';
				nsp_print_option( $option_title, $option_var, $val );
			?>
			</tr>
			<tr>
			<?php
				$option_title = __( 'number of visits for IP', 'newstatpress' );
				$option_var   = 'newstatpress_visits_per_ip_newspy';
				nsp_print_option( $option_title, $option_var, $val );
			?>
			</tr>
			<tr>
			<th class='padd' scope='row' colspan='3'></th>
			</tr>
			<tr>
			<th class='padd' scope='row' rowspan='2'>
				<?php esc_html_e( 'Parameters to ignore', 'newstatpress' ); ?>
			</th>
			<?php
				$option_title = __( 'number of bot per page', 'newstatpress' );
				$option_var   = 'newstatpress_bot_per_page_spybot';
				nsp_print_option( $option_title, $option_var, $val );
			?>
			</tr>
			<tr>
			<?php
			$option_title = __( 'number of bot for IP', 'newstatpress' );
			$option_var   = 'newstatpress_visits_per_bot_spybot';
			nsp_print_option( $option_title, $option_var, $val );
			?>
		</table>
		</div>

		<!-- tab 'details' -->
		<div id='details'>
		<table class='form-tableH'>
			<tr>
			<th class='padd' scope='row' rowspan='14'>
				<?php esc_html_e( 'Element numbers to display in', 'newstatpress' ); ?>
			</th>
		<?php
			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Top days (default %d)', 'newstatpress' ), intval( $nsp_option_vars['top_days']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['top_days'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'O.S. (default %d)', 'newstatpress' ), intval( $nsp_option_vars['os']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['os'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Browser (default %d)', 'newstatpress' ), intval( $nsp_option_vars['browser']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['browser'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Feed (default %d)', 'newstatpress' ), intval( $nsp_option_vars['feed']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['feed'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Search Engines (default %d)', 'newstatpress' ), intval( $nsp_option_vars['searchengine']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['searchengine'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Top Search Terms (default %d)', 'newstatpress' ), intval( $nsp_option_vars['search']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['search'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Top Referrer (default %d)', 'newstatpress' ), intval( $nsp_option_vars['referrer']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['referrer'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Countries/Languages (default %d)', 'newstatpress' ), intval( $nsp_option_vars['languages']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['languages'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Spiders (default %d)', 'newstatpress' ), intval( $nsp_option_vars['spiders']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['spiders'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Top Pages (default %d)', 'newstatpress' ), intval( $nsp_option_vars['pages']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['pages'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Top Days - Unique visitors (default %d)', 'newstatpress' ), intval( $nsp_option_vars['visitors']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['visitors'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Top Days - Pageviews (default %d)', 'newstatpress' ), intval( $nsp_option_vars['daypages']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['daypages'], $input_size, $input_maxlength );

			// translators: add int value at placeholder.
			$option_title = sprintf( __( 'Top IPs - Pageviews (default %d)', 'newstatpress' ), intval( $nsp_option_vars['ippages']['value'] ) );
			nsp_print_row_input( $option_title, $nsp_option_vars['ippages'], $input_size, $input_maxlength );
		?>
		</table>
		</div>

		<!-- tab 'database'  -->
		<div id='database'>
		<?php
			$option_description  = __( 'Select the interval of date from today you want to use for updating your database with new definitions', 'newstatpress' );
			$option_description .= ' ';
			$option_description .= __( '(To update your database, go to Tools page)', 'newstatpress' );
			$option_description .= '.';
			$option_description2 = __( 'Note: Be aware, larger is the interval, longer is the update and bigger are the resources required.', 'newstatpress' );
		?>


		<p><?php echo esc_html( $option_description ); ?></p>
		<p><span class="dashicons dashicons-warning"></span><i><?php echo esc_html( $option_description2 ); ?></i></p>

		<table class='form-tableH'>

			<tr >
				<?php
				$val          = array( array( '', 'All' ), array( 1, 'week' ), array( 2, 'weeks' ), array( 3, 'weeks' ), array( 1, 'month' ), array( 2, 'months' ), array( 3, 'months' ), array( 6, 'months' ), array( 9, 'months' ), array( 12, 'months' ) );
				$option_title = __( 'Update data in the given period', 'newstatpress' );
				$option_var   = 'newstatpress_updateint';
				nsp_print_option( $option_title, $option_var, $val );
				?>
			</tr>
		</table>
		<br />
		</div>

		<!-- tab 'mail'  -->
		<div id='mail'>
		<?php
			$option_description         = __( 'This option allows you to get periodic reports by email (dashboard informations). You can customize the frequency and the publishing time of the reports and also the description of Sender.', 'newstatpress' );
			$option_description2        = __( 'Note: WP Cron job need to be operational in aim to schedule Email Notification.', 'newstatpress' );
			$mailaddress_description    = __( 'Mailing address accept only one email address, check is well valid before reporting issues.', 'newstatpress' );
			$timepublishing_description = __( 'Notification will be sent at UTC time.', 'newstatpress' );
			$from_description           = __( 'Sender could be personalized according your website (by default : \'NewStatPress\').', 'newstatpress' );

			$time_format = 'H:i:s';

			$tzstring       = get_option( 'timezone_string' );
			$current_offset = get_option( 'gmt_offset' );

		if ( $current_offset >= 0 ) {
			$current_offset = '+' . $current_offset;
		}

		if ( '' === $tzstring ) {
			$tz = sprintf( 'UTC%s', $current_offset );
		} else {
			$tz = sprintf( '%s (UTC%s)', str_replace( '_', ' ', $tzstring ), $current_offset );
		}

			$name  = $nsp_option_vars['mail_notification_address']['name'];
			$email = get_option( $name );
		if ( '' === $email ) {
			$current_user = wp_get_current_user();
			$email        = $current_user->user_email;
		}
			$name   = $nsp_option_vars['mail_notification_sender']['name'];
			$sender = get_option( $name );
		if ( '' === $sender ) {
			$sender = $nsp_option_vars['mail_notification_sender']['value'];
		}

		?>

		<p><?php echo esc_html( $option_description ); ?></p>
		<p><span class="dashicons dashicons-warning"></span><i><?php echo esc_html( $option_description2 ); ?></i></p>

		<table class='form-tableH'>
			<tr>
			<th scope='row' rowspan='2'><?php esc_html_e( 'Statistics notification is', 'newstatpress' ); ?></th>
			</tr>
			<tr>
			<td>
				<fieldset>
				<?php
				$name    = $nsp_option_vars['mail_notification']['name'];
				$default = $nsp_option_vars['mail_notification']['value'];
				?>
				<form id="myForm">
				<p>
					<input class="tog" type='radio' id='dis' name='<?php echo esc_attr( $name ); ?>' value='disabled'<?php nsp_input_selected( $name, 'disabled', $default ); ?> /><label> <?php esc_html_e( 'Disabled', 'newstatpress' ); ?></label>
				</p>
				<p>
					<input class="tog" type='radio' id='ena' name='<?php echo esc_attr( $name ); ?>' value='enabled'<?php nsp_input_selected( $name, 'enabled', $default ); ?>  /><label> <?php esc_html_e( 'Enabled', 'newstatpress' ); ?></label>
				</p>
				</form>
				</fieldset>
			</td>
			</tr>
			<tr>
			<th scope='row' rowspan='2'><?php esc_html_e( 'Event schedule', 'newstatpress' ); ?></th>
			</tr>
			<tr>
			<td>
				<?php
				$name = $nsp_option_vars['mail_notification_freq']['name'];
				nsp_print_schedules( get_option( $name ) );
				?>
			</td>
			</tr>
			<tr>
			<th scope='row' rowspan='2'><?php esc_html_e( 'Publishing time', 'newstatpress' ); ?></th>
			</tr>
			<tr>
			<td>
				<select name="newstatpress_mail_notification_time" id="mail_time">
			<option value="0">- <?php esc_html_e( 'Select', 'newstatpress' ); ?> -</option> ?>
			<?php
			$name     = $nsp_option_vars['mail_notification_time']['name'];
			$timeuser = get_option( $name );

			for ( $h = 0; $h <= 23; $h++ ) {
				for ( $m = 0; $m <= 45; $m += 15 ) {
					$value = sprintf( '%02d', $h ) . ':' . sprintf( '%02d', $m );
					if ( $timeuser === $value ) {
						echo '<option value="' . esc_html( $value ) . '" selected>' . esc_html( $value ) . '</option>\n';
					} else {
						echo '<option value="' . esc_attr( $value ) . '">' . esc_html( $value ) . '</option>\n';
					}
				}
			}
			?>
			</select>
			<span id="utc-time">
			<?php
									// translators: placeholders for time.
									printf( esc_html__( 'UTC time is %s', 'wp-crontrol' ), '<code>' . esc_html( date_i18n( $time_format, false, true ) ) . '</code>' );
			?>
													</span>			
			<span id="local-time">
			<?php
						// translators: placeholders for time.
						printf( esc_html__( 'Local time is %s', 'wp-crontrol' ), '<code>' . esc_html( date_i18n( $time_format ) ) . '</code>' );
			?>
			</span>
			<p class="description"><?php echo esc_html( $timepublishing_description ); ?></p>

			</td>
			</tr>
			<tr>
			<th scope='row' rowspan='2'><?php esc_html_e( 'Sender Description (From)', 'newstatpress' ); ?></th>
			</tr>
			<tr>
			<td>
				<input id="sender" class='left' type='text' name='newstatpress_mail_notification_sender' value='<?php echo esc_html( $sender ); ?>' size=20 maxlength=60 />
				<p class="description"><?php echo esc_html( $from_description ); ?></p>
			</td>
			</tr>
			<tr>
			<th scope='row' rowspan='2'><?php esc_html_e( 'Mailing address', 'newstatpress' ); ?></th>
			</tr>
			<tr>
			<td>
				<input id="mail_address" class='left' type='email' name='newstatpress_mail_notification_emailaddress' value='<?php echo esc_html( $email ); ?>' size=20 maxlength=60 />
				<button id="testmail" class='button button-secondary' type=submit name=saveit value=mailme><?php esc_html_e( 'Email Test', 'newstatpress' ); ?></button>
				<p class="description"><?php echo esc_html( $mailaddress_description ); ?></p>
			</label>
			<input type=hidden name='newstatpress_mail_notification_info' value=
			<?php
			$current_user = wp_get_current_user();
			echo esc_html( $current_user->display_name );
			?>
				/>
			</td>
			</tr>
		</table>
		</div>

		<!-- tab 'API' -->
		<div id='api'>
		<?php
			$newstatpress_url    = nsp_plugin_url();
			$url2                = $newstatpress_url . 'doc/external_api.pdf';
			$option_description  = __( 'The external API is build to let you to use the collected data from your Newstatpress plugin in an other web server application (for example you can show data relative to your WordPress blog, inside a Drupal site that run since an another server).', 'newstatpress' );
			$option_description .= ' <strong>' . __( 'However the external API is also used by Newstatpress itself for speedup page rendering of queried data (when are processed AJAX calls), so \'overview page & widget dashboard\' will be not working if you not activate it.', 'newstatpress' );
			$option_description .= '</strong><br /> ' . __( 'To use it, a key is needed to allow NewStatpress to recognize that you and only you want the data and not the not authorized people (Let the input form blank means that you allow everyone to get data without authorization if external API is activated).', 'newstatpress' );
			$option_description .= "<br/><br/><a target=\'_blank\' href='$url2'>" . __( 'Full documentation (PDF)', 'newstatpress' ) . '</a><br />';
			$option_description3 = __( 'You must generate or set manually a private key for the external API : only alphanumeric characters are allowed (A-Z, a-z, 0-9), length should be between 64 and 128 characters.', 'newstatpress' );

			$option_title = __( 'Enable External API', 'newstatpress' );
			$option_var   = 'newstatpress_externalapi';
		?>
		<div class="optiondescription">
			<p>
			<?php
			echo wp_kses(
				$option_description,
				array(
					'a'      => array(
						'href'   => array(),
						'target' => array(),
					),
					'br'     => array(),
					'strong' => array(),
				)
			);
			?>
			</p>
		</div>
		<table class='form-tableH'>
			<tr>
			<th scope='row' rowspan='2'><?php esc_html_e( 'Extern API', 'newstatpress' ); ?></th>
			</tr>
			<?php
			nsp_print_checked( $option_title, $option_var );

			$option_title = __( 'API key', 'newstatpress' );
			$option_var   = 'newstatpress_apikey';
			?>
			<tr>
			<th scope='row' rowspan='2'>
				<p class='ign'>
				<label for=<?php echo esc_html( $option_var ); ?>><?php echo esc_html( $option_title ); ?></label>
				<!-- </p> -->

			</p>
			</th>
			</tr>


			<tr>
			<td>
				<div class='center'>
				<p class="textarealimited">
					<textarea class='large-text code api' minlength='64' maxlength='128' cols='50' rows='3' name='<?php echo esc_html( $option_var ); ?>' id='<?php echo esc_html( $option_var ); ?>'><?php echo esc_html( get_option( $option_var ) ); ?></textarea>
				</p>
				<p class="description textarealimited"><?php echo esc_html( $option_description3 ); ?></p>
				</div>

			<div class='left'>
				<div class='button' type='button' onClick='nspGenerateAPIKey()'><?php esc_html_e( 'Generate new API key', 'newstatpress' ); ?></div>
			</div>
			<br/>
			</td>
		</tr>
		</table>
		</div>

		<!-- Save Options Button -->
		<?php wp_nonce_field( 'nsp_submit', 'nsp_option_post' ); ?>
		<input type="hidden" name="page" value="newstatpress">
		<input type="hidden" name="newstatpress_action" value="options">
		<button class="button button-primary" type="submit" name="saveit" value="all"><?php esc_html_e( 'Save options', 'newstatpress' ); ?></button>

		</div>
	</form>
	<?php nsp_load_time(); ?>
</div>

<script type="text/javascript">
	//jQuery("#usual1 ul").idTabs(general);
	jQuery(document).ready(function($){
	$("#usual1").tabs();
	});
</script>

	<?php

}
