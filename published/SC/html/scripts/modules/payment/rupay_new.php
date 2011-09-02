<?php

/**
 * @connect_module_class_name CRuPayNew
 * @package DynamicModules
 * @subpackage Payment
 */
class CRuPayNew extends PaymentModule
{
    var $language = 'obsolete_module';
    var $type = PAYMTD_TYPE_ONLINE;
    
    function _initVars()
    {
        parent::_initVars();
        
        $callback_url = $_SERVER['HTTP_HOST'].'/published/SC/html/scripts/rupay.php';
        
		$this->title 		= 'RUpay (новый способ интеграции, март 2008)';
		$this->description 	= str_replace('{0}', $callback_url, 'Обновленный модуль для платежной системы RUpay (www.rupay.com), учитывающий последние изменения интеграции с их платежной системой.<br> <strong><i>ВНИМАНИЕ:</i> Устаревший модуль. Используйте модуль RBK Money (платежная система RUpay теперь называется RBK Money).</strong>');
		$this->sort_order 	= 1;
        
        $this->Settings = array(
            'CONF_PAYMENT_RUPAYNEW_ESHOPID'
           ,'CONF_PAYMENT_RUPAYNEW_SECRET'
        );
    }
	
    function _initSettingFields()
    {
		$this->SettingsFields['CONF_PAYMENT_RUPAYNEW_ESHOPID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Номер сайта продавца', 
			'settings_description' 	=> 'Номер вашего аккаунта в платежной системе RUpay, на который будет поступать оплата по заказам.', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		
		$this->SettingsFields['CONF_PAYMENT_RUPAYNEW_SECRET'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Секретный ключ', 
			'settings_description' 	=> 'Ваш секретный ключ в системе RUpay, известный только вам. Необходим для проверки ответа от платежной системы RUpay.', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
    }
	
	function after_processing_html($order_id)
	{
	    $Register = &Register::getInstance();
	    $smarty = &$Register->get(VAR_SMARTY);
	    
	    $order_info = ordGetOrder($order_id);

        $order_info['order_amount'] = number_format($order_info['order_amount'], 2, '.', '');

	    $smarty->assign('order_id', $order_id);
	    $smarty->assign('eshop_id', $this->_getSettingValue('CONF_PAYMENT_RUPAYNEW_ESHOPID'));
	    $smarty->assign('order_info', $order_info);
		$smarty->assign('success_url', getTransactionResultURL('success'));
		$smarty->assign('fail_url', getTransactionResultURL('failure'));
	    
	    return $smarty->fetch('../payment/rupay-new.html');
	}
};

?>