<?php
/**
 * @connect_module_class_name CInvoicePhys
 * @package DynamicModules
 * @subpackage Payment
 */
// Модуль формирования квитанции на оплату для физических

define('CINVOICEPHYS_DB_TABLE', DBTABLE_PREFIX.'_module_payment_invoice_phys');

class CInvoicePhys extends PaymentModule {

	var $type = PAYMTD_TYPE_MANUAL;
	var $language = 'rus';
	var $default_logo = 'http://www.webasyst.net/collections/design/payment-icons/receipt.gif';
	
	var $DB_TABLE = '';
	
	function _initVars(){
		
		parent::_initVars();
		$this->connected_printforms[] = 'invoicephys';
		$this->title 		= "Квитанция";
		$this->description 	= "Модуль формирования квитанции на оплату";
		$this->sort_order 	= 2;
		
		$this->Settings = array(
				"CONF_PAYMENTMODULE_INVOICE_PHYS_CURRENCY",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_DESCRIPTION",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_EMAIL_HTML_INVOICE",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_COMPANYNAME",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_ACCOUNT_NUMBER",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_INN",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_KPP",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_BANKNAME",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_KOR_NUMBER",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_BIK",
				"CONF_PAYMENTMODULE_INVOICE_PHYS_SECOND_NAME"
			);
	}

	function _initSettingFields(){

		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_CURRENCY'] = array(
			'settings_value' 		=> '0', 
			'settings_title' 			=> 'Валюта квитанции', 
			'settings_description' 	=> 'Выберите валюту, в которой будет указываться сумма в квитанции. Если тип вылюты не определен, то квитанция будет выписываться в той валюте, которая выбрана пользователем при оформлении заказа', 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_DESCRIPTION'] = array(
			'settings_value' 		=> 'Оплата заказа №[orderID]', 
			'settings_title' 			=> 'Описание покупки', 
			'settings_description' 	=> 'Укажите описание платежей. Вы можете использовать строку <i>[orderID]</i> - она автоматически будет заменена на номер заказа', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_EMAIL_HTML_INVOICE'] = array(
			'settings_value' 		=> '1', 
			'settings_title' 			=> 'Отправлять покупателю HTML-квитанцию', 
			'settings_description' 	=> 'Включите эту опцию, если хотите, чтобы покупателю автоматически отправлялась квитанция в HTML-формате. Если опция выключена, то покупателю будет отправлена ссылка на квитанцию на сайте магазина', 
			'settings_html_function' 	=> 'setting_CHECK_BOX(', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_COMPANYNAME'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Название компании', 
			'settings_description' 	=> 'Укажите название организации, от имени которой выписывается квитанция', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_ACCOUNT_NUMBER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Расчетный счет', 
			'settings_description' 	=> 'Номер расчетного счета организации', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_INN'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'ИНН', 
			'settings_description' 	=> 'ИНН организации', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_KPP'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'КПП', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_BANKNAME'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Наименование банка', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_KOR_NUMBER'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Корреспондентский счет', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_BIK'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'БИК', 
			'settings_description' 	=> '', 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_PAYMENTMODULE_INVOICE_PHYS_SECOND_NAME'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> 'Отчество', 
			'settings_description' 	=> 'Выберите из списка поле в форме регистрации отвечающее за отчество покупателя — одно из полей, которое можно добавить в разделе Настройки &raquo; Форма регистрации и оформления заказов', 
			'settings_html_function' 	=> 'setting_SELECT_BOX(CInvoicePhys::_getCustomerFields(),', 
			'sort_order' 			=> 1,
		);


		//создать таблицу, в которую будет записывать информацию для квитанции
		// - сумма к оплате в выбранной валюте
		if(!db_table_exists(CINVOICEPHYS_DB_TABLE)){
			
			$sql = 'CREATE TABLE '.CINVOICEPHYS_DB_TABLE.' 
		(`module_id` int(10) unsigned default NULL,
  		`orderID` int(11) default NULL,
  		`order_amount_string` varchar(64) default NULL
		) DEFAULT CHARSET=utf8';
			db_query($sql);
		}
	}
	
	function _getCustomerFields()
	{
		$fields = GetRegFields();
		$res = array('не указано:0');
		foreach($fields as $field){
			 $res[] = xHtmlSpecialChars($field['reg_field_name'].':'.$field['reg_field_ID']);
		}
		return implode(',',$res);		
	}

	function after_processing_php( $orderID )
	{
		//сохранить сумму квитанции
		$orderID = (int) $orderID;
		$order = ordGetOrder( $orderID );
		if ($order)
		{
			$q = db_query("select count(*) from ".CINVOICEPHYS_DB_TABLE."  where orderID=$orderID AND module_id='{$this->ModuleConfigID}'");
			$row = db_fetch_row($q);
			if ($row[0] > 0) //удалить все старые записи
			{
				db_query("delete from ".CINVOICEPHYS_DB_TABLE." where orderID=$orderID AND module_id='{$this->ModuleConfigID}'");
			}

			//добавить новую запись
			$dbq = "INSERT INTO ?#CINVOICEPHYS_DB_TABLE (module_id, orderID, order_amount_string) VALUES (?,?,?)";
			db_phquery($dbq,$this->ModuleConfigID, $orderID, show_price($order["order_amount"], $this->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_CURRENCY')));

			//отправить квитанцию покупателю по электронной почте
			if ($this->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_EMAIL_HTML_INVOICE') == 1) //html
			{

				$mySmarty = new ViewSC; //core smarty object
				//define smarty vars
				$mySmarty->template_dir = DIR_FTPLS;
				$mySmarty->hassign( "billing_lastname", $order["billing_lastname"] );
				if(($secondNameID = $this->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_SECOND_NAME'))){				
					$regFields = GetRegFieldsValuesByOrderID($orderID);
					foreach($regFields as $regField){
						if($regField["reg_field_ID"]!=$secondNameID)continue;
						$mySmarty->hassign('second_name', $regField['reg_field_value']);
						break;
					}
				}
				$mySmarty->hassign( "billing_firstname", $order["billing_firstname"] );
				$mySmarty->hassign( "billing_city", $order["billing_city"] );
				$mySmarty->hassign( "billing_address", $order["billing_address"] );
				$mySmarty->hassign( "invoice_description", str_replace("[orderID]", (string)$order['orderID_view'], $this->_getSettingValue('CONF_PAYMENTMODULE_INVOICE_PHYS_DESCRIPTION')) );

				//сумма квитанции
				$q = db_query("select order_amount_string from ".CINVOICEPHYS_DB_TABLE." where orderID=$orderID AND module_id='{$this->ModuleConfigID}'");
				$row = db_fetch_row($q);
				if ($row) //сумма найдена в файле с описанием квитанции
				{
					$mySmarty->assign( "invoice_amount", $row[0] );
				}
				else //сумма не найдена - показываем в текущей валюте
				{
					$mySmarty->assign( "invoice_amount", show_price($order["order_amount"]) );
				}
		
				$invoice_data = array(
					'CONF_PAYMENTMODULE_INVOICE_PHYS_COMPANYNAME' => '',
					'CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_ACCOUNT_NUMBER' => '',
					'CONF_PAYMENTMODULE_INVOICE_PHYS_INN' => '',
					'CONF_PAYMENTMODULE_INVOICE_PHYS_KPP' => '',
					'CONF_PAYMENTMODULE_INVOICE_PHYS_BANKNAME' => '',
					'CONF_PAYMENTMODULE_INVOICE_PHYS_BANK_KOR_NUMBER' => '',
					'CONF_PAYMENTMODULE_INVOICE_PHYS_BIK' => '',
				);
				foreach ($invoice_data as $k=>$v){
					$invoice_data[$k] = $this->_getSettingValue($k);
				}
				
				
				$mySmarty->assign('invoice_data', $invoice_data);
				$invoice = $mySmarty->fetch("invoice_phys.tpl.html");

				ss_mail($order["customer_email"],"Квитанция на оплату", $invoice, 2);
			}
			else //ссылка на квитанцию
			{
				$URLprefix = trim( CONF_FULL_SHOP_URL );
				$URLprefix = str_replace("http://",  "", $URLprefix);
				$URLprefix = str_replace("https://", "", $URLprefix);
				if ($URLprefix[ strlen($URLprefix)-1 ] == '/')
				{
					$URLprefix = substr($URLprefix, 0, strlen($URLprefix)-1 );
				}

				//$invoice_url = set_query("?ukey=invoice_phys&moduleID=".$this->ModuleConfigID."&orderID=$orderID&order_time=" . base64_encode( $order["order_time_mysql"] ) . "&customer_email=" . base64_encode( $order["customer_email"] ),"http://".$URLprefix);
				$invoice_url = set_query("?ukey=print_form&orderID={$orderID}&form_class=invoicephys&order_time=".base64_encode( $order["order_time_mysql"] )."&customer_email=".base64_encode( $order["customer_email"] ),CONF_FULL_SHOP_URL);

				ss_mail($order["customer_email"],"Квитанция на оплату","Здравствуйте!\n\nСпасибо за Ваш заказ.\nКвитанцию на оплату Вы можете посмотреть и распечатать по адресу:\n" . $invoice_url . "\n\nС уважением,\n".CONF_SHOP_NAME, 2);
			}

		}

		return "";
	}

	function after_processing_html( $orderID) 
	{
		//открыть окно с квитанцией
		$order = ordGetOrder( $orderID );

		if(!$this->ModuleConfigID){
			
			$sql = '
				SELECT module_id FROM '.MODULES_TABLE.' WHERE module_name="'.$this->title.'"
			';
			@list($this->ModuleConfigID) = db_fetch_row(db_query($sql));
		}
		$res = "";
		$query = "?ukey=invoice_phys&moduleID="
				.$this->ModuleConfigID
				."&orderID={$orderID}&order_time="
				.base64_encode( $order["order_time_mysql"] )
				."&customer_email="
				.base64_encode( $order["customer_email"]);
		$query = "?ukey=print_form&form_class=invoicephys"
				."&orderID={$orderID}&order_time="
				.base64_encode( $order["order_time_mysql"] )
				."&customer_email="
				.base64_encode( $order["customer_email"] );					

		$res .="<script type=\"text/javascript\">\n".
		"<!--
			show_invoice_phys = function(){open_window('".set_query($query)."',700,600);}\n".
		"// -->
</script>\n";
		$res .=
			'<form action="'.xHtmlSetQuery($query).'" method="GET" target="_blank">
			<input type="button" value="Распечатать квитанцию"  onclick="show_invoice_phys();return false;"/>
			</form>';
		return $res;
	}

	function uninstall($_ModuleConfigID = 0){

		PaymentModule::uninstall($_ModuleConfigID);
		
		if(!count(modGetModuleConfigs(get_class($this)))){
			
			//удалить таблицу с информацией о счетах
			db_query("DROP TABLE IF EXISTS ".CINVOICEPHYS_DB_TABLE);
		}else {
			
			$sql = '
				DELETE FROM '.CINVOICEPHYS_DB_TABLE.' WHERE module_id="'.$this->ModuleConfigID.'"
			';
		}
	}
}
?>