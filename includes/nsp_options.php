<?php

function getmicrotime()
{
    // découpe le tableau de microsecondes selon les espaces
    list($usec, $sec) = explode(" ",microtime());
    // replace dans l'ordre
    return ((float)$usec + (float)$sec);
}

/** *@desc Affiche le temps écoulé (en microsecondes) depuis la dernière étape.
* L'argument $nom_etape permet de spécifier ce qui est mesuré (ex. "page de stats" ou "requête numéro 7") */
function benchmark ($nom_etape)
{
    global $etape_prec;
    $temps_ecoule = ($etape_prec) ? round((getmicrotime() - $etape_prec)*1000) : 0;
    $retour = '<p class="alerte">' . $nom_etape . ' : ' . $temps_ecoule . 'ms</p>';
    $etape_prec = getmicrotime();
    return $retour;
}
/**
 * Filter the given value for preventing XSS attacks
 *
 * @param _value the value to filter
 * @return filtered value
 */
function nsp_FilterForXss($_value){
  $_value=trim($_value);

  // Avoid XSS attacks
  $clean_value = preg_replace('/[^a-zA-Z0-9\,\.\/\ \-\_\?=&;]/', '', $_value);
  if (strlen($_value)==0) {
    return array();
  }
  else {
    $array_values = explode(',',$clean_value);
    array_walk($array_values, 'nsp_TrimValue');
    return $array_values;
  }
}

/**
 * Trim the given string
 */
function nsp_TrimValue(&$value) {
  $value = trim($value);
}



/**
 * Print the options
 *
 * @param option_title the title for option
 * @param option_var the variable for option
 * @param var variables
 */
function nsp_PrintOption($option_title, $option_var, $var) {

  if($option_var!='newstatpress_menuoverview_cap' AND $option_var!='newstatpress_menudetails_cap' AND $option_var!='newstatpress_menuvisits_cap' AND $option_var!='newstatpress_menusearch_cap' AND $option_var!='newstatpress_menutools_cap' AND $option_var!='newstatpress_menuoptions_cap')
    echo "<td>$option_title</td>\n";
  echo "<td><select name=$option_var>\n";
  if($option_var=='newstatpress_menuoverview_cap' OR $option_var=='newstatpress_menudetails_cap' OR $option_var=='newstatpress_menuvisits_cap' OR $option_var=='newstatpress_menusearch_cap' OR $option_var=='newstatpress_menutools_cap' OR $option_var=='newstatpress_menuoptions_cap') {
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
  echo "</select></td>\n";
}

/**
 * Gets a sorted (according to interval) list of the cron schedules
 */
function get_schedules() {
  $schedules = wp_get_schedules();
  uasort( $schedules, create_function( '$a, $b', 'return $a["interval"] - $b["interval"];' ) );
  return $schedules;
}

function interval( $since ) {
  // array of time period chunks
  $chunks = array(
    array( 60 * 60 * 24 * 365, _n_noop( '%s year', '%s years', 'wp-crontrol' ) ),
    array( 60 * 60 * 24 * 30, _n_noop( '%s month', '%s months', 'wp-crontrol' ) ),
    array( 60 * 60 * 24 * 7, _n_noop( '%s week', '%s weeks', 'wp-crontrol' ) ),
    array( 60 * 60 * 24, _n_noop( '%s day', '%s days', 'wp-crontrol' ) ),
    array( 60 * 60, _n_noop( '%s hour', '%s hours', 'wp-crontrol' ) ),
    array( 60, _n_noop( '%s minute', '%s minutes', 'wp-crontrol' ) ),
    array( 1, _n_noop( '%s second', '%s seconds', 'wp-crontrol' ) ),
  );

  if ( $since <= 0 ) {
    return __( 'now', 'wp-crontrol' );
  }

  // we only want to output two chunks of time here, eg:
  // x years, xx months
  // x days, xx hours
  // so there's only two bits of calculation below:

  // step one: the first chunk
  for ( $i = 0, $j = count( $chunks ); $i < $j; $i++ ) {
    $seconds = $chunks[ $i ][0];
    $name = $chunks[ $i ][1];

    // finding the biggest chunk (if the chunk fits, break)
    if ( ( $count = floor( $since / $seconds ) ) != 0 ) {
      break;
    }
  }

  // set output var
  $output = sprintf( translate_nooped_plural( $name, $count, 'wp-crontrol' ), $count );

  // step two: the second chunk
  if ( $i + 1 < $j ) {
    $seconds2 = $chunks[ $i + 1 ][0];
    $name2 = $chunks[ $i + 1 ][1];

    if ( ( $count2 = floor( ( $since - ( $seconds * $count ) ) / $seconds2 ) ) != 0 ) {
      // add to output var
      $output .= ' ' . sprintf( translate_nooped_plural( $name2, $count2, 'wp-crontrol' ), $count2 );
    }
  }

  return $output;
}

/**
 * Displays a dropdown filled with the possible schedules, including non-repeating.
 * added by cHab
 *
 * @param boolean $current The currently selected schedule
 */

function testr($current){
  $schedules = get_schedules();
  $name=$nsp_option_vars['mail_notification_freq']['name'];
  $valu=$nsp_option_vars['mail_notification_freq']['value'];
    ?>
    <select name="newstatpress_mail_notification_freq" id="mail_freq">
    <option <?php selected( $current, '_oneoff' ); ?> value="_oneoff"><?php esc_html_e( 'Non-repeating', 'wp-crontrol' ); ?></option>
    <?php foreach ( $schedules as $sched_name => $sched_data ) { ?>
      <option <?php selected( $current, $sched_name ); ?> value="<?php echo esc_attr( $sched_name ); ?>"><?php printf('%s (%s)', esc_html( $sched_data['display'] ), esc_html( interval($sched_data['interval'] )));?></option>
    <?php } ?>
    </select>
    <?php
}

/**
 * Print a row of input
 * added by cHab
 *
 * @param option_title the title for options
 * @param nsp_option_vars the variables for options
 * @param input_size the size of input
 * @param input_maxlength the max length of the input
 ****************************************************/
function nsp_PrintRowInput($option_title, $nsp_option_vars, $input_size, $input_maxlength) {
  echo "<tr><td><label for=$nsp_option_vars[name]>$option_title</label></td>\n";
  echo "<td><input class='right' type='text' name=$nsp_option_vars[name] value=";
  echo (get_option($nsp_option_vars['name'])=='') ? $nsp_option_vars['value']:get_option($nsp_option_vars['name']);
  echo " size=$input_size maxlength=$input_maxlength />\n</td></tr>\n";
}

function input_selected($name,$value,$default) {

  $status=get_option($name);
  if ($status=='')
    $status=$default;
  if ($status==$value)
    echo " checked";
}

/**
 * Print a row with given title
 *
 * @param option_title the title for option
 ******************************************/
function nsp_PrintRow($option_title) {
  echo "<tr><td>$option_title</td></tr>\n";
}

// add by chab
/**
 * Print a checked row
 *
 * @param option_title the title for options
 * @param option_var the variables for options
 */
function nsp_PrintChecked($option_title, $option_var) {
  echo "<tr><td><input type=checkbox name='$option_var' value='checked' ".get_option($option_var)."> $option_title</td></tr>\n";
}

// add by chab
/**
 * Print a text area
 *
 * @param option_title the title for options
 * @param option_var the variables for options
 * @param option_description the descriotion for options
 */
function nsp_PrintTextaera($option_title, $option_var, $option_description) {
  echo "<tr><td>\n<p class='ign'><label for=$option_var>$option_title</label></p>\n";
  echo "<p>$option_description</p>\n";
  echo "<p><textarea class='large-text code' cols='40' rows='2' name=$option_var id=$option_var>";
  echo implode(',', get_option($option_var,array()));
  echo "</textarea></p>\n";
  echo "</td></tr>\n";
}

/**
 * Manages the options that the user can choose
 */
function nsp_Options() {
?>

<div class='wrap'><h2><?php _e('NewStatPress Settings','newstatpress'); ?></h2>

    <?php
    if(isset($_POST['saveit']) && $_POST['saveit'] == 'yes') { //option update request by user

      $i=isset($_POST['newstatpress_collectloggeduser']) ? $_POST['newstatpress_collectloggeduser'] : '';
      update_option('newstatpress_collectloggeduser', $i);

      $i=isset($_POST['newstatpress_donotcollectspider']) ? $_POST['newstatpress_donotcollectspider'] : '';
      update_option('newstatpress_donotcollectspider', $i);

      $i=isset($_POST['newstatpress_cryptip']) ? $_POST['newstatpress_cryptip'] : '';
      update_option('newstatpress_cryptip', $i);

      $i=isset($_POST['newstatpress_dashboard']) ? $_POST['newstatpress_dashboard'] : '';
      update_option('newstatpress_dashboard', $i);

      $i=isset($_POST['newstatpress_externalapi']) ? $_POST['newstatpress_externalapi'] : '';
      update_option('newstatpress_externalapi', $i);

      global $nsp_option_vars;

      foreach($nsp_option_vars as $var) {

        if ($var['name'] == 'newstatpress_ignore_ip')
          update_option('newstatpress_ignore_ip', nsp_FilterForXss($_POST['newstatpress_ignore_ip']));
        elseif ($var['name'] == 'newstatpress_ignore_users')
        update_option('newstatpress_ignore_users', nsp_FilterForXss($_POST['newstatpress_ignore_users']));
        elseif ($var['name'] == 'newstatpress_ignore_permalink')
          update_option('newstatpress_ignore_permalink', nsp_FilterForXss($_POST['newstatpress_ignore_permalink']));
        else update_option($var['name'], $_POST[$var['name']]);
      }

      // update database too and print message confirmation
      nsp_BuildPluginSQLTable('update');
      print "<br /><div class='updated'><p>".__('Options saved!','newstatpress')."</p></div>";
    }
    ?>

    <form method=post>

      <div id="usual1" class="usual">
      <ul>
        <?php
        $ToolsPage_tabs = array('general' => __('General','newstatpress'),
                                'data' => __('Filters','newstatpress'),
                                'overview' => __('Overview Menu','newstatpress'),
                                'details' => __('Details Menu','newstatpress'),
                                'visits' => __('Visits Menu','newstatpress'),
                                'database' => __('Database','newstatpress'),
                                'mail' => __('Email Notification','newstatpress'),
                                'api' => __('API','newstatpress')
                                );
        foreach( $ToolsPage_tabs as $tab => $name ) {
            echo "  <li><a href='#$tab'>$name</a></li>\n  ";
        }
        ?>
      </ul>

  <!-- tab 'general' -->
  <div id='general'>
    <table class='form-tableH'>
      <tr>
      <?php
  global $nsp_option_vars;

  // input parameters
  $input_size='2';
  $input_maxlength='3';

  echo "<th scope='row' rowspan='2'>"; _e('Dashboard','newstatpress'); echo "</th></tr>";
  $option_title=__('Enable NewStatPress widget','newstatpress');
  $option_var='newstatpress_dashboard';
  nsp_PrintChecked($option_title,$option_var);

  echo "<tr><th scope='row' rowspan='1'>".__("Minimum capability to display each specific menu",'newstatpress')."(<a href='http://codex.wordpress.org/Roles_and_Capabilities' target='_blank'>".__("more info",'newstatpress')."</a>)</th></tr>";

  $option_title=__('Overview menu','newstatpress');
  echo "<tr><th scope='row' rowspan='1' class='tab'>"; echo $option_title."</th>";
  $option_var='newstatpress_menuoverview_cap';
  $val=get_option($option_var);
  nsp_PrintOption('',$option_var,$val);

  echo "</tr>";
  echo "<tr>";
  $option_title=__('Detail menu','newstatpress');
  echo "<tr><th scope='row' rowspan='2' class='tab'>"; echo $option_title."</th>";
  // $option_var=$nsp_option_vars['menudetails_cap']['name'];
  $option_var='newstatpress_menudetails_cap';
  $val=get_option($option_var);
  nsp_PrintOption('',$option_var,$val);
  echo "</tr>";
  echo "<tr>";

  $option_title=__('Visits menu','newstatpress');
  echo "<tr><th scope='row' rowspan='2' class='tab'>"; echo $option_title."</th>";
  $option_var='newstatpress_menuvisits_cap';
  $val=get_option($option_var);
  nsp_PrintOption('',$option_var,$val);
  echo "</tr>";
  echo "<tr>";

  $option_title=__('Search menu','newstatpress');
  echo "<tr><th scope='row' rowspan='2' class='tab'>"; echo $option_title."</th>";
  $option_var='newstatpress_menusearch_cap';
  $val=get_option($option_var);
  nsp_PrintOption('',$option_var,$val);
  echo "</tr>";
  echo "<tr>\n";

  $option_title=__('Tools menu','newstatpress');
  echo "<tr><th scope='row' rowspan='2' class='tab'>"; echo $option_title."</th>";
  $option_var='newstatpress_menutools_cap';
  $val=get_option($option_var);
  nsp_PrintOption('',$option_var,$val);
  echo "</tr>";
  echo "<tr>\n";

  $option_title=__('Options menu','newstatpress');
  echo "<tr><th scope='row' rowspan='2' class='tab'>"; echo $option_title."</th>";
  $option_var='newstatpress_menuvisits_cap';
  $val=get_option($option_var);
  nsp_PrintOption('',$option_var,$val);
  ?>
    </tr>
    </table>
  </div>

  <!-- tab 'overview' : -->
  <div id='overview'>
    <table class='form-tableH'>
      <tr>
        <th scope='row' rowspan='2'> <?php _e('Visits calculation method','newstatpress'); ?> </th>
      </tr>
      <tr>
      <?php
      $name=$nsp_option_vars['calculation']['name'];
      $valu=$nsp_option_vars['calculation']['value'];

  echo "<td>
      <fieldset>
        <p><input type='radio' name='$name' value=";

              if ((get_option($name)=='') OR (get_option($name)==$valu)) {
                // echo $nsp_option_vars['calculation']['value'];
                echo $valu." checked";
              }
              echo " />
              <label>"; _e('Simple sum of distinct IPs (Classic method)','newstatpress'); echo "</label>
          </p>
          <p>
              <input type='radio' name='$name' value=";

                echo 'sum';
                  if (get_option($name)=='sum') {
                    echo " checked";
                  }

            echo " />
            <label>"; _e('Sum of the distinct IPs of each day (slower than classic method for big database)','newstatpress'); echo "</label>
                    </p>
      </fieldset>
      </td>";
  echo "</tr>";
  echo "<tr>";

  echo "<th scope='row' rowspan='2'>"; _e('Graph','newstatpress'); echo "</th>";
  echo "</tr>";
  echo "<tr>";

  $val=array(array(7,''),array(10,''),array(20,''),array(30,''),array(50,''));
  $option_title=__('Days number in Overview graph','newstatpress');
  $option_var='newstatpress_daysinoverviewgraph';
  nsp_PrintOption($option_title,$option_var,$val);
  echo "</tr>";
  echo "<tr>";

  echo "<th scope='row' rowspan='2'>"; _e('Overview','newstatpress'); echo "</th>";

  $option_title=sprintf(__('Elements in Overview (default %d)','newstatpress'), $nsp_option_vars['overview']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['overview'],$input_size,$input_maxlength);
  ?>
  </table>
  </div>

  <!-- tab 'data' : -->
  <div id='data'>
    <table class='form-tableH'>
      <tr>
  <?php
  // traduction $variable addition for Poedit parsing
  __('Never','newstatpress');
  __('All','newstatpress');
  __('month','newstatpress');
  __('months','newstatpress');
  __('week','newstatpress');
  __('weeks','newstatpress');

  echo "<th scope='row' rowspan='4'>"; _e('Data collection','newstatpress'); echo "</th></tr>";

  $option_title=__('Crypt IP addresses','newstatpress');
  $option_var='newstatpress_cryptip';
  nsp_PrintChecked($option_title,$option_var);
  // echo "<tr></tr>";
  $option_title=__('Collect data about logged users, too.','newstatpress');
  $option_var='newstatpress_collectloggeduser';
  nsp_PrintChecked($option_title,$option_var);
  // echo "<tr></tr>";
  $option_title=__('Do not collect spiders visits','newstatpress');
  $option_var='newstatpress_donotcollectspider';
  nsp_PrintChecked($option_title,$option_var);
  ?>
  </table>
  <table class='form-tableH'>
    <tr>
      <th class='padd' scope='row' rowspan='4'><?php _e('Data purge','newstatpress'); ?></th>
    </tr>
    <tr>
    <?php
  $val=array(array('0', 'Never'),array(1, 'month'),array(3, 'months'),array(6, 'months'),array(12, 'months'));
  $option_title=__('Automatically delete all visits older than','newstatpress');
  $option_var='newstatpress_autodelete';
  nsp_PrintOption($option_title,$option_var,$val);
  echo "</tr>";
  echo "<tr>";

  $option_title=__('Automatically delete only spiders visits older than','newstatpress');
  $option_var='newstatpress_autodelete_spiders';
  nsp_PrintOption($option_title,$option_var,$val);
  ?>
  </tr>
  </table>

  <table class='form-tableH'>
  <tr>
    <th class='padd' scope='row' rowspan='9'>
      <?php _e('Parameters to ignore','newstatpress'); ?>
    </th>
    <?php
  // echo '<tr><td><h3>'; _e('Parameters to ignore','newstatpress'); echo '</h3><td><td></td></tr></table>';
  // echo "<table class='option2'>";

  $option_title=__('Logged users','newstatpress');
  $option_var='newstatpress_ignore_users';
  $option_description=__('Enter a list of users you don\'t want to track, separated by commas, even if collect data about logged users is on','newstatpress');
  nsp_PrintTextaera($option_title,$option_var,$option_description);

  $option_title=__('IP addresses','newstatpress');
  $option_var='newstatpress_ignore_ip';
  $option_description=__('Enter a list of networks you don\'t want to track, separated by commas. Each network <strong>must</strong> be defined using the CIDR notation (i.e. <em>192.168.1.1/24</em>). <br />If the format is incorrect, NewStatPress may not track pageviews properly.','newstatpress');
  nsp_PrintTextaera($option_title,$option_var,$option_description);

  $option_title=__('Pages and posts','newstatpress');
  $option_var='newstatpress_ignore_permalink';
  $option_description=__('Enter a list of permalinks you don\'t want to track, separated by commas. You should omit the domain name from these resources: <em>/about, p=1</em>, etc. <br />NewStatPress will ignore all the pageviews whose permalink <strong>contains</strong> at least one of them.','newstatpress');
  nsp_PrintTextaera($option_title,$option_var,$option_description);
  ?>
  </table>
  </div>

  <!-- tab 'visits' -->
  <div id='visits'>
    <table class='form-tableH'>
      <tr>
        <th scope='row' rowspan='2'>
          <?php _e('Visitors by Spy','newstatpress'); ?>
        </th>
        <?php
        $val=array(array(20,''),array(50,''),array(100,''));
        $option_title=__('number of IP per page','newstatpress');
        $option_var='newstatpress_ip_per_page_newspy';
        nsp_PrintOption($option_title,$option_var,$val);
        ?>
      </tr>
      <tr>
        <?php
        $option_title=__('number of visits for IP','newstatpress');
        $option_var='newstatpress_visits_per_ip_newspy';
        nsp_PrintOption($option_title,$option_var,$val);
        ?>
      </tr>
      <tr>
        <th class='padd' scope='row' colspan='3'></th>
      </tr>
      <tr>
        <th class='padd' scope='row' rowspan='2'>
          <?php _e('Parameters to ignore','newstatpress'); ?>
        </th>
        <?php
          $option_title=__('number of bot per page','newstatpress');
          $option_var='newstatpress_bot_per_page_spybot';
          nsp_PrintOption($option_title,$option_var,$val);
        ?>
      </tr>
      <tr>
        <?php
        $option_title=__('number of bot for IP','newstatpress');
        $option_var='newstatpress_visits_per_bot_spybot';
        nsp_PrintOption($option_title,$option_var,$val);
        ?>
    </table>
  </div>

<!-- tab 'details' -->
<div id='details'>
  <table class='form-tableH'>
    <tr>
      <th class='padd' scope='row' rowspan='14'>
        <?php _e('Element numbers to display in','newstatpress'); ?>
      </th>
  <?php
  $option_title=sprintf(__('Top days (default %d)','newstatpress'), $nsp_option_vars['top_days']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['top_days'],$input_size,$input_maxlength);

  $option_title=sprintf(__('O.S. (default %d)','newstatpress'), $nsp_option_vars['os']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['os'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Browser (default %d)','newstatpress'), $nsp_option_vars['browser']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['browser'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Feed (default %d)','newstatpress'), $nsp_option_vars['feed']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['feed'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Search Engines (default %d)','newstatpress'), $nsp_option_vars['searchengine']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['searchengine'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Top Search Terms (default %d)','newstatpress'), $nsp_option_vars['search']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['search'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Top Referrer (default %d)','newstatpress'), $nsp_option_vars['referrer']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['referrer'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Countries/Languages (default %d)','newstatpress'), $nsp_option_vars['languages']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['languages'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Spiders (default %d)','newstatpress'), $nsp_option_vars['spiders']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['spiders'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Top Pages (default %d)','newstatpress'), $nsp_option_vars['pages']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['pages'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Top Days - Unique visitors (default %d)','newstatpress'), $nsp_option_vars['visitors']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['visitors'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Top Days - Pageviews (default %d)','newstatpress'), $nsp_option_vars['daypages']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['daypages'],$input_size,$input_maxlength);

  $option_title=sprintf(__('Top IPs - Pageviews (default %d)', 'newstatpress'), $nsp_option_vars['ippages']['value']);
  nsp_PrintRowInput($option_title,$nsp_option_vars['ippages'],$input_size,$input_maxlength);
?>
  </table>
</div>

<!-- tab 'database' -->
<div id='database'>
  <h3><?php _e('Database update option','newstatpress'); ?></h3>
  <table>
    <!-- <p class='table-databaseupdate'> -->
      <tr>
        <td>
      <?php
      _e('Select the interval of date from today you want to use for updating your database with new definitions. ','newstatpress');
      _e('Be aware, larger is the interval, longer is the update and bigger are the resources required.','newstatpress');
      // _e('You can choose to not update some fields if you want.','newstatpress')
      ?>
    </td></tr>
    <!-- </p> -->
    <tr>
   <?php
   $val= array(array('', 'All'),array(1, 'week'),array(2, 'weeks'),array(3, 'weeks'),array(1, 'month'),array(2, 'months'),array(3, 'months'),array(6, 'months'),array(9, 'months'),array(12, 'months'));
   $option_title=__('Update data in the given period','newstatpress');
   $option_var='newstatpress_updateint';
   nsp_PrintOption($option_title,$option_var,$val);
   ?>
  </table>
</div>

<!-- **************** -->
<!-- ** tab 'mail' ** -->
<!-- **************** -->
<div id='mail'>
  <table class='form-tableH'>
    <tr>
      <th scope='row' rowspan='2'><?php _e('Statistics notification is','newstatpress'); ?></th>
    </tr>
    <tr>
    <td>
      <fieldset>
      <?php
        $name=$nsp_option_vars['mail_notification']['name'];
        $default=$nsp_option_vars['mail_notification']['value'];
      ?>
      <p>
        <input type='radio' id='dis' name='<?php echo $name ?>' value='disabled'<?php input_selected($name,'disabled',$default);?> /> <label> <?php _e('Disabled','newstatpress'); ?></label>
      </p>
      <p>
        <input type='radio' id='ena' name='<?php echo $name ?>' value='enabled'<?php input_selected($name,'enabled');?> onclick='disable()' /><label> <?php _e('Enabled','newstatpress') ?></label>
      </p>
      <p class='option_list'>
        <label>
          <?php _e('Event schedule','newstatpress')?>&nbsp;:
          <?php
            $name=$nsp_option_vars['mail_notification_freq']['name'];
            testr(get_option($name));
          ?>
        </label>
      </p>
      </fieldset>
      <p class='option_list'>
        <label>
          <?php _e('Time','newstatpress')?>&nbsp;:
      <select name="mail_freq" id="mail_freq">
        <option value="0">- <?php _e('Select','newstatpress')?> -</option> ?>
      <?php
        for ($h = 0; $h <= 23; $h++) {
          for($m = 0; $m <= 45; $m += 15) {
            $value = sprintf('%02d', $h) . 'h' . sprintf('%02d', $m);
            echo '<option value="'. $value.'">'. $value.'</option>\n';
          }
        }
      ?>
      </select>
      </label>
    </p>
    <p class='option_list'>
      <label for='newstatpress_mail_notification_emailaddress'><?php _e('Email address','newstatpress')?>&nbsp;:
      <input class='left' type='text' name='newstatpress_mail_notification_emailaddress' value='<?php
      $name=$nsp_option_vars['mail_notification_address']['name'];
      $email=get_option($name);
      if($email=='') {
        $current_user = wp_get_current_user();
        $email=$current_user->user_email;
      }
      // echo (get_option($nsp_option_vars['name'])=='') ? $nsp_option_vars['value']:get_option($nsp_option_vars['name']);
      echo $email;

      ?>' size=20 maxlength=30 />
      </label>
      <input type=hidden name='newstatpress_mail_notification_info' value=<?php $current_user = wp_get_current_user();
         echo $current_user->display_name;?> />
    </p>
    <!-- <p class="description">Note: Notification will be sent to the email address specified by the user in Settings>General (ie :
    <?php
      //global $current_user;
      //get_currentuserinfo();
      //echo $current_user->user_email;

      // global $current_user;
      //       get_currentuserinfo();
      //
      //       echo 'Username: ' . $current_user->user_login . "\n";
      //         echo 'User email: ' . $current_user->user_email . "\n";
      //       echo 'User level: ' . $current_user->user_level . "\n";
      //       echo 'User first name: ' . $current_user->user_firstname . "\n";
      //       echo 'User last name: ' . $current_user->user_lastname . "\n";
      //       echo 'User display name: ' . $current_user->display_name . "\n";
      //       echo 'User ID: ' . $current_user->ID . "\n";

          ?>
    ).</p> -->
  </td>
  </tr>
  </table>
</div>

<!-- tab 'API' -->
<div id='api'>
  <table class='form-tableH'>

<?php
$option_title=__('Enable External API','newstatpress');
$option_var='newstatpress_externalapi';
nsp_PrintChecked($option_title,$option_var);

$option_title=__('API key','newstatpress');
$option_var='newstatpress_apikey';
$option_description=__('The external access API is build to let you to use the collected data from your Newstatpress plugin  in an other web server application (for example you can show data relative to your Wordpress blog, inside a Drupal site that run in another server). This key allows Newstatpress to recognize that you and only you want the data and not the not authorized people. Let the input form blank means that you allow everyone to get data without authorization if external API is activated. The API should be activated with the flag box. Please be aware that the external API will be also used by Newstatpress itself when are processed AJAX calls for speedup page rendering of queried data, so you will need to choose an key and activate it.

<br/><br/>To retrieve data from Newstatpress plugin, you can generate automatically or set manually a private key for the external API (used for example from Multi-Newstatpress software) : only alphanumeric characters are allowed (A-Z, a-z, 0-9), length should be between 64 and 128 characters.','newstatpress');

echo "<tr><td>\n<p class='ign'><label for=$option_var>$option_title</label></p>\n";
echo "<p>$option_description</p>\n";
echo "<div class='justified'>";

echo "<p><textarea class='large-text code api' minlength='64' maxlength='128' cols='50' rows='3' name=$option_var id=$option_var>";
echo get_option($option_var);
?>
</textarea>
</p>
</div>
  <tr><td>
    <div class='justified'>
      <div class='button' type='button' onClick='myFunction()'>
        <?php _e('Generate new API key','newstatpress'); ?>
      </div>
    </div>
  </td></tr>
</table></div>
    <p class='submit'>
      <input class='button button-primary' type=submit value="<?php _e('Save options','newstatpress'); ?>">
      <input type=hidden name=saveit value=yes>
      <input type=hidden name=page value=newstatpress><input type=hidden name=newstatpress_action value=options>
    </p>
  </div>
</form>

  <script type="text/javascript">
  jQuery("#usual1 ul").idTabs(general);

  function validateCode() {
    // var TCode = document.getElementById('TCode').value;
    var obj = document.getElementById("newstatpress_apikey").value;

    if( /[^a-zA-Z0-9]/.test( obj ) ) {
       alert('Input is not alphanumeric');
       return false;
    }
    return true;
 }

 function randomString(length, chars) {
     var result = '';
     for (var i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
     return result;
 }

 function myFunction() {
     var obj = document.getElementById("newstatpress_apikey");
     var txt = randomString(128, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
     obj.value = txt;
 }



  </script>

<?php

}
