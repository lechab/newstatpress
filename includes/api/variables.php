<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if($_SERVER['REQUEST_METHOD'] != "GET") die("API available only from Newstatpress");

require_once('../../../../../wp-load.php');

// get the parameter from URL
$var = $_REQUEST["VAR"];

global $wpdb;
$table_name = $wpdb->prefix . "statpress";


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

   echo $qry[0]->pageview;
} elseif ($var=='visits') {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
        date = '".gmdate("Ymd",current_time('timestamp'))."' AND
        spider='' and feed='';
      ");
    echo $qry[0]->pageview;  
} elseif ($var=='yvisits') {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
        date = '".gmdate("Ymd",current_time('timestamp')-86400)."' AND
        spider='' and feed='';
      ");
    echo $qry[0]->pageview;  
} elseif ($var=='mvisits') {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
        date LIKE '".gmdate('Ym', current_time('timestamp'))."%'
        spider='' and feed='';
      ");  
    echo $qry[0]->pageview;
} elseif ($var=='totalvisits') {
    $qry = $wpdb->get_results(
      "SELECT count(DISTINCT(ip)) AS pageview
       FROM $table_name
       WHERE
         spider='' AND
         feed='';
      ");
    echo $qry[0]->pageview;
}  elseif ($var=='totalpageviews') {
     $qry = $wpdb->get_results(
      "SELECT count(id) AS pageview
       FROM $table_name
       WHERE
         spider='' AND
         feed='';
      ");  
     echo $qry[0]->pageview;
}  elseif ($var=='todaytotalpageviews') {
    $qry = $wpdb->get_results(
      "SELECT count(id) AS pageview
       FROM $table_name
       WHERE
         date = '".gmdate("Ymd",current_time('timestamp'))."' AND
         spider='' AND
         feed='';
      ");  
    echo $qry[0]->pageview;
}  elseif ($var=='thistotalvisits') {

    /// need to pass the url to use

    /// echo $qry[0]->pageview;  
} 

?> 

/*
plugins_url('newstatpress')."/includes/api/variables.html"

Number of users: <span id="alltotalvisits">_</span>
<script type="text/javascript">
   var xmlhttp = new XMLHttpRequest();

   xmlhttp.onreadystatechange = function() {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      document.getElementById("alltotalvisits").innerHTML=xmlhttp.responseText;
    }
   }

    var url="http://localhost/blog/wp-content/plugins/newstatpress/includes/api/variables.php?VAR=alltotalvisits"

   xmlhttp.open("GET", url, true);
   xmlhttp.send();
</script>



*/




