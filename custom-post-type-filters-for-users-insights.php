<?php
/**
 * Plugin Name: Custom Post Type Filters For Users Insights
 * Description: Extends the Users Insights plugin by adding an option to list and filter the users by the number of posts they have created from each custom post type
 * Version: 1.0.1
 * Author: denizz
 * Author URI: https://usersinsights.com/
 * Text Domain: custom-post-type-filters-for-users-insights
 * License: GPLv2 or later
 * Requires at least: 4.7
 */

 if(!defined( 'ABSPATH' )) {
 	exit;
 }
 /**
  * Includes the main plugin initialization functionality.
  */
 class CPTUI_Initializer {
	 
	 protected $requires_usin_version = '3.0.0';
	 protected $usin_page_slug = 'users_insights';
	 
	 /**
	  * Registers the required hooks.
	  */
	 public function __construct() {
		 add_action('plugins_loaded', array($this, 'init'));
	 }
	 
	 
	 /**
	  * Initializes the plugin.
	  */
	 public function init() {
		if(!is_admin()){
		 //this plugin runs in the admin only
			 return;
		}

		if(!$this->check_requirements()){
			 return;
		}
		
		//load the text domain
		$this->load_textdomain();
		
		//enqueue the assets
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_assets') );
		
		
		if(function_exists('usin_manager')){
			$manager = usin_manager();
			if(isset($manager->slug)){
				$this->usin_page_slug = $manager->slug;
			}
		}

		$this->include_files();

		if ( ! defined( 'CPTUI_PLUGIN_FILE' ) ) {
			 define( 'CPTUI_PLUGIN_FILE', __FILE__);
		}
		if ( ! defined( 'CPTUI_VERSION' ) ) {
			 define( 'CPTUI_VERSION', '1.0.0');
		}
		
		$settings_page = CPTUI_Settings_Page::get_instance($this->usin_page_slug);
		new CPTUI_Filters();

	 }
	 
	 /**
	  * Checks whether Users Insights is activated and checks against the
	  * minimum version required.
	  * @return boolean true if Users Insights is activated and its version is
	  * equal or bigger than the required version and false otherwise.
	  */
	 protected function check_requirements(){
		 
		 if(!defined('USIN_VERSION')){
			 add_action( 'admin_notices', array($this,'show_plugin_inactive_notice'));
			 return false;
		 }
		 
		 if(!version_compare(USIN_VERSION, $this->requires_usin_version, '>=')){
			 add_action( 'admin_notices', array($this,'show_version_required_notice'));
			 return false;
		 }
		 
		 return true;
		 
	 }
	 
	 
	 /**
	  * Adds an admin notice when the Users Insights plugin is not active.
	  */
	 public function show_plugin_inactive_notice(){
		?>
	    <div class="notice notice-warning">
	        <p><?php _e( 'Custom Post Types For Users Insights requires the 
			<a href="https://usersinsights.com/?utm_source=wprepo&utm_campaign=cpt" target="_blank">Users Insights plugin</a> 
			to be active.', 'custom-post-type-filters-for-users-insights' ); ?></p>
	    </div>
	    <?php
	 }
	 
	 /**
	  * Adds an admin notice when the currently installed version of Users Insights
	  * is smaller than the required verion.
	  */
	 public function show_version_required_notice(){
		 ?>
 	    <div class="notice notice-warning">
 	        <p><?php printf(__( 'Custom Post Types For Users Insights requires 
			Users Insights version %s or later.', 'custom-post-type-filters-for-users-insights' ), 
				$this->requires_usin_version); ?></p>
 	    </div>
 	    <?php
	 }
	 
	 /**
	  * Includes the required PHP files.
	  */
	 protected function include_files() {
		 include_once( 'includes/settings-page.php' );
		 include_once( 'includes/settings.php' );
		 include_once( 'includes/filters.php' );
	 }
	 
	 /**
	  * Loads the plugin text domain.
	  */
	 public function load_textdomain(){
		 load_plugin_textdomain('custom-post-type-filters-for-users-insights');
	 }
	 
	 public function enqueue_assets(){
		 global $current_screen;

 		if(strpos( $current_screen->base, $this->usin_page_slug ) !== false){
			//this is the Users Insights page
			wp_enqueue_style('cptui_user_page_styles', 
				plugins_url('css/user-page.css', __FILE__), 
				array('usin_main_css'), 
				CPTUI_VERSION);
		}
	 }
	 
 }
 
 
 new CPTUI_Initializer();