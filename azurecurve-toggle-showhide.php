<?php
/*
Plugin Name: azurecurve Toggle Show/Hide
Plugin URI: http://wordpress.azurecurve.co.uk/plugins/toggle-show-hide
Description: Toggle to show or hide a section of content
Version: 1.1.0
Author: Ian Grieve
Author URI: http://wordpress.azurecurve.co.uk

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.

The full copy of the GNU General Public License is available here: http://www.gnu.org/licenses/gpl.txt

*/

add_shortcode( 'toggle', 'azc_toggle_show_hide' );

add_action('wp_enqueue_scripts', 'azc_tsh_load_css');
add_action('wp_enqueue_scripts', 'azc_tsh_load_jquery');

function azc_tsh_load_css(){
	wp_enqueue_style( 'azurecurve-tsh', plugins_url( 'style.css', __FILE__ ), '', '1.0.0' );
}

function azc_tsh_load_jquery(){
	wp_enqueue_script( 'azurecurve-tsh', plugins_url('jquery.js', __FILE__), array('jquery'), '3.9.1');
}

function azc_toggle_show_hide($atts, $content = null) {
	$options = get_option( 'azc_tsh_options' );
	$network_options = get_site_option( 'azc_tsh_options' );
	//set default title
	if (strlen($options['title']) > 0){
		$title = stripslashes($options['title']);
	}elseif (strlen($network_options['title']) > 0){
		$title = stripslashes($network_options['title']);
	}else{
		$title = __('Click to show/hide', 'azc-tsh');
	}
	//set default title color
	if (strlen($options['title_color']) > 0){
		$title_color = stripslashes($options['title_color']);
	}elseif (strlen($network_options['title_color']) > 0){
		$title_color = stripslashes($network_options['title_color']);
	}else{
		$title_color = "";
	}
	//set default border
	if (strlen($options['border']) > 0){
		$border = stripslashes($options['border']);
	}elseif (strlen($network_options['border']) > 0){
		$border = stripslashes($network_options['border']);
	}else{
		$border = "";
	}
	
	extract(shortcode_atts(array(
		'title' => $title,
		'title_color' => $title_color,
		'expand' => 0,
		'border' => $border
	), $atts));
	
	$border_style='';
	$link_style='';
	if($expand == 1){
		$expand = '_open';
		$expand_active = $expand.'_active';
	}else{
		$expand = '';
		$expand_active = '';
	}
	if (strlen($border) > 0){ $border = "border: $border;"; }
	if (strlen($title_color) > 0){ $title_color = "color: $title_color;"; }
	if (strlen($border) > 0 or strlen($title_color) > 0){
		$link_style = " style='$title_color'";
		$border_style = " style='$border'";
	}
	if($options['allow_shortcodes'] == 1){
		$title = do_shortcode($title);
		$content = do_shortcode($content);
	}
	
	$output = "<h3 class='azc_tsh_toggle$expand_active'$border_style><a href='#'$link_style>$title</a></h3><div class='azc_tsh_toggle_container$expand'$border_style>$content</div>";
	
	return $output;
}

function azc_tsh_load_plugin_textdomain(){
	
	$loaded = load_plugin_textdomain( 'azurecurve-tsh', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action('plugins_loaded', 'azc_tsh_load_plugin_textdomain');


function azc_tsh_set_default_options($networkwide) {
	
	$new_options = array(
				'border' => ''
				,'title' => ''
				,'title_color' => ''
				,'allow_shortcodes' => 0
			);
	
	// set defaults for multi-site
	if (function_exists('is_multisite') && is_multisite()) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ($networkwide) {
			global $wpdb;

			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			$original_blog_id = get_current_blog_id();

			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );

				if ( get_option( 'azc_tsh_options' ) === false ) {
					add_option( 'azc_tsh_options', $new_options );
				}
			}

			switch_to_blog( $original_blog_id );
		}else{
			if ( get_option( 'azc_tsh_options' ) === false ) {
				add_option( 'azc_tsh_options', $new_options );
			}
		}
		if ( get_site_option( 'azc_tsh_options' ) === false ) {
			add_site_option( 'azc_tsh_options', $new_options );
		}
	}
	//set defaults for single site
	else{
		if ( get_option( 'azc_tsh_options' ) === false ) {
			add_option( 'azc_tsh_options', $new_options );
		}
	}
}
register_activation_hook( __FILE__, 'azc_tsh_set_default_options' ); 

function azc_tsh_plugin_action_links($links, $file) {
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=azurecurve-toggle-showhide">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}
add_filter('plugin_action_links', 'azc_tsh_plugin_action_links', 10, 2);



function azc_tsh_settings_menu() {
	add_options_page( 'azurecurve Toggle Show/Hide',
	'azurecurve Toggle Show/Hide', 'manage_options',
	'azurecurve-toggle-showhide', 'azc_tsh_config_page' );
}
add_action( 'admin_menu', 'azc_tsh_settings_menu' );

function azc_tsh_config_page() {
	if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.', 'azurecurve-tsh'));
    }
	
	// Retrieve plugin configuration options from database
	$options = get_option( 'azc_tsh_options' );
	?>
	<div id="azc-tsh-general" class="wrap">
		<fieldset>
			<h2>azurecurve Togglew Show/Hide <?php _e('Settings', 'azurecurve-tsh'); ?></h2>
			<?php if( isset($_GET['settings-updated']) ) { ?>
				<div id="message" class="updated">
					<p><strong><?php _e('Settings have been saved.') ?></strong></p>
				</div>
			<?php } ?>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="save_azc_tsh_options" />
				<input name="page_options" type="hidden" value="tsh_suffix" />
				
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field( 'azc_tsh' ); ?>
				<table class="form-table">
				<tr><td colspan=2>
					<p><?php _e('Set the default title and border settings. If multisite is being used leave this blank to get multisite default.', 'azurecurve-tsh'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="width"><?php _e('Title', 'azurecurve-tsh'); ?></label></th><td>
					<input type="text" name="title" value="<?php echo esc_html( stripslashes($options['title']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default title text (e.g. Click here to toggle show/hide)', 'azurecurve-tsh'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="width"><?php _e('Title Color', 'azurecurve-tsh'); ?></label></th><td>
					<input type="text" name="title_color" value="<?php echo esc_html( stripslashes($options['title_color']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default title color (e.g. #000)', 'azurecurve-tsh'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="width"><?php _e('Border', 'azurecurve-tsh'); ?></label></th><td>
					<input type="text" name="border" value="<?php echo esc_html( stripslashes($options['border']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default border (e.g. 1px solid #00F000)', 'azurecurve-tsh'); ?></p>
				</td></tr>
				<tr><th scope="row">Allow Shortcodes?</th><td>
					<fieldset><legend class="screen-reader-text"><span><?php _e('Allow shortcodes within toggle?', 'azurecurve-tsh'); ?></span></legend>
					<label for="allow_shortcodes"><input name="allow_shortcodes" type="checkbox" id="allow_shortcodes" value="1" <?php checked( '1', $options['allow_shortcodes'] ); ?> /><?php _e('Allow shortcodes within toggle?', 'azurecurve-tsh'); ?></label>
					</fieldset>
				</td></tr>
				</table>
				<input type="submit" value="Submit" class="button-primary"/>
			</form>
		</fieldset>
	</div>
<?php }


function azc_tsh_admin_init() {
	add_action( 'admin_post_save_azc_tsh_options', 'process_azc_tsh_options' );
}
add_action( 'admin_init', 'azc_tsh_admin_init' );

function process_azc_tsh_options() {
	// Check that user has proper security level
	if ( !current_user_can( 'manage_options' ) ){
		wp_die( __('You do not have permissions for this action', 'azurecurve-tsh'));
	}
	// Check that nonce field created in configuration form is present
	check_admin_referer( 'azc_tsh' );
	settings_fields('azc_tsh');
	
	// Retrieve original plugin options array
	$options = get_option( 'azc_tsh_options' );
	
	$option_name = 'title';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'title_color';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'border';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'allow_shortcodes';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = 1;
	}else{
		$options[$option_name] = 0;
	}
	
	// Store updated options array to database
	update_option( 'azc_tsh_options', $options );
	
	// Redirect the page to the configuration form that was processed
	wp_redirect( add_query_arg( 'page', 'azurecurve-toggle-showhide&settings-updated', admin_url( 'options-general.php' ) ) );
	exit;
}


function add_azc_tsh_network_settings_page() {
	if (function_exists('is_multisite') && is_multisite()) {
		add_submenu_page(
			'settings.php',
			'azurecurve Toggle Show/Hide Settings',
			'azurecurve Toggle Show/Hide',
			'manage_network_options',
			'azurecurve-tsh',
			'azc_tsh_network_settings_page'
			);
	}
}
add_action('network_admin_menu', 'add_azc_tsh_network_settings_page');

function azc_tsh_network_settings_page(){
	$options = get_site_option('azc_tsh_options');

	?>
	<div id="azc-tsh-general" class="wrap">
		<fieldset>
			<h2>azurecurve Toggle Show/Hide <?php _e('Network Settings', 'azurecurve-tsh'); ?></h2>
			<?php if( isset($_GET['settings-updated']) ) { ?>
				<div id="message" class="updated">
					<p><strong><?php _e('Network Settings have been saved.') ?></strong></p>
				</div>
			<?php } ?>
			<form method="post" action="admin-post.php">
				<input type="hidden" name="action" value="save_azc_tsh_options" />
				<input name="page_options" type="hidden" value="suffix" />
				
				<!-- Adding security through hidden referrer field -->
				<?php wp_nonce_field( 'azc_tsh' ); ?>
				<table class="form-table">
				<tr><td colspan=2>
					<p><?php _e('Set the default title and border. If multisite is being used these options will be used when site options are blank; if the network options are blank defaults in CSS will be used.', 'azurecurve-tsh'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="width"><?php _e('Title', 'azurecurve-tsh'); ?></label></th><td>
					<input type="text" name="title" value="<?php echo esc_html( stripslashes($options['title']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default title text (e.g. Click here to toggle show/hide)', 'azurecurve-tsh'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="width"><?php _e('Title Color', 'azurecurve-tsh'); ?></label></th><td>
					<input type="text" name="title_color" value="<?php echo esc_html( stripslashes($options['title_color']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default title color (e.g. #000)', 'azurecurve-tsh'); ?></p>
				</td></tr>
				<tr><th scope="row"><label for="width"><?php _e('Border', 'azurecurve-tsh'); ?></label></th><td>
					<input type="text" name="border" value="<?php echo esc_html( stripslashes($options['border']) ); ?>" class="large-text" />
					<p class="description"><?php _e('Set default border (e.g. 1px solid #00F000)', 'azurecurve-tsh'); ?></p>
				</td></tr>
				<tr><th scope="row">Allow Shortcodes?</th><td>
					<fieldset><legend class="screen-reader-text"><span><?php _e('Allow shortcodes within toggle?', 'azurecurve-tsh'); ?></span></legend>
					<label for="allow_shortcodes"><input name="allow_shortcodes" type="checkbox" id="allow_shortcodes" value="1" <?php checked( '1', $options['allow_shortcodes'] ); ?> /><?php _e('Allow shortcodes within toggle?', 'azurecurve-tsh'); ?></label>
					</fieldset>
				</td></tr>
				</table>
				<input type="submit" value="Submit" class="button-primary" />
			</form>
		</fieldset>
	</div>
	<?php
}


function process_azc_tsh_network_options(){     
	if(!current_user_can('manage_network_options')) wp_die(_e('You do not have permissions to maintain these settings.'));
	check_admin_referer('azc_tsh');
	
	// Retrieve original plugin options array
	$options = get_site_option( 'azc_tsh_options' );

	$option_name = 'title';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}

	$option_name = 'title_color';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}

	$option_name = 'border';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = ($_POST[$option_name]);
	}
	
	$option_name = 'allow_shortcodes';
	if ( isset( $_POST[$option_name] ) ) {
		$options[$option_name] = 1;
	}else{
		$options[$option_name] = 0;
	}
	
	update_site_option( 'azc_tsh_options', $options );

	wp_redirect(network_admin_url('settings.php?page=azurecurve-toggle-showhide&settings-updated'));
	exit;  
}
add_action('network_admin_edit_update_azc_tsh_network_options', 'process_azc_tsh_network_options');

?>