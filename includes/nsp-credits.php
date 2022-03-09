<?php
/**
 * Credits functions
 *
 * @package NewStatpress
 */

// Make sure plugin remains secure if called directly.
if ( ! defined( 'ABSPATH' ) ) {
	if ( ! headers_sent() ) {
		header( 'HTTP/1.1 403 Forbidden' );
	}
	die( esc_html( __( 'ERROR: This plugin requires WordPress and will not function if called directly.', 'newstatpress' ) ) );
}

/**
 * Display the page with credits (contributors, translators, donors)
 * added by cHab
 */
function nsp_display_credits_page() {

	global $pagenow;
	global $credits_introduction;

	$page = 'nsp-credits';

	$credits_page_tabs = array(
		'development' => __( 'Development', 'newstatpress' ),
		'ressources'  => __( 'Ressources', 'newstatpress' ),
		'translation' => __( 'Translation', 'newstatpress' ),
		'donation'    => __( 'Donation', 'newstatpress' ),
	);

	$support_pluginpage   = "<a href='" . NSP_SUPPORT_URL . "' target='_blank'>" . __( 'the support page', 'newstatpress' ) . '</a>';
	$author_linkpage      = "<a href='" . NSP_PLUGIN_URL . "/?page_id=2' target='_blank'>" . __( 'the author', 'newstatpress' ) . '</a>';
	$credits_introduction = __( 'If you have found this plugin usefull and you like it, thank you to take a moment to rate it.', 'newstatpress' );
	// translators: placeholders to add a link inside the text.
	$credits_introduction .= ' ' . sprintf( __( 'You can help to the plugin development by reporting bugs on %1$s or by adding/updating translation by contacting directly %2$s.', 'newstatpress' ), $support_pluginpage, $author_linkpage );
	$credits_introduction .= '<br />';
	$credits_introduction .= __( 'NewStatPress is provided for free and is maintained only on free time, you can also consider a donation to support further work.', 'newstatpress' );

	?>

	<div id="pagecredits" class='wrap'>
	<h2><?php esc_html_e( 'Credits', 'newstatpress' ); ?></h2>
	<table class='widefat'>
		<tr>
		<td>
			<?php
			echo wp_kses(
				$credits_introduction,
				array(
					'a'  => array(
						'href'   => array(),
						'target' => array(),
					),
					'br' => array(),
				)
			);
			?>
		</td>
		<td class='don'>
			<br/>
			<a class="button button-primary rate" href='<?php echo esc_url( NSP_RATING_URL ); ?>' target='_blank'><?php esc_html_e( 'Rate the plugin', 'newstatpress' ); ?></a>
			<br/>
			<a class="button button-primary donate" href='<?php echo esc_url( NSP_DONATE_URL ); ?>' target='_blank'><?php esc_html_e( 'Make a donation', 'newstatpress' ); ?></a>
		</td>
		</tr>
	</table>

	<?php
	if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && $page === $_GET['page'] ) {
		?>

	<div id="usual1" class="usual">
	<ul>
		<?php
		foreach ( $credits_page_tabs as $tab => $name ) {
			echo "<li><a href='#" . esc_attr( $tab ) . "'>" . esc_html( $name ) . "</a></li>\n";
		}
		?>
	</ul>

	<!-- tab 'development' -->
	<div id='development'>
		<p class="dev_intro"><?php esc_html_e( 'This plugin is a fork of the plugin', 'newstatpress' ); ?><span> Statpress, </span><?php esc_html_e( 'originally developed (and not longer maintened) by', 'newstatpress' ); ?><span class="strong"> Daniele Lippi </span>.</p>
		<table class='credit'>
		<thead>
			<tr>
			<th class='cell-l'><?php esc_html_e( 'Contributor', 'newstatpress' ); ?></th>
			<th class='cell-r'><?php esc_html_e( 'Description', 'newstatpress' ); ?></th>
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
			<th class='cell-l'><?php esc_html_e( 'Reference', 'newstatpress' ); ?></th>
			<th class='cell-r'><?php esc_html_e( 'Description', 'newstatpress' ); ?></th>
			<th class='cell-r'><?php esc_html_e( 'Website', 'newstatpress' ); ?></th>
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
			<th class='cell-l'><?php esc_html_e( 'Language', 'newstatpress' ); ?></th>
			<th class='cell-r'><?php esc_html_e( 'Contributor', 'newstatpress' ); ?></th>
			<th class='cell-r'><?php esc_html_e( 'Status', 'newstatpress' ); ?></th>
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
			<th class='cell-l'><?php esc_html_e( 'Contributor', 'newstatpress' ); ?></th>
			<th class='cell-r'><?php esc_html_e( 'Date', 'newstatpress' ); ?></th>
			</tr>
		</thead>
		<tbody id="donatorlist"></tbody>
		</table>
	</div>

	</div>

	<table class='credit-footer'>
	<tr>
		<td> <?php esc_html_e( 'Plugin homepage', 'newstatpress' ); ?>
		<a target='_blank' href='http://newstatpress.altervista.org'>Newstatpress</a>
		</td>
	</tr>
	<tr>
		<td> <?php esc_html_e( 'RSS news', 'newstatpress' ); ?>
		<a target='_blank' href='http://newstatpress.altervista.org/?feed=rss2'> <?php esc_html_e( 'News', 'newstatpress' ); ?></a>
		</td>
	</tr>
	</table>

</div>

	<script type="text/javascript">
		//jQuery("#usual1 ul").idTabs(development);
		jQuery(document).ready(function($){
			$("#usual1").tabs();
		});
	</script>

		<?php
	}
}

?>
