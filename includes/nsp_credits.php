<?php

/**
 * Display the page with credits (contributors, translators, donors)
 * added by cHab
 */
function nsp_DisplayCreditsPage() {

  global $pagenow;
  global $support_pluginpage;
  global $author_linkpage;
  global $credits_introduction;

  $page='nsp_credits';

  $CreditsPage_tabs = array( 'development' => __('Development','newstatpress'),
                             'ressources' => __('Ressources','newstatpress'),
                             'translation' => __('Translation','newstatpress'),
                             'donation' => __('Donation','newstatpress')
  );

  $credits_introduction=__('If you have found this plugin usefull and you like it, thank you to take a moment to rate it.',nsp_TEXTDOMAIN);
  $credits_introduction.=' '.sprintf(__('You can help to the plugin development by reporting bugs on the %s or by adding/updating translation by contacting directly %s.',nsp_TEXTDOMAIN), $support_pluginpage, $author_linkpage);
  $credits_introduction.='<br />';
  $credits_introduction.=__('NewStatPress is provided for free and is maintained only on free time, you can also consider a donation to support further work.','newstatpress');

  ?>

  <div id="pagecredits" class='wrap'>
    <h2><?php _e('Credits','newstatpress'); ?></h2>
    <table class='widefat'>
      <tr>
        <td>
          <?php echo $credits_introduction; ?>
        </td>
        <td class='don'>
          <a class="button button-primary" href='<?php echo nsp_RATING_URL ?>' target='_blank'><?php _e('Rate the plugin',nsp_TEXTDOMAIN); ?></a>
          <br/>
          <a class="button button-primary" href='<?php echo nsp_DONATE_URL ?>' target='_blank'><?php _e('Make a donation',nsp_TEXTDOMAIN); ?></a>
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
          echo "<li><a href='#$tab'>$name</a></li>\n";
      }
    ?>
    </ul>

    <!-- tab 'development' -->
    <div id='development'>
      <p class="dev_intro"><?php _e('This plugin is a fork of','newstatpress'); ?><span>Statpress</span> plugin, originaly develop by <span>Daniele Lippi</span> and not anymore maintened.</p>
      <table class='credit'>
        <thead>
          <tr>
            <th class='cell-l'><?php _e('Contributor','newstatpress'); ?></th>
            <th class='cell-r'><?php _e('Description','newstatpress'); ?></th>
          </tr>
        </thead>
        <tbody id="addresses"></tbody>
      </table>
    </div>

    <!-- tab 'ressources' -->
    <div id='ressources'>
      <table class='credit'>
        <thead>
          <tr>
            <th class='cell-l'><?php _e('Reference','newstatpress'); ?></th>
            <th class='cell-r'><?php _e('Description','newstatpress'); ?></th>
            <th class='cell-r'><?php _e('Website','newstatpress'); ?></th>
          </tr>
        </thead>
        <tbody id="ressourceslist"></tbody>
      </table>
    </div>

    <!-- tab 'translation' -->
    <div id='translation'>
      <table class='credit'>
        <thead>
          <tr>
            <th class='cell-l'><?php _e('Language','newstatpress'); ?></th>
            <th class='cell-r'><?php _e('Contributor','newstatpress'); ?></th>
            <th class='cell-r'><?php _e('Status','newstatpress'); ?></th>
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
            <th class='cell-l'><?php _e('Contributor','newstatpress'); ?></th>
            <th class='cell-r'><?php _e('Date','newstatpress'); ?></th>
          </tr>
        </thead>
        <tbody id="donatorlist"></tbody>
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

  </table>
    <!-- </table> -->
    <?php
  }
}

?>
