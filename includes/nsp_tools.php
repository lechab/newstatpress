<?php

/****** List of Functions available ******
 *
 * nsp_DisplayToolsPage()
 * nsp_RemovePluginDatabase()
 * nsp_IP2nationDownload()
 * nsp_ExportNow()
 * nsp_Export()
 *****************************************/

function nsp_DisplayToolsPage() {

  global $pagenow;
  $page='nsp_tools';
  $ToolsPage_tabs = array( 'IP2nation' => __('IP2nation','newstatpress'),
                            'update' => __('Update','newstatpress'),
                            'export' => __('Export','newstatpress'),
                            'remove' => __('Remove','newstatpress')
                          );

  $default_tab=array_values($ToolsPage_tabs)[0];

  print "<div class='wrap'><h2>".__('Database Tools','newstatpress')."</h2>";

  if ( isset ( $_GET['tab'] ) ) nsp_DisplayTabsNavbarForMenuPage($ToolsPage_tabs,$_GET['tab'],$page);
  else nsp_DisplayTabsNavbarForMenuPage($ToolsPage_tabs, $default_tab, $page);

  if ( $pagenow == 'admin.php' && $_GET['page'] == $page ) {

    if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
    else $tab = $default_tab;

    switch ($tab) {

      case 'IP2nation' :
      // Importation if requested by user
      if (isset($_POST['download']) && $_POST['download'] == 'yes' ) {
        $install_result=nsp_IP2nationDownload();
      }
      ?>
      <div class='wrap'><h3><?php _e('To import IP2nation database','newstatpress'); ?></h3>

        <?php
        if ( isset($install_result) AND $install_result !='') {
          print "<br /><div class='updated'><p>".__($install_result,'newstatpress')."</p></div>";
        }

        $file_ip2nation= WP_PLUGIN_DIR . '/' .dirname(plugin_basename(__FILE__)) . '/includes/ip2nation.sql';
        if (file_exists($file_ip2nation)) {
          $i=sprintf(__('Last version installed: %s','newstatpress'), date('d/m/Y', filemtime($file_ip2nation)));
          echo $i.'<br /><br />';
          _e('To update the IP2nation database, just click on the button bellow.','newstatpress');
          $button_name='Update';
        }
        else {
          _e('Last version installed: none ','newstatpress');
          echo '<br /><br />';
          _e('To download and to install the IP2nation database, just click on the button bellow.','newstatpress');
          $button_name='Download';
        }
        ?>
        <br /><br />
        <form method=post>
          <input type=hidden name=page value=newstatpress>
          <input type=hidden name=download value=yes>
          <input type=hidden name=newstatpress_action value=ip2nation>
          <button class='button button-primary' type=submit><?php _e($button_name,'newstatpress'); ?></button>
        </form>

      </div><?php
      break;

      case 'export' :
      nsp_Export();
      break;

      case 'update' :
      // database update if requested by user
      if (isset($_POST['update']) && $_POST['update'] == 'yes' ) {
        iriNewStatPressUpdate();
        die;
      }
      ?>
      <div class='wrap'><h3><?php _e('Database update','newstatpress'); ?></h3>
      <?php
      _e('To update the newstatpress database, just click on the button bellow.','newstatpress');
      ?>
      <br /><br />
      <form method=post>
        <input type=hidden name=page value=newstatpress>
        <input type=hidden name=update value=yes>
        <input type=hidden name=newstatpress_action value=update>
        <button class='button button-primary' type=submit><?php _e('Update','newstatpress'); ?></button>
      </form>
    </div><?php
      break;

      case 'remove' :
      nsp_RemovePluginDatabase();
      break;
    }
  }
}


// add by chab
function nsp_IP2nationDownload() {

    //Request to make http request with WP functions
    if( !class_exists( 'WP_Http' ) ) {
      include_once( ABSPATH . WPINC. '/class-http.php' );
    }

    // Definition $var
    $timeout=300;
    $db_file_url = 'http://www.ip2nation.com/ip2nation.zip';
    $upload_dir = wp_upload_dir();
    $temp_zip_file = $upload_dir['basedir'] . '/ip2nation.zip';

    //delete old file if exists
    unlink( $temp_zip_file );

    $result = wp_remote_get ($db_file_url, array( 'timeout' => $timeout ));

    //Writing of the ZIP db_file
    if ( !is_wp_error( $result ) ) {
      //Headers error check : 404
      if ( 200 != wp_remote_retrieve_response_code( $result ) ){
        $install_status = new WP_Error( 'http_404', trim( wp_remote_retrieve_response_message( $result ) ) );
      }

      // Save file to temp directory
      // ******To add a md5 routine : to check the integrity of the file
      $content = wp_remote_retrieve_body($result);
      $zip_size = file_put_contents ($temp_zip_file, $content);
      if (!$zip_size) { // writing error
        $install_status=__('Failure to save content locally, please try to re-install.','newstatpress');
      }
    }
    else { // WP_error
      $error_message = $result->get_error_message();
      echo '<div id="message" class="error"><p>' . $error_message . '</p></div>';
    }

    // require PclZip if not loaded
    if(! class_exists('PclZip')) {
      require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
    }

    // Unzip Db Archive
    $archive = new PclZip($temp_zip_file);
    $newstatpress_includes_path = WP_PLUGIN_DIR . '/' .dirname(plugin_basename(__FILE__)) . '/includes';
    if ($archive->extract(PCLZIP_OPT_PATH, $newstatpress_includes_path , PCLZIP_OPT_REMOVE_ALL_PATH) == 0) {
      $install_status=__('Failure to unzip archive, please try to re-install','newstatpress');
    }
    else {
      $install_status=__('Instalation of IP2nation database was successful','newstatpress');
    }

    // Remove Zip file
    unlink( $temp_zip_file );
    return $install_status;
}



function nsp_Export() {
?>
<!--TODO chab, check if the input format is ok  -->
	<div class='wrap'><h3><?php _e('Export stats to text file','newstatpress'); ?> (csv)</h3>
    <p><?php _e('You should define the stats period you want to export:','newstatpress'); ?><p>
	<form method=get>
    <table>
      <tr>
        <td><?php _e('From:','newstatpress'); ?> </td>
        <td><input type=text size=10 maxlength=8 =from placeholder='<?php _e('YYYYMMDD','newstatpress');?>'></td>
      </tr>
      <tr>
        <td><?php _e('To:','newstatpress'); ?> </td>
        <td><input type=text size=10 maxlength=8 name=to placeholder='<?php _e('YYYYMMDD','newstatpress');?>'></td>
      </tr>
    </table>
    <table>
      <tr>
        <td><?php _e('You should choose a fields delimiter to separate the data:','newstatpress'); ?> </td>
        <td><select name=del>
          <option>,</option>
          <option>tab</option>
          <option>;</option>
          <option>|</option></select>
      </tr>
    </table>
    <input class='button button-primary' type=submit value=<?php _e('Export','newstatpress'); ?>>
    <input type=hidden name=page value=newstatpress><input type=hidden name=newstatpress_action value=exportnow>
</form>
	</div>
<?php
}

/**
 * Export the NewStatPress data
 */
function nsp_ExportNow() {
  global $wpdb;
  $table_name = $wpdb->prefix . "statpress";
  $filename=get_bloginfo('title' )."-newstatpress_".$_GET['from']."-".$_GET['to'].".csv";
  header('Content-Description: File Transfer');
  header("Content-Disposition: attachment; filename=$filename");
  header('Content-Type: text/plain charset=' . get_option('blog_charset'), true);
  $qry = $wpdb->get_results(
    "SELECT *
     FROM $table_name
     WHERE
       date>='".(date("Ymd",strtotime(substr($_GET['from'],0,8))))."' AND
       date<='".(date("Ymd",strtotime(substr($_GET['to'],0,8))))."';
    ");
  $del=substr($_GET['del'],0,1);
  if ($del=="t") {
    $del="\t";
  }
  print "date".$del."time".$del."ip".$del."urlrequested".$del."agent".$del."referrer".$del."search".$del."nation".$del."os".$del."browser".$del."searchengine".$del."spider".$del."feed\n";
  foreach ($qry as $rk) {
    print '"'.$rk->date.'"'.$del.'"'.$rk->time.'"'.$del.'"'.$rk->ip.'"'.$del.'"'.$rk->urlrequested.'"'.$del.'"'.$rk->agent.'"'.$del.'"'.$rk->referrer.'"'.$del.'"'.$rk->search.'"'.$del.'"'.$rk->nation.'"'.$del.'"'.$rk->os.'"'.$del.'"'.$rk->browser.'"'.$del.'"'.$rk->searchengine.'"'.$del.'"'.$rk->spider.'"'.$del.'"'.$rk->feed.'"'."\n";
  }
  die();
}

/**
 * Generate HTML for remove menu in Wordpress
 */
function nsp_RemovePluginDatabase() {

  if(isset($_POST['removeit']) && $_POST['removeit'] == 'yes') {
    global $wpdb;
    $table_name = $wpdb->prefix . "statpress";
    $results =$wpdb->query( "DELETE FROM " . $table_name);
    print "<br /><div class='remove'><p>".__('All data removed','newstatpress')."!</p></div>";
  }
  else {
      ?>
        <div class='wrap'><h3><?php _e('Remove NewStatPress database','newstatpress'); ?></h3>
          <br />
          <div class='error'><p>
        <?php _e('Warning: pressing the below button will make all your stored data to be erased!',"newstatpress"); ?>
      </p></div>
        <form method=post>
        <?php
        _e("It is added for the people that did not want to use the plugin anymore and so they want to remove the stored data.","newstatpress");
        echo "<br />";
        _e("If you are in doubt about this function, don't use it.","newstatpress");
        ?>
        <br /><br />
        <input class='button button-primary' type=submit value="<?php _e('Remove','newstatpress'); ?>" onclick="return confirm('<?php _e('Are you sure?','newstatpress'); ?>');" >
        <input type=hidden name=removeit value=yes>
        </form>
        </div>
      <?php
  }
 }

 ?>
