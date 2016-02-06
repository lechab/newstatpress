<?php

/**
 * Show overwiew
 *
 *****************/
function nsp_NewStatPressMain() {
  global $wpdb;
  $table_name = nsp_TABLENAME;

  //nsp_NoticeNew(1);
  nsp_MakeOverview('main');

  $_newstatpress_url=PluginUrl();

  // determine the structure to use for URL
  $permalink_structure = get_option('permalink_structure');
  if ($permalink_structure=='') $extra="/?";
  else $extra="/";

  $querylimit=((get_option('newstatpress_el_overview')=='') ? 10:get_option('newstatpress_el_overview'));

  $lasthits = $wpdb->get_results("
    SELECT *
    FROM $table_name
    WHERE (os<>'' OR feed<>'')
    ORDER bY id DESC LIMIT $querylimit
  ");
  $lastsearchterms = $wpdb->get_results("
    SELECT date,time,referrer,urlrequested,search,searchengine
    FROM $table_name
    WHERE search<>''
    ORDER BY id DESC LIMIT $querylimit
  ");

  $lastreferrers = $wpdb->get_results("
    SELECT date,time,referrer,urlrequested
    FROM $table_name
    WHERE
     ((referrer NOT LIKE '".get_option('home')."%') AND
      (referrer <>'') AND
      (searchengine='')
     ) ORDER BY id DESC LIMIT $querylimit
  ");

  ?>
  <!-- Last hits table -->
  <div class='wrap'>
    <h2> <?php echo  __('Last hits',nsp_TEXTDOMAIN); ?></h2>
    <table class='widefat nsp'>
      <thead>
        <tr>
          <th scope='col'><?php _e('Date',nsp_TEXTDOMAIN); ?></th>
          <th scope='col'><?php _e('Time',nsp_TEXTDOMAIN); ?></th>
          <th scope='col'><?php _e('IP',nsp_TEXTDOMAIN); ?></th>
          <th scope='col'><?php echo __('Country',nsp_TEXTDOMAIN).'/'.__('Language',nsp_TEXTDOMAIN); ?></th>
          <th scope='col'><?php _e('Page',nsp_TEXTDOMAIN); ?></th>
          <th scope='col'><?php _e('Feed',nsp_TEXTDOMAIN); ?></th>
          <th></th>
          <th scope='col' style='width:120px;'><?php _e('OS',nsp_TEXTDOMAIN); ?></th>
          <th></th>
          <th scope='col' style='width:120px;'><?php _e('Browser',nsp_TEXTDOMAIN); ?></th>
        </tr>
      </thead>
      <tbody id='the-list'>
      <?php
      foreach ($lasthits as $fivesdraft) {
        print "<tr>";
        print "<td>". nsp_hdate($fivesdraft->date) ."</td>";
        print "<td>". $fivesdraft->time ."</td>";
        print "<td>". $fivesdraft->ip ."</td>";
        print "<td>". $fivesdraft->nation ."</td>";
        print "<td>". nsp_Abbreviate(nsp_DecodeURL($fivesdraft->urlrequested),30) ."</td>";
        print "<td>". $fivesdraft->feed . "</td>";

        if($fivesdraft->os != '') {
          $img=$_newstatpress_url."/images/os/".str_replace(" ","_",strtolower($fivesdraft->os)).".png";
          print "<td class='browser'><img class='img_browser' SRC='$img'></td>";
        }
        else {
            print "<td></td>";
        }
        print "<td>".$fivesdraft->os . "</td>";

        if($fivesdraft->browser != '') {
          $img=str_replace(" ","",strtolower($fivesdraft->browser)).".png";
          print "<td><img class='img_browser' SRC='".$_newstatpress_url."/images/browsers/$img'></td>";
        }
        else {
           print "<td></td>";
        }
        print "<td>".$fivesdraft->browser."</td></tr>\n";
        // print "</tr>";
      }
      ?>
      </tbody>
    </table>
  </div>

  <!-- Last Search terms table -->
  <div class='wrap'>
    <h2><?php _e('Last search terms',nsp_TEXTDOMAIN) ?></h2>
    <table class='widefat nsp'>
      <thead>
        <tr>
          <th scope='col'><?php _e('Date',nsp_TEXTDOMAIN) ?></th>
          <th scope='col'><?php _e('Time',nsp_TEXTDOMAIN) ?></th>
          <th scope='col'><?php _e('Terms',nsp_TEXTDOMAIN) ?></th>
          <th scope='col'><?php _e('Engine',nsp_TEXTDOMAIN) ?></th>
          <th scope='col'><?php _e('Result',nsp_TEXTDOMAIN) ?></th>
        </tr>
      </thead>
      <tbody id='the-list'>
      <?php
        foreach ($lastsearchterms as $rk) {
          print "<tr>
                  <td>".nsp_hdate($rk->date)."</td><td>".$rk->time."</td>
                  <td><a href='".$rk->referrer."' target='_blank'>".$rk->search."</a></td>
                  <td>".$rk->searchengine."</td><td><a href='".get_bloginfo('url').$extra.$rk->urlrequested."' target='_blank'>". __('page viewed',nsp_TEXTDOMAIN). "</a></td>
                </tr>\n";
        }
      ?>
      </tbody>
    </table>
  </div>

  <!-- Last Referrers table -->
  <div class='wrap'>
    <h2><?php _e('Last referrers',nsp_TEXTDOMAIN) ?></h2>
    <table class='widefat nsp'>
      <thead>
        <tr>
          <th scope='col'><?php _e('Date',nsp_TEXTDOMAIN) ?></th>
          <th scope='col'><?php _e('Time',nsp_TEXTDOMAIN) ?></th>
          <th scope='col'><?php _e('URL',nsp_TEXTDOMAIN) ?></th>
          <th scope='col'><?php _e('Result',nsp_TEXTDOMAIN) ?></th>
        </tr>
      </thead>
      <tbody id='the-list'>
      <?php
        foreach ($lastreferrers as $rk) {
          print "<tr><td>".nsp_hdate($rk->date)."</td><td>".$rk->time."</td><td><a href='".$rk->referrer."' target='_blank'>".nsp_Abbreviate($rk->referrer,80)."</a></td><td><a href='".get_bloginfo('url').$extra.$rk->urlrequested."'  target='_blank'>". __('page viewed',nsp_TEXTDOMAIN). "</a></td></tr>\n";
        }
      ?>
      </tbody>
    </table>
  </div>

<?php
  # Last Agents
  print "<div class='wrap'><h2>".__('Last agents',nsp_TEXTDOMAIN)."</h2><table class='widefat nsp'><thead><tr><th scope='col'>".__('Agent',nsp_TEXTDOMAIN)."</th><th scope='col'></th><th scope='col' style='width:120px;'>". __('OS',nsp_TEXTDOMAIN). "</th><th scope='col'></th><th scope='col' style='width:120px;'>". __('Browser',nsp_TEXTDOMAIN).'/'. __('Spider',nsp_TEXTDOMAIN). "</th></tr></thead>";
  print "<tbody id='the-list'>";
  $qry = $wpdb->get_results("
    SELECT agent,os,browser,spider
    FROM $table_name
    GROUP BY agent,os,browser,spider
    ORDER BY id DESC LIMIT $querylimit
  ");
  foreach ($qry as $rk) {
    print "<tr><td>".$rk->agent."</td>";
    if($rk->os != '') {
      $img=str_replace(" ","_",strtolower($rk->os)).".png";
      print "<td><IMG class='img_browser' SRC='".$_newstatpress_url."/images/os/$img'> </td>";
    } else {
        print "<td></td>";
      }
    print "<td>". $rk->os . "</td>";
    if($rk->browser != '') {
      $img=str_replace(" ","",strtolower($rk->browser)).".png";
      print "<td><IMG class='img_browser' SRC='".$_newstatpress_url."/images/browsers/$img'></td>";
    } else {
        print "<td></td>";
      }
    print "<td>".$rk->browser." ".$rk->spider."</td></tr>\n";
  }
  print "</table></div>";


  # Last pages
  print "<div class='wrap'><h2>".__('Last pages',nsp_TEXTDOMAIN)."</h2><table class='widefat nsp'><thead><tr><th scope='col'>".__('Date',nsp_TEXTDOMAIN)."</th><th scope='col'>".__('Time',nsp_TEXTDOMAIN)."</th><th scope='col'>".__('Page',nsp_TEXTDOMAIN)."</th><th scope='col' style='width:17px;'></th><th scope='col' style='width:120px;'>".__('OS',nsp_TEXTDOMAIN)."</th><th style='width:17px;'></th><th scope='col' style='width:120px;'>".__('Browser',nsp_TEXTDOMAIN)."</th></tr></thead>";
  print "<tbody id='the-list'>";
  $qry = $wpdb->get_results("
    SELECT date,time,urlrequested,os,browser,spider
    FROM $table_name
    WHERE (spider='' AND feed='')
    ORDER BY id DESC LIMIT $querylimit
  ");
  foreach ($qry as $rk) {
    print "<tr><td>".nsp_hdate($rk->date)."</td><td>".$rk->time."</td><td>".nsp_Abbreviate(nsp_DecodeURL($rk->urlrequested),60)."</td>";
    if($rk->os != '') {
      $img=str_replace(" ","_",strtolower($rk->os)).".png";
      print "<td><IMG class='img_browser' SRC='".$_newstatpress_url."/images/os/$img'> </td>";
    } else {
        print "<td></td>";
      }
    print "<td>". $rk->os . "</td>";
    if($rk->browser != '') {
      $img=str_replace(" ","",strtolower($rk->browser)).".png";
      print "<td><IMG class='img_browser' SRC='".$_newstatpress_url."/images/browsers/$img'></td>";
    } else {
        print "<td></td>";
      }
    print "<td>".$rk->browser." ".$rk->spider."</td></tr>\n";
  }
  print "</table></div>";


  # Last Spiders
  print "<div class='wrap'><h2>".__('Last spiders',nsp_TEXTDOMAIN)."</h2><table class='widefat nsp'><thead><tr><th scope='col'>".__('Date',nsp_TEXTDOMAIN)."</th><th scope='col'>".__('Time',nsp_TEXTDOMAIN)."</th><th scope='col'></th><th scope='col'>".__('Spider',nsp_TEXTDOMAIN)."</th><th scope='col'>".__('Agent',nsp_TEXTDOMAIN)."</th></tr></thead>";
  print "<tbody id='the-list'>";
  $qry = $wpdb->get_results("
    SELECT date,time,agent,os,browser,spider
    FROM $table_name
    WHERE (spider<>'')
    ORDER BY id DESC LIMIT $querylimit
  ");
  foreach ($qry as $rk) {
    print "<tr><td>".nsp_hdate($rk->date)."</td><td>".$rk->time."</td>";
    if($rk->spider != '') {
      $img=str_replace(" ","_",strtolower($rk->spider)).".png";
      print "<td><IMG class='img_os' SRC='".$_newstatpress_url."/images/spider/$img'> </td>";
    } else print "<td></td>";
    print "<td>".$rk->spider."</td><td> ".$rk->agent."</td></tr>\n";
  }
  print "</table></div>";

  print "<br />";
  print "&nbsp;<i>StatPress table size: <b>".nsp_TableSize(nsp_TABLENAME)."</b></i><br />";
  print "&nbsp;<i>StatPress current time: <b>".current_time('mysql')."</b></i><br />";
  print "&nbsp;<i>RSS2 url: <b>".get_bloginfo('rss2_url').' ('.nsp_ExtractFeedFromUrl(get_bloginfo('rss2_url')).")</b></i><br />";
  nsp_load_time();
}

/**
 * Abbreviate the given string to a fixed length
 *
 * @param s the string
 * @param c the number of chars
 * @return the abbreviate string
 ***********************************************/
function nsp_Abbreviate($s,$c) {
  $s=__($s);
  $res=""; if(strlen($s)>$c) { $res="..."; }
  return substr($s,0,$c).$res;
}


?>
