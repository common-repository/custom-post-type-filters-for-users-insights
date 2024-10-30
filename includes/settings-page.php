<?php

if(!defined( 'ABSPATH' )) {
   exit;
}

class CPTUI_Settings_Page{
	
	protected $capability = 'list_users';
	protected $slug = 'cptui_settings';
	protected $usin_page_slug;
	protected static $instance;
	
	protected $action = 'cptui_save_settings';
	protected $nonce_key = 'cptui_save_settings_nonce';

	protected function __construct($usin_page_slug){
		$this->usin_page_slug = $usin_page_slug;
	}
	
	public static function get_instance($usin_page_slug){
		if(! self::$instance ){
			self::$instance = new CPTUI_Settings_Page($usin_page_slug);
			self::$instance->init();
		}
		return self::$instance;
	}
	
	protected function init(){
		if(defined('USIN_Capabilities::MANAGE_OPTIONS')){
			$this->capability = USIN_Capabilities::MANAGE_OPTIONS;
		}
		$this->title = __('Custom Post Types', 'custom-post-type-filters-for-users-insights');
		add_action ( 'admin_menu', array($this, 'add_menu_page'), 15 );
	}
	
	public function add_menu_page(){
		add_submenu_page( $this->usin_page_slug, $this->title, $this->title, 
			$this->capability, $this->slug, array($this, 'print_page_markup') );
	}
	
	/**
	 * Prints the main page markup.
	 */
	public function print_page_markup(){
		?>
		<div class="cptui-wrap">
			<h2><?php echo $this->title; ?></h2>
			
			<?php $this->check_form_submission(); ?>
			
			<h4><?php echo __('Select post types to enable on the User Table', 'custom-post-type-filters-for-users-insights'); ?></h4>
			<?php 
			$post_types = get_post_types(array(), 'objects'); 
			$enabled_post_types = CPTUI_Settings::get_enabled_post_types();
			$exclude = array('revision', 'nav_menu_item');
			?>
			<form method="post">
				<ul>
					<?php foreach ($post_types as $post_type ) {
						if(!in_array($post_type->name, $exclude)){
						?>
						<li>
							<?php $checked = in_array($post_type->name, $enabled_post_types) ? ' checked="checked"' : ''; ?>
							<input type="checkbox" name="cptui_post_types[]" value="<?php echo $post_type->name; ?>"<?php echo $checked; ?>>
							<?php printf("%s (%s)", $post_type->label, $post_type->name); ?>
						</li>
						<?php
						}
					} ?>
				</ul>
				<input type="submit" class="button button-primary" value="<?php echo __('Save Changes', 'custom-post-type-filters-for-users-insights'); ?>">
				<input type="hidden" name="<?php echo $this->action; ?>" value="true" />
				<?php wp_nonce_field( $this->action, $this->nonce_key ); ?>
			</form>
			
		</div>
		<?php
	}
	
	public function check_form_submission(){
		if(isset($_POST[$this->action])){
			if (!isset($_POST[$this->nonce_key] ) || !wp_verify_nonce($_POST[$this->nonce_key], $this->action)) {
				$this->print_notice('Error: Your nonce did not verify', 'error');
				return;
			}
			if(!current_user_can($this->capability)){
				$this->print_notice('Error: You are not allowed to perform this action', 'error');
				return;
			}
			
			$post_types = isset($_POST['cptui_post_types']) ? $_POST['cptui_post_types'] : array();
			
			CPTUI_Settings::save_post_types($post_types);
			$this->print_notice(__('Changes saved', 'custom-post-type-filters-for-users-insights'));
			
		}
	}
	
	protected function print_notice($message, $type = 'success'){
		?>
		<div class="notice notice-<?php echo $type; ?>">
			<p><?php echo $message; ?></p>
		</div>
		<?php
	}
	
}