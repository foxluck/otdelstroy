<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
class EraseProductsController extends ActionsController {
	
	function erase_products(){
		
		imDeleteAllProducts();
		TagManager::removeTags();
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'prdcat_products_erased', '', array('name'=>'success'));
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$smarty = &$Register->get(VAR_SMARTY);
		/*@var $smarty Smarty*/

		$smarty->assign('admin_sub_dpt', 'erase_products.htm');
	}
}

ActionsController::exec('EraseProductsController');
?>