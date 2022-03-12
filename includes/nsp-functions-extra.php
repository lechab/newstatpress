<?php
/**
 * Extra functions
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
 * Get valide user IP even behind proxy or load balancer (Could be fake)
 * added by cHab
 *
 * @return $user_ip
 */
function nsp_get_user_ip() {
	$user_ip      = '';
	$ip_pattern   = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/';
	$http_headers = array(
		'HTTP_X_REAL_IP',
		'HTTP_X_CLIENT',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_CLIENT_IP',
		'REMOTE_ADDR',
	);

	foreach ( $http_headers as $header ) {
		if ( isset( $_SERVER[ $header ] ) ) {
			if ( function_exists( 'filter_var' ) && filter_var( wp_unslash( $_SERVER[ $header ] ), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
				$user_ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
				break;
			} else { // for php version < 5.2.0.
				if ( preg_match( $ip_pattern, sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) ) ) {
					$user_ip = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
					break;
				}
			}
		}
	}

	return $user_ip;
}

/**
 * Get if connection is ssl
 *
 * @return boolean the ssl state
 */
function nsp_connexion_is_ssl() {
	if ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) {
		return true; }
	if ( ! empty( $_SERVER['SERVER_PORT'] ) && ( '443' === $_SERVER['SERVER_PORT'] ) ) {
		return true; }
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
		return true; }
	if ( ! empty( $_SERVER['HTTP_X_FORWARDED_SSL'] ) && 'off' !== $_SERVER['HTTP_X_FORWARDED_SSL'] ) {
		return true; }
	return false;
}
// ---------------------------------------------------------------------------
// CRON Functions
// ---------------------------------------------------------------------------

/**
 * Add Cron intervals : 4 times/day, Once/week, Once/mounth
 * added by cHab
 *
 * @param string $schedules the schedules.
 * @return $schedules
 */
function nsp_cron_intervals( $schedules ) {
	$schedules['fourlybyday'] = array(
		'interval' => 21600, // seconds.
		'display'  => __( 'Four time by Day', 'newstatpress' ),
	);
	$schedules['weekly']      = array(
		'interval' => 604800,
		'display'  => __( 'Once a Week', 'newstatpress' ),
	);
	$schedules['monthly']     = array(
		'interval' => 2635200,
		'display'  => __( 'Once a Month', 'newstatpress' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'nsp_cron_intervals' );


/**
 * Calculate nsp_calculate_epoch_offset_time
 *
 * @param int    $t1 time 1.
 * @param int    $t2 time 2.
 * @param string $output_unit unit for output.
 * @return int delta
 */
function nsp_calculate_epoch_offset_time( $t1, $t2, $output_unit ) {
	// to complete with more output_unit.
	$offset_time_in_seconds = abs( $t1 - $t2 );

	if ( 'day' === $output_unit ) {
		$offset_time = $offset_time_in_seconds / 86400;
	}
	if ( 'hour' === $output_unit ) {
		$offset_time = $offset_time_in_seconds / 3600;
	} else {
		$offset_time = $offset_time_in_seconds;
	}

	return $offset_time;
}

/**
 * Get the number of days since installation
 *
 * @return int the number
 */
function nsp_get_days_installed() {
	global $nsp_option_vars;
	$name          = $nsp_option_vars['settings']['name'];
	$settings      = get_option( $name );
	$install_date  = empty( $settings['install_date'] ) ? time() : $settings['install_date'];
	$num_days_inst = nsp_calculate_epoch_offset_time( $install_date, time(), 'day' );
	if ( $num_days_inst < 1 ) {
		$num_days_inst = 1;
	}

	return $num_days_inst;
}

// ---------------------------------------------------------------------------
// URL Functions
// ---------------------------------------------------------------------------

/**
 * Extract the feed from the given url
 *
 * @param string $url the url to parse.
 * @return the extracted url
 *************************************/
function nsp_extract_feed_from_url( $url ) {
	list($null,$q) = array_pad( explode( '?', $url, 2 ), 2, null );

	if ( strpos( $q, '&' ) !== false ) {
		list($res,$null) = explode( '&', $q );
	} else {
		$res = $q;
	}

	return $res;
}

/**
 * Get the url
 */
function nsp_get_url() {
	$url = nsp_connexion_is_ssl() ? 'https://' : 'http://';
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$url .= NSP_SERVER_NAME . sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}
	return esc_url( $url );
}

/**
 * Fix poorly formed URLs so as not to throw errors or cause problems
 *
 * @param string $url the url.
 * @param string $rem_frag the frag.
 * @param string $rem_query the query.
 * @param string $rev the rev.
 * @return  $url the url
 */
function nsp_fix_url( $url, $rem_frag = false, $rem_query = false, $rev = false ) {
	$url = trim( $url );
	/* Too many forward slashes or colons after http */
	$url = preg_replace( '~^(https?)\:+/+~i', '$1://', $url );
	/* Too many dots */
	$url = preg_replace( '~\.+~i', '.', $url );
	/* Too many slashes after the domain */
	$url = preg_replace( '~([a-z0-9]+)/+([a-z0-9]+)~i', '$1/$2', $url );
	/* Remove fragments */
	if ( ! empty( $rem_frag ) && strpos( $url, '#' ) !== false ) {
		$url_arr = explode( '#', $url );
		$url     = $url_arr[0]; }
	/* Remove query string completely */
	if ( ! empty( $rem_query ) && strpos( $url, '?' ) !== false ) {
		$url_arr = explode( '?', $url );
		$url     = $url_arr[0]; }
	/* Reverse */
	if ( ! empty( $rev ) ) {
		$url = strrev( $url ); }
	return $url;
}

/**
 * Get query string array from URL
 *
 * @param string $url the url.
 */
function nsp_get_query_args( $url ) {
	if ( empty( $url ) ) {
		return array(); }
	$query_str = nsp_get_query_string( $url );
	parse_str( $query_str, $args );
	return $args;
}

/**
 * Get query string
 *
 * @param string $url the url.
 */
function nsp_get_query_string( $url ) {
	/***
	* Get query string from URL
	* Filter URLs with nothing after http
	*/
	if ( empty( $url ) || preg_match( '~^https?\:*/*$~i', $url ) ) {
		return ''; }
	/* Fix poorly formed URLs so as not to throw errors when parsing */
	$url = nsp_fix_url( $url );
	/* NOW start parsing */
	$parsed = @wp_parse_url( $url );
	/* Filter URLs with no query string */
	if ( empty( $parsed['query'] ) ) {
		return ''; }
	$query_str = $parsed['query'];
	return $query_str;
}

/**
 * Admin nag notices
 */
function nsp_admin_nag_notices() {
	global $current_user;
	$nag_notices = get_user_meta( $current_user->ID, 'newstatpress_nag_notices', true );
	if ( ! empty( $nag_notices ) ) {
		$nid           = $nag_notices['nid'];
		$style         = $nag_notices['style'];
		$timenow       = time();
		$url           = nsp_get_url();
		$query_args    = nsp_get_query_args( $url );
		$query_str     = '?' . http_build_query(
			array_merge(
				$query_args,
				array(
					'newstatpress_hide_nag' => '1',
					'nid'                   => $nid,
				)
			)
		);
		$query_str_con = 'QUERYSTRING';
		$notice        = str_replace( array( $query_str_con ), array( $query_str ), $nag_notices['notice'] );

		global $pagenow;
		$page_nsp = 0;

		if ( isset( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case 'nsp-main':
							$page_nsp = 1;
					break;
				case 'nsp-details':
							$page_nsp = 1;
					break;
				case 'nsp-visits':
							$page_nsp = 1;
					break;
				case 'nsp-search':
							$page_nsp = 1;
					break;
				case 'nsp-tools':
							$page_nsp = 1;
					break;
				case 'nsp-options':
							$page_nsp = 1;
					break;
				case 'nsp-credits':
							$page_nsp = 1;
					break;

				default:
							$page_nsp = 0;
					break;
			}
		}

		// Display NSP box if user are in plugins page or nsp plugins.
		if ( ( 'n03' !== $nid && 1 === $page_nsp ) || ( 'n03' === $nid && 'plugins.php' === $pagenow ) ) {
			?>
				<div id="nspnotice" class="<?php echo esc_html( $style ); ?>" style="padding:10px">
			<?php
			if ( 'n03' === $nid ) {
				echo '<a id="close" class="close" href="' . esc_attr( $query_str ) . '" target="_self" rel="external"><span class="dashicons dashicons-no"></span>close</a>';
				echo '<h4>' . esc_html__( 'NewStatPress News', 'newstatpress' ) . '</h4>';
			}
			echo wp_kses(
				$notice,
				array(
					'a'  => array(
						'href'   => array(),
						'target' => array(),
						'class'  => array(),
					),
					'br' => array(),
					'p'  => array(),
					'i'  => array(),
				)
			);
			?>
		</div>
			<?php
		}
	}
}

/**
 * Check nag notices
 */
function nsp_check_nag_notices() {
	global $current_user;
	$status = get_user_meta( $current_user->ID, 'newstatpress_nag_status', true );
	if ( ! empty( $status['currentnag'] ) ) {
		add_action( 'admin_notices', 'nsp_admin_nag_notices' );
		return; }
	if ( ! is_array( $status ) ) {
		$status = array();
		update_user_meta( $current_user->ID, 'newstatpress_nag_status', $status ); }
	$timenow       = time();
	$num_days_inst = nsp_get_days_installed();
	$votedate      = 14;
	$donatedate    = 90;
	$query_str_con = 'QUERYSTRING';
	/* Notices (Positive Nags) */
	if ( empty( $status['news'] ) ) {
		$nid                  = 'n03';
		$style                = 'updated ';
		$notice_text          = '<p>' . __( 'Since the introduction of new method for speed up the display (needed for dashboard and overview), you must activate the external API in Options>Api page.', 'newstatpress' ) . '</p>';
		$notice_text         .= __( 'This major change was necessary in aim to improve the plugin for users with big database. In addition, it will allow to be more flexible about the graphical informations in next versions.', 'newstatpress' ) . '</p>';
		$status['currentnag'] = true;
		$status['news']       = false;
	}

	if ( empty( $status['currentnag'] ) && ( empty( $status['lastnag'] ) || $status['lastnag'] <= $timenow - 1209600 ) ) {
		if ( empty( $status['vote'] ) && $num_days_inst >= $votedate ) {
			$nid   = 'n01';
			$style = 'notice';

			$notice_text  = '<p>' . __( 'It looks like you\'ve been using NewStatPress for a while now. That\'s great!', 'newstatpress' ) . '</p>';
			$notice_text .= '<p>' . __( 'If you find this plugin useful, would you take a moment to give it a rating on WordPress.org?', 'newstatpress' );
			$notice_text .= '<br /><i> (' . __( 'NB: please open a ticket on the support page instead of adding it to your rating commentaries if you wish to report an issue with the plugin, it will be processed more quickly by the team.', 'newstatpress' ) . ')</i></p>';
			$notice_text .= '<a class=\"button button-primary\" href=\"' . NSP_RATING_URL . '\" target=\"_blank\" rel=\"external\">' . __( 'Yes, I\'d like to rate it!', 'newstatpress' ) . '</a>';
			$notice_text .= ' &nbsp; ';
			$notice_text .= '<a class=\"button button-default\" href=\"' . $query_str_con . '\" target=\"_self\" rel=\"external\">' . __( 'I already did!', 'newstatpress' ) . '</a>';

			$status['currentnag'] = true;
			$status['vote']       = false;
		} elseif ( empty( $status['donate'] ) && $num_days_inst >= $donatedate ) {
			$nid   = 'n02';
			$style = 'notice';

			$notice_text  = '<p>' . __( 'You\'ve been using NewStatPress for several months now. We hope that means you like it and are finding it helpful.', 'newstatpress' ) . '</p>';
			$notice_text .= '<p>' . __( 'NewStatPress is provided for free and maintained only on free time. If you like the plugin, consider a donation to help further its development', 'newstatpress' ) . '</p>';
			$notice_text .= '<a class=\"button button-primary\" href=\"' . NSP_DONATE_URL . '\" target=\"_blank\" rel=\"external\">' . __( 'Yes, I\'d like to donate!', 'newstatpress' ) . '</a>';
			$notice_text .= ' &nbsp; ';
			$notice_text .= '<a class=\"button button-default\" href=\"' . $query_str_con . '\" target=\"_self\" rel=\"external\">' . __( 'I already did!', 'newstatpress' ) . '</a>';

			$status['currentnag'] = true;
			$status['donate']     = false;
		}
	}

	if ( ! empty( $status['currentnag'] ) ) {
		add_action( 'admin_notices', 'nsp_admin_nag_notices' );
		$new_nag_notice = array(
			'nid'    => $nid,
			'style'  => $style,
			'notice' => $notice_text,
		);
		update_user_meta( $current_user->ID, 'newstatpress_nag_notices', $new_nag_notice );
		update_user_meta( $current_user->ID, 'newstatpress_nag_status', $status );
	}
}

/**
 * Admin notices
 */
function nsp_admin_notices() {
	$admin_notices = get_option( 'newstatpress_admin_notices' );
	if ( ! empty( $admin_notices ) ) {
		$style         = $admin_notices['style'];
		$notice        = $admin_notices['notice'];
		$query_str_con = 'QUERYSTRING';
		echo '<div class="' . esc_attr( $style ) . '"><p>' . esc_html( $notice ) . '</p></div>';
	}
	delete_option( 'newstatpress_admin_notices' );
}

add_action( 'admin_init', 'nsp_hide_nag_notices', -10 );

/**
 * Hide Nag notice
 */
function nsp_hide_nag_notices() {
	$ns_codes = array(
		'n01' => 'vote',
		'n02' => 'donate',
		'n03' => 'news',
	);
	if ( ! isset( $_GET['newstatpress_hide_nag'], $_GET['nid'], $ns_codes[ $_GET['nid'] ] ) || '1' !== $_GET['newstatpress_hide_nag'] ) {
		return; }
	global $current_user;
	$status     = get_user_meta( $current_user->ID, 'newstatpress_nag_status', true );
	$timenow    = time();
	$url        = nsp_get_url();
	$query_args = nsp_get_query_args( $url );
	unset( $query_args['newstatpress_hide_nag'], $query_args['nid'] );
	$query_str = http_build_query( $query_args );
	if ( '' !== $query_str ) {
		$query_str = '?' . $query_str; }
	$redirect_url         = nsp_fix_url( $url, true, true ) . $query_str;
	$status['currentnag'] = false;
	if ( 'n03' !== $_GET['nid'] ) {
		$status['lastnag'] = $timenow;
	}
	$status[ $ns_codes[ sanitize_text_field( wp_unslash( $_GET['nid'] ) ) ] ] = true;
	update_user_meta( $current_user->ID, 'newstatpress_nag_status', $status );
	update_user_meta( $current_user->ID, 'newstatpress_nag_notices', array() );
	wp_safe_redirect( $redirect_url );
	exit;
}

// ---------------------------------------------------------------------------
// OTHER Functions
// ---------------------------------------------------------------------------

/**
 * Show loading time
 */
function nsp_load_time() {
	echo "<font size='1'>Page generated in " . esc_html( timer_stop( 0, 2 ) ) . 's ' . esc_html( get_num_queries() ) . ' SQL queries</font>';
}

/**
 * Display tabs of navigation bar for menu in page
 *
 * @param string $menu_tabs list of menu tabs.
 * @param string $current current tabs.
 * @param string $ref page reference.
 */
function nsp_display_tabs_navbar_for_menu_page( $menu_tabs, $current, $ref ) {
	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';
	foreach ( $menu_tabs as $tab => $name ) {
		$class = ( $tab === $current ) ? ' nav-tab-active' : '';
		echo "<a class='nav-tab" . esc_attr( $class ) . ' tab' . esc_attr( $tab ) . "' href='?page=" . esc_attr( $ref ) . '&tab=' . esc_attr( $tab ) . "'>" . esc_html( $name ) . '</a>';
	}
	echo '</h2>';
}

// ---------------------------------------------------------------------------
// TABLE Functions
// ---------------------------------------------------------------------------

/**
 * Show tables size
 *
 * @param string $table the table to use.
 */
function nsp_table_size( $table ) {
	global $wpdb;
	// use prepare.
	$res = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table ) ); // db call ok; no-cache ok.
	foreach ( $res as $fstatus ) {
		$data_lenght = $fstatus->Data_length; // phpcs:ignore -- not in valid snake_case format: it is a DB field!
		$data_rows   = $fstatus->Rows; // phpcs:ignore -- not in valid snake_case format: it is a DB field!
	}
	return number_format( ( $data_lenght / 1024 / 1024 ), 2, ',', ' ' ) . " Mb ($data_rows " . __( 'records', 'newstatpress' ) . ')';
}

/**
 * Show tables size 2
 *
 * @param string $table the table to use.
 */
function nsp_table_size2( $table ) {
	global $wpdb;
	// use prepare.
	$res = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table ) ); // db call ok; no-cache ok.
	foreach ( $res as $fstatus ) {
		$data_lenght = $fstatus->Data_length; // phpcs:ignore -- not in valid snake_case format: it is a DB field!
		$data_rows   = $fstatus->Rows; // phpcs:ignore -- not in valid snake_case format: it is a DB field!
	}
	return number_format( ( $data_lenght / 1024 / 1024 ), 2, ',', ' ' ) . '  ' . __( 'Mb', 'newstatpress' );
}

/**
 * Show tables records
 *
 * @param string $table the table to use.
 */
function nsp_table_records( $table ) {
	global $wpdb;
	// use prepare.
	$res = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table ) ); // db call ok; no-cache ok.
	foreach ( $res as $fstatus ) {
		$data_lenght = $fstatus->Data_length; // phpcs:ignore -- not in valid snake_case format: it is a DB field!
		$data_rows   = $fstatus->Rows; // phpcs:ignore -- not in valid snake_case format: it is a DB field!
	}
	return $data_rows;
}


?>
