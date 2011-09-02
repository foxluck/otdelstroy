<?php
include_once(DIR_FUNC.'/affiliate_functions.php' );

class AffiliateProgram extends Module {
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'short_affiliate_program' => array(
				'name' => 'Партнерская программа - кратко',
				'method' => 'methodShortAffiliateProgram',
			),
			'fbalance' => array(
				'name' => 'Баланс (кабинет пользователя)',
				'method' => 'methodFBalance',
			),
			'fpayments_history' => array(
				'name' => 'История выплат (кабинет пользователя)',
				'method' => 'methodFPaymentsHistory',
			),
			'fsettings' => array(
				'name' => 'Настройки (кабинет пользователя)',
				'method' => 'methodFSettings',
			),
			'fattract_guide' => array(
				'name' =>  'Как привлечь покупателя (кабинет пользователя)',
				'method' => 'methodFAttractGuide',
			),
			'b_custord_affpr' => array(
				'name' => 'Управление программой (админка)',
				'method' => 'methodBCustordAffPr',
			),
			'buser_info' => array(
				'name' => 'Информация о пользователе (админка)',
				'type' => INTDIVAVAILABLE,
			),
		);
	}

	function methodBCustordAffPr(){
		
		global $smarty;
		
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/custord_affiliate.php');
	}
	
	function methodFAttractGuide(){
		
		if(!CONF_AFFILIATE_PROGRAM_ENABLED || !isset($_SESSION['log']) || !$_SESSION['log'])RedirectSQ('?ukey=page_not_found');
		
		$this->_initUserAccountSubs();
		global $smarty;
		
		$_GET['sub'] = 'attract_guide';
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/affiliate_program.php');
	}

	function methodFSettings(){
		
		if(!CONF_AFFILIATE_PROGRAM_ENABLED || !isset($_SESSION['log']) || !$_SESSION['log'])RedirectSQ('?ukey=page_not_found');
		
		$this->_initUserAccountSubs();
		global $smarty;
		
		$_GET['sub'] = 'settings';
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/affiliate_program.php');
	}

	function methodFPaymentsHistory(){
		
		if(!CONF_AFFILIATE_PROGRAM_ENABLED || !isset($_SESSION['log']) || !$_SESSION['log'])RedirectSQ('?ukey=page_not_found');
		
		$this->_initUserAccountSubs();
		global $smarty;
		
		$_GET['sub'] = 'payments_history';
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/affiliate_program.php');
	}

	function methodFBalance(){
		
		if(!CONF_AFFILIATE_PROGRAM_ENABLED || !isset($_SESSION['log']) || !$_SESSION['log'])RedirectSQ('?ukey=page_not_found');
		
		$this->_initUserAccountSubs();
		global $smarty;
		
		$_GET['sub'] = 'balance';
		include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/affiliate_program.php');
	}
	
	function methodShortAffiliateProgram(){

		if(!CONF_AFFILIATE_PROGRAM_ENABLED || !isset($_SESSION['log']) || !$_SESSION['log'])return '';
		
		global $smarty;
		$customerID = regGetIdByLogin( $_SESSION["log"] );
		$SubDivs = DivisionModule::getChildDivisions(DivisionModule::getDivisionIDByUnicKey('affiliate_program'), array('xEnabled'=>1));
		$_TC = count($SubDivs);
		$SubDivsInfo = array();
		for ($y=0; $y<$_TC; $y++){
			
			$SubDivsInfo[] = array(
				'name' => $SubDivs[$y]->Name,
				'id' => $SubDivs[$y]->ID,
				'unickey' => $SubDivs[$y]->UnicKey,
				'ukey' => $SubDivs[$y]->UnicKey,
			);
		}
		$smarty->assign('SubDivs', $SubDivsInfo);
		$smarty->assign('affiliate_customers', affp_getCustomersNum($customerID));
		$smarty->assign('CONF_AFFILIATE_EMAIL_NEW_PAYMENT', CONF_AFFILIATE_EMAIL_NEW_PAYMENT);
		$smarty->assign('CONF_AFFILIATE_EMAIL_NEW_COMMISSION', CONF_AFFILIATE_EMAIL_NEW_COMMISSION);
		
		return 'short_affiliate_program.tpl.html';
	}
	
	function _initUserAccountSubs(){
		
		global $smarty, $CurrDivision;
		$smarty->assign('main_content_template', 'user_account_sub.tpl.html');
		$UserAccountDivs = &DivisionModule::getChildDivisions(DivisionModule::getDivisionIDByUnicKey('office'), array('xEnabled'=>1));
		$_TC = count($UserAccountDivs);
		$UserAccountDivsInfo = array();
		for($j = 0; $j<$_TC;$j++){
			
			$UserAccountDivsInfo[] = array(
				'name' => $UserAccountDivs[$j]->Name,
				'id' => $UserAccountDivs[$j]->ID,
			);
		}
		$smarty->assign('UserAccountDivs', $UserAccountDivsInfo);
		
		$SubDivs = DivisionModule::getChildDivisions(DivisionModule::getDivisionIDByUnicKey('affiliate_program'), array('xEnabled'=>1));
		$_TC = count($SubDivs);
		$SubDivsInfo = array();
		for ($y=0; $y<$_TC; $y++){
			
			$SubDivsInfo[] = array(
				'name' => $SubDivs[$y]->Name,
				'id' => $SubDivs[$y]->ID,
				'unickey' => $SubDivs[$y]->UnicKey,
			);
		}
		$smarty->assign('SubDivs', $SubDivsInfo);
	}
}
?>