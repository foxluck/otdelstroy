<?php
class ProductList extends DBObject {
	
	var $id;
	var $name;
	
	var $__db_table = TBL_PRODUCT_LIST;
	var $__primary_key = 'id';
	var $__primary_key_autoincrement = false;
	
	static function stc_deleteProductFromLists($productID){
		
		db_phquery('DELETE FROM ?#TBL_PRODUCT_LIST_ITEM WHERE productID=?', $productID);
	}
	
	static function stc_getLists($return_objects = true){

		$dbres = db_phquery("SELECT pl.*, COUNT(pli.list_id) as products_num FROM ?#TBL_PRODUCT_LIST pl LEFT JOIN ?#TBL_PRODUCT_LIST_ITEM pli ON pl.id=pli.list_id GROUP BY pl.id ORDER BY `id` ASC");
		$product_lists = array();
		while ($row = db_fetch_assoc($dbres)){
			
			if($return_objects){
				$objectEntry = new ProductList();
				$objectEntry->loadFromArray($row);
				$product_lists[] = $objectEntry;
			}else{
				
				$product_lists[] = $row;
			}
		}
		
		return $product_lists;
	}
	
	function checkInfo($scheme = null){
		
		$res = parent::checkInfo($scheme);
		if(PEAR::isError($res))return $res;
		$this->id = strtolower($this->id);
		
		if(!preg_match('/^[a-z0-9]+$/u', $this->id))return PEAR::raiseError('prdlist_wrong_chars_in_id');
	}
	
	function delete(){
		
		$dbq = 'DELETE FROM ?#TBL_PRODUCT_LIST_ITEM WHERE list_id=?';
		db_phquery($dbq, $this->id);
		
		parent::delete();
	}
	
	function addProduct($productID){
		
		if(!intval($productID))return;
		
		$dbq = 'SELECT 1 FROM ?#TBL_PRODUCT_LIST_ITEM WHERE list_id=? AND productID=?';
		
		if(db_phquery_fetch(DBRFETCH_FIRST, $dbq, $this->id, $productID))return;
		
		$dbq = 'INSERT ?#TBL_PRODUCT_LIST_ITEM (list_id, productID) VALUES(?,?)';
		db_phquery($dbq, $this->id, $productID);
	}
	
	function getProducts($enabled = null){
		
		$dbq = '
			SELECT t1.*, t3.thumbnail FROM ?#PRODUCTS_TABLE t1 LEFT JOIN ?#TBL_PRODUCT_LIST_ITEM t2 ON t1.productID=t2.productID
			LEFT JOIN ?#PRODUCT_PICTURES t3 ON t1.default_picture=t3.photoID
			WHERE'.(is_null($enabled)?'':' t1.enabled='.intval($enabled).' AND t1.categoryID != 1 AND').' t2.list_id=? ORDER BY t2.priority ASC
		';
		
		$dbres = db_phquery($dbq, $this->id);
		$products = array();
		while($row = db_fetch_assoc($dbres)){
			
			LanguagesManager::ml_fillFields(PRODUCTS_TABLE, $row);
			if(!$row['thumbnail'] || !file_exists(DIR_PRODUCTS_PICTURES.'/'.$row['thumbnail']))unset($row['thumbnail']);
			$row['price_str'] = show_price($row['Price']);
			$products[] = $row;
		}
		return $products;
	}
	
	function deleteProduct($productID){
		
		db_phquery('DELETE FROM ?#TBL_PRODUCT_LIST_ITEM WHERE list_id=? AND productID=?', $this->id, $productID);
	}
}
?>