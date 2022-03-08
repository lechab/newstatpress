<?php
/**
 * Overview
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
 * Generate overwiew meta-box-order
 **********************************/
function nsp_generate_overview_agents() {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	global $newstatpress_dir;
	$_newstatpress_url = nsp_plugin_url();

	// determine the structure to use for URL.
	$permalink_structure = get_option( 'permalink_structure' );
	if ( '' === $permalink_structure ) {
		$extra = '/?';
	} else {
		$extra = '/';
	}

	$querylimit = ( ( get_option( 'newstatpress_el_overview' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_overview' ) ) );
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$useragents = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT agent,os,browser,spider
       FROM `$table_name`
       GROUP BY agent,os,browser,spider
       ORDER BY id DESC LIMIT %d
       ",
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK.
	?>
	<table class='widefat nsp'>
	<thead>
		<tr>
		<th scope='col'><?php esc_html_e( 'Agent', 'newstatpress' ); ?> (<a id="hider" class='hider'>Hide Spiders</a>)</th>
		<th scope='col'></th><th style='width:140px;'><?php esc_html_e( 'OS', 'newstatpress' ); ?></th>
		<th scope='col'></th><th style='width:120px;'>
		<?php
		esc_html_e( 'Browser', 'newstatpress' );
		echo '/';
		esc_html_e( 'Spider', 'newstatpress' );
		?>
		</th>
		</tr>
	</thead>
	<tbody id='the-list'>
	<?php
	foreach ( $useragents as $rk ) {
		if ( null !== $rk->spider ) {
			print "<tr class='spiderhide' style=\"background-color: #f6f6f0;\"><td>" . esc_html( $rk->agent ) . '</td>';
		} else {
			print '<tr><td>' . esc_html( $rk->agent ) . '</td>';
		}
		if ( '' !== $rk->os ) {
			$val = nsp_get_os_img( $rk->os );
			$img = str_replace( ' ', '_', strtolower( $val ) ) . '.png';
			print "<td class='right nospace-r'><img class='img_os' src='" . esc_attr( $_newstatpress_url ) . 'images/os/' . esc_attr( $img ) . "'></td>";
		} else {
			print '<td></td>';
		}
		if ( '' !== $rk->os ) {
			print "<td class='left nospace-l'>" . esc_html( $rk->os ) . '</td>';
		} else {
			print '<td>unknow</td>';
		}
		if ( '' !== $rk->browser ) {
			$val = nsp_get_browser_img( $rk->browser );
			$img = str_replace( ' ', '', strtolower( $val ) ) . '.png';
			print "<td class='right nospace-r'><img class='img_browser' src='" . esc_attr( $_newstatpress_url ) . 'images/browsers/' . esc_attr( $img ) . "'></td>";
		} else {
			print '<td></td>';
		}
		print "<td class='left nospace-l'>" . esc_html( $rk->browser ) . ' ' . esc_html( $rk->spider ) . "</td></tr>\n";
	}
	?>
	</tbody>
	</table>
	<?php
}

/**
 * Generate overview lasthits
 */
function nsp_generate_overview_lasthits() {

	global $wpdb;
	$table_name = NSP_TABLENAME;

	global $newstatpress_dir;
	$_newstatpress_url = nsp_plugin_url();

	// determine the structure to use for URL.
	$permalink_structure = get_option( 'permalink_structure' );
	if ( '' === $permalink_structure ) {
		$extra = '/?';
	} else {
		$extra = '/';
	}

	$querylimit = ( ( get_option( 'newstatpress_el_overview' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_overview' ) ) );
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$lasthits = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
      FROM `$table_name`
      WHERE (os<>'' OR feed<>'')
      ORDER bY id DESC LIMIT %d
      ",
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK.
	?>
		<table class='widefat nsp'>
		<thead>
			<tr>
			<th scope='col'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Time', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'IP', 'newstatpress' ); ?></th>
			<th scope='col'><?php echo esc_html__( 'Country', 'newstatpress' ) . '/' . esc_html__( 'Language', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Page', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Feed', 'newstatpress' ); ?></th>
			<th></th>
			<th scope='col' style='width:120px;'><?php esc_html_e( 'OS', 'newstatpress' ); ?></th>
			<th></th>
			<th scope='col' style='width:120px;'><?php esc_html_e( 'Browser', 'newstatpress' ); ?></th>
			</tr>
		</thead>
		<tbody id='the-list'>
		<?php
		foreach ( $lasthits as $fivesdraft ) {
			print '<tr>
                    <td>' . esc_html( nsp_hdate( $fivesdraft->date ) ) . '</td>
                    <td>' . esc_html( $fivesdraft->time ) . '</td>
                    <td>' . esc_html( $fivesdraft->ip ) . '</td>
                    <td>' . esc_html( $fivesdraft->nation ) . '</td>
                    <td>' . esc_html( nsp_abbreviate( nsp_decode_url( filter_var( $fivesdraft->urlrequested, FILTER_SANITIZE_URL ) ), 30 ) ) . '</td>
                    <td>' . esc_html( $fivesdraft->feed ) . '</td>';

			if ( '' !== $fivesdraft->os ) {
				$val = nsp_get_os_img( $fivesdraft->os );
				$img = $_newstatpress_url . 'images/os/' . str_replace( ' ', '_', strtolower( $val ) ) . '.png';
				print "<td class='right nospace-r'><img class='img_os' src='" . esc_attr( $img ) . "'></td>";
			} else {
				print '<td></td>';
			}

			print "<td class='left nospace-l'> " . esc_html( $fivesdraft->os ) . '</td>';

			if ( '' !== $fivesdraft->browser ) {
				$val = nsp_get_browser_img( $fivesdraft->browser );
				$img = $_newstatpress_url . 'images/browsers/' . str_replace( ' ', '_', strtolower( $val ) ) . '.png';
				print "<td class='right nospace-r'><img class='img_browser' src='" . esc_attr( $img ) . "'></td>";
			} else {
				print '<td></td>';
			}
			print "<td class='left nospace-l'>" . esc_html( $fivesdraft->browser ) . "</td></tr>\n";
		}
		?>
		</tbody>
		</table>
	<?php
}

/**
 * Generate overview lastsearchterms
 */
function nsp_generate_overview_lastsearchterms() {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	global $newstatpress_dir;
	$_newstatpress_url = nsp_plugin_url();

	// determine the structure to use for URL.
	$permalink_structure = get_option( 'permalink_structure' );
	if ( '' === $permalink_structure ) {
		$extra = '/?';
	} else {
		$extra = '/';
	}

	$querylimit = ( ( get_option( 'newstatpress_el_overview' ) === '' ) ? 10 : get_option( 'newstatpress_el_overview' ) );
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$lastsearchterms = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT date,time,referrer,urlrequested,search,searchengine
       FROM `$table_name`
       WHERE search<>''
       ORDER BY id DESC LIMIT %d
      ",
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK.
	?>
	<table class='widefat nsp'>
	<thead>
		<tr>
		<th scope='col'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Time', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Terms', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Engine', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Result', 'newstatpress' ); ?></th>
		</tr>
	</thead>
	<tbody id='the-list'>
	<?php
	foreach ( $lastsearchterms as $rk ) {
		print '<tr>
                <td>' . esc_html( nsp_hdate( $rk->date ) ) . '</td><td>' . esc_html( $rk->time ) . "</td>
                <td><a href='" . esc_attr( $rk->referrer ) . "' target='_blank'>" . esc_html( $rk->search ) . '</a></td>
                <td>' . esc_html( $rk->searchengine ) . "</td><td><a href='" . esc_attr( get_bloginfo( 'url' ) . $extra . filter_var( $rk->urlrequested, FILTER_SANITIZE_URL ) ) . "' target='_blank'>" . esc_html__( 'page viewed', 'newstatpress' ) . "</a></td>
              </tr>\n";
	}
	?>
	</tbody>
	</table>
	<?php
}

/**
 * Generate overview lastreferrers
 */
function nsp_generate_overview_lastreferrers() {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	global $newstatpress_dir;
	$_newstatpress_url = nsp_plugin_url();

	// determine the structure to use for URL.
	$permalink_structure = get_option( 'permalink_structure' );
	if ( '' === $permalink_structure ) {
		$extra = '/?';
	} else {
		$extra = '/';
	}

	$querylimit = ( ( get_option( 'newstatpress_el_overview' ) === '' ) ? 10 : get_option( 'newstatpress_el_overview' ) );
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$lastreferrers = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT date,time,referrer,urlrequested
      FROM `$table_name`
      WHERE
       ((referrer NOT LIKE %s) AND
        (referrer <>'') AND
        (searchengine='')
       ) ORDER BY id DESC LIMIT %d
      ",
			get_option( 'home' ) . '%',
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK.
	?>
	<table class='widefat nsp'>
	<thead>
		<tr>
		<th scope='col'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Time', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'URL', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Result', 'newstatpress' ); ?></th>
		</tr>
	</thead>
	<tbody id='the-list'>
	<?php
	foreach ( $lastreferrers as $rk ) {
		print '<tr>
                <td>' . esc_html( nsp_hdate( $rk->date ) ) . '</td>
                <td>' . esc_html( $rk->time ) . "</td>
                <td ><a class='urlicon' href='" . esc_attr( $rk->referrer ) . "' target='_blank'>" . esc_html( nsp_abbreviate( $rk->referrer, 80 ) ) . "</a></td>
                <td><a href='" . esc_attr( get_bloginfo( 'url' ) ) . filter_var( $extra . $rk->urlrequested, FILTER_SANITIZE_URL ) . "'  target='_blank'>" . esc_html__( 'page viewed', 'newstatpress' ) . "</a></td>
              </tr>\n";
	}
	?>
	</tbody>
	</table>
	<?php
}

/**
 * Generate overview pages
 */
function nsp_generate_overview_pages() {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	global $newstatpress_dir;
	$_newstatpress_url = nsp_plugin_url();

	// determine the structure to use for URL.
	$permalink_structure = get_option( 'permalink_structure' );
	if ( '' === $permalink_structure ) {
		$extra = '/?';
	} else {
		$extra = '/';
	}

	$querylimit = ( ( '' === get_option( 'newstatpress_el_overview' ) ) ? 10 : get_option( 'newstatpress_el_overview' ) );
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$pages = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT date,time,urlrequested,os,browser,spider
       FROM `$table_name`
       WHERE (spider='' AND feed='')
       ORDER BY id DESC LIMIT %d
       ",
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK.
	?>
	<table class='widefat nsp'>
	<thead>
		<tr>
		<th scope='col'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Time', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Page', 'newstatpress' ); ?></th>
		<th scope='col' style='width:17px;'></th>
		<th scope='col' style='width:120px;'><?php esc_html_e( 'OS', 'newstatpress' ); ?></th>
		<th style='width:17px;'></th>
		<th scope='col' style='width:120px;'><?php esc_html_e( 'Browser', 'newstatpress' ); ?></th>
		</tr>
	</thead>
	<tbody id='the-list'>
	<?php
	foreach ( $pages as $rk ) {
		print '<tr><td>' . esc_html( nsp_hdate( $rk->date ) ) . '</td><td>' . esc_html( $rk->time ) . "</td>\n<td>" . esc_html( nsp_abbreviate( nsp_decode_url( filter_var( $rk->urlrequested, FILTER_SANITIZE_URL ) ), 60 ) ) . '</td>';
		if ( '' !== $rk->os ) {
			$val = nsp_get_os_img( $rk->os );
			$img = str_replace( ' ', '_', strtolower( $val ) ) . '.png';
			print "<td><img class='img_os' src='" . esc_attr( $_newstatpress_url ) . 'images/os/' . esc_attr( $img ) . "'></td>";
		} else {
			print '<td></td>';
		}
		print '<td>' . esc_html( $rk->os ) . '</td>';
		if ( '' !== $rk->browser ) {
			$val = nsp_get_browser_img( $rk->browser );
			$img = str_replace( ' ', '', strtolower( $val ) ) . '.png';
			print "<td><IMG class='img_browser' SRC='" . esc_attr( $_newstatpress_url ) . 'images/browsers/' . esc_attr( $img ) . "'></td>";
		} else {
			print '<td></td>';
		}
		print '<td>' . esc_html( $rk->browser ) . ' ' . esc_html( $rk->spider ) . "</td></tr>\n";
	}
	?>
	</tbody>
	</table>
	<?php
}

/**
 * Generate overview spiders
 */
function nsp_generate_overview_spiders() {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	global $newstatpress_dir;
	$_newstatpress_url = nsp_plugin_url();

	// determine the structure to use for URL.
	$permalink_structure = get_option( 'permalink_structure' );
	if ( '' === $permalink_structure ) {
		$extra = '/?';
	} else {
		$extra = '/';
	}

	$querylimit = ( ( get_option( 'newstatpress_el_overview' ) === '' ) ? 10 : intval( get_option( 'newstatpress_el_overview' ) ) );
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$spiders = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT date,time,agent,os,browser,spider
      FROM `$table_name`
      WHERE (spider<>'')
      ORDER BY id DESC LIMIT %d
      ",
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK.
	?>
	<table class='widefat nsp'>
	<thead>
		<tr>
		<th scope='col'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Time', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Terms', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Engine', 'newstatpress' ); ?></th>
		<th scope='col'><?php esc_html_e( 'Result', 'newstatpress' ); ?></th>
		</tr>
	</thead>
	<tbody id='the-list'>
	<?php
	foreach ( $spiders as $rk ) {
		print '<tr>
                <td>' . esc_html( nsp_hdate( $rk->date ) ) . '</td>
                <td>' . esc_html( $rk->time ) . '</td>';
		if ( '' !== $rk->spider ) {
			$img = str_replace( ' ', '_', strtolower( $rk->spider ) ) . '.png';
			print "<td><IMG class='img_os' SRC='" . esc_attr( $_newstatpress_url ) . '/images/spider/' . esc_attr( $img ) . "'> </td>";
		} else {
			print '<td></td>';
		}
		print '<td>' . esc_html( $rk->spider ) . '</td>
             <td> ' . esc_html( $rk->agent ) . "</td></tr>\n";
	}
	?>
	</tbody>
	</table>
	<?php
}


/**
 * Show overwiew
 *****************/
function nsp_new_stat_press_main() {

	?>

		<div class="wrap">
		<h2><?php esc_html_e( 'Overview', 'newstatpress' ); ?></h2>

	<?php
		wp_nonce_field( 'some-action-nonce' );

	/* Used to save closed meta boxes and their order */
	wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
	wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
	?>

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-1">
			<div id="post-body-content">
				<div class='wrap testnsp'>
				<div id="nsp_result-overview">
					<div class="loadAJAX">
					<?php
					global $_newstatpress;
					$api_key           = get_option( 'newstatpress_apikey' );
					$_newstatpress_url = nsp_plugin_url();
					$url               = $_newstatpress_url . '/includes/api/external.php';

					$msg_activated     = __( 'Loading... (Refresh page if no information is displayed).', 'newstatpress' );
					$msg_not_activated = '<span class=\'bold\'>' . __( 'Impossible to load the overview:', 'newstatpress' ) . '</span> ' . __( 'You must activate the external api first (Page Option>Api)', 'newstatpress' );

					get_option( 'newstatpress_externalapi' ) === 'checked' ? $message = $msg_activated : $message = $msg_not_activated;

					wp_enqueue_script( 'wp_ajax_nsp_js_overview', plugins_url( './js/nsp_overview.js', __FILE__ ), array( 'jquery' ), $_newstatpress['version'], true );
					wp_localize_script(
						'wp_ajax_nsp_js_overview',
						'nsp_externalAjax_overview',
						array(
							'ajaxurl'          => admin_url( 'admin-ajax.php' ),
							'Key'              => md5( gmdate( 'm-d-y H i' ) . $api_key ),
							'postCommentNonce' => wp_create_nonce( 'newstatpress-nsp_external-nonce' ),
						)
					);

					echo '<img id="nsp_error-overview" class="imgerror" src="' . esc_attr( $_newstatpress_url ) . '/images/error.png">';
					echo '<img id="nsp_loader-overview" src="' . esc_attr( $_newstatpress_url ) . '/images/ajax-loader.gif"> ' . wp_kses( $message, array( 'span' => array( 'class' => array() ) ) );
					?>
				</div>
				</div>
				</div> <!-- .wrap -->

				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes( '', 'normal', null ); ?>
					<?php do_meta_boxes( '', 'advanced', null ); ?>
				</div>

			</div> <!-- #post-body content-->
		</div> <!-- #post-body -->
		</div> <!-- #poststuff -->
		</div> <!-- .wrap -->

	<?php

}

/**
 *  NewStatpress main
 */
function nsp_new_stat_press_main3() {
	global $wpdb;
	$table_name = NSP_TABLENAME;

	global $newstatpress_dir;

	echo "<div class='wrap'><h2>" . esc_html__( 'Overview', 'newstatpress' ) . '</h2>';

	$api_key           = get_option( 'newstatpress_apikey' );
	$_newstatpress_url = nsp_plugin_url();
	$url               = $_newstatpress_url . '/includes/api/external.php';

	wp_enqueue_script( 'wp_ajax_nsp_js_overview', plugins_url( './js/nsp_overview.js', __FILE__ ), array( 'jquery' ), $_newstatpress['version'], true );
	wp_localize_script(
		'wp_ajax_nsp_js_overview',
		'nsp_externalAjax_overview',
		array(
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'Key'              => md5( gmdate( 'm-d-y H i' ) . $api_key ),
			'postCommentNonce' => wp_create_nonce( 'newstatpress-nsp_external-nonce' ),
		)
	);
	echo '<div id="nsp_result-overview"><img id="nsp_loader-overview" src="' . esc_attr( $_newstatpress_url ) . '/images/ajax-loader.gif"></div>';

	$_newstatpress_url = nsp_plugin_url();

	// determine the structure to use for URL.
	$permalink_structure = get_option( 'permalink_structure' );
	if ( '' === $permalink_structure ) {
		$extra = '/?';
	} else {
		$extra = '/';
	}

	$querylimit = ( ( '' === get_option( 'newstatpress_el_overview' ) ) ? 10 : get_option( 'newstatpress_el_overview' ) );
	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$lasthits = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT *
    FROM `$table_name`
    WHERE (os<>'' OR feed<>'')
    ORDER bY id DESC LIMIT %d
  ",
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK.

	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$lastsearchterms = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT date,time,referrer,urlrequested,search,searchengine
       FROM `$table_name`
       WHERE search<>''
       ORDER BY id DESC LIMIT %d
     ",
			$querylimit
		)
	);// phpcs:ignore: unprepared SQL OK.

	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$lastreferrers = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT date,time,referrer,urlrequested
      FROM `$table_name`
      WHERE
       ((referrer NOT LIKE %s) AND
       (referrer <>'') AND
       (searchengine='')
      ) ORDER BY id DESC LIMIT %d
      ",
			get_option( 'home' ) . '%',
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK

	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$useragents = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT agent,os,browser,spider
      FROM `$table_name`
      GROUP BY agent,os,browser,spider
      ORDER BY id DESC LIMIT %d
     ",
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK

	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$pages = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT date,time,urlrequested,os,browser,spider
       FROM `$table_name`
       WHERE (spider='' AND feed='')
       ORDER BY id DESC LIMIT %d
      ",
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK

	// use prepare.
	// phpcs:ignore -- db call ok; no-cache ok.
	$spiders = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT date,time,agent,os,browser,spider
      FROM `$table_name`
       WHERE (spider<>'')
       ORDER BY id DESC LIMIT %d
      ",
			$querylimit
		)
	); // phpcs:ignore: unprepared SQL OK
	?>

	<!-- Last hits table -->
	<div class='wrap'>
	<h2> <?php echo esc_html__( 'Last hits', 'newstatpress' ); ?></h2>
	<table class='widefat nsp'>
		<thead>
		<tr>
			<th scope='col'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Time', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'IP', 'newstatpress' ); ?></th>
			<th scope='col'><?php echo esc_html__( 'Country', 'newstatpress' ) . '/' . esc_html__( 'Language', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Page', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Feed', 'newstatpress' ); ?></th>
			<th></th>
			<th scope='col' style='width:120px;'><?php esc_html_e( 'OS', 'newstatpress' ); ?></th>
			<th></th>
			<th scope='col' style='width:120px;'><?php esc_html_e( 'Browser', 'newstatpress' ); ?></th>
		</tr>
		</thead>
		<tbody id='the-list'>
		<?php
		foreach ( $lasthits as $fivesdraft ) {
			print '<tr>';
			print '<td>' . esc_html( nsp_hdate( $fivesdraft->date ) ) . '</td>';
			print '<td>' . esc_html( $fivesdraft->time ) . '</td>';
			print '<td>' . esc_html( $fivesdraft->ip ) . '</td>';
			print '<td>' . esc_html( $fivesdraft->nation ) . '</td>';
			print '<td>' . esc_html( nsp_abbreviate( nsp_decode_url( filter_var( $fivesdraft->urlrequested, FILTER_SANITIZE_URL ) ), 30 ) ) . '</td>';
			print '<td>' . esc_html( $fivesdraft->feed ) . '</td>';

			if ( '' !== $fivesdraft->os ) {
				$val = nsp_get_browser_img( $fivesdraft->os );
				$img = $_newstatpress_url . '/images/os/' . str_replace( ' ', '_', strtolower( $val ) ) . '.png';
				print "<td class='browser'><img class='img_browser' SRC='" . esc_attr( $img ) . "'></td>";
			} else {
				print '<td></td>';
			}
			print '<td>' . esc_html( $fivesdraft->os ) . '</td>';

			if ( '' !== $fivesdraft->browser ) {
				$img = str_replace( ' ', '', strtolower( $fivesdraft->browser ) ) . '.png';
				print "<td><img class='img_browser' SRC='" . esc_attr( $_newstatpress_url ) . '/images/browsers/' . esc_attr( $img ) . "'></td>";
			} else {
				print '<td></td>';
			}
			print '<td>' . esc_html( $fivesdraft->browser ) . "</td></tr>\n";
		}
		?>
		</tbody>
	</table>
	</div>

	<!-- Last Search terms table -->
	<div class='wrap'>
	<h2><?php esc_html_e( 'Last search terms', 'newstatpress' ); ?></h2>
	<table class='widefat nsp'>
		<thead>
		<tr>
			<th scope='col'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Time', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Terms', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Engine', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Result', 'newstatpress' ); ?></th>
		</tr>
		</thead>
		<tbody id='the-list'>
		<?php
		foreach ( $lastsearchterms as $rk ) {
			print '<tr>
                  <td>' . esc_html( nsp_hdate( $rk->date ) ) . '</td><td>' . esc_html( $rk->time ) . "</td>
                  <td><a href='" . esc_attr( $rk->referrer ) . "' target='_blank'>" . esc_html( $rk->search ) . '</a></td>
                  <td>' . esc_html( $rk->searchengine ) . "</td><td><a href='" . esc_attr( get_bloginfo( 'url' ) . $extra . filter_var( $rk->urlrequested, FILTER_SANITIZE_URL ) ) . "' target='_blank'>" . esc_html__( 'page viewed', 'newstatpress' ) . "</a></td>
                </tr>\n";
		}
		?>
		</tbody>
	</table>
	</div>

	<!-- Last Referrers table -->
	<div class='wrap'>
	<h2><?php esc_html_e( 'Last referrers', 'newstatpress' ); ?></h2>
	<table class='widefat nsp'>
		<thead>
		<tr>
			<th scope='col'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Time', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'URL', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Result', 'newstatpress' ); ?></th>
		</tr>
		</thead>
		<tbody id='the-list'>
		<?php
		foreach ( $lastreferrers as $rk ) {
			print '<tr><td>' . esc_html( nsp_hdate( $rk->date ) ) . '</td><td>' . esc_html( $rk->time ) . "</td><td><a href='" . esc_attr( $rk->referrer ) . "' target='_blank'>" . esc_html( nsp_abbreviate( $rk->referrer, 80 ) ) . "</a></td><td><a href='" . esc_attr( get_bloginfo( 'url' ) . $extra . filter_var( $rk->urlrequested, FILTER_SANITIZE_URL ) ) . "'  target='_blank'>" . esc_html__( 'page viewed', 'newstatpress' ) . "</a></td></tr>\n";
		}
		?>
		</tbody>
	</table>
	</div>

	<!-- Last Agents -->
	<div class='wrap'>
	<h2><?php esc_html_e( 'Last agents', 'newstatpress' ); ?></h2>
	<table class='widefat nsp'>
		<caption><?php esc_html_e( 'Last agents', 'newstatpress' ); ?></caption>
		<thead>
		<tr>
			<th scope='col'><?php esc_html_e( 'Agent', 'newstatpress' ); ?></th>
			<th scope='col'></th><th scope='col' style='width:120px;'><?php esc_html_e( 'OS', 'newstatpress' ); ?></th>
			<th scope='col'></th>
			<th scope='col' style='width:120px;'> 
		<?php
			esc_html_e( 'Browser', 'newstatpress' );
			echo '/';
			esc_html_e( 'Spider', 'newstatpress' );
		?>
			<button id="hider">Hide</button><button id="shower">Show</button></th>
		</tr>
		</thead>
		<tbody id='the-list'>
		<?php
		foreach ( $useragents as $rk ) {
			if ( null !== $rk->spider ) {
				print "<tr class='spiderhide' style=\"background-color: #f6f6f0;\"><td>" . esc_html( $rk->agent ) . '</td>';
			} else {
				print '<tr><td>' . esc_html( $rk->agent ) . '</td>';
			}
			if ( '' !== $rk->os ) {
				$val = nsp_get_os_img( $rk->os );

				$img = str_replace( ' ', '_', strtolower( $val ) ) . '.png';
				print "<td><IMG class='img_browser' SRC='" . esc_attr( $_newstatpress_url ) . 'images/os/' . esc_attr( $img ) . "'> </td>";
			} else {
				print '<td></td>';
			}
			if ( '' !== $rk->os ) {
				print '<td>' . esc_html( $rk->os ) . '</td>';
			} else {
				print '<td>unknow</td>';
			}
			if ( '' !== $rk->browser ) {
				$val = nsp_get_browser_img( $rk->browser );
				$img = str_replace( ' ', '', strtolower( $val ) ) . '.png';
				print "<td><IMG class='img_browser' SRC='" . esc_attr( $_newstatpress_url ) . 'images/browsers/' . esc_attr( $img ) . "'></td>";
			} else {
				print '<td></td>';
			}
			print '<td>' . esc_html( $rk->browser ) . ' ' . esc_html( $rk->spider ) . "</td></tr>\n";
		}
		?>
		</tbody>
	</table>
	</div>

	<!-- Last Pages -->
	<div class='wrap'>
	<h2><?php esc_html__( 'Last pages', 'newstatpress' ); ?></h2>
	<table class='widefat nsp'>
		<thead>
		<tr>
			<th scope='col'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Time', 'newstatpress' ); ?></th>
			<th scope='col'><?php esc_html_e( 'Page', 'newstatpress' ); ?></th>
			<th scope='col' style='width:17px;'></th>
			<th scope='col' style='width:120px;'><?php esc_html_e( 'OS', 'newstatpress' ); ?></th>
			<th style='width:17px;'></th>
			<th scope='col' style='width:120px;'><?php esc_html_e( 'Browser', 'newstatpress' ); ?></th>
		</tr>
		</thead>
		<tbody id='the-list'>
		<?php
		foreach ( $pages as $rk ) {
			print '<tr><td>' . esc_html( nsp_hdate( $rk->date ) ) . '</td><td>' . esc_html( $rk->time ) . "</td>\n<td>" . esc_html( nsp_abbreviate( nsp_decode_url( filter_var( $rk->urlrequested, FILTER_SANITIZE_URL ) ), 60 ) ) . '</td>';
			if ( '' !== $rk->os ) {
				$img = str_replace( ' ', '_', strtolower( $rk->os ) ) . '.png';
				print "<td><IMG class='img_browser' SRC='" . esc_attr( $_newstatpress_url ) . '/images/os/' . esc_attr( $img ) . "'> </td>";
			} else {
				print '<td></td>';
			}
			print '<td>' . esc_html( $rk->os ) . '</td>';
			if ( '' !== $rk->browser ) {
				$img = str_replace( ' ', '', strtolower( $rk->browser ) ) . '.png';
				print "<td><IMG class='img_browser' SRC='" . esc_attr( $_newstatpress_url ) . '/images/browsers/' . esc_attr( $img ) . "'></td>";
			} else {
				print '<td></td>';
			}
			print '<td>' . esc_html( $rk->browser ) . ' ' . esc_html( $rk->spider ) . "</td></tr>\n";
		}
		?>
		</tbody>
	</table>
	</div>


	<?php
	// Last Spiders.
	print "<div class='wrap'><h2>" . esc_html__( 'Last spiders', 'newstatpress' ) . "</h2><table class='widefat nsp'><thead><tr><th scope='col'>" . esc_html__( 'Date', 'newstatpress' ) . "</th><th scope='col'>" . esc_html__( 'Time', 'newstatpress' ) . "</th><th scope='col'></th><th scope='col'>" . esc_html__( 'Spider', 'newstatpress' ) . "</th><th scope='col'>" . esc_html__( 'Agent', 'newstatpress' ) . '</th></tr></thead>';
	print "<tbody id='the-list'>";

	foreach ( $spiders as $rk ) {
		print '<tr><td>' . esc_html( nsp_hdate( $rk->date ) ) . '</td><td>' . esc_html( $rk->time ) . '</td>';
		if ( '' !== $rk->spider ) {
			$img = str_replace( ' ', '_', strtolower( $rk->spider ) ) . '.png';
			print "<td><IMG class='img_os' SRC='" . esc_attr( $_newstatpress_url ) . '/images/spider/' . esc_attr( $img ) . "'> </td>";
		} else {
			print '<td></td>';
		}
		print '<td>' . esc_html( $rk->spider ) . '</td><td> ' . esc_html( $rk->agent ) . "</td></tr>\n";
	}
	print '</table></div>';

	print '<br />';
	print '&nbsp;<i>StatPress table size: <b>' . esc_html( nsp_table_size( NSP_TABLENAME ) ) . '</b></i><br />';
	print '&nbsp;<i>StatPress current time: <b>' . esc_html( current_time( 'mysql' ) ) . '</b></i><br />';
	print '&nbsp;<i>RSS2 url: <b>' . esc_html( get_bloginfo( 'rss2_url' ) ) . ' (' . esc_html( nsp_extract_feed_from_url( get_bloginfo( 'rss2_url' ) ) ) . ')</b></i><br />';
	nsp_load_time();
}

/**
 * Abbreviate the given string to a fixed length
 *
 * @param string $s the string.
 * @param int    $c the number of chars.
 * @return the abbreviate string
 ***********************************************/
function nsp_abbreviate( $s, $c ) {
	// $s   = __( $s );
	$res = '';
	if ( strlen( $s ) > $c ) {
		$res = '...'; }
	return substr( $s, 0, $c ) . $res;
}


?>
