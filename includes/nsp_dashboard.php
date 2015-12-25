<?php

echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js\"></script>";

/**
 * Show statistics in dashboard
 *
 *******************************/
function nsp_BuildDashboardWidget() {
  global $newstatpress_dir;
  
  $api_key=get_option('newstatpress_apikey');
  $newstatpress_url=PluginUrl();
  $url=$newstatpress_url."/includes/api/external.php";


  ///nsp_MakeOverview('dashboard');
    echo "<script type=\"text/javascript\">
           $.post(\"$url\", {
             VAR: \"overview\",
             KEY: \"".md5(gmdate('m-d-y H i').$api_key)."\",
             PAR: \"dashboard\",
             TYP: \"HTML\"
           }, 
           function(data,status){
             $( \"#loader-dashboard\").hide();
             $( \"#result-dashboard\" ).html( data );
           }, \"html\");
         </script>"; 
    echo "<div id=\"result-dashboard\"><img id=\"loader-dashboard\" src=\"$newstatpress_url/images/ajax-loader.gif\"></div>";
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
