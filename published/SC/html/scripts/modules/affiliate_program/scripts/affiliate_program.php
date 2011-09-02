<?php
	$SubPage = isset($_GET['sub'])?$_GET['sub']:'balance';
	$fACTION = isset($_POST['fACTION'])?$_POST['fACTION']:'';
	
	$customerID 				= regGetIdByLogin( $_SESSION["log"] );
	$affp_CustomersNum 			= affp_getCustomersNum($customerID);
	
	#post-requests handler
	switch ($fACTION){
		case 'SAVE_SETTINGS':
			affp_saveSettings($customerID, 
				isset($_POST['EmailOrders']),
				isset($_POST['EmailPayments']));
			Redirect(set_query('save_settings=ok'));
			break;
	}
	
	#loading data for subpages
	switch ($SubPage){
		case 'balance':
			$Commissions 	= affp_getCommissionsAmount($customerID);
			$Payments 		= affp_getPaymentsAmount($customerID);
			$smarty->assign('CommissionsNumber', count($Commissions));
			$smarty->assign('PaymentsNumber', count($Payments));
			$smarty->assign('CommissionsAmount', $Commissions);
			$smarty->assign('PaymentsAmount', $Payments);
			$smarty->assign('CurrencyISO3', currGetAllCurrencies());
			break;
		case 'payments_history':
			$Payments 		= affp_getPayments($customerID);
			$smarty->assign('PaymentsNumber', count($Payments));
			$smarty->hassign('Payments', (affp_getPayments($customerID, '', '', '', 'pID ASC')));
			break;
		case 'settings':
			$smarty->assign('SettingsSaved', isset($_GET['save_settings']));
			$smarty->assign('Settings', affp_getSettings($customerID));
			break;
		case 'attract_guide':
			$smarty->assign('_AFFP_STRING_ATTRACT_GUIDE', str_replace(
				array('{URL}', '{aff_percent}', '{login}'), 
				array(set_query('?refid='.$customerID, CONF_FULL_SHOP_URL),CONF_AFFILIATE_AMOUNT_PERCENT, $_SESSION["log"]), translate("affp_attract_guide")));
			break;
		
	}
	
	$smarty->assign('CONF_AFFILIATE_EMAIL_NEW_PAYMENT', CONF_AFFILIATE_EMAIL_NEW_PAYMENT);
	$smarty->assign('CONF_AFFILIATE_EMAIL_NEW_COMMISSION', CONF_AFFILIATE_EMAIL_NEW_COMMISSION);
	$smarty->assign('affiliate_customers', $affp_CustomersNum);
	$smarty->assign('SubPage', $SubPage);
	$smarty->assign('CurrentSubTpl', 'affiliate_program.tpl.html');
?>