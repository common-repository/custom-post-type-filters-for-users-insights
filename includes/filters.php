<?php

if(!defined( 'ABSPATH' )) {
   exit;
}

class CPTUI_Filters{
	
	protected $prefix = 'cptui_count_';
	protected $table_alias = 'cptui_posts';
	protected $post_types = array();
	protected $is_export_request;

	public function __construct(){
		$this->is_export_request =isset($_GET['usin_export']);
		if($this->is_export_request){
			$this->init();
		}else{
			add_action('admin_init', array($this, 'init'));
		}
	}
	
	public function init(){
		$require_post_type_existence_check = !$this->is_export_request;
		$this->post_types = CPTUI_Settings::get_enabled_post_types($require_post_type_existence_check);
		
		add_filter('usin_fields', array($this, 'register_fields'));
		add_filter('usin_db_map', array($this, 'filter_db_map'), 100);
		add_filter('usin_query_join_table', array($this, 'filter_query_joins'), 10, 2);
	}
	
	public function register_fields($fields){
		
		foreach ($this->post_types as $post_type_name) {
			$post_type = get_post_type_object($post_type_name);
			$fields[]=array(
				'name' => $post_type->label,
				'id' => $this->prefix.$post_type->name,
				'order' => 'DESC',
				'show' => false,
				'fieldType' => 'cptui',
				'icon' => 'cptui',
				'filter' => array(
					'type' => 'number',
					'disallow_null' => true
				)
			);
		}
		
		return $fields;
		
	}
	
	public function filter_db_map($db_map){
		
		foreach ($this->post_types as $post_type) {
			$db_map[$this->prefix.$post_type] = array(
				'db_ref'=>"`$post_type`", 
				'db_table'=>$this->table_alias, 
				'null_to_zero'=>true, 
				'set_alias'=>true
			);
		}
		
		return $db_map;
	}
	
	
	function filter_query_joins($query_joins, $table){
		global $wpdb;
		
		if($table == $this->table_alias){
			
			//set the count for each post type
			$selects = array();
			foreach ($this->post_types as $post_type) {
				$selects[]="SUM(CASE WHEN post_type = '".$post_type."' THEN 1 ELSE 0 END) AS `$post_type`";
			}
			
			//limit the post list to the ones that we want to count
			$post_types_string = "'".implode("','", $this->post_types)."'";
			$where = "WHERE post_type IN ($post_types_string)";
			
			//limit the post status
			if(method_exists('USIN_Helper', 'get_allowed_post_statuses')){
				$allowed_statuses = USIN_Helper::get_allowed_post_statuses('sql_string');
				if(!empty($allowed_statuses)){
					$where .= " AND post_status IN ($allowed_statuses) ";
				}
			}
			
			$query_joins .= " LEFT JOIN (SELECT post_author, ". implode(', ', $selects).
				" FROM $wpdb->posts ".$where."GROUP BY post_author) $this->table_alias ".
			"ON $wpdb->users.ID = $this->table_alias.post_author";
			
		}
		
		return $query_joins;
	}
	
}