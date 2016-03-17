<?php
#error_reporting(E_ALL);
#ini_set('display_errors', 1);

if($_SERVER['REQUEST_METHOD'] != "GET") die("API available only from Newstatpress");

require_once('../../../../../wp-load.php');

// get the parameter from URL
$var = $_REQUEST["VAR"];

global $wpdb;
global $nsp_option_vars;
$table_name = $wpdb->prefix . "statpress";


$offsets = get_option($nsp_option_vars['stats_offsets']['name']);
   // $offsets['alltotalvisits'];
// $offsets['visitorsfeeds'];
   // $offsets['pageviews'];
// $offsets['spy'];
// $offsets['pageviewfeeds'];

// test all vars
if ($var=='alltotalvisits') {
  $qry = $wpdb->get_results(
  "SELECT count(distinct urlrequested, ip) AS pageview
   FROM $table_name AS t1
   WHERE
    spider='' AND
    feed='' AND
    urlrequested!='';
   ");
   if ($qry != null) {
     echo $qry[0]->pageview+$offsets['alltotalvisits'];
   }
} elseif ($var=='visits') {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
        date = '".gmdate("Ymd",current_time('timestamp'))."' AND
        spider='' and feed='';
      ");
   if ($qry != null) {
     echo $qry[0]->pageview;
   }
} elseif ($var=='yvisits') {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
        date = '".gmdate("Ymd",current_time('timestamp')-86400)."' AND
        spider='' and feed='';
      ");
   if ($qry != null) {
     echo $qry[0]->pageview;
   }
} elseif ($var=='mvisits') {
    if (get_option($nsp_option_vars['calculation']['name'])=='sum') {
      $qry = $wpdb->get_results(
        "SELECT SUM(pagv) AS pageview FROM (
          SELECT count(DISTINCT(ip)) AS pagv
          FROM $table_name
          WHERE
           DATE >= DATE_FORMAT(CURDATE(), '%Y%m01') AND
           spider='' and feed=''
          GROUP BY DATE
         ) AS pageview;
      ");
    } else {
        $qry = $wpdb->get_results(
          "SELECT count(DISTINCT(ip)) AS pageview
           FROM $table_name
           WHERE
            DATE >= DATE_FORMAT(CURDATE(), '%Y%m01') AND
            spider='' and feed='';
        ");
      }
    if ($qry != null) {
      echo $qry[0]->pageview;
    }
} elseif ($var=='wvisits') {
    if (get_option($nsp_option_vars['calculation']['name'])=='sum') {
      $qry = $wpdb->get_results(
        "SELECT SUM(pagv) AS pageview FROM (
          SELECT count(DISTINCT(ip)) AS pagv
          FROM $table_name
          WHERE
           YEARWEEK (date) = YEARWEEK( CURDATE()) AND
           spider='' and feed=''
          GROUP BY DATE
         ) AS pageview;
          ");
    } else {
        $qry = $wpdb->get_results(
          "SELECT count(DISTINCT(ip)) AS pageview
           FROM $table_name
           WHERE
             YEARWEEK (date) = YEARWEEK( CURDATE()) AND
             spider='' and feed='';
          ");
      }
   if ($qry != null) {
     echo $qry[0]->pageview;
   }
} elseif ($var=='totalvisits') {
    if (get_option($nsp_option_vars['calculation']['name'])=='sum') {
      $qry = $wpdb->get_results(
        "SELECT SUM(pagv) AS pageview FROM (
          SELECT count(DISTINCT(ip)) AS pagv
          FROM `avwp_statpress`
          WHERE
           spider='' AND
           feed=''
          GROUP BY DATE
         ) AS pageview;
          ");
    } else {
      $qry = $wpdb->get_results(
        "SELECT count(DISTINCT(ip)) AS pageview
         FROM $table_name
         WHERE
           spider='' AND
           feed='';
        ");
      }
   if ($qry != null) {
     echo $qry[0]->pageview;
   }
} elseif ($var=='totalpageviews') {
    $qry = $wpdb->get_results(
      "SELECT count(id) AS pageview
       FROM $table_name
       WHERE
         spider='' AND
         feed='';
      ");
   if ($qry != null) {
     echo $qry[0]->pageview+$offsets['pageviews'];;
   }
} elseif ($var=='todaytotalpageviews') {
    $qry = $wpdb->get_results(
      "SELECT count(id) AS pageview
       FROM $table_name
       WHERE
         date = '".gmdate("Ymd",current_time('timestamp'))."' AND
         spider='' AND
         feed='';
      ");
   if ($qry != null) {
     echo $qry[0]->pageview;
   }
} elseif ($var=='thistotalvisits') {
    $url = esc_sql($_REQUEST["URL"]);

    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
         spider='' AND
         feed='' AND
         urlrequested='".$url."';
      ");
   if ($qry != null) {
     echo $qry[0]->pageview;
   }
} elseif ($var=='monthtotalpageviews'){
    $qry = $wpdb->get_results(
      "SELECT count(id) AS pageview
       FROM $table_name
       WHERE
        DATE >= DATE_FORMAT(CURDATE(), '%Y%m01') AND
        spider='' and feed='';
      ");
   if ($qry != null) {
     echo $qry[0]->pageview;
   }
} elseif ($var=='widget_topposts') {
    $limit = intval($_REQUEST["LIMIT"]);
    $showcounts = $_REQUEST["FLAG"];

    $res="\n<ul>\n";
    $qry = $wpdb->get_results(
      "SELECT urlrequested,count(*) as totale
       FROM $table_name
       WHERE
         spider='' AND
         feed='' AND
         urlrequested LIKE '%p=%'
       GROUP BY urlrequested
       ORDER BY totale DESC LIMIT $limit;
      ");
   foreach ($qry as $rk) {
     $res.="<li><a href='?".$rk->urlrequested."' target='_blank'>".nsp_DecodeURL($rk->urlrequested)."</a></li>\n";
     if(strtolower($showcounts) == 'checked') { $res.=" (".$rk->totale.")"; }
   }
   echo "$res</ul>\n";
}

?>
