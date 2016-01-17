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


  $donators = array(
    array('Sergio L.', '08/12/2014 <br /> 29/12/2013 <br /> 14/09/2013 <br /> 01/09/2013 <br />03/08/2013'),
    array('Ottavio F.', '14/10/2013'),
    array('Hubert H.', '01/08/2013'),
    array('Fleisher D.', '12/02/2015'),
    array('Douglas R.', '09/01/2016')
  );

?>

  <div id="pagecredits" class='wrap'>
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
      <tbody id="addresses"></tbody>
    </table>
    </div>

    <!-- tab 'translation' -->
    <div id='translation'>
    <table class='credit'>
      <thead>
        <tr>
          <th class='cell-l'>
            <?php _e('Language','newstatpress'); ?>
          </th>
          <th class='cell-r'>
            <?php _e('Contributor','newstatpress'); ?>
          </th>
          <th class='cell-r'>
            <?php _e('Status','newstatpress'); ?>
          </th>
        </tr>
      </thead>
      <tbody id="langr"></tbody>
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


    <table class='credit-footer'>
      <tr>
        <td> <?php _e('Plugin homepage','newstatpress'); ?>
          <a target='_blank' href='http://newstatpress.altervista.org'>Newstatpress</a>
        </td>
      </tr>
      <tr>
        <td> <?php _e('RSS news','newstatpress'); ?>
          <a target='_blank' href='http://newstatpress.altervista.org/?feed=rss2'> <?php _e('News','newstatpress'); ?></a>
        </td>
      </tr>
      <!-- </tr> -->
    </table>
    </table>
    <?php
  }
}

?>
