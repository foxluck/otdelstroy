<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
class cattreeActions extends ActionsController {
	
	function getCategoryProducts(){
		
		global $_RESULT;
		
		$per_page = 20;
		$navigatorParams = array('offset' => intval($this->getData('offset')), 'CountRowOnPage' => $per_page);
		$callbackParams = array('categoryID'=>$this->getData('categoryID'));
		if($this->getData('productID'))$callbackParams['!productID'] = $this->getData('productID');
		
		$_RESULT['products'] = prdSearchProductByTemplate( $callbackParams, $count_row, $navigatorParams);
		if($count_row>($navigatorParams['offset'] + $per_page)){
			
			$_RESULT['next_offset'] = $navigatorParams['offset'] + $per_page;
		}
		if($navigatorParams['offset']>0){
			
			$_RESULT['prev_offset'] = $navigatorParams['offset'] - $per_page;
			if($_RESULT['prev_offset']<0)$_RESULT['prev_offset'] = 0;
		}
		
		die;
	}
	
	function expandCategory(){
		
		if((int)$this->getData('return_subs')){
			
			global $_RESULT;
			$_RESULT['categories'] = _recursiveGetCategoryCList( $this->getData('categoryID'), 0, $_SESSION["expandedCategoryID_Array"], 'NUM', false );
		}
		
		catExpandCategory($this->getData('categoryID'), 'expandedCategoryID_Array');
		
		die;
	}
	
	function collapseCategory(){
		
		catShrinkCategory($this->getData('categoryID'), 'expandedCategoryID_Array');
		die;
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/* @var $smarty Smarty */
		$GetVars = &$Register->get(VAR_GET);
		
		$categories = catGetCategoryCList( $_SESSION["expandedCategoryID_Array"] );
		$js_action = isset($GetVars['js_action'])?$GetVars['js_action']:'';
		
		$smarty->assign('categories', $categories);
		$smarty->assign('js_action', $GetVars['js_action']);
	}
}

ActionsController::exec('cattreeActions');
?>