<?php
/**
 * Tools functions
 *
 * @package NewStatpress
 */

// Make sure plugin remains secure if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	if ( ! headers_sent() ) {
		header( 'HTTP/1.1 403 Forbidden' ); }
	die( esc_html__( 'ERROR: This plugin requires WordPress and will not function if called directly.', 'newstatpress' ) );
}

require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

/****** List of Functions available ******
 *
 * Nsp_display_tools_page()
 * nsp_remove_plugin_database()
 * nsp_export_now()
 * nsp_export()
 *****************************************/

/**
 * Display the tools page using tabs
 */
function nsp_display_tools_page() {
	global $pagenow;
	$page            = 'nsp-tools';
	$tools_page_tabs = array(
		'IP2nation' => __( 'IP2nation', 'newstatpress' ),
		'update'    => __( 'Update', 'newstatpress' ),
		'export'    => __( 'Export', 'newstatpress' ),
		'optimize'  => __( 'Optimize', 'newstatpress' ),
		'repair'    => __( 'Repair', 'newstatpress' ),
		'remove'    => __( 'Remove', 'newstatpress' ),
		'info'      => __( 'Informations', 'newstatpress' ),
	);

	$default_tab = 'IP2nation';

	print "<div class='wrap'><h2>" . esc_html__( 'Database Tools', 'newstatpress' ) . '</h2>';

	if ( isset( $_GET['tab'] ) ) {
		nsp_display_tabs_navbar_for_menu_page( $tools_page_tabs, sanitize_text_field( wp_unslash( $_GET['tab'] ) ), $page );
	} else {
		nsp_display_tabs_navbar_for_menu_page( $tools_page_tabs, $default_tab, $page );
	}

	if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && $page === $_GET['page'] ) {

		if ( isset( $_GET['tab'] ) ) {
			$tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
		} else {
			$tab = $default_tab;
		}

		switch ( $tab ) {

			case 'IP2nation':
				nsp_ip2nation();
				break;

			case 'export':
				nsp_export();
				break;

			case 'update':
				nsp_update();
				break;

			case 'optimize':
				nsp_optimize();
				break;

			case 'repair':
				nsp_repair();
				break;

			case 'remove':
				nsp_remove_plugin_database();
				break;

			case 'info':
				nsp_display_database_info();
				break;
		}
	}
}

/**
 * Get table size of index
 *
 * @param string $table table to search.
 */
function nsp_index_table_size( $table ) {
	global $wpdb;
	// no needs prepare.
	$res = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLE STATUS LIKE %s', $table ) ); // db call ok; no-cache ok.
	foreach ( $res as $fstatus ) {
		$index_lenght = $fstatus->Index_length;  // phpcs:ignore -- not in valid snake_case format: it is a DB field!
	}
	return number_format( ( $index_lenght / 1024 / 1024 ), 2, ',', ' ' ) . ' Mb';
}


/**
 * IP2nation form function
 *************************/
function nsp_ip2nation() {
	// Install or Remove if requested by user.
	if ( isset( $_POST['installation'] ) && 'install' === $_POST['installation'] ) {

		check_admin_referer( 'nsp_tool', 'nsp_tool_post' );
		if ( ! current_user_can( 'administrator' ) ) {
			die( 'NO permission' );
		}

		if ( ! ( isset( $_REQUEST['nsp_tool_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nsp_tool_post'] ) ), 'nsp_tool' ) ) ) {
			die( 'Failed security check' );
		}

		$install_result = nsp_ip2nation_install();
	} elseif ( isset( $_POST['installation'] ) && 'remove' === $_POST['installation'] ) {

		check_admin_referer( 'nsp_tool', 'nsp_tool_post' );
		if ( ! current_user_can( 'administrator' ) ) {
			die( 'NO permission' );
		}

		if ( ! ( isset( $_REQUEST['nsp_tool_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nsp_tool_post'] ) ), 'nsp_tool' ) ) ) {
			die( 'Failed security check' );
		}

		$install_result = nsp_ip2nation_remove();
	}

	// Display message if present.
	if ( isset( $install_result ) && '' !== $install_result ) {
		print "<br /><div class='updated'><p>" . esc_html( $install_result ) . '</p></div>';
	}

	global $nsp_option_vars;
	global $wpdb;

	// Create IP2nation variable if not exists: value 'none' by default or date when installed.
	$installed = get_option( $nsp_option_vars['ip2nation']['name'] );
	if ( '' === $installed ) {
		add_option( $nsp_option_vars['ip2nation']['name'], $nsp_option_vars['ip2nation']['value'], '', 'yes' );
	}

	echo '<br /><br />';
	$file_ip2nation = plugin_dir_path( __FILE__ ) . '/includes/ip2nation.sql';
	$date           = gmdate( 'd/m/Y', filemtime( $file_ip2nation ) );

	$table_name = 'ip2nation';
	$val        = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ); // db call ok; no-cache ok.
	if ( $val !== $table_name ) {
		$value_remove = 'none';
		$class_inst   = 'desactivated';
		$installed    = $nsp_option_vars['ip2nation']['value'];
	} else {
		$value_remove = 'remove';
		$class_inst   = '';
		$installed    = get_option( $nsp_option_vars['ip2nation']['name'] );
		if ( 'none' === $installed ) {
			$installed = esc_html__( 'unknow', 'newstatpress' );
		}
	}

	// Display status.
	// translators: placeholder for number of version.
	$i = sprintf( __( 'Last version available: %s', 'newstatpress' ), $date );
	echo esc_html( $i ) . '<br />';
	if ( 'none' !== $installed ) {
		// translators: placeholder for number of version.
		$i = sprintf( __( 'Last version installed: %s', 'newstatpress' ), $installed );
		echo esc_html( $i ) . '<br /><br />';
		esc_html_e( 'To update the IP2nation database, just click on the button bellow.', 'newstatpress' );
		if ( $installed === $date ) {
			$button_name   = __( 'Update', 'newstatpress' );
			$value_install = 'none';
			$class_install = 'desactivated';
		} else {
			$button_name = __( 'Install', 'newstatpress' );
		}
	} else {
		esc_html_e( 'Last version installed: none ', 'newstatpress' );
		echo '<br /><br />';
		esc_html_e( 'To download and to install the IP2nation database, just click on the button bellow.', 'newstatpress' );
		$button_name = __( 'Install', 'newstatpress' );
	}

	?>

	<br /><br />
		<form method=post>
			<input type=hidden name=page value=newstatpress>
			<?php wp_nonce_field( 'nsp_tool', 'nsp_tool_post' ); ?>

			<input type=hidden name=newstatpress_action value=ip2nation>
			<button class='<?php echo esc_attr( $class_install ); ?> button button-primary' type=submit name=installation value=install>
			<?php esc_html( $button_name ); ?>
			</button>

			<input type=hidden name=newstatpress_action value=ip2nation>
			<button class='<?php echo esc_attr( $class_inst ); ?> button button-primary' type=submit name=installation value=<?php echo esc_attr( $value_remove ); ?> >
			<?php esc_html_e( 'Remove', 'newstatpress' ); ?>
			</button>
		</form>
	</div>

	<div class='update-nag help'>

	<?php
	esc_html_e( 'What is ip2nation?', 'newstatpress' );
	echo '<br/>';
	echo wp_kses(
		_e( 'ip2nation is a free MySQL database that offers a quick way to map an IP to a country. The database is optimized to ensure fast lookups and is based on information from ARIN, APNIC, RIPE etc. You may install the database using the link to the left. (see: <a href="http://www.ip2nation.com/">http://www.ip2nation.com</a>)', 'newstatpress' ),
		array(
			'a' => array(
				'href' => array(),
			),
		)
	);
	echo "<br/><br />
          <span class='strong'>"
			. esc_html__( 'Note: The installation may take some times to complete.', 'newstatpress' ) .
			'</span>';

	?>
	</div>
	<?php
}

// TODO integrate error check.
/**
 * Install ip2nation table
 */
function nsp_ip2nation_install() {
	global $wpdb;
	global $nsp_option_vars;

	$file_ip2nation = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/includes/ip2nation.sql';

	$sql       = WP_Filesystem_Direct::get_contents( $file_ip2nation );
	$sql_array = explode( ';', $sql );
	foreach ( $sql_array as $val ) {
		$wpdb->query( $wpdb->prepare( '%s', $val ) ); // db call ok; no-cache ok.

	}
	$date = gmdate( 'd/m/Y', filemtime( $file_ip2nation ) );
	update_option( $nsp_option_vars['ip2nation']['name'], $date );
	$install_status = __( 'Installation of IP2nation database was successful', 'newstatpress' );

	return $install_status;
}

// TODO integrate error check.
/**
 * Remove ip2nation table
 */
function nsp_ip2nation_remove() {

	global $wpdb;
	global $nsp_option_vars;

	// no need prepare.
	$wpdb->query( 'DROP TABLE IF EXISTS ip2nation;' ); // db call ok; no-cache ok.
	$wpdb->query( 'DROP TABLE IF EXISTS ip2nationCountries;' ); // db call ok; no-cache ok.

	update_option( $nsp_option_vars['ip2nation']['name'], $nsp_option_vars['ip2nation']['value'] );

	$install_status = __( 'IP2nation database was remove successfully', 'newstatpress' );

	return $install_status;
}


/**
 * Export form function
 */
function nsp_export() {
	$export_description  = esc_html__( 'The export tool allows you to save your statistics in a local file for a date interval defined by yourself.', 'newstatpress' );
	$export_description .= '<br />';
	$export_description .= esc_html__( 'You can define the filename and the file extension, and also the fields delimiter used to separate the data.', 'newstatpress' );
	$export_description2 = esc_html__( 'Note: the parameters chosen will be saved automatically as default values.', 'newstatpress' );

	$delimiter_description = esc_html__( 'default value : semicolon', 'newstatpress' );
	$extension_description = esc_html__( 'default value : CSV (readable by Excel)', 'newstatpress' );
	$filename_description  = esc_html__( 'If the field remain blank, the default value is \'BLOG_TITLE-newstatpress\'.', 'newstatpress' );
	$filename_description .= '<br />';
	$filename_description .= esc_html__( 'The date interval will be added to the filename (i.e. BLOG_TITLE-newstatpress_20160229-20160331.csv).', 'newstatpress' );

	$export_option = get_option( 'newstatpress_exporttool' );
	?>
<!--TODO chab, check if the input format is ok  -->
	<div class='wrap'>
	<!-- <h3><?php // _e('Export stats to text file','newstatpress'); ?> (csv)</h3> -->
	<p><?php echo esc_html( $export_description ); ?></p>
	<p><i><?php echo esc_html( $export_description2 ); ?></i></p>

	<form method=get>
	<table class='form-tableH'>
		<tr>
		<th class='padd' scope='row' rowspan='3'>
			<?php esc_html_e( 'Date interval', 'newstatpress' ); ?>
		</th>
		</tr>
		<tr>
		<td><?php esc_html_e( 'From:', 'newstatpress' ); ?> </td>
		<td>
			<div class="input-container">
			<div class="icon-ph"><span class="dashicons dashicons-calendar-alt"></span>        </div>

			<input class="pik" id="datefrom" type="text" size="10" required maxlength="8" minlength="8" name="from" placeholder='<?php esc_html_e( 'YYYYMMDD', 'newstatpress' ); ?>'>  
			</div>

		</td>
		</tr>
		<tr>
		<td><?php esc_html_e( 'To:', 'newstatpress' ); ?> </td>
		<td>
			<div class="input-container">
			<div class="icon-ph"><span class="dashicons dashicons-calendar-alt"></span>        </div>
			<input class="pik" id="dateto" type="text" size="10" required maxlength="8" minlength="8" name="to" placeholder='<?php esc_html_e( 'YYYYMMDD', 'newstatpress' ); ?>'></td>
		</div>

		</tr>
	</table>
	<table class='form-tableH'>
		<tr>
			<th class='padd' scope='row' rowspan='2'>
				<?php esc_html_e( 'Filename', 'newstatpress' ); ?>
			</th>
			</tr>
		<tr>
		<td>
			<input class="" id="filename" type="text" size="30" maxlength="30" name="filename" placeholder='<?php esc_html_e( 'enter a filename', 'newstatpress' ); ?>' value="<?php echo esc_html( $export_option['filename'] ); ?>">
			<p class="description"><?php echo esc_html( $filename_description ); ?></p>
		</td>
		</tr>
	</table>
	<table class='form-tableH'>
		<tr>
			<th class='padd' scope='row' rowspan='2'>
			<?php esc_html_e( 'File extension', 'newstatpress' ); ?>
			</th>
			</tr>
		<tr>
		<td>
			<select name=ext>
			<option 
			<?php
			if ( 'csv' === $export_option['ext'] ) {
				echo 'selected';}
			?>
>csv</option>
			<option 
			<?php
			if ( 'txt' === $export_option['ext'] ) {
				echo 'selected';}
			?>
>txt</option>
			</select>
			<p class="description"><?php echo esc_html( $extension_description ); ?></p>
		</td>
		</tr>
	</table>
	<table class='form-tableH'>
		<tr>
			<th class='padd' scope='row' rowspan='2'>
				<?php esc_html_e( 'Fields delimiter', 'newstatpress' ); ?>
			</th>
			</tr>
		<tr>
		<td><select name=del>
			<option 
			<?php
			if ( ',' === $export_option['del'] ) {
				echo 'selected';}
			?>
>,</option>
			<option 
			<?php
			if ( 'tab' === $export_option['del'] ) {
				echo 'selected';}
			?>
			>tab</option>
			<option 
			<?php
			if ( ';' === $export_option['del'] ) {
				echo 'selected';}
			?>
			>;</option>
			<option 
			<?php
			if ( '|' === $export_option['del'] ) {
				echo 'selected';}
			?>
			>|</option></select>
			<p class="description"><?php echo esc_html( $delimiter_description ); ?></p>

		</td>
		</tr>
	</table>
	<?php wp_nonce_field( 'nsp_tool', 'nsp_tool_post' ); ?>
	<input class='button button-primary' type=submit value=<?php esc_html_e( 'Export', 'newstatpress' ); ?>>
	<input type=hidden name=page value=newstatpress><input type=hidden name=newstatpress_action value=exportnow>
	</form>
	</div>
	<?php
}

/**
 * Export the NewStatPress data
 */
function nsp_export_now() {
	global $wpdb;

	check_admin_referer( 'nsp_tool', 'nsp_tool_post' );

	if ( ! ( isset( $_REQUEST['nsp_tool_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nsp_tool_post'] ) ), 'nsp_tool' ) ) ) {
		die( 'Failed security check' );
	}

	$table_name = NSP_TABLENAME;

	// sanitize from date.
	if ( isset( $_GET['from'] ) ) {
		$from = gmdate( 'Ymd', strtotime( sanitize_text_field( wp_unslash( $_GET['from'] ) ) ) );
	} else {
		$from = '19990101';
	}

	// sanitize to date.
	if ( isset( $_GET['to'] ) ) {
		$to = gmdate( 'Ymd', strtotime( sanitize_text_field( wp_unslash( $_GET['to'] ) ) ) );
	} else {
		$to = '29990101';
	}

	// sanitize extension.
	if ( isset( $_GET['ext'] ) ) {
		switch ( $_GET['ext'] ) {
			case 'csv':
			case 'txt':
				$ext = sanitize_text_field( wp_unslash( $_GET['ext'] ) );
				break;
			default:
				$ext = 'txt';
		}
	} else {
		$ext = 'txt';
	}

	// sanitize delimiter.
	if ( isset( $_GET['del'] ) ) {
		$del = substr( sanitize_text_field( wp_unslash( $_GET['del'] ) ), 0, 1 );

		switch ( $del ) {
			case ';':
			case '|':
			case ',':
			case 't':
				break;
			default:
				$del = ';';
		}
	} else {
		$del = ';';
	}

	if ( isset( $_GET['filename'] ) ) {
		$name = sanitize_file_name( wp_unslash( $_GET['filename'] ) );
	}

	// sanitize file name.
	if ( '' === $name ) {
		$filename = get_bloginfo( 'title' ) . '-newstatpress_' . $from . '-' . $to . '.' . $ext;
	} else {
		$filename = $name . '_' . $from . '-' . $to . '.' . $ext;
	}

	$ti['filename'] = $name;
	$ti['del']      = $del;
	$ti['ext']      = $ext;
	update_option( 'newstatpress_exporttool', $ti );

	header( 'Content-Description: File Transfer' );
	header( "Content-Disposition: attachment; filename=$filename" );
	header( 'Content-Type: text/plain charset=' . get_option( 'blog_charset' ), true );

	$i_from = strtotime( $from );
	$i_to   = strtotime( $to );

	// use prepare.
	$qry = $wpdb->get_results(
		$wpdb->prepare(
			'SELECT *
      FROM %s
      WHERE
       date>= %s AND
       date<= %s;
    ',
			$table_name,
			gmdate( 'Ymd', $i_from ),
			gmdate( 'Ymd', $i_to )
		)
	); // db call ok; no-cache ok.

	if ( 't' === $del ) {
		$del = "\t";
	}
	print 'date' . esc_html( $del ) . 'time' . esc_html( $del ) . 'ip' . esc_html( $del ) . 'urlrequested' . esc_html( $del ) . 'agent' . esc_html( $del ) . 'referrer' . esc_html( $del ) . 'search' . esc_html( $del ) . 'nation' . esc_html( $del ) . 'os' . esc_html( $del ) . 'browser' . esc_html( $del ) . 'searchengine' . esc_html( $del ) . 'spider' . esc_html( $del ) . "feed\n";
	foreach ( $qry as $rk ) {
		print '"' . esc_html( $rk->date ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->time ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->ip ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->urlrequested ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->agent ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->referrer ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->search ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->nation ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->os ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->browser ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->searchengine ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->spider ) . '"' . esc_html( $del ) . '"' . esc_html( $rk->feed ) . '"' . "\n";
	}
	die();
}

/**
 * Generate HTML for remove menu in WordPress
 */
function nsp_remove_plugin_database() {

	if ( isset( $_POST['removeit'] ) && 'yes' === $_POST['removeit'] ) {

		check_admin_referer( 'nsp_tool', 'nsp_tool_post' );
		if ( ! current_user_can( 'administrator' ) ) {
			die( 'NO permission' );
		}

		if ( ! ( isset( $_POST['nsp_tool_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nsp_tool_post'] ) ), 'nsp_tool' ) ) ) {
			die( 'Failed security check' );
		}

		global $wpdb;
		$table_name = NSP_TABLENAME;
		$results    = $wpdb->query( $wpdb->prepare( 'DELETE FROM %s', $table_name ) ); // db call ok; no-cache ok.
		print "<br /><div class='remove'><p>" . esc_html__( 'All data removed', 'newstatpress' ) . '!</p></div>';
	} else {
		?>

		<div class='wrap'><h3><?php esc_html_e( 'Remove NewStatPress database', 'newstatpress' ); ?></h3>
			<br />

		<form method=post>
				<?php esc_html_e( 'To remove the Newstatpress database, just click on the button bellow.', 'newstatpress' ); ?>
			<br /><br />
		<?php wp_nonce_field( 'nsp_tool', 'nsp_tool_post' ); ?>
		<input class='button button-primary' type=submit value="<?php esc_html_e( 'Remove', 'newstatpress' ); ?>" onclick="return confirm('<?php esc_html_e( 'Are you sure?', 'newstatpress' ); ?>');" >
		<input type=hidden name=removeit value=yes>
		</form>
		<div class='update-nag help'>
			<?php
			esc_html_e( 'This operation will remove all collected data by NewStatpress. This function is useful at people who did not want use the plugin any more or who want simply purge the stored data.', 'newstatpress' );
			?>
			<br />
			<span class='strong'>
			<?php esc_html_e( "If you have doubt about this function, don't use it.", 'newstatpress' ); ?>
		</span>
			</div>
			<div class='update-nag warning'><p>
		<?php esc_html_e( 'Warning: pressing the below button will make all your stored data to be erased!', 'newstatpress' ); ?>
		</p></div>
		</div>
		<?php
	}
}

/**
 * Get the days a user has choice for updating the database
 *
 * @return the number of days of -1 for all days
 */
function nsp_duration_to_days() {

	// get the number of days for the update.
	switch ( get_option( 'newstatpress_updateint' ) ) {
		case '1 week':
			$days = 7;
			break;
		case '2 weeks':
			$days = 14;
			break;
		case '3 weeks':
			$days = 21;
			break;
		case '1 month':
			$days = 30;
			break;
		case '2 months':
			$days = 60;
			break;
		case '3 months':
			$days = 90;
			break;
		case '6 months':
			$days = 180;
			break;
		case '9 months':
			$days = 270;
			break;
		case '12 months':
			$days = 365;
			break;
		default:
			$days = -1; // infinite in the past, for all day.
	}

	return $days;
}

/**
 * Extract the feed from the given url
 *
 * @param string $url the url to parse.
 * @return the extracted url
 *************************************/
function nsp_extract_feed_req( $url ) {
	list($null,$q) = explode( '?', $url );
	if ( strpos( $q, '&' ) !== false ) {
		list($res,$null) = explode( '&', $q );
	} else {
		$res = $q;
	}
	return $res;
}

/**
 * Update form function
 ***********************/
function nsp_update() {
	// database update if requested by user.
	if ( 'yes' == isset( $_POST['update'] ) && sanitize_file_name( wp_unslash( $_POST['update'] ) ) ) {
		check_admin_referer( 'nsp_tool', 'nsp_tool_post' );
		if ( ! current_user_can( 'administrator' ) ) {
			die( 'NO permission' );
		}

		if ( ! ( isset( $_POST['nsp_tool_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nsp_tool_post'] ) ), 'nsp_tool' ) ) ) {
			die( 'Failed security check' );
		}

		nsp_update_now();
		die;
	}
	?>
	<div class='wrap'>
		<h3><?php esc_html_e( 'Database update', 'newstatpress' ); ?></h3>
			<?php esc_html_e( 'To update the newstatpress database, just click on the button bellow.', 'newstatpress' ); ?>
		<br /><br />
	<form method=post>
	<?php wp_nonce_field( 'nsp_tool', 'nsp_tool_post' ); ?>
	<input type=hidden name=page value=newstatpress>
	<input type=hidden name=update value=yes>
	<input type=hidden name=newstatpress_action value=update>
	<button class='button button-primary' type=submit><?php esc_html_e( 'Update', 'newstatpress' ); ?></button>
	</form>
	</div>

	<div class='update-nag help'>

	<?php

	esc_html_e( 'Update the database is particularly useful when the ip2nation data and definitions data (OS, browser, spider) have been updated. An option in future will allow an automatic update of the database..', 'newstatpress' );
	echo "<br/><br />
        <span class='strong'>"
			. esc_html__( 'Note: The update may take some times to complete.', 'newstatpress' ) .
			'</span>';

	?>
	</div>

	<?php
}

/**
 * Dispaly dattabase information
 */
function nsp_display_database_info() {
	global $wpdb;
	global $newstatpress_dir;

	$table_name = NSP_TABLENAME;

	$wpdb->flush();     // flush for counting right the queries.
	$start_time = microtime( true );

	$days = nsp_duration_to_days();  // get the number of days for the update.

	$to_date = gmdate( 'Ymd', current_time( 'timestamp' ) );

	if ( -1 === $days ) {
		$from_date = '19990101';   // use a date where this plugin was not present.
	} else {
		$from_date = gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 * $days );
	}

	$_newstatpress_url = nsp_plugin_url();

	$wpdb->show_errors();

	?>
  <div class='wrap'>
			<?php esc_html_e( 'This tool display basic informations about the newstatpress database. It should be usefull to check the functionning of the plugin.', 'newstatpress' ); ?>
		<br /><br />

		<table class='widefat nsp'>
		<thead>
			<tr>
		<th scope='col'><?php esc_html_e( 'Database', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Size', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Number of Records', 'newstatpress' ); ?></th>

		</tr>
		</thead>
		<tbody id='the-list'>
			<tr>
			<td>
			<?php
			esc_html_e( 'Structure', 'newstatpress' );
			echo ' ' . esc_html( $table_name );
			?>
			</td>
			<td><?php echo esc_html( nsp_table_size2( $wpdb->prefix . 'statpress' ) ); ?></td>
			<td><?php echo esc_html( nsp_table_records( $wpdb->prefix . 'statpress' ) ); ?></td>
			</tr>
			<tr>
			<td>
			<?php
			esc_html_e( 'Index', 'newstatpress' );
			echo ' ' . esc_html( $table_name );
			?>
			</td>
			<td><?php echo esc_html( nsp_index_table_size( $wpdb->prefix . 'statpress' ) ); ?></td>
			<td></td>
			</tr>
		</tbody>
	</div>

	<?php
}

/**
 * Performes database update with new definitions
 */
function nsp_update_now() {
	global $wpdb;
	global $newstatpress_dir;

	$table_name = NSP_TABLENAME;

	$wpdb->flush();     // flush for counting right the queries.
	$start_time = microtime( true );

	$days = nsp_duration_to_days();  // get the number of days for the update.

	$to_date = gmdate( 'Ymd', current_time( 'timestamp' ) );

	if ( -1 === $days ) {
		$from_date = '19990101';   // use a date where this plugin was not present.
	} else {
		$from_date = gmdate( 'Ymd', current_time( 'timestamp' ) - 86400 * $days );
	}

	$_newstatpress_url = nsp_plugin_url();

	$wpdb->show_errors();

	// add by chab
	// $var requesting the absolute path.
	$img_ok = $_newstatpress_url . 'images/ok.gif';

	print "<div class='wrap'><h2>" . esc_html__( 'Database Update', 'newstatpress' ) . '</h2><br />';

	print "<table class='widefat nsp'><thead><tr><th scope='col'>" . esc_html__( 'Updating...', 'newstatpress' ) . "</th><th scope='col' style='width:400px;'>" . esc_html__( 'Size', 'newstatpress' ) . "</th><th scope='col' style='width:100px;'>" . esc_html__( 'Result', 'newstatpress' ) . '</th><th></th></tr></thead>';
	print "<tbody id='the-list'>";

	// update table.
	nsp_build_plugin_sql_table( 'update' );

	echo '<tr>
          <td>' . esc_html__( 'Structure', 'newstatpress' ) . ' ' . esc_html( $table_name ) . '</td>
          <td>' . esc_html( nsp_table_size( $wpdb->prefix . 'statpress' ) ) . "</td>
          <td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td>
        </tr>";

	print '<tr><td>' . esc_html__( 'Index', 'newstatpress' ) . ' ' . esc_html( $table_name ) . '</td>';
	print '<td>' . esc_html( ( $wpdb->prefix . 'statpress' ) ) . '</td>';
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	// Update Feed.
	print '<tr><td>' . esc_html__( 'Feeds', 'newstatpress' ) . '</td>';
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE `$table_name`
      SET feed=''
      WHERE date BETWEEN %s AND %s
      ",
			$from_date,
			$to_date
		)
	); // phpcs:ignore: unprepared SQL OK.

	// not standard.
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE `$table_name`
      SET feed='RSS2'
      WHERE
        urlrequested LIKE %s AND
        date BETWEEN %s AND %s
      ",
			'%%/feed/%%',
			$from_date,
			$to_date
		)
	); // phpcs:ignore: unprepared SQL OK.

	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE `$table_name`
      SET feed='RSS2'
      WHERE
        urlrequested LIKE %s AND
        date BETWEEN %s AND %s
     ",
			'%%wp-feed.php%%',
			$from_date,
			$to_date
		)
	); // phpcs:ignore: unprepared SQL OK.

	// standard blog info urls.
	$s = nsp_extract_feed_req( get_bloginfo( 'comments_atom_url' ) );
	if ( '' !== $s ) {
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name`
        SET feed='COMMENT'
        WHERE
          INSTR(urlrequested, %s)>0 AND
          date BETWEEN %s AND %s
       ",
				$s,
				$from_date,
				$to_date
			)
		); // phpcs:ignore: unprepared SQL OK.
	}
	$s = nsp_extract_feed_req( get_bloginfo( 'comments_rss2_url' ) );
	if ( '' !== $s ) {
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name`
        SET feed='COMMENT'
        WHERE
          INSTR(urlrequested, %s)>0 AND
          date BETWEEN %s AND %s
        ",
				$s,
				$from_date,
				$to_date
			)
		); // phpcs:ignore: unprepared SQL OK.
	}
	$s = nsp_extract_feed_req( get_bloginfo( 'atom_url' ) );
	if ( '' !== $s ) {
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name`
        SET feed='ATOM'
        WHERE
          INSTR(urlrequested, %s)>0 AND
          date BETWEEN %s AND %s
        ",
				$s,
				$from_date,
				$to_date
			)
		); // phpcs:ignore: unprepared SQL OK.
	}
	$s = nsp_extract_feed_req( get_bloginfo( 'rdf_url' ) );
	if ( '' !== $s ) {
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name`
        SET feed='RDF'
        WHERE
          INSTR(urlrequested, %s)>0 AND
          date BETWEEN %s AND %s
       ",
				$s,
				$from_date,
				$to_date
			)
		); // phpcs:ignore: unprepared SQL OK.
	}
	$s = nsp_extract_feed_req( get_bloginfo( 'rss_url' ) );
	if ( '' !== $s ) {
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name`
        SET feed='RSS'
        WHERE
          INSTR(urlrequested, %s)>0 AND
          date BETWEEN %s AND %s
        ",
				$s,
				$from_date,
				$to_date
			)
		); // phpcs:ignore: unprepared SQL OK.
	}
	$s = nsp_extract_feed_req( get_bloginfo( 'rss2_url' ) );
	if ( '' !== $s ) {
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name`
         SET feed='RSS2'
         WHERE
         INSTR(urlrequested, %s)>0 AND
          date BETWEEN %s AND %s
        ",
				$s,
				$from_date,
				$to_date
			)
		); // phpcs:ignore: unprepared SQL OK.
	}

	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE `$table_name`
      SET feed = ''
      WHERE
        isnull(feed) AND
        date BETWEEN %s AND %s
      ",
			$from_date,
			$to_date
		)
	); // phpcs:ignore: unprepared SQL OK.

	print '<td></td>';
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	// Update OS.
	print '<tr><td>' . esc_html__( 'OSes', 'newstatpress' ) . '</td>';
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE `$table_name`
      SET os = ''
      WHERE date BETWEEN %s AND %s
      ",
			$from_date,
			$to_date
		)
	); // phpcs:ignore: unprepared SQL OK.

	$lines = file( $newstatpress_dir . '/def/os.dat' );
	foreach ( $lines as $line_num => $os ) {
		list($nome_os,$id_os) = explode( '|', $os );
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name`
        SET os = %s
        WHERE
          os='' AND
          replace(agent,' ','') LIKE %s AND
          date BETWEEN %s AND %s
       ",
				$nome_os,
				'%' . $id_os . '%',
				$from_date,
				$to_date
			)
		); // phpcs:ignore: unprepared SQL OK.
	}
	print '<td></td>';
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	// Update Browser.
	print '<tr><td>' . esc_html__( 'Browsers', 'newstatpress' ) . '</td>';
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE `$table_name`
       SET browser = ''
       WHERE date BETWEEN %s AND %s
   ",
			$from_date,
			$to_date
		)
	); // phpcs:ignore: unprepared SQL OK.

	$lines = file( $newstatpress_dir . '/def/browser.dat' );
	foreach ( $lines as $line_num => $browser ) {
		list($nome,$id) = explode( '|', $browser );
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name`
        SET browser = %s
        WHERE
          browser='' AND
          replace(agent,' ','') LIKE %s AND
          date BETWEEN %s AND %s
       ",
				$nome,
				'%' . $id . '%',
				$from_date,
				$to_date
			)
		); // phpcs:ignore: unprepared SQL OK.
	}
	print '<td></td>';
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	// Update Spider.
	print '<tr><td>' . esc_html__( 'Spiders', 'newstatpress' ) . '</td>';
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE `$table_name`
      SET spider = ''
      WHERE date BETWEEN %s AND %s
      ",
			$from_date,
			$to_date
		)
	); // phpcs:ignore: unprepared SQL OK.

	$lines = file( $newstatpress_dir . '/def/spider.dat' );
	foreach ( $lines as $line_num => $spider ) {
		list($nome,$id) = explode( '|', $spider );
		// use prepare.
		// phpcs:ignore -- db call ok; no-cache ok.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE `$table_name`
        SET spider = %s,os='',browser=''
        WHERE
          spider='' AND
          replace(agent,' ','') LIKE %s AND
          date BETWEEN %s AND %s
        ",
				$nome,
				'%' . $id . '%',
				$from_date,
				$to_date
			)
		); // phpcs:ignore: unprepared SQL OK.
	}
	print '<td></td>';
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	// Update Search engine.
	print '<tr><td>' . esc_html__( 'Search engines', 'newstatpress' ) . ' </td>';
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE `$table_name`
       SET searchengine = '', search=''
       WHERE date BETWEEN %s AND %s
      ",
			$from_date,
			$to_date
		)
	); // phpcs:ignore: unprepared SQL OK.

	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$qry = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, referrer
       FROM `$table_name`
       WHERE
         length(referrer)!=0 AND
         date BETWEEN %s AND %s
       ",
			$from_date,
			$to_date
		)
	); // phpcs:ignore: unprepared SQL OK.

	foreach ( $qry as $rk ) {
		list($searchengine,$search_phrase) = explode( '|', nsp_get_se( $rk->referrer ) );
		if ( '' !== $searchengine ) {
			// use prepare.
			// phpcs:ignore -- db call ok; no-cache ok.
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE `$table_name`
          SET searchengine = %s, search=%s 
          WHERE
            id= %d AND
            date BETWEEN %s AND %s
          ",
					$searchengine,
					addslashes( $search_phrase ),
					$rk->id,
					$from_date,
					$to_date
				)
			); // phpcs:ignore: unprepared SQL OK.
		}
	}
	print '<td></td>';
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	$end_time    = microtime( true );
	$sql_queries = $wpdb->num_queries;

	// Final statistics.
	print '<tr><td>' . esc_html__( 'Final Structure', 'newstatpress' ) . ' ' . esc_html( $table_name ) . '</td>';
	print '<td>' . esc_html( nsp_table_size( $wpdb->prefix . 'statpress' ) ) . '</td>'; // todo chab : to clean.
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	print '<tr><td>' . esc_html__( 'Final Index', 'newstatpress' ) . ' ' . esc_html( $table_name ) . '</td>';
	print '<td>' . esc_html( nsp_index_table_size( $wpdb->prefix . 'statpress' ) ) . '</td>'; // todo chab : to clean.
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	print '<tr><td>' . esc_html__( 'Duration of the update', 'newstatpress' ) . '</td>';
	print '<td>' . esc_html( round( $end_time - $start_time, 2 ) ) . ' sec</td>';
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	print '<tr><td>' . esc_html__( 'This update was done in', 'newstatpress' ) . '</td>';
	print '<td>' . esc_html( $sql_queries ) . ' ' . esc_html__( 'SQL queries', 'newstatpress' ) . '</td>';
	print "<td><img class'update_img' src='" . esc_attr( $img_ok ) . "'></td></tr>";

	print "</tbody></table></div><br>\n";
	$wpdb->hide_errors();
}

/**
 * Optimize form function
 */
function nsp_optimize() {

	// database update if requested by user.
	if ( isset( $_POST['optimize'] ) && 'yes' === $_POST['optimize'] ) {
		check_admin_referer( 'nsp_tool', 'nsp_tool_post' );
		if ( ! current_user_can( 'administrator' ) ) {
			die( 'NO permission' );
		}

		if ( ! ( isset( $_POST['nsp_tool_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nsp_tool_post'] ) ), 'nsp_tool' ) ) ) {
			die( 'Failed security check' );
		}

		nsp_optimize_now();
		die;
	}
	?>
	<div class='wrap'>
	<h3><?php esc_html_e( 'Optimize table', 'newstatpress' ); ?></h3>
	<?php esc_html_e( 'To optimize the statpress table, just click on the button bellow.', 'newstatpress' ); ?>
	<br /><br />
	<form method=post>
		<?php wp_nonce_field( 'nsp_tool', 'nsp_tool_post' ); ?>
		<input type=hidden name=page value=newstatpress>
		<input type=hidden name=optimize value=yes>
		<input type=hidden name=newstatpress_action value=optimize>
		<button class='button button-primary' type=submit><?php esc_html_e( 'Optimize', 'newstatpress' ); ?></button>
	</form>

	<div class='update-nag help'>
		<?php esc_html_e( 'Optimize a table is an database operation that can free some server space if you had lot of delation (like with prune activated) in it.', 'newstatpress' ); ?>
		<br /><br />
		<span class='strong'>
		<?php esc_html_e( 'Be aware that this operation may take a lot of server time to finish the processing (depending on your database size). So so use it only if you know what you are doing.', 'newstatpress' ); ?>
		</span>
	</div>
	</div>
	<?php
}

/**
 * Repair form function
 */
function nsp_repair() {
	// database update if requested by user.
	if ( isset( $_POST['repair'] ) && 'yes' === $_POST['repair'] ) {
		check_admin_referer( 'nsp_tool', 'nsp_tool_post' );
		if ( ! current_user_can( 'administrator' ) ) {
			die( 'NO permission' );
		}

		if ( ! ( isset( $_POST['nsp_tool_post'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nsp_tool_post'] ) ), 'nsp_tool' ) ) ) {
			die( 'Failed security check' );
		}

		nsp_repair_now();
		die;
	}
	?>
	<div class='wrap'>
		<h3><?php esc_html_e( 'Repair table', 'newstatpress' ); ?></h3>
		<?php esc_html_e( 'To repair the statpress table if damaged, just click on the button bellow.', 'newstatpress' ); ?>
		<br /><br />
	<form method=post>
	<?php wp_nonce_field( 'nsp_tool', 'nsp_tool_post' ); ?>
	<input type=hidden name=page value=newstatpress>
	<input type=hidden name=repair value=yes>
	<input type=hidden name=newstatpress_action value=repair>
	<button class='button button-primary' type=submit><?php esc_html_e( 'Repair', 'newstatpress' ); ?></button>
	</form>

	<div class='update-nag help'>
		<?php esc_html_e( 'Repair is an database operation that can fix a corrupted table.', 'newstatpress' ); ?>
	<br /><br />
	<span class='strong'>
	<?php esc_html_e( 'Be aware that this operation may take a lot of server time to finish the processing (depending on your database size). So so use it only if you know what you are doing.', 'newstatpress' ); ?>
	</span>
	</div>
	</div><?php
}

/**
 * Optimize the table
 */
function nsp_optimize_now() {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	$wpdb->query( $wpdb->prepare( 'OPTIMIZE TABLE %s', $table_name ) ); // db call ok; no-cache ok.
	print "<br /><div class='optimize'><p>" . esc_html__( 'Optimization finished', 'newstatpress' ) . '!</p></div>';
}

/**
 * Repair the table
 */
function nsp_repair_now() {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	$wpdb->query( $wpdb->prepare( 'REPAIR TABLE %s', $table_name ) ); // db call ok; no-cache ok.
	print "<br /><div class='repair'><p>" . esc_html__( 'Repair finished', 'newstatpress' ) . '!</p></div>';
}

?>
