<?php
/*
Plugin Name: Gelocate WP
Plugin URI: http://aaronshoudy.com
Description: Geolocation via Twitter API
Author: Aaron Shoudy
Author URI: http://www.aaronshoudy.com
Version: 0.0.1
*/

class Gelocate_WP {
	static $add_script;
	static function init() {
		add_shortcode('foodtrucks', array(__CLASS__, 'handle_shortcode'));

		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
    self::food_panel();
	}
  
  static function food_panel(){
    // Hook for adding admin menus
add_action('admin_menu', 'ft_add_pages');
register_activation_hook( __FILE__, 'ft_install' );
register_activation_hook( __FILE__, 'ft_install_data' );

// action function for above hook
function ft_add_pages() {
    add_menu_page(__('Food Trucks','menu-test'), __('Food Trucks','menu-test'), 'manage_options', 'ft-top-level-handle', 'ft_toplevel_page' );
    add_submenu_page('ft-top-level-handle', __('Twitter','menu-test'), __('Twitter','menu-test'), 'manage_options', 'sub-page', 'ft_sublevel_page');
   add_submenu_page('ft-top-level-handle', __('Settings','menu-test'), __('Settings','menu-test'), 'manage_options', 'sub-page2', 'ft_sublevel_page2');

}

function ft_toplevel_page() {
    echo "<h2>" . __( 'Food Trucks', 'menu-test' ) . "</h2>
    <p>Written By: <a href='http://www.aaronshoudy.com'>Aaron Shoudy</a></p>
    <p>Questions, Bug Fixing, Maintence <a href='mailto:ashoudy@gmail.comSubject=Hello%20again' target='_top'> ashoudy@gmail.com</a>" ;
}


function ft_sublevel_page() {
  	global $wpdb;
 $results = $wpdb->get_results('SELECT * FROM wp_foodtrucks', ARRAY_A );
    echo "<h2>" . __( 'Twitter', 'menu-test' ) . "</h2>";
  echo"<select size='12' class='twitterhandlessel'>";
  foreach($results as $result){
    echo"<option value='".$result['id']."' title='".$result['name']."'>'".$result['name']."' - ".$result['handle']."</option>";
    }
  echo"</select>";
  echo"<div class='action_bar_ft'><button id='addhandle'>Add</button><button id='remhandle'>Delete</button>";
}



function ft_sublevel_page2() {
    echo "<h2>" . __( 'Settings', 'menu-test' ) . "</h2>";
  }
    
   global $ft_db_version;
$ft_db_version = '1.0';

function ft_install() {
	global $wpdb;
	global $ft_db_version;

	$table_name = $wpdb->prefix . 'foodtrucks';
	
	/*
	 * We'll set the default character set and collation for this table.
	 * If we don't do this, some characters could end up being converted 
	 * to just ?'s when saved in our table.
	 */
	$charset_collate = '';

	if ( ! empty( $wpdb->charset ) ) {
	  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
	}

	if ( ! empty( $wpdb->collate ) ) {
	  $charset_collate .= " COLLATE {$wpdb->collate}";
	}

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		handle text NOT NULL,
		UNIQUE KEY id (id)
	) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'ft_db_version', $ft_db_version );
}

function ft_install_data() {
	global $wpdb;
	
	$welcome_name = '9IB';
	$welcome_text = '@OFFICAL9IB';
	
	$table_name = $wpdb->prefix . 'foodtrucks';
	
	$wpdb->insert( 
		$table_name, 
		array( 
			'name' => $welcome_name, 
			'handle' => $welcome_text, 
		) 
	);
}
   }
  

	static function handle_shortcode($atts) {
		self::$add_script = true;

		// actual shortcode handling here
    
    return '<div class="acf-map"></div>';
	}

	static function register_script() {
		wp_register_script('ftdata', plugins_url('ft-data.php', __FILE__), array('jquery'), '1.0', true);
    wp_register_script('ftgooglemaps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false', true);
	}

	static function print_script() {
		if ( ! self::$add_script )
			return;
wp_print_scripts('ftgooglemaps');
		wp_print_scripts('ftdata');
	}
}

Geolocate_WP::init();
