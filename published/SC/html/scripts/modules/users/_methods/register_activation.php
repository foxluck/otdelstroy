<?php
/**
 * Activation of registration
 */
if(CONF_ENABLE_REGCONFIRMATION){
	
	/* @var $smarty Smarty */
	$smarty = &Core::getSmarty();
	
	$ActErr = false;
	if(isset($_GET['act_code'])){
		
		if($_GET['act_code']){
			
			$sql = '
				SELECT customerID, Login, cust_password FROM '.CUSTOMERS_TABLE.'
				WHERE ActivationCode="'.xEscapeSQLstring($_GET['act_code']).'"
				AND ActivationCode<>"" AND ActivationCode IS NOT NULL
			';
			$Result = db_query($sql);
			$Customer = db_fetch_row($Result);
			
			if(isset($Customer['Login'])&&$Customer['Login']){
	
				regActivateCustomer($Customer['customerID']);
				regAuthenticate($Customer['Login'], Crypt::PasswordDeCrypt($Customer['cust_password'], null) );
				if (isset($_GET['order2'])&&xDataExists('xREGMAILCONF_URLORDER2')) {
					Redirect(xPopData('xREGMAILCONF_URLORDER2'));
				}else {
					RedirectSQ('&act_code=&act_ok=1');
				}
			}else{
				
				$smarty->hassign('ActCode', $_GET['act_code']);
				$ActErr = true;
			}
		}else {
			
			$ActErr = true;
		}
	}
	
	if(isset($_GET['act_ok']))$smarty->assign('ActOk', 1);
	if(isset($_GET['notact']))$smarty->assign('NoAct', 1);
	if($ActErr)$smarty->assign('ActErr', 1);
	$smarty->assign('main_content_template', 'register_activation.tpl.html');
}
?>