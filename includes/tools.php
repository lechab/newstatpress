<?php

/****** List of Functions available ******
 *
 * nsp_DisplayToolsPage()
 * iriNewStatPressRemove()
 *****************************************/

function nsp_DisplayToolsPage() {

  global $pagenow;
  $ToolsPage_tabs = array( 'IP2nation' => __('IP2nation','newstatpress'),
                            'update' => __('Update','newstatpress'),
                            'export' => __('Export','newstatpress'),
                            'remove' => __('Remove','newstatpress')
                          );
  $ref='tools-page';
  $default_tab=array_values($ToolsPage_tabs)[0];

  print "<div class='wrap'><h2>".__('Database Tools','newstatpress')."</h2>";

  if ( isset ( $_GET['tab'] ) ) nsp_DisplayTabsNavbarForMenuPage($ToolsPage_tabs,$_GET['tab'],$ref);
  else nsp_DisplayTabsNavbarForMenuPage($ToolsPage_tabs, $default_tab, $ref);

  if ( $pagenow == 'admin.php' && $_GET['page'] == $ref ) {

    if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
    else $tab = $default_tab;

    switch ($tab) {

      case 'IP2nation' :
      // Importation if requested by user
      if (isset($_POST['download']) && $_POST['download'] == 'yes' ) {
        $install_result=iriNewStatPressIP2nationDownload();
      }
      ?>
      <div class='wrap'><h3><?php _e('To import IP2nation database','newstatpress'); ?></h3>

        <?php
        if ($install_result !='') {
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
      iriNewStatPressExport();
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
      iriNewStatPressRemove();
      break;
    }
  }
}


/**
 * Generate HTML for remove menu in Wordpress
 */
function iriNewStatPressRemove() {

  if(isset($_POST['removeit']) && $_POST['removeit'] == 'yes') {
    global $wpdb;
    $table_name = $wpdb->prefix . "statpress";
    $results =$wpdb->query( "DELETE FROM " . $table_name);
    print "<br /><div class='remove'><p>".__('All data removed','newstatpress')."!</p></div>";
  }
  else {
      ?>
        <div class='wrap'><h2><?php _e('Remove NewStatPress database','newstatpress'); ?></h2>
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
