<?php

/**
 * Get valide user IP even behind proxy or load balancer (Could be fake)
 * added by cHab
 *
 * @return $user_IP
 */
function nsp_GetUserIP() {
  $user_IP = "";
  $ip_pattern = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/';
	$http_headers = array('HTTP_X_REAL_IP',
                        'HTTP_X_CLIENT',
                        'HTTP_X_FORWARDED_FOR',
                        'HTTP_CLIENT_IP',
                        'REMOTE_ADDR'
                      );

  foreach($http_headers as $header) {
    if ( isset($_SERVER[$header]) ) {
      if (function_exists('filter_var') && filter_var($_SERVER[$header], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE) !== false ) {
          $user_IP = $_SERVER[$header];
          break;
      }
      else { // for php version < 5.2.0
        if(preg_match($ip_pattern,$_SERVER[$header])) {
          $user_IP = $_SERVER[$header];
          break;
        }
      }
    }
  }

	return $user_IP;
}


//---------------------------------------------------------------------------
// CRON Functions
//---------------------------------------------------------------------------

/**
 * Add Cron intervals : 4 times/day, Once/week, Once/mounth
 * added by cHab
 *
 * @param $schedules
 * @return $schedules
 */
function nsp_CronIntervals($schedules) {
  $schedules['fourlybyday'] = array(
   'interval' => 21600, // seconds
   'display' => __('Four time by Day',nsp_TEXTDOMAIN)
  );
  $schedules['weekly'] = array(
   'interval' => 604800,
   'display' => __('Once a Week',nsp_TEXTDOMAIN)
  );
  $schedules['monthly'] = array(
   'interval' => 2635200,
   'display' => __('Once a Month',nsp_TEXTDOMAIN)
  );
  return $schedules;
}
add_filter( 'cron_schedules', 'nsp_CronIntervals');


/**
 * Calculate offset_time in second to add to epoch format
 * added by cHab
 *
 * @param $t,$tu
 * @return $offset_time
 ***********************************************************/
function nsp_calculationOffsetTime($t,$tu) {

  list($current_hour, $current_minute) = explode(":", date("H:i",$t));
  list($publishing_hour, $publishing_minutes) = explode(":", $tu);

  if($current_hour>$publishing_hour)
    $plus_hour=24-$current_hour+$publishing_hour;
  else
    $plus_hour=$publishing_hour-$current_hour;

  if($current_minute>$publishing_minutes) {
    $plus_minute=60-$current_minute+$publishing_minutes;
    if($plus_hour==0)
      $plus_hour=23;
    else
      $plus_hour=$plus_hour-1;
  }
  else
    $plus_minute=$publishing_minutes-$current_minute;

  return $offset_time=$plus_hour*60*60+$plus_minute*60;
}


//---------------------------------------------------------------------------
// NOTICE Functions
//---------------------------------------------------------------------------
/**
 * display notice
 * added by cHab
 *
 * @param $activation 1: display, 0 : do noting
 ***********************************************************/
function nsp_NoticeNew($activation) {
  if(!$activation) {
    $description=__('This new version integrates a new major function : <strong>Email Notification</strong> (see Option Page) to get periodic reports of your statistics. This function remains a bit experimental until it\'s tested recursively, thanks to be comprehensive. <br/> <i>Thanks to <strong>Douglas R.</strong> to support our work with his donation.</i>',nsp_TEXTDOMAIN);
  ?>
    <div id="nspnotice" class="notice" style="padding:10px">
      <a id="close" class="close"><span class="dashicons dashicons-no"></span>close</a>
      <span>
        <?php echo $description ?>
      </span>
    </div>
  <?php
  }
}

function nsp_CalculateEpochOffsetTime( $t1, $t2, $output_unit ) { //to complete with more output_unit
  $offset_time_in_seconds = abs($t1-$t2);

  if($output_unit=='day')
    $offset_time=$offset_time_in_seconds/86400;
  if($output_unit=='hour')
      $offset_time=$offset_time_in_seconds/3600;
  else {
    $offset_time=$offset_time_in_seconds;
  }

  return $offset_time;
}

function nsp_GetDaysInstalled() {
  global $nsp_option_vars;
  $name=$nsp_option_vars['settings']['name'];
  $settings=get_option($name);
	$install_date	= empty( $settings['install_date'] ) ? time() : $settings['install_date'];
	$num_days_inst	= nsp_CalculateEpochOffsetTime($install_date, time(), 'day');
  if( $num_days_inst < 1 )
    $num_days_inst = 1;

	return $num_days_inst;
}

//---------------------------------------------------------------------------
// URL Functions
//---------------------------------------------------------------------------

/**
 * Extract the feed from the given url
 *
 * @param the url to parse
 * @return the extracted url
 *************************************/
function nsp_ExtractFeedFromUrl($url) {
  list($null,$q)=explode("?",$url);

  if (strpos($q, "&")!== false)
    list($res,$null)=explode("&",$q);
  else
    $res=$q;

  return $res;
}

function nsp_GetUrl() {
	// $url  = _is_ssl() ? 'https://' : 'http://';
  $url = 'http://';
	$url .= nsp_SERVER_NAME.$_SERVER['REQUEST_URI'];
	return $url;
}

/**
* Fix poorly formed URLs so as not to throw errors or cause problems
*
* @return $url
*/
function nsp_FixUrl( $url, $rem_frag = FALSE, $rem_query = FALSE, $rev = FALSE ) {
	$url = trim( $url );
	/* Too many forward slashes or colons after http */
	$url = preg_replace( "~^(https?)\:+/+~i", "$1://", $url );
	/* Too many dots */
	$url = preg_replace( "~\.+~i", ".", $url );
	/* Too many slashes after the domain */
	$url = preg_replace( "~([a-z0-9]+)/+([a-z0-9]+)~i", "$1/$2", $url );
	/* Remove fragments */
	if( !empty( $rem_frag ) && strpos( $url, '#' ) !== FALSE ) { $url_arr = explode( '#', $url ); $url = $url_arr[0]; }
	/* Remove query string completely */
	if( !empty( $rem_query ) && strpos( $url, '?' ) !== FALSE ) { $url_arr = explode( '?', $url ); $url = $url_arr[0]; }
	/* Reverse */
	if( !empty( $rev ) ) { $url = strrev($url); }
	return $url;
}

/***
* Get query string array from URL
***/
function nsp_GetQueryArgs( $url ) {
	if( empty( $url ) ) { return array(); }
	$query_str = nsp_GetQueryString( $url );
	parse_str( $query_str, $args );
	return $args;
}

function nsp_GetQueryString( $url ) {
	/***
	* Get query string from URL
	* Filter URLs with nothing after http
	***/
	if( empty( $url ) || preg_match( "~^https?\:*/*$~i", $url ) ) { return ''; }
	/* Fix poorly formed URLs so as not to throw errors when parsing */
	$url = nsp_FixUrl( $url );
	/* NOW start parsing */
	$parsed = @parse_url($url);
	/* Filter URLs with no query string */
	if( empty( $parsed['query'] ) ) { return ''; }
	$query_str = $parsed['query'];
	return $query_str;
}

function nsp_AdminNagNotices() {
	global $current_user;
	$nag_notices = get_user_meta( $current_user->ID, 'newstatpress_nag_notices', TRUE );
	if( !empty( $nag_notices ) ) {
		$nid			= $nag_notices['nid'];
		$style		= $nag_notices['style']; /* 'error'  or 'updated' */
		$timenow	= time();
		$url			= nsp_GetUrl();
		$query_args		= nsp_GetQueryArgs( $url );
		$query_str		= '?' . http_build_query( array_merge( $query_args, array( 'newstatpress_hide_nag' => '1', 'nid' => $nid ) ) );
		$query_str_con	= 'QUERYSTRING';
		$notice			= str_replace( array( $query_str_con ), array( $query_str ), $nag_notices['notice'] );
		// echo '<div class="'.$style.'"><p>'.$notice.'</p></div>';
    ?>
      <div id="nspnotice" class="<?php echo $style; ?>" style="padding:10px">
        <!-- <a  id="close" class="close"><span class="dashicons dashicons-no"></span>close</a> -->
        <p><?php echo $notice ?></p>
      </div>
    <?php
	}
}

function nsp_CheckNagNotices() {
	global $current_user;
	$status	= get_user_meta( $current_user->ID, 'newstatpress_nag_status', TRUE );
	if( !empty( $status['currentnag'] ) ) { add_action( 'admin_notices', 'nsp_AdminNagNotices' ); return; }
	if( !is_array( $status ) ) { $status = array(); update_user_meta( $current_user->ID, 'newstatpress_nag_status', $status ); }
	$timenow		= time();
	$num_days_inst	= nsp_GetDaysInstalled();
  $votedate=14;
  $donatedate=90;
  $num_days_inst=95; //debug
	$query_str_con	= 'QUERYSTRING';
	/* Notices (Positive Nags) */
	if( empty( $status['currentnag'] ) && ( empty( $status['lastnag'] ) || $status['lastnag'] <= $timenow - 1209600 ) ) {
		if( empty( $status['vote'] ) && $num_days_inst >= $votedate ) {
			$nid = 'n01';
      $style = 'notice';

      $notice_text = '<p>'. __( 'It looks like you\'ve been using NewStatPress for a while now. That\'s great!', nsp_TEXTDOMAIN ).'</p>';
      $notice_text.= '<p>'. __( 'If you find this plugin useful, would you take a moment to give it a rating on WordPress.org?', nsp_TEXTDOMAIN ).'</p>';
      $notice_text.= '<a class=\"button button-primary\" href=\"'.nsp_RATING_URL.'\" target=\"_blank\" rel=\"external\">'. __( 'Yes, I\'d like to rate it!', nsp_TEXTDOMAIN ) .'</a>';
      $notice_text.= ' &nbsp; ';
      $notice_text.= '<a class=\"button button-default\" href=\"'.$query_str_con.'\" target=\"_self\" rel=\"external\">'. __( 'I already did!', nsp_TEXTDOMAIN ) .'</a>';

      $status['currentnag'] = TRUE;
      $status['vote'] = FALSE;
		}
		elseif( empty( $status['donate'] ) && $num_days_inst >= $donatedate ) {
			$nid = 'n02';
      $style = 'notice';

      $notice_text = '<p>'. __( 'You\'ve been using NewStatPress for several months now. We hope that means you like it and are finding it helpful.', nsp_TEXTDOMAIN ).'</p>';
      $notice_text.= '<p>'. __( 'NewStatPress is provided for free and maintained only on free time. If you like the plugin, consider a donation to help further its development', nsp_TEXTDOMAIN ).'</p>';
      $notice_text.= '<a class=\"button button-primary\" href=\"'.nsp_DONATE_URL.'\" target=\"_blank\" rel=\"external\">'. __( 'Yes, I\'d like to donate!', nsp_TEXTDOMAIN ) .'</a>';
      $notice_text.= ' &nbsp; ';
      $notice_text.= '<a class=\"button button-default\" href=\"'.$query_str_con.'\" target=\"_self\" rel=\"external\">'. __( 'I already did!', nsp_TEXTDOMAIN ) .'</a>';

			$status['currentnag'] = TRUE;
      $status['donate'] = FALSE;
		}
	}

	if( !empty( $status['currentnag'] ) ) {
		add_action( 'admin_notices', 'nsp_AdminNagNotices' );
		$new_nag_notice = array( 'nid' => $nid, 'style' => $style, 'notice' => $notice_text );
		update_user_meta( $current_user->ID, 'newstatpress_nag_notices', $new_nag_notice );
		update_user_meta( $current_user->ID, 'newstatpress_nag_status', $status );
	}
}

function nsp_AdminNotices() {
	$admin_notices = get_option('newstatpress_admin_notices');
	if( !empty( $admin_notices ) ) {
		$style 	= $admin_notices['style']; /* 'error' or 'updated' */
		$notice	= $admin_notices['notice'];
		echo '<div class="'.$style.'"><p>'.$notice.'</p></div>';
	}
	delete_option('newstatpress_admin_notices');
}

add_action( 'admin_init', 'nsp_HideNagNotices', -10 );
function nsp_HideNagNotices() {
	// if( !nsp_is_user_admin() ) { return; }
	$ns_codes		= array( 'n01' => 'vote',
                       'n02' => 'donate', );
	if( !isset( $_GET['newstatpress_hide_nag'], $_GET['nid'], $ns_codes[$_GET['nid']] ) || $_GET['newstatpress_hide_nag'] != '1' ) { return; }
	global $current_user;
	$status			= get_user_meta( $current_user->ID, 'newstatpress_nag_status', TRUE );
	$timenow		= time();
	$url			= nsp_GetUrl();
	$query_args		= nsp_GetQueryArgs( $url ); unset( $query_args['newstatpress_hide_nag'],$query_args['nid'] );
	$query_str		= http_build_query( $query_args ); if( $query_str != '' ) { $query_str = '?'.$query_str; }
	$redirect_url	= nsp_FixUrl( $url, TRUE, TRUE ) . $query_str;
	$status['currentnag'] = FALSE; $status['lastnag'] = $timenow; $status[$ns_codes[$_GET['nid']]] = TRUE;
	update_user_meta( $current_user->ID, 'newstatpress_nag_status', $status );
	update_user_meta( $current_user->ID, 'newstatpress_nag_notices', array() );
	wp_redirect( $redirect_url );
	exit;
}


//---------------------------------------------------------------------------
// OTHER Functions
//---------------------------------------------------------------------------


function nsp_load_time()
{
	echo "<font size='1'>Page generated in " . timer_stop(0,2) . "s</font>";
}



/**
 * Display tabs pf navigation bar for menu in page
 *
 * @param menu_tabs list of menu tabs
 * @param current current tabs
 * @param ref page reference
 */
function nsp_DisplayTabsNavbarForMenuPage($menu_tabs, $current, $ref) {
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $menu_tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=$ref&tab=$tab'>$name</a>";
    }
    echo '</h2>';
}


// function nsp_DisplayTabsNavbarForMenuPages($menu_tabs, $current, $ref) {
//
//     echo "<div id='usual1' class='icon32 usual'><br></div>";
//     echo "<h2  class='nav-tab-wrapper'>";
//     foreach( $menu_tabs as $tab => $name ){
//         $class = ( $tab == $current ) ? ' nav-tab-active selected' : '';
//         echo "<a class='nav-tab$class' href='#$tab'>$name</a>";
//     }
//     echo '</h2>';
// }

/**
 * Display data in table extracted from the given query
 *
 * @param fld GROUP BY argument of query
 * @param fldtitle title of field
 * @param limit quantity of elements to extract
 * @param param extra arguemnt for query (like DISTINCT)
 * @param queryfld field of query
 * @param exclude WHERE argument of query
 * @param print TRUE if the table is to print in page
 * @return return the HTML output accoding to the sprint state
 */
function nsp_GetDataQuery2($fld, $fldtitle, $limit = 0, $param = "", $queryfld = "", $exclude= "", $print = TRUE) {
  global $wpdb;
  $table_name = nsp_TABLENAME;

  if ($queryfld == '') {
    $queryfld = $fld;
  }
  $text = "<div class='wrap'><table class='widefat'>\n<thead><tr><th scope='col' class='keytab-head'><h2>$fldtitle</h2></th><th scope='col' style='width:20%;text-align:center;'>".__('Visits','newstatpress')."</th><th></th></tr></thead>\n";
  $rks = $wpdb->get_var("
     SELECT count($param $queryfld) as rks
     FROM $table_name
     WHERE 1=1 $exclude;
  ");

  if($rks > 0) {
    $sql="
      SELECT count($param $queryfld) as pageview, $fld
      FROM $table_name
      WHERE 1=1 $exclude
      GROUP BY $fld
      ORDER BY pageview DESC
    ";
    if($limit > 0) {
      $sql=$sql." LIMIT $limit";
    }
    $qry = $wpdb->get_results($sql);
    $tdwidth=450;

    // Collects data
    $data=array();
    foreach ($qry as $rk) {
      $pc=round(($rk->pageview*100/$rks),1);
      if($fld == 'nation') { $rk->$fld = strtoupper($rk->$fld); }
      if($fld == 'date') { $rk->$fld = nsp_hdate($rk->$fld); }
      if($fld == 'urlrequested') { $rk->$fld = nsp_DecodeURL($rk->$fld); }
      $data[substr($rk->$fld,0,250)]=$rk->pageview;
    }
  }

  // Draw table body
  $text .= "<tbody id='the-list'>";
  if($rks > 0) {  // Chart!

    if($fld == 'nation') { // Nation chart
      $charts=plugins_url('newstatpress')."/includes/geocharts.html".nsp_GetGoogleGeo($data);
    }
    else { // Pie chart
      $charts=plugins_url('newstatpress')."/includes/piecharts.html".nsp_GetGooglePie($fldtitle, $data);
    }

    foreach ($data as $key => $value) {
      $text .= "<tr><td class='keytab'>".$key."</td><td class='valuetab'>".$value."</td></tr>";
    }

    $text .= "<tr><td colspan=2 style='width:50%;'>
        <iframe src='".$charts."' class='framebox'>
          <p>[_e('This section requires a browser that supports iframes.]','newstatpress')</p>
        </iframe></td></tr>";
  }
  $text .= "</tbody></table></div><br>\n";
  if ($print) print $text;
  else return $text;
}

/**
 * Get google url query for geo data
 *
 * @param data_array the array of data_array
 * @return the url with data
 */
function nsp_GetGoogleGeo($data_array) {
  if(empty($data_array)) { return ''; }
  // get hash
  foreach($data_array as $key => $value ) {
    $values[] = $value;
    $labels[] = $key;
  }
  return "?cht=Country&chd=".(implode(",",$values))."&chlt=Popularity&chld=".(implode(",",$labels));
}

/**
 * Get google url query for pie data
 *
 * @param data_array the array of data_array
 * @param title the title to use
 * @return the url with data
 */
function nsp_GetGooglePie($title, $data_array) {
  if(empty($data_array)) { return ''; }
  // get hash
  foreach($data_array as $key => $value ) {
    $values[] = $value;
    $labels[] = $key;
  }

  return "?title=".$title."&chd=".(implode(",",$values))."&chl=".urlencode(implode("|",$labels));
}




//---------------------------------------------------------------------------
// TABLE Functions
//---------------------------------------------------------------------------

function nsp_TableSize($table) {
  global $wpdb;
  $res = $wpdb->get_results("SHOW TABLE STATUS LIKE '$table'");
  foreach ($res as $fstatus) {
    $data_lenght = $fstatus->Data_length;
    $data_rows = $fstatus->Rows;
  }
  return number_format(($data_lenght/1024/1024), 2, ",", " ")." Mb ($data_rows ". __('records',nsp_TEXTDOMAIN).")";
}


?>
