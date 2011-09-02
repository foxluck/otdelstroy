<?php
	class CurrenciesSetupController extends ActionsController {

		function delete_curreny(){

			currDeleteCurrency($this->getData('CID'));
			die;
		}
		
		function save_exchange_rate(){
			
			$new_rate = floatval(str_replace(',', '.', $this->getData('new_rate')));
			if($new_rate<=0){
				Message::raiseAjaxMessage(MSG_ERROR, 0, 'curr_enter_positive_rate');
				die;
			}
			$currencyEntry = new Currency();
			$currencyEntry->loadByCID($this->getData('CID'));
			$currencyEntry->currency_value = $new_rate;
			$currencyEntry->save();
			
			Message::raiseAjaxMessage(MSG_SUCCESS, 0, 'msg_information_save');
			die;
		}
		
		function save_currency(){
			
			$currencyEntry = new Currency();
			$currencyEntry->loadByCID($this->getData('CID'));
			$currencyEntry->loadFromArray($this->getData());
			$res = $currencyEntry->checkInfo('form');
			if(PEAR::isError($res)){
				Message::raiseAjaxMessage(MSG_ERROR, 0, $res->getMessage());
				die;
			}
			
			if(!$this->getData('CID')){
				$currencyEntry->currency_value = 1;
			}
			$currencyEntry->save();
			
			Message::raiseAjaxMessage(MSG_SUCCESS, 0, 'msg_information_save');
			global $_RESULT;
			$_RESULT['form_data'] =  $currencyEntry->getVars();
			$_RESULT['form_data']['output_example'] = $currencyEntry->getView(12345.67);
			LanguagesManager::ml_fillFields(CURRENCY_TYPES_TABLE, $_RESULT['form_data']);
			die;
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			
			$currencies = currGetAllCurrencies();
			foreach ($currencies as $key => $currency){
				
				$currencyEntry = new Currency();
				$currencyEntry->loadFromArray($currency);
				$currencies[$key]['output_example'] = $currencyEntry->getView(12345.67);
				if($currencyEntry->CID == CONF_DEFAULT_CURRENCY)
				$smarty->assign('default_currency', $currencies[$key]);
			}

			$smarty->assign('currencies', $currencies);
			
			$decimal_symbols = array(',', '.');
			$decimal_places = array(0,1,2,3,4,5,6);
			$thousands_delimiters = array(translate('curr_nothing') => '', translate('curr_space') => '_', ',' => ',', '.' => '.', '`' => '`');

			$smarty->assign('decimal_symbols', $decimal_symbols);
			$smarty->assign('decimal_places', $decimal_places);
			$smarty->assign('thousands_delimiters', $thousands_delimiters);

			$default_values = array('decimal_places' => 2, 'thousands_delimiter' => ',', 'decimal_symbol' => '.', LanguagesManager::ml_getLangFieldName('display_template', LanguagesManager::getDefaultLanguage()) => '{value}');
			$smarty->assign('currency_default_values', $default_values);
			$smarty->assign('admin_sub_dpt', 'conf_currencies.tpl.html');
		}
	}
	
	ActionsController::exec('CurrenciesSetupController');
?>