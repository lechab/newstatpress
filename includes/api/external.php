<?php

// Make sure plugin remains secure if called directly
if( !defined( 'ABSPATH' ) ) {
  if( !headers_sent() ) { header('HTTP/1.1 403 Forbidden'); }
  die(__('ERROR: This plugin requires WordPress and will not function if called directly.','newstatpress'));
}

require('nsp-api-version.php');
require('nsp-api-wpversion.php');
require('nsp-api-dashboard.php');
require('nsp-api-overview.php');

/**
 * body function of external API Nonce
 */
function nsp_externalApiAjaxN() {
  $nonce = $_POST['postCommentNonce'];

  // check to see if the submitted nonce matches with the
  // generated nonce we created earlier
  if (!wp_verify_nonce($nonce, 'newstatpress-nsp_external-nonce')) {
    die ( 'Busted!');
  }

  nsp_externalApiAjax();
}

/**
 * body function of external API
 */
function nsp_externalApiAjax() {
  global $_NEWSTATPRESS;
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
  $var = substr(preg_replace("/[^a-z]+/", "", $_REQUEST["VAR"]), 0, 9);
  $key = preg_replace("/[^a-z0-9]+/", "", $_REQUEST["KEY"]);            # key readed is md5(date('m-d-y H i').'Key')
  $par = intval($_REQUEST["PAR"]);                                      # can be empty
  $typ = substr(preg_replace("/[^A-Z]+/", "", $_REQUEST["TYP"]), 0, 4); # can be empty

  if ($typ == null) $typ="JSON";

  if ($typ != "JSON" && $typ != "HTML") {
    header('HTTP/1.0 403 Forbidden');
    die("Return type not available");
    return;
  }

  if( !preg_match('/^[a-f0-9]{32}$/',$key) )  {
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
      $result=nsp_api_version($typ);
      break;
    case 'wpversion':
      $result=nsp_ApiWpVersion($typ);
      break;
    case 'dashboard':
      $result=nsp_ApiDashboard($typ);
      break;
    case 'overview':
      $result=nsp_ApiOverview($typ, $par);
      break;
    default:
      header('HTTP/1.0 403 Forbidden');
      die("Not recognized API.");
      return;
  }

  if ($typ == 'JSON') {
    // response output
    header( "Content-Type: application/json" );
    // gives the complete output according to $resultJ
    echo json_encode(
      $result
    );
  }

  if ($typ == 'HTML') {
    // response output
    header( "Content-Type: application/html" );
    // gives the complete output according to $resultH
    echo $result;
  }
  wp_die();
}
?>
