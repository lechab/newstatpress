<?php

function iriNewStatPressCredits() {

  $contributors = [
    ['Stefano Tognon', 'NewStatPress develoup'],
    ['cHab', 'NewStatPress collaborator'],
    ['Daniele Lippi', 'Original StatPress develoup'],
    ['Sisko', 'Open link in new tab/window<br>New displays of data for spy function<br>'],
    ['from wp_slimstat', 'Add option for not track given IPs<br /> Add option for not track given permalinks'],
    ['Ladislav', 'Let Search function to works again'],
    ['from statpress-visitors', 'Add new OS (+44), browsers (+52) and spiders (+71)<br /> Add in the option the ability to update in a range of date<br /> New spy and bot'],
    ['Maurice Cramer','Add dashboard widget<br /> Fix total since in overwiew<br /> Fix missing browser image and IE aligment failure in spy section<br /> Fix nation image display in spy'],
    ['Ruud van der Veen', 'Add tab delimiter for exporting data'],
    ['kjmtsh', 'Many fixes about empty query result and obsolete functions'],
    ['shilom', 'French translation Update'],
    ['Alphonse PHILIPPE ', 'French translation Update'],
    ['Vincent G', 'Lithuanian translation Addition'],
    ['Christopher Meng', 'Add Simplified Chinese translation'],
    ['godOFslaves', 'Russian translation Update'],
    ['Branco', 'Add Slovak translation'],
    ['Peter Bago', 'Add Hungarian translation'],
    ['Boulis Antoniou', 'Add Greek translation'],
    ['Michael Yunat', 'Add Ukranian translation'],
    ['Pawel Dworniak', 'Polish translation Update']
  ];
  echo "<div class='wrap'><h2>"; _e('Credits','newstatpress'); echo "</h2>";
  echo "<br /><table id='credit'>\n";
  echo "<thead>\n<tr><th class='cell-l'>";  _e('Contributor','newstatpress'); echo "</th>\n<th class='cell-r'>"; _e('Description','newstatpress'); echo "</th></tr>\n</thead>\n<tbody>";
  foreach($contributors as list($name, $contribution))
  {
    echo "<tr>\n";
    echo "<td class='cell-l'>$name</td>\n";
    echo "<td class='cell-r'>$contribution</td>\n";
    echo "</tr>\n";
  };
  echo "<tbody></table></div>";

  echo "<br /><div><table>\n";
  echo "<tr>\n<td>"; _e('Plugin homepage','newstatpress'); echo ": <a target='_blank' href='http://newstatpress.altervista.org'>Newstatpress</a></td></tr>";
  echo "<tr>\n<td>"; _e('RSS news','newstatpress'); echo ": </td></tr>";
  echo "<tr><td>Make a donation: </td>";

  echo "</tr></table></div>";

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
        <div class='wrap'><h2><?php _e('Remove','newstatpress'); ?></h2>
        <form method=post>
        <?php
        echo "<strong>";
        _e("Warning: pressing the below button will make all your stored data to be erased!","newstatpress");
        echo "</strong><br />";
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
