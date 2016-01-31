<?php

/**
 * Get valide user IP even behind proxy or load balancer (Could be fake)
 * added by cHab
 *
 * @return $user_IP
 */
function nsp_GetUserIP()
{
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
function nsp_cron_intervals($schedules) {
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


function nsp_load_time()
{
	echo "<font size='1'>Page generated in " . timer_stop(0,2) . "s</font>";
}

/**
 * Send an email notification with the overview statistics
 * added by cHab
 *
 * @param $arg : type of mail ('' or 'test')
 * @return $email_confirmation
 *************************************/
// function nsp_stat_by_email($arg='') {
//   global $nsp_option_vars;
//   $date = date('m/d/Y h:i:s a', time());
//
//   $name=$nsp_option_vars['mail_notification']['name'];
//   $status=get_option($name);
//   $name=$nsp_option_vars['mail_notification_freq']['name'];
//   $freq=get_option($name);
//
//   $userna = get_option('newstatpress_mail_notification_info');
//
//   $headers= 'From:NewStatPress';
//   $blog_title = get_bloginfo('name');
//   $subject=sprintf(__('Visits statistics from [%s]',nsp_TEXTDOMAIN), $blog_title);
//   if($arg=='test')
//     $subject=sprintf(__('This is a test from [%s]',nsp_TEXTDOMAIN), $blog_title);
//
// // echo $demo=nsp_BASENAME . '/includes/api/nsp_api_dashboard.php';
// //   include ('../includes/api/nsp_api_dashboard.php');
//     include ($newstatpress_url."/includes/api/nsp_api_dashboard.php");
//   $resultH=nsp_ApiDashboard("HTML");
//
//   $name=$nsp_option_vars['mail_notification_address']['name'];
//   $email_address=get_option($name);
//
//   $support_pluginpage="<a href='".nsp_SUPPORT_URL."' target='_blank'>".__('support page',nsp_TEXTDOMAIN)."</a>";
//   $author_linkpage="<a href='".nsp_PLUGIN_URL."/?page_id=2' target='_blank'>".__('the author',nsp_TEXTDOMAIN)."</a>";
//
//   $credits_introduction=sprintf(__('If you have found this plugin usefull and you like it, you can support the development by reporting bugs on the %s or  by adding/updating translation by contacting directly %s. As this plugin is maintained only on free time, you can also make a donation directly on the plugin website or through the plugin (Credits Page).',nsp_TEXTDOMAIN), $support_pluginpage, $author_linkpage);
//
//   $warning=__('This option is yet experimental, please report bugs or improvement (see link on the bottom)',nsp_TEXTDOMAIN);
//   $advising=__('You receive this email because you have enabled the statistics notification in the NewStatpress plugin (option menu) from your WP website ',nsp_TEXTDOMAIN);
//   $message = __('Dear',nsp_TEXTDOMAIN)." $userna, <br /> <br />
//              <i>$advising<STRONG>$blog_title</STRONG>.</i>
//              <mark>$warning.</mark> <br />
//              <br />".
//              __('Statistics at',nsp_TEXTDOMAIN)." $date (".__('server time',nsp_TEXTDOMAIN).") from  $blog_title: <br />
//              $resultH <br /> <br />"
//              .__('Best Regards from',nsp_TEXTDOMAIN)." <i>NewStatPress Team</i>. <br />
//              <br />
//              <br />
//              -- <br />
//              $credits_introduction";
//
//   $email_confirmation = wp_mail($email_address, $subject, $message);
//
//   return $email_confirmation;
// }





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


function nsp_DisplayTabsNavbarForMenuPages($menu_tabs, $current, $ref) {

    echo "<div id='usual1' class='icon32 usual'><br></div>";
    echo "<h2  class='nav-tab-wrapper'>";
    foreach( $menu_tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active selected' : '';
        echo "<a class='nav-tab$class' href='#$tab'>$name</a>";
    }
    echo '</h2>';
}

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


/**
 * Extract the feed from the given url
 *
 * @param url the url to parse
 * @return the extracted url
 *************************************/
function nsp_ExtractFeedFromUrl($url) {
  list($null,$q)=explode("?",$url);
  if (strpos($q, "&")!== false) list($res,$null)=explode("&",$q);
  else $res=$q;
  return $res;
}


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
