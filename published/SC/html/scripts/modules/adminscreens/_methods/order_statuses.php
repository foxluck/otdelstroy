<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
class OrderStatusesController extends ActionsController{

	function save_statuses(){
		
		safeMode(true);
	
		if (!LanguagesManager::ml_isEmpty('status_name', $this->getData())){
			
			ostAddOrderStatus($this->getData(), $this->getData('sort_order'), $this->getData('color'), $this->getData('bold'), $this->getData('italic'));
		}
		
		$updateData = scanArrayKeysForID($_POST, array( 'color', 'bold', 'italic','status_name_\w{2}', 'sort_order' ) );
		foreach( $updateData as $key => $value ){
			
			if(false&&ost_isPredefinedStatus($key)){
			
				db_phquery('UPDATE ?#ORDER_STATUSES_TABLE SET color=?, bold=?, italic=? WHERE statusID=?', $value['color'], (int)isset($value['bold']), (int)isset($value['italic']), $key);
			}else{
				
				ostUpdateOrderStatus( $key, $value, $value['sort_order'], $value['color'], isset($value['bold']), isset($value['italic']));
			}
		}

		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', translate("msg_saved_changes"));
	}
	
	function delete_status(){
		
		safeMode(true, 'statusID=');
			
		if ( !ostDeleteOrderStatus( $this->getData('statusID') ) ){
			Message::raiseMessageRedirectSQ(MSG_ERROR, 'statusID=', translate("err_couldnt_delete_order_status"));		
		}

		RedirectSQ( 'statusID=' );
	}
	
	function main(){
		
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/* @var $smarty Smarty */
		
		$smarty->assign('custom_statuses', ost_getOrderStatuses(false) );
		$smarty->assign('predefined_statuses', ost_getOrderStatuses(true) );
		$smarty->assign('admin_sub_dpt', 'order_statuses.html');		
	}
}

ActionsController::exec('OrderStatusesController');
?>