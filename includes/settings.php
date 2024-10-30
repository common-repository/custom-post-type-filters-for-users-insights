<?php

if(!defined( 'ABSPATH' )) {
   exit;
}

class CPTUI_Settings{
	
	protected static $option_key = '_cptui_post_types';
	protected static $default_post_types = array('post', 'page');
	
	public static function get_enabled_post_types($require_existence_check = true){
		
		$enabled_post_types = get_option(self::$option_key, null);
		if($enabled_post_types === null || !is_array($enabled_post_types)){
			$enabled_post_types = self::$default_post_types;
		}
		
		if($require_existence_check) {
			// return only the existing post types
			$enabled_post_types = self::validate_post_types($enabled_post_types);
		}
		
		return $enabled_post_types;
	}
	
	public static function save_post_types($post_types){
		//validate the post types
		$post_types = self::validate_post_types($post_types);
		update_option(self::$option_key, $post_types);
	}
	
	/**
	 * Removes the post types that don't exist
	 */
	protected static function validate_post_types($post_types){
		foreach ($post_types as $key => $post_type ) {
			if(!post_type_exists( $post_type )){
				unset($post_types[$key]);
			}
		}
		
		return array_values($post_types);
	}
}