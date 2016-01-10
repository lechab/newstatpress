<?php
header('Access-Control-Allow-Origin: *'); 

#error_reporting(E_ALL);
#ini_set('display_errors', 1);

require_once('../../../../../wp-load.php');
require('nsp_api_version.php');
require('nsp_api_dashboard.php');

$var;
$key; 
$par; 
$typ;

body();


/**
 * body function
 */
function body() {
  global $_NEWSTATPRESS;
  global $var;
  global $key; 
  global $par; 
  global $typ; 
  global $wpdb;

  header('HTTP/1.0 200 Ok');

  if($_SERVER['REQUEST_METHOD'] != "POST") {
    header('HTTP/1.0 403 Forbidden'); 
    die("Invalid use of API");
    return;
  }

  if (get_option('newstatpress_externalapi')!='checked') {
    header('HTTP/1.0 403 Forbidden'); 
    die("API not activated");
    return;
  }

  // read key from wordpress option
  $api_key=get_option('newstatpress_apikey');
  $api_key=md5(gmdate('m-d-y H i').$api_key);

  // get the parameter from URL
  $var = $_REQUEST["VAR"];
  $key = $_REQUEST["KEY"];  # key readed is md5(date('m-d-y H i').'Key')
  $par = $_REQUEST["PAR"];  # can be empty
  $typ = $_REQUEST["TYP"];  # can be empty

  if ($typ == null) $typ="JSON";

  if ($typ != "JSON" && $typ != "HTML") {
    header('HTTP/1.0 403 Forbidden'); 
    die("Return type not available");
    return;
  }

  if( !preg_match("/^[a-zA-Z0-9 ]*$/",$key) )  {
    header('HTTP/1.0 403 Forbidden');
    die("Invalid key");
    return;
  }

  if ($var == null && $key == null) {
    header('HTTP/1.0 403 Forbidden');
    die("API needs parameters");
    return;
  }

  // test if can use API
  if ($key != $api_key) {
    header('HTTP/1.0 403 Forbidden');
    die("Not authorized API access");
    return;
  }

  switch ($var) {
    case 'version':
      $result=nsp_ApiVersion($typ);
      break;
    case 'dashboard':
      $result=nsp_ApiDashboard($typ);
      break;
    default: 
      header('HTTP/1.0 403 Forbidden'); 
      die("Not recognized API.");
      return;
  }

  if ($typ == 'JSON') {
    // gives the complete output according to $resultJ
    echo json_encode(
      $result
    ); 
  }

  if ($typ == 'HTML') {
    // gives the complete output according to $resultH
    echo $result; 
  }
}

?> 



