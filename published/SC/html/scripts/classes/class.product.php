<?php
class Product extends DBObject {
	
	var $productID;
	var $categoryID;
	var $customers_rating;
	var $Price;
	var $in_stock;
	var $customer_votes;
	var $items_sold;
	var $enabled = 1;
	var $list_price;
	var $product_code;
	var $sort_order;
	var $default_picture;
	var $date_added;
	var $date_modified;
	var $viewed_times;
	var $eproduct_filename;
	var $eproduct_available_days;
	var $eproduct_download_times;
	var $weight;
	var $free_shipping;
	var $min_order_amount = 1;
	var $shipping_freight;
	var $classID;
	var $name;
	var $brief_description;
	var $description;
	var $meta_title;
	var $meta_description;
	var $meta_keywords;
	var $ordering_available;
	var $slug;
	var $add2cart_counter;
	var $vkontakte_update_timestamp;
	var $id_1c;
	//var $id_quickbooks;
	
	var $__primary_key = 'productID';
	var $__db_table = PRODUCTS_TABLE;
	var $__className = 'product';
	
	function save($force_insert = false){
		
		if(!$this->{$this->__primary_key}){
			
			$this->date_added = date('Y-m-d H:i:s');
			$this->date_modified = date('Y-m-d H:i:s');
		}else{
			
			$this->date_modified = date('Y-m-d H:i:s');
		}
		
		parent::save($force_insert);
	}
	
	function __isAvailableSlug($slug){
		
		return !intval(db_phquery_fetch(DBRFETCH_FIRST, 'SELECT 1 FROM ?#PRODUCTS_TABLE WHERE slug=? AND productID<>?', $slug, $this->productID));
	}
	
	function checkInfo($scheme = null){

		if($this->slug && !$this->__isAvailableSlug($this->slug))
			return PEAR::raiseError('msg_occupied_slug');
	}
	
	function loadBySlug($slug){
		if($this->getRegisteredByID($slug)) return;
		$product = db_phquery_fetch(DBRFETCH_ASSOC, 'SELECT * FROM ?#PRODUCTS_TABLE WHERE slug=?', $slug);
		LanguagesManager::ml_fillFields($this->__db_table, $product);
		$this->loadFromArray($product);
		$this->registerByID($product['productID']);
		$this->registerByID($product['slug']);
	}
	
	function loadByID($id){
		$res = parent::loadByID($id);
		if($res){
			$this->registerByID($this->slug);
		}
		return $res;
	}

	function correctFieldData($field, $data){
		
		if(in_array($field, array('Price', 'list_price', 'weight', 'shipping_freight', 'min_order_amount'))){
			$data = str_replace(',', '.', $data);
		}
		return $data;
	}

}
?>