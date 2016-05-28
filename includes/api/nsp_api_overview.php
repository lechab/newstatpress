<?php

#error_reporting(E_ALL);
#ini_set('display_errors', 1);

/**
 * API: Overview
 *
 * Return the overview according to the passed parameters as json encoded
 *
 * @param typ the type of result (Json/Html)
 * @param par the number of days for the graph (20 default, if 0 use the one in NewStatPress option)
 * @return the result
 */
function nsp_ApiOverview($typ, $par) {
  global $wpdb;
  global $nsp_option_vars;

  $table_name = nsp_TABLENAME;

  $since = nsp_ExpandVarsInsideCode('%since%');
  $lastmonth = nsp_Lastmonth();
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


  // get the days of the graph
  $gdays=intval($par);
  if($gdays == 0) { $gdays=get_option('newstatpress_daysinoverviewgraph'); }
  if($gdays == 0) { $gdays=20; }

  // get result of dashboard as some date is shared with this
  $resultJ=nsp_ApiDashboard("JSON");




  // output an HTML representation of the collected data


  $overview_table='';

  // dashboard
  $overview_table.="<table class='widefat center nsp'>
                     <thead>
                      <tr class='sup'>
                       <th></th>
                       <th>". __('Total since','newstatpress'). "</th>
                       <th scope='col'>". __('This year','newstatpress'). "</th>
                       <th scope='col'>". __('Last month','newstatpress'). "</th>
                       <th scope='col' colspan='2'>". __('This month','newstatpress'). "</th>
                       <th scope='col' colspan='2'>". __('Target This month','newstatpress'). "</th>
                       <th scope='col'>". __('Yesterday','newstatpress'). "</th>
                       <th scope='col'>". __('Today','newstatpress'). "</th>
                      </tr>
                      <tr class='inf'>
                       <th></th>
                       <th><span>$since</span></th>
                       <th><span>$thisyearHeader</span></th>
                       <th><span>$lastmonthHeader</span></th>
                       <th colspan='2'><span > $thismonthHeader </span></th>
                       <th colspan='2'><span > $thismonthHeader </span></th>
                       <th><span>$yesterdayHeader</span></th>
                       <th><span>$todayHeader</span></th>
                      </tr>
                     </thead>
                    <tbody class='overview-list'>";

  // build body table overview
  $overview_rows=array('visitors','visitors_feeds','pageview','feeds','spiders');

  foreach ($overview_rows as $row) {
    $result=nsp_CalculateVariation($resultJ[$row.'_tmonth'],$resultJ[$row.'_lmonth']);

    // build full current row
    $overview_table.="<tr><td class='row_title $row'>".$resultJ[$row.'_title']."</td>";
    $overview_table.="<td class='colc'>".$resultJ[$row.'_lmonth']."</td>\n";
    $overview_table.="<td class='colr'>".$resultJ[$row.'_tmonth'].$result[0] ."</td>\n";
    $overview_table.="<td class='colr'> $result[1] $result[2] </td>\n";
    $overview_table.="<td class='colc'>".$resultJ[$row.'_qry_y']."</td>\n";
    $overview_table.="<td class='colc'>".$resultJ[$row.'_qry_t']."</td>\n";
    $overview_table.="</tr>";
  }

  $overview_table.="</tr></table>";

  $resultH=$overview_table;
  return $resultH;

}?>
