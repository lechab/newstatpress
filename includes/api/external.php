<?php
if($_SERVER['REQUEST_METHOD'] != "POST") die("Invalid use of API");

require_once('../../../../../wp-load.php');

if (get_option('newstatpress_externalapi')!='checked') die("API not activated");;

// get the parameter from URL
$var = $_REQUEST["VAR"];
$key = $_REQUEST["KEY"];  # key readed is md5(date('m-d-y H i')+'Key')
$par = $_REQUEST["PAR"];  # can be empty

if( !preg_match("/^[a-zA-Z0-9 ]*$/",$key) ) die("Invalid key");

if ($var == null && $key == null) die("API needs parameters");

global $wpdb;
$table_name = $wpdb->prefix . "statpress";

// read key from wordpress option
$api_key=get_option('newstatpress_apikey');
$api_key=md5(gmdate('m-d-y H i')+$api_key);


// test if can use API
if ($key != $api_key)  die("Not authorized API access.");

switch ($var) {
  case 'version':
    nsp_ApiVersion();
    break;
  case 'overview':
    // use a switch to avoid invalid parameters are passed
    switch ($par):
      case 'main':
      case 'dashboard':
      case 'null':
        nsp_ApiOverview($par);
        break;
      default:
        die("Invalid PAR value for overview API.");        
    break;  
  default: 
    die("Not recognized API.");
}

/**
 * API: version
 *
 * Return the current version of newstatpress as json encoded
 */
function nsp_ApiVersion() {
  echo json_encode(
    array(
      $var => $_NEWSTATPRESS['version']
    )
  );  
}

/**
 * API: overview
 *
 * Return the overview according to the passed parameters as json encoded
 *
 * @param par the API paramenter
 */
function nsp_ApiOverview($par) {
  $result='';

  $thisyear = gmdate('Y', current_time('timestamp'));

  $overview_rows=array('visitors','visitors_feeds','pageview','feeds','spiders');
  foreach ($overview_rows as $row) {

    switch($row) {
      case 'visitors' :
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider=''";
       break;
      case 'visitors_feeds' :
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed<>'' AND spider='' AND agent<>''";
        break;
      case 'pageview' :
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider=''";
        break;
      case 'spiders' :
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider<>''";
        break;
      case 'feeds' :
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed<>'' AND spider=''";
        break;
    }

    $qry_total = $wpdb->get_row($sql_QueryTotal);
    $qry_tyear = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$thisyear%'"); 

    $result+="$row._visitors_total => $qry_total
              $row._visitors_tyear => $qry_tyear"; 
  }

  // gives the complete output
  echo json_encode(
    array(
      $result
    )
  );  
}


?> 



