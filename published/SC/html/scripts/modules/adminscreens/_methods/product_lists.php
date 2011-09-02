<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
class ProductListsController extends ActionsController{
		
	function _search_products($searchstring){
		
		$searchstring = trim($searchstring);
		if(!$searchstring)return;
		$searchstring = _searchPatternReplace($searchstring);
		$searchstring = xEscapeSQLstring($searchstring);
		$fl_names = LanguagesManager::ml_getLangFieldNames('name');

		$gridEntry = ClassManager::getInstance('grid');
		/*@var $gridEntry Grid*/
		
		$qr_where = '
			WHERE (t1.name LIKE "%'.$searchstring.'%") OR
			(p.product_code LIKE "%'.$searchstring.'%") OR
			( (p.'.implode(') LIKE "%'.$searchstring.'%" OR (p.', $fl_names).') LIKE ("%'.$searchstring.'%") )
			';
		
		$qr_from = '
			FROM ?#PRODUCTS_TABLE p 
			LEFT JOIN ?#TAGGED_OBJECTS_TBL t2 ON p.productID=t2.object_id 
			LEFT JOIN ?#TAGS_TBL t1 ON t2.tag_id=t1.id AND t2.object_type="product"
		';
		
		$gridEntry->query_total_rows_num = 'SELECT COUNT(DISTINCT p.productID) '.$qr_from.$qr_where;
		$gridEntry->query_select_rows = 'SELECT p.productID, p.Price, p.enabled, p.slug, '.LanguagesManager::sql_prepareField('p.name').' AS p_name '.$qr_from.$qr_where.'	GROUP BY p.productID';

		$gridEntry->show_rows_num_select = false;
		$gridEntry->default_sort_direction = 'ASC';
		$gridEntry->rows_num = 20;
		
		$gridEntry->registerHeader(translate("prdset_product_name"), 'p_name', true, 'asc');
		$gridEntry->prepare();
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/

		$rows = $smarty->get_template_vars('GridRows');
		for($k = count($rows)-1; $k>=0; $k--){
			$rows[$k]['name'] = $rows[$k]['p_name'];
			$rows[$k]['price_str'] = show_price($rows[$k]['Price']);
		}
		
		$smarty->assign('GridRows', $rows);
	}
	
	function save_order(){

		$scan_result = scanArrayKeysForID($this->getData(), 'priority');
		$sql = '
			UPDATE ?#TBL_PRODUCT_LIST_ITEM SET priority=? WHERE list_id=? AND productID=?
		';
		
		foreach ($scan_result as $productID=>$scan_info){
			
			db_phquery($sql, $scan_info['priority'], $this->getData('list_id'), $productID);
		}
		
		Message::raiseAjaxMessage(MSG_SUCCESS, '', 'order_saved');
		die;
	}
	
	function delete_product(){
		
		$objectEntry = new ProductList();
		$res = $objectEntry->loadByID($this->getData('list_id'));
		if(!$res)RedirectSQ('?ukey=product_lists');
		
		$objectEntry->deleteProduct($this->getData('productID'));
		RedirectSQ('action=edit_list');
	}
	
	function add_product(){
		
		$objectEntry = new ProductList();
		$res = $objectEntry->loadByID($this->getData('list_id'));
		if(!$res)RedirectSQ('?ukey=product_lists');
		
		$objectEntry->addProduct($this->getData('productID'));
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'action=edit_list', 'prdlist_product_added');
	}
	
	function delete_list(){
		
		$objectEntry = new ProductList();
		$objectEntry->loadByID($this->getData('list_id'));
		$objectEntry->delete();
		RedirectSQ('list_id=');
	}
	
	function edit_list(){
		
		renderURL('action=edit_list', '', true);
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		$GetVars = &$Register->get(VAR_GET);
		$PostVars = &$Register->get(VAR_POST);

		if(isset($PostVars['searchstring'])){
			
			renderURL('searchstring=', urlencode($PostVars['searchstring']), '', true);
			$GetVars['searchstring'] = urlencode($PostVars['searchstring']);
		}

		$searchstring = isset($GetVars['searchstring'])?urldecode($GetVars['searchstring']):'';
		
		$objectEntry = new ProductList();
		$res = $objectEntry->loadByID($this->getData('list_id'));
		if(!$res)RedirectSQ('?ukey=product_lists');
		
		$this->_search_products($searchstring);
		if($searchstring)$smarty->assign('searchstring', $searchstring);
		
		$smarty->assign('productList', $objectEntry);
		$smarty->assign('products', $objectEntry->getProducts());
		
		$smarty->assign('admin_sub_dpt', 'product_list.edit.html');
	}
	
	function save_lists(){
		
		$data = scanArrayKeysForID($this->getData(), 'name');
		$objectEntry = new ProductList();
		foreach ($data as $list_id => $_values){

			$res = $objectEntry->loadByID($list_id);
			if(!$res)continue;
			
			$objectEntry->name = $_values['name'];
			$objectEntry->save();
		}
		
		if($this->getData('id')||$this->getData('name')){
			
			$objectEntry = new ProductList();
			$objectEntry->id = trim($this->getData('id'));
			$objectEntry->name = trim($this->getData('name'));
			$res = $objectEntry->checkInfo();
			if(PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage(), '', array('Data' => $this->getData()));
			
			$eObject = new ProductList();
			$res = $eObject->loadByID($objectEntry->id);
			if($res)Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'prdlist_list_id_reserved', '', array('Data' => $this->getData()));
			
			$objectEntry->save(true);
		}
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/
		
		$product_lists = ProductList::stc_getLists(false);
		
		Message::loadData2Smarty('new_list');
		
		$smarty->assign('product_lists', $product_lists);
		$smarty->assign('admin_sub_dpt', 'product_lists.html');
	}
}

ActionsController::exec('ProductListsController');
?>