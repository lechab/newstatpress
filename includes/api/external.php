<?php
header('Access-Control-Allow-Origin: *'); 

#error_reporting(E_ALL);
#ini_set('display_errors', 1);

require_once('../../../../../wp-load.php');

$var;
$key; 
$par; 
$typ;
$resultJ;
$resultH;

$resultJ=array();
$resultH="";

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
  global $resultJ;
  global $resultH;

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
      nsp_ApiVersion();
      break;
    case 'overview':
      // use a switch to avoid invalid parameters are passed
      switch ($par) {
        case 'main':
        case 'dashboard':
        case 'null':
          nsp_ApiOverview();
          break;
        default:
          header('HTTP/1.0 403 Forbidden');
          die("Invalid PAR value for overview API.");    
          return;   
      }
      break;  
    default: 
      header('HTTP/1.0 403 Forbidden'); 
      die("Not recognized API.");
      return;
  }

  if ($typ == 'JSON') {
    // gives the complete output according to $resultJ
    echo json_encode(
      $resultJ
    ); 
  }

  if ($typ == 'HTML') {
    // gives the complete output according to $resultH
    echo $resultH; 
  }
}

/**
 * API: version
 *
 * Return the current version of newstatpress as json/html
 */
function nsp_ApiVersion() {
  global $_NEWSTATPRESS;
  global $var;
  global $typ;
  global $resultJ;
  global $resultH;

  $resultJ=array(
    $var => $_NEWSTATPRESS['version']
  );

  if ($typ=="JSON") return;  // avoid to calculte HTML if not necessary

  $resultH="<div>".$resultJ[$var]."</div>";  
}

/**
 * API: overview
 *
 * Return the overview according to the passed parameters as json encoded
 */
function nsp_ApiOverview() {
  global $var;
  global $par;
  global $typ; 
  global $wpdb;
  global $resultJ;
  global $resultH;
  global $nsp_option_vars;
  
  $table_name = nsp_TABLENAME;


  $lastmonth = nsp_Lastmonth();

///  $resultJ['since']=nsp_ExpandVarsInsideCode('%since%');  // export

  $thisyear = gmdate('Y', current_time('timestamp'));
  $thismonth = gmdate('Ym', current_time('timestamp'));
  $yesterday = gmdate('Ymd', current_time('timestamp')-86400);
  $today = gmdate('Ymd', current_time('timestamp'));
  $tlm[0]=substr($lastmonth,0,4); $tlm[1]=substr($lastmonth,4,2);

  $thisyearHeader = gmdate('Y', current_time('timestamp'));
  $lastmonthHeader = gmdate('M, Y',gmmktime(0,0,0,$tlm[1],1,$tlm[0]));
  $thismonthHeader = gmdate('M, Y', current_time('timestamp'));
  $yesterdayHeader = gmdate('d M', current_time('timestamp')-86400);
  $todayHeader = gmdate('d M', current_time('timestamp'));


  $resultJ['lastmonth']=$lastmonth;                       // export
  $resultJ['thisyear']=$thisyear;                         // export
  $resultJ['thismonth']=$thismonth;                       // export
  $resultJ['yesterday']=$yesterday;                       // export
  $resultJ['today']=$today;                               // export

  $thismonth1 = gmdate('Ym', current_time('timestamp')).'01';
  $thismonth31 = gmdate('Ymt', current_time('timestamp'));
  $lastmonth1 = $lastmonth.'01';
  $lastmonth31 = gmdate('Ymt', strtotime($lastmonth1));


  $overview_rows=array('visitors','visitors_feeds','pageview','feeds','spiders');

  foreach ($overview_rows as $row) {

    switch($row) {
      case 'visitors' :
        $row2='DISTINCT ip';
        $row_title=__('Visitors','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider=''";
        break;
      case 'visitors_feeds' :
        $row2='DISTINCT ip';
        $row_title=__('Visitors through Feeds','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed<>'' AND spider='' AND agent<>''";
        break;
      case 'pageview' :
        $row2='date';
        $row_title=__('Pageviews','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider=''";
        break;
      case 'spiders' :
        $row2='date';
        $row_title=__('Spiders','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed='' AND spider<>''";
        break;
      case 'feeds' :
        $row2='date';
        $row_title=__('Pageviews through Feeds','newstatpress');
        $sql_QueryTotal="SELECT count($row2) AS $row FROM $table_name WHERE feed<>'' AND spider=''";
        break;
    }

    if ($par=="main") {
      $qry_total = $wpdb->get_row($sql_QueryTotal);
      $qry_tyear = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$thisyear%'"); 

      $resultJ[$row.'_total'] = $qry_total->$row;  // export
      $resultJ[$row.'_tyear'] = $qry_tyear->$row;  // export
    }

    if (get_option($nsp_option_vars['calculation']['name'])=='sum') {

      // alternative calculation by mouth: sum of unique visitors of each day
      $tot=0;
      $t = getdate(current_time('timestamp'));
      $year = $t['year'];
      $month = sprintf('%02d', $t['mon']);
      $day= $t['mday'];
      $totlm=0;

      for($k=$t['mon'];$k>0;$k--)
      {
        //current month

      }
      for($i=0;$i<$day;$i++)
      {
        $qry_daylmonth = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$lastmonth$i%'");
        $qry_day=$wpdb->get_row($sql_QueryTotal. " AND date LIKE '$year$month$i%'");
        $tot+=$qry_day->$row;
        $totlm+=$qry_daylmonth->$row;

      }
      // echo $totlm." ,";
      $qry_tmonth->$row=$tot;
      $qry_lmonth->$row=$totlm;

    }
    else { // classic
      $qry_tmonth = $wpdb->get_row($sql_QueryTotal. " AND date BETWEEN '$thismonth1' AND '$thismonth31'");
      $qry_lmonth = $wpdb->get_row($sql_QueryTotal. " AND date BETWEEN '$lastmonth1' AND '$lastmonth31'");
    }

    $resultJ[$row.'_tmonth'] = $qry_tmonth->$row;  // export
    $resultJ[$row.'_lmonth'] = $qry_lmonth->$row;  // export

    $qry_y = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$yesterday'");
    $qry_t = $wpdb->get_row($sql_QueryTotal. " AND date LIKE '$today'");

    $resultJ[$row.'_qry_y'] = $qry_y->$row;  // export
    $resultJ[$row.'_qry_t'] = $qry_t->$row;  // export

    if($resultJ[$row.'_lmonth'] <> 0) $resultJ[$row.'_perc_change'] = round( 100 * ($resultJ[$row.'_tmonth'] / $resultJ[$row.'_lmonth'] ) - 100,1)."%";  // export
    else $resultJ[$row.'_perc_change'] ='';
    
    $resultJ[$row.'_title']=$row_title;       // export
  }

  if ($typ=="JSON") return;  // avoid to calculte HTML if not necessary

  // output a HTML representation of the collected data

  $overview_table='';

  // dashboard
  $overview_table.="<table class='widefat center nsp'>
                      <thead>
                      <tr class='sup dashboard'>
                      <th></th>
                          <th scope='col'>". __('M-1','newstatpress'). "</th>
                          <th scope='col' colspan='2'>". __('M','newstatpress'). "</th>
                          <th scope='col'>". __('Y','newstatpress'). "</th>
                          <th scope='col'>". __('T','newstatpress'). "</th>
                      </tr>
                      <tr class='inf dashboard'>
                      <th></th>
                          <th><span>$lastmonthHeader</span></th>
                          <th colspan='2'><span > $thismonthHeader </span></th>
                          <th><span>$yesterdayHeader</span></th>
                          <th><span>$todayHeader</span></th>
                      </tr></thead>
                      <tbody class='overview-list'>";

  foreach ($overview_rows as $row) {
    $result=nsp_CalculateVariation($resultJ[$row.'_tmonth'],$resultJ[$row.'_lmonth']);

    // build full current row
    $overview_table.="<tr><td class='row_title $row'>".$resultJ[$row.'_title']."</td>";
    if ($par=='main')
      $overview_table.="<td class='colc'>".$resultJ[$row.'_total']."</td>\n";
    if ($par=='main')
      $overview_table.="<td class='colc'>".$resultJ[$row.'_tyear']."</td>\n";
    $overview_table.="<td class='colc'>".$resultJ[$row.'_lmonth']."</td>\n";
    $overview_table.="<td class='colr'>".$resultJ[$row.'_tmonth'].$result[0] ."</td>\n";
    if ($par=='main')
      $overview_table.="<td class='colr'>".$result[1]." ".$result[2]."</td>\n";
    $overview_table.="<td class='colc'>".$resultJ[$row.'_qry_y']."</td>\n";
    $overview_table.="<td class='colc'>".$resultJ[$row.'_qry_t']."</td>\n";
    $overview_table.="</tr>";
  }

  $overview_table.="</tr></table>";

  $resultH=$overview_table;  

}


?> 



