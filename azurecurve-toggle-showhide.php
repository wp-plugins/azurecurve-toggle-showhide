<?php
/*
Plugin Name: azurecurve Toggle Show/Hide
Plugin URI: http://wordpress.azurecurve.co.uk/plugins/toggle-show-hide
Description: Toggle to show or hide a section of content
Version: 1.0.5
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
add_action('plugins_loaded', 'azc_tsh_load_plugin_textdomain');

function azc_tsh_load_css(){
	wp_enqueue_style( 'azurecurve-toggle-show-hide', plugins_url( 'style.css', __FILE__ ), '', '1.0.0' );
}

function azc_tsh_load_jquery(){
	wp_enqueue_script( 'azurecurve-toggle-show-hide', plugins_url('jquery.js', __FILE__), array('jquery'), '3.9.1');
}

function azc_tsh_load_plugin_textdomain(){
	
	$loaded = load_plugin_textdomain( 'azurecurve-toggle-show-hide', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	//if ($loaded){ echo 'true'; }else{ echo 'false'; }
}

function azc_toggle_show_hide($atts, $content = null) {
	
	extract(shortcode_atts(array(
		'title' => "Click to show/hide",
		'expand' => 0,
		'border' => ''
	), $atts));
	
	if($expand == 1){
		$expand = '_open';
		$expand_active = $expand.'_active';
	}else{
		$expand = '';
		$expand_active = '';
	}
	if (strlen($border) > 0){
		$border = " style='border: $border;'";
	}
	
	$output = "<h3 class='azc_tsh_toggle$expand_active'$border><a href='#'>".$title."</a></h3><div class='azc_tsh_toggle_container$expand'$border>".$content."</div>";
	
	return $output;
}

?>