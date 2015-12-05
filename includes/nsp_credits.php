<?php

/**
 * Display the page with credits (contributors, translators, donors)
 */
function nsp_DisplayCreditsPage() {

  global $pagenow;

  $page='nsp_credits';

  $support_pluginpage="<a href='https://wordpress.org/support/plugin/newstatpress' target='_blank'>".__('support page','newstatpress')."</a>";

  $author_linkpage="<a href='http://newstatpress.altervista.org/?page_id=2' target='_blank'>".__('the author','newstatpress')."</a>";

  $credits_introduction=sprintf(__('If you have found this plugin usefull and you like it, you can support the development by reporting bugs on the %s or  by adding/updating translation by contacting directly %s. As this plugin is maintained only on free time, you can also make a donation by clicking on the button to support the work.','newstatpress'), $support_pluginpage, $author_linkpage);

  $CreditsPage_tabs = array( 'development' => __('Development','newstatpress'),
                             'translation' => __('Translation','newstatpress'),
                             'donation' => __('Donation','newstatpress')
  );

  $contributors = array(
    array('Stefano Tognon', 'NewStatPress developer'),
    array('cHab', 'NewStatPress collaborator'),
    array('Daniele Lippi', 'Original StatPress developer'),
    array('Sisko', 'Open link in new tab/window<br>New displays of data for spy function<br>'),
    array('from wp_slimstat', 'Add option for not track given IPs<br /> Add option for not track given permalinks'),
    array('Ladislav', 'Let Search function to works again'),
    array('from statpress-visitors', 'Add new OS (+44), browsers (+52) and spiders (+71)<br /> Add in the option the ability to update in a range of date<br /> New spy and bot'),
    array('Maurice Cramer','Add dashboard widget<br /> Fix total since in overwiew<br /> Fix missing browser image and IE aligment failure in spy section<br /> Fix nation image display in spy'),
    array('Ruud van der Veen', 'Add tab delimiter for exporting data'),
    array('kjmtsh', 'Many fixes about empty query result and obsolete functions'),
    array('Adrián M. F.', 'Find a XSS and a SQL injection'),
    array('White Fir Design', 'Find a SQL injection'),
    array('Gwss', 'Fix domain.dat manages inside nsp_visits.php'),
  );

  $translators = array(
    array('Stefano Tognon', 'Italian Update'),
    array('cHab', 'French Update'),
    array('shilom', 'French Update'),
    array('Alphonse PHILIPPE', 'French Update'),
    array('Vincent G.', 'Lithuanian Addition'),
    array('Christopher Meng', 'Simplified Chinese Addition'),
    array('godOFslaves', 'Russian Update'),
    array('Branco', 'Slovak Addition'),
    array('Peter Bago', 'Hungarian Addition'),
    array('Boulis Antoniou', 'Greek Addition'),
    array('Michael Yunat', 'Ukranian Addition'),
    array('Pawel Dworniak', 'Polish Update'),
    array('Jiri Borovy', 'Czech Update')
  );

  $donators = array(
    array('Sergio L.', '08/12/2014 <br /> 29/12/2013 <br /> 14/09/2013 <br /> 01/09/2013 <br />03/08/2013'),
    array('Ottavio F.', '14/10/2013'),
    array('Hubert H.', '01/08/2013'),
    array('Fleisher D.', '12/02/2015')
  );

?>

  <div class='wrap'>
    <h2>
      <?php _e('Credits','newstatpress'); ?>
    </h2>
  <table>
    <tr>
      <td>
        <?php echo $credits_introduction; ?>
      </td>
      <td class='don'>
        <form  method='post' target='_blank' action='https://www.paypal.com/cgi-bin/webscr'>
          <input type='hidden' value='_s-xclick' name='cmd'></input>
          <input type='hidden' value='F5S5PF4QBWU7E' name='hosted_button_id'></input>
          <input class='button button-primary perso' type=submit value=' <?php _e('Make a donation','newstatpress'); ?>'>
        </form>
      </td>
    </tr>
  </table>
  
  <?php
    if ( $pagenow == 'admin.php' && $_GET['page'] == $page ){
  ?>

  <div id="usual1" class="usual">
    <ul>
    <?php
      foreach( $CreditsPage_tabs as $tab => $name ) {
          echo "<li><a href='#$tab'>$name</a></li>";
      }
    ?>
    </ul>

    <!-- tab 'development' -->
    <div id='development'>
    <table class='credit'>
      <thead>
        <tr>
          <th class='cell-l'>
            <?php _e('Contributor','newstatpress'); ?>
          </th>
          <th class='cell-r'>
            <?php _e('Description','newstatpress'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach($contributors as $contributors) {
          echo "<tr>\n<td class='cell-l'>$contributors[0]</td>\n<td class='cell-r'>$contributors[1]</td>\n</tr>\n";
        };
        ?>
      </tbody>
    </table>
    </div>

    <!-- tab 'translation' -->
    <div id='translation'>
    <table class='credit'>
      <thead>
        <tr>
          <th class='cell-l'>
            <?php _e('Contributor','newstatpress'); ?>
          </th>
          <th class='cell-r'>
            <?php _e('Language','newstatpress'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
          foreach($translators as $contributors) {
          echo "<tr>\n<td class='cell-l'>$contributors[0]</td>\n<td class='cell-r'>$contributors[1]</td>\n</tr>\n";
          };
        ?>
      </tbody>
    </table>
    </div>

    <!-- tab 'donation' -->
    <div id='donation'>
    <table class='credit'>
      <thead>
        <tr>
          <th class='cell-l'>
            <?php _e('Contributor','newstatpress'); ?>
          </th>
          <th class='cell-r'>
            <?php _e('Date','newstatpress'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
          foreach($donators as $contributors) {
          echo "<tr>\n<td class='cell-l'>$contributors[0]</td>\n<td class='cell-r'>$contributors[1]</td>\n</tr>\n";
          };
        ?>
      </tbody>
    </table>
    </div>
  </div>

  <script type="text/javascript">
    jQuery("#usual1 ul").idTabs(development);
  </script>

<?php
    echo "<table class='credit-footer'>\n<tr>\n<td>"; _e('Plugin homepage','newstatpress');
    echo ": <a target='_blank' href='http://newstatpress.altervista.org'>Newstatpress</a></td></tr>";
    echo "<tr>\n<td>"; _e('RSS news','newstatpress');
    echo ": <a target='_blank' href='http://newstatpress.altervista.org/?feed=rss2'>"; _e('News','newstatpress'); echo "</a></td></tr>";
    echo "</tr></table>";
    echo "</table>";
  }
}

?>
