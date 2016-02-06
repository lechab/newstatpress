<?php

/**
 * Show statistics in dashboard
 *
 *******************************/
function nsp_BuildDashboardWidget() {
  global $newstatpress_dir;

  $api_key=get_option('newstatpress_apikey');
  $newstatpress_url=PluginUrl();
  $url=$newstatpress_url."/includes/api/external.php";

  wp_register_script('wp_ajax_nsp_js_dashbord', plugins_url('./js/nsp_dashboard.js', __FILE__), array('jquery'));
  wp_enqueue_script('jquery');
  wp_enqueue_script('wp_ajax_nsp_js_dashbord');
  wp_localize_script( 'wp_ajax_nsp_js_dashbord', 'ExtData', array(
    'Url' => $url,
    'Key' => md5(gmdate('m-d-y H i').$api_key)
  ));


  ///nsp_MakeOverview('dashboard');
  /*
    echo "<script type=\"text/javascript\">
           $.post(\"$url\", {
             VAR: \"dashboard\",
             KEY: \"".md5(gmdate('m-d-y H i').$api_key)."\",
             PAR: \"\",
             TYP: \"HTML\"
           },
           function(data,status){
             $( \"#nsp_loader-dashboard\").hide();
             $( \"#nsp_result-dashboard\" ).html( data );
           }, \"html\");
         </script>";
         */
    echo "<div id=\"nsp_result-dashboard\"><img id=\"nsp_loader-dashboard\" src=\"$newstatpress_url/images/ajax-loader.gif\"></div>";
  ?>
  <ul class='nsp_dashboard'>
    <li>
      <a href='admin.php?page=nsp_details'><?php _e('Details','newstatpress')?></a> |
    </li>
    <li>
      <a href='admin.php?page=nsp_visits'><?php _e('Visits','newstatpress')?></a> |
    </li>
    <li>
      <a href='admin.php?page=nsp_options'><?php _e('Options','newstatpress')?>
      </li>
  </ul>
  <?php
}

// Create the function use in the action hook
function nsp_AddDashBoardWidget() {

  global $wp_meta_boxes;
  $title=__('NewStatPress Overview','newstatpress');

  //Add the dashboard widget if user option is 'yes'
  if (get_option('newstatpress_dashboard')=='checked')
    wp_add_dashboard_widget('dashboard_NewsStatPress_overview', $title, 'nsp_BuildDashboardWidget');
  else unset($wp_meta_boxes['dashboard']['side']['core']['wp_dashboard_setup']);

}
?>
