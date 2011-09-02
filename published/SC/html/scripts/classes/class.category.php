<?php
class Category extends DBObject {
	
	var $categoryID;
	var $parent;
	var $products_count;
	var $picture;
	var $products_count_admin;
	var $sort_order;
	var $viewed_times;
	var $allow_products_comparison;
	var $allow_products_search;
	var $show_subcategories_products;
	var $name;
	var $description;
	var $meta_title;
	var $meta_description;
	var $meta_keywords;
	var $slug;
	var $vkontakte_type;
	var $id_1c;
	
	var $__db_table = CATEGORIES_TABLE;
	var $__primary_key = 'categoryID';
	
	function __isAvailableSlug($slug){
		
		return !intval(db_phquery_fetch(DBRFETCH_FIRST, 'SELECT 1 FROM ?#CATEGORIES_TABLE WHERE slug=? AND categoryID<>?', $slug, $this->categoryID));
	}
	
	function checkInfo($scheme = null){

		if($this->slug && !$this->__isAvailableSlug($this->slug))
			return PEAR::raiseError('msg_occupied_slug');
	}
		
	function loadBySlug($slug){
		if($this->getRegisteredByID($slug)) return;
		$category = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#CATEGORIES_TABLE WHERE slug=?', $slug);
		LanguagesManager::ml_fillFields($this->__db_table, $category);
		$this->loadFromArray($category);
	}
	function loadByID($id){
		$res = parent::loadByID($id);
		if($res){
			$this->registerByID($this->slug);
		}
		return $res;
	}
}
?>