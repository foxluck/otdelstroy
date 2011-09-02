<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	class ChangeDefaultCurrencyController extends ActionsController {

		function set_default_currency(){
			
			if($this->getData('btn_cancel')){
				RedirectSQ('?ukey=currencies');
			}
			
			$currencyEntry = new Currency();
			if($currencyEntry->loadByCID($this->getData('new_default_currency'))===false)return;
			
			$res = $currencyEntry->setDefault();
			if(PEAR::isError($res)){
				
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
			}
			
			RedirectSQ('saved=ok');
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			
			$smarty->assign('currencies', currGetAllCurrencies());
			
			$smarty->assign('admin_sub_dpt', 'change_default_currency.html');
		}
	}
	
	ActionsController::exec('ChangeDefaultCurrencyController');
?>