<?php
/**
 * Filter the given value for preventing XSS attacks
 *
 * @param _value the value to filter
 * @return filtered value
 */
function iriNewStatPress_filter_for_xss($_value){
  $_value=trim($_value);

  // Avoid XSS attacks
  $clean_value = preg_replace('/[^a-zA-Z0-9\,\.\/\ \-\_\?=&;]/', '', $_value);
  if (strlen($_value)==0){
    return array();
  } else {
      $array_values = explode(',',$clean_value);
      array_walk($array_values, 'iriNewStatPress_trim_value');
      return $array_values;
    }
}

/**
 * Trim the given string
 */
function iriNewStatPress_trim_value(&$value) {
  $value = trim($value);
}


function print_option($option_title,$option_var,$var) {

  echo "<tr>\n<td>$option_title</td>\n";
  echo "<td><select name=$option_var>\n";
  if($option_var=='newstatpress_mincap') {
    $role = get_role('administrator');
    foreach($role->capabilities as $cap => $grant) {
      print "<option ";
      if($var == $cap) {
        print "selected ";
      }
      print ">$cap</option>";
    }
  } else {
    foreach($var as $option) {
      // list($i,$j) = $option;
      echo "<option value=$option[0]";
      if(get_option($option_var)==$option[0]) {
        echo " selected";
      }
      echo ">". $option[0];
      if ($option[1] !=  '') {
        echo " ";
        _e($option[1],'newstatpress');
      }
      echo "</option>\n";
    }
  }
  echo "</select></td></tr>";
}

// add by chab
function print_row_input($option_title,$nsp_option_vars,$input_size,$input_maxlength) {
  echo "<tr><td><label for=$nsp_option_vars[name]>$option_title</label></td>\n";
  echo "<td><input class='right' type='text' name=$nsp_option_vars[name] value=";
  echo (get_option($nsp_option_vars['name'])=='') ? $nsp_option_vars['value']:get_option($nsp_option_vars['name']);
  echo " size=$input_size maxlength=$input_maxlength />\n</td></tr>\n";
}

function print_row($option_title) {
  echo "<tr><td>$option_title</td></tr>\n";
}

// add by chab
function print_checked($option_title,$option_var) {
  echo "<tr><td><input type=checkbox name='$option_var' value='checked' ".get_option($option_var)."> $option_title</td></tr>\n";
}

// add by chab
function print_textaera($option_title,$option_var,$option_description) {
  echo "<tr><td>\n<h4><label for=$option_var>$option_title</label></h4>\n";
  echo "<p>$option_description</p>\n";
  echo "<p><textarea class='large-text code' cols='40' rows='2' name=$option_var id=$option_var>";
  echo implode(',', get_option($option_var,array()));
  echo "</textarea></p>\n";
  echo "</td></tr>\n";
}


/**
 * Generate HTML for option menu in Wordpress
 */
function iriNewStatPressOptions() {

  if(isset($_POST['saveit']) && $_POST['saveit'] == 'yes') { //option update request by user

    $i=isset($_POST['newstatpress_collectloggeduser']) ? $_POST['newstatpress_collectloggeduser'] : '';
    update_option('newstatpress_collectloggeduser', $i);

    $i=isset($_POST['newstatpress_donotcollectspider']) ? $_POST['newstatpress_donotcollectspider'] : '';
    update_option('newstatpress_donotcollectspider', $i);

    $i=isset($_POST['newstatpress_cryptip']) ? $_POST['newstatpress_cryptip'] : '';
    update_option('newstatpress_cryptip', $i);

    $i=isset($_POST['newstatpress_dashboard']) ? $_POST['newstatpress_dashboard'] : '';
    update_option('newstatpress_dashboard', $i);

    global $nsp_option_vars;

    foreach($nsp_option_vars as $var) {

      if ($var['name'] == 'newstatpress_ignore_ip')
        update_option('newstatpress_ignore_ip', iriNewStatPress_filter_for_xss($_POST['newstatpress_ignore_ip']));
      elseif ($var['name'] == 'newstatpress_ignore_users')
      update_option('newstatpress_ignore_users', iriNewStatPress_filter_for_xss($_POST['newstatpress_ignore_users']));
      elseif ($var['name'] == 'newstatpress_ignore_permalink')
        update_option('newstatpress_ignore_permalink', iriNewStatPress_filter_for_xss($_POST['newstatpress_ignore_permalink']));
      else update_option($var['name'], $_POST[$var['name']]);
    }

    // update database too and print message confirmation
    nsp_BuildPluginSQLTable('update');
    print "<br /><div class='updated'><p>".__('Options saved!','newstatpress')."</p></div>";
  }
  ?>
  <div id='settings' class='wrap'><h2><?php _e('NewStatPress Settings','newstatpress'); ?></h2>

    <form method=post>
      <h3 class='r'> <?php _e('General option','newstatpress'); ?></h3>

      <!-- General option -->
      <table class='table-option'>

        <?php

        global $nsp_option_vars;

        // input parameters
        $input_size='2';
        $input_maxlength='3';

        // traduction $variable addition for Poedit parsing
        __('Never','newstatpress');
        __('All','newstatpress');
        __('month','newstatpress');
        __('months','newstatpress');
        __('week','newstatpress');
        __('weeks','newstatpress');

        $option_title=__('Collect data about logged users, too.','newstatpress');
        $option_var='newstatpress_collectloggeduser';
        print_checked($option_title,$option_var);

        $option_title=__('Do not collect spiders visits','newstatpress');
        $option_var='newstatpress_donotcollectspider';
        print_checked($option_title,$option_var);

        $option_title=__('Crypt IP addresses','newstatpress');
        $option_var='newstatpress_cryptip';
        print_checked($option_title,$option_var);

        $option_title=__('Show NewStatPress dashboard widget','newstatpress');
        $option_var='newstatpress_dashboard';
        print_checked($option_title,$option_var);
        echo '<tr><th colspan="2"><hr /><th></tr>';
        $option_title=sprintf(__('Elements in Overview (default %d)','newstatpress'), $nsp_option_vars['overview']['value']);
        print_row_input($option_title,$nsp_option_vars['overview'],$input_size,$input_maxlength);

        $val=array(array(20,''),array(50,''),array(100,''));
        $option_title=__('Visitors by Spy: number of IP per page','newstatpress');
        $option_var='newstatpress_ip_per_page_newspy';
        print_option($option_title,$option_var,$val);

        $option_title=__('Visitors by Spy: number of visits for IP','newstatpress');
        $option_var='newstatpress_visits_per_ip_newspy';
        print_option($option_title,$option_var,$val);

        $option_title=__('Spy Bot: number of bot per page','newstatpress');
        $option_var='newstatpress_bot_per_page_spybot';
        print_option($option_title,$option_var,$val);

        $option_title=__('Spy Bot: number of bot for IP','newstatpress');
        $option_var='newstatpress_visits_per_bot_spybot';
        print_option($option_title,$option_var,$val);

        $val=array(array('', 'Never'),array(1, 'month'),array(3, 'months'),array(6, 'months'),array(12, 'months'));
        $option_title=__('Automatically delete visits older than','newstatpress');
        $option_var='newstatpress_autodelete';
        print_option($option_title,$option_var,$val);

        $option_title=__('Automatically delete only spiders visits older than','newstatpress');
        $option_var='newstatpress_autodelete_spiders';
        print_option($option_title,$option_var,$val);

        $val=array(array(7,''),array(10,''),array(20,''),array(30,''),array(50,''));
        $option_title=__('Days number in Overview graph','newstatpress');
        $option_var='newstatpress_daysinoverviewgraph';
        print_option($option_title,$option_var,$val);

        $option_title=__('Minimum capability to view stats','newstatpress')." (<a href='http://codex.wordpress.org/Roles_and_Capabilities' target='_blank'>".__("more info",'newstatpress')."</a>)";
        $option_var='newstatpress_mincap';
        $val=get_option('newstatpress_mincap');
        print_option($option_title,$option_var,$val);

        ?>
      </table>

      <!-- Parameters to ignore -->
      <h3><?php _e('Parameters to ignore','newstatpress') ?></h3>
      <table class='option2'>
        <?php

        $option_title=__('Logged users to ignore','newstatpress');
        $option_var='newstatpress_ignore_users';
        $option_description=__('Enter a list of users you don\'t want to track, separated by commas, even if collect data about logged users is on','newstatpress');
        print_textaera($option_title,$option_var,$option_description);


        $option_title=__('IP addresses to ignore','newstatpress');
        $option_var='newstatpress_ignore_ip';
        $option_description=__('Enter a list of networks you don\'t want to track, separated by commas. Each network <strong>must</strong> be defined using the CIDR notation (i.e. <em>192.168.1.1/24</em>). <br />If the format is incorrect, NewStatPress may not track pageviews properly.','newstatpress');
        print_textaera($option_title,$option_var,$option_description);

        $option_title=__('Pages and posts to ignore','newstatpress');
        $option_var='newstatpress_ignore_permalink';
        $option_description=__('Enter a list of permalinks you don\'t want to track, separated by commas. You should omit the domain name from these resources: <em>/about, p=1</em>, etc. <br />NewStatPress will ignore all the pageviews whose permalink <strong>contains</strong> at least one of them.','newstatpress');
        print_textaera($option_title,$option_var,$option_description);

        ?>
      </table>

      <!-- Details menu options -->
      <h3><label for="newstatpress_details_options"><?php _e('Details menu options','newstatpress') ?></label></h3>
      <table>
        <?php

        $option_title=sprintf(__('Elements in Top days (default %d)','newstatpress'), $nsp_option_vars['top_days']['value']);
        print_row_input($option_title,$nsp_option_vars['top_days'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in O.S. (default %d)','newstatpress'), $nsp_option_vars['os']['value']);
        print_row_input($option_title,$nsp_option_vars['os'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Browser (default %d)','newstatpress'), $nsp_option_vars['browser']['value']);
        print_row_input($option_title,$nsp_option_vars['browser'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Feed (default %d)','newstatpress'), $nsp_option_vars['feed']['value']);
        print_row_input($option_title,$nsp_option_vars['feed'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Search Engines (default %d)','newstatpress'), $nsp_option_vars['searchengine']['value']);
        print_row_input($option_title,$nsp_option_vars['searchengine'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Search Terms (default %d)','newstatpress'), $nsp_option_vars['search']['value']);
        print_row_input($option_title,$nsp_option_vars['search'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Referrer (default %d)','newstatpress'), $nsp_option_vars['referrer']['value']);
        print_row_input($option_title,$nsp_option_vars['referrer'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Countries/Languages (default %d)','newstatpress'), $nsp_option_vars['languages']['value']);
        print_row_input($option_title,$nsp_option_vars['languages'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Spiders (default %d)','newstatpress'), $nsp_option_vars['spiders']['value']);
        print_row_input($option_title,$nsp_option_vars['spiders'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Pages (default %d)','newstatpress'), $nsp_option_vars['pages']['value']);
        print_row_input($option_title,$nsp_option_vars['pages'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Days - Unique visitors (default %d)','newstatpress'), $nsp_option_vars['visitors']['value']);
        print_row_input($option_title,$nsp_option_vars['visitors'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top Days - Pageviews (default %d)','newstatpress'), $nsp_option_vars['daypages']['value']);
        print_row_input($option_title,$nsp_option_vars['daypages'],$input_size,$input_maxlength);

        $option_title=sprintf(__('Elements in Top IPs - Pageviews (default %d)', 'newstatpress'), $nsp_option_vars['ippages']['value']);
        print_row_input($option_title,$nsp_option_vars['ippages'],$input_size,$input_maxlength);

        ?>
      </table>

      <h3><?php _e('Database update option','newstatpress'); ?></h3>
      <table>
        <p  class='table-databaseupdate'>
          <?php
          _e('Select the interval of date from today you want to use for updating your database with new definitions. ','newstatpress');
          _e('Be aware, larger is the interval, longer is the update and bigger are the resources required.','newstatpress');
          // _e('You can choose to not update some fields if you want.','newstatpress')
          ?>
       </p>

       <?php
       $val= array(array('', 'All'),array(1, 'week'),array(2, 'weeks'),array(3, 'weeks'),array(1, 'month'),array(2, 'months'),array(3, 'months'),array(6, 'months'),array(9, 'months'),array(12, 'months'));
       $option_title=__('Update data in the given period','newstatpress');
       $option_var='newstatpress_updateint';
       print_option($option_title,$option_var,$val);
       ?>

      <tr><td><br><input class='button button-primary' type=submit value="<?php _e('Save options','newstatpress'); ?>"></td></tr>
      </table>
        <input type=hidden name=saveit value=yes>
        <input type=hidden name=page value=newstatpress><input type=hidden name=newstatpress_action value=options>
      </form>
      </div>
      <?php
    }
?>
