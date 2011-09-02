<?php
/**
 * @connect_module_class_name RussianpostLabel113
 * @package DynamicModules
 * @subpackage PrintForms
 * @_type module
 * @_sub_type russianpost
 * @_language rus
 * @_side backend
 * @_name Бланк Почты России ф. 113эн (наложенный платеж)
 * @_description  ф. 113эн (наложенный платеж)
 * @__no_settings -
 */
class RussianpostLabel113 extends Forms
{
	function _initSettingFields()
	{
		parent::_initSettingFields();
		
		 
		 $this->SettingsFields['COMPANY_NAME'] = array(
		 'settings_value' 		=> CONF_SHOP_NAME,
		 'settings_title' 			=> 'Получатель наложенного платежа (магазин)',
		 'settings_description' 	=> 'Для юридического лица — полное или краткое наименование; для гражданина — ФИО полностью.',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );
		 
		 $this->SettingsFields['ADDRESS1'] = array(
		 'settings_value' 		=> '',
		 'settings_title' 			=> 'Адрес получателя наложенного платежа (магазина), строка 1',
		 'settings_description' 	=> 'Почтовый адрес получателя наложенного платежа.',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );
		 $this->SettingsFields['ADDRESS2'] = array(
		 'settings_value' 		=> '',
		 'settings_title' 			=> 'Адрес получателя наложенного платежа (магазина), строка 2',
		 'settings_description' 	=> 'Заполните, если адрес не помещается в одну строку.',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );
		 $this->SettingsFields['POST_CODE'] = array(
		 'settings_value' 		=> '',
		 'settings_title' 			=> 'Индекс получателя наложенного платежа (магазина)',
		 'settings_description' 	=> 'Индекс должен состоять ровно из 6 цифр.',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );
		 $this->SettingsFields['INN'] = array(
		 'settings_value' 		=> '',
		 'settings_title' 			=> 'ИНН получателя наложенного платежа (магазина)',
		 'settings_description' 	=> 'Заполняется только для юридических лиц. 10 цифр.',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );
		 $this->SettingsFields['BANK_KOR_NUMBER'] = array(
		 'settings_value' 		=> '',
		 'settings_title' 			=> 'Кор. счет получателя наложенного платежа (магазина)',
		 'settings_description' 	=> 'Заполняется только для юридических лиц. 20 цифр.',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );
		 $this->SettingsFields['BANK_NAME'] = array(
		 'settings_value' 		=> '',
		 'settings_title' 			=> 'Наименование банка получателя наложенного платежа (магазина)',
		 'settings_description' 	=> 'Заполняется только для юридических лиц.',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );
		 $this->SettingsFields['BANK_ACCOUNT_NUMBER'] = array(
		 'settings_value' 		=> '',
		 'settings_title' 			=> 'Расчетный счет получателя наложенного платежа (магазина)',
		 'settings_description' 	=> 'Заполняется только для юридических лиц. 20 цифр.',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );
		 $this->SettingsFields['BIK'] = array(
		 'settings_value' 		=> '',
		 'settings_title' 			=> 'БИК получателя наложенного платежа (магазина)',
		 'settings_description' 	=> 'Заполняется только для юридических лиц. 9 цифр.',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );
		 
		 $this->SettingsFields['COLOR'] = array(
		 'settings_value' 		=> '1',
		 'settings_title' 			=> 'Печатать желтую полосу',
		 'settings_description' 	=> '',
		 'settings_html_function' 	=> 'checkbox',
		 'sort_order' 			=> 1,
		 );
		 
		/*
		 $this->SettingsFields['COMPANY_NAME'] = array(
		 'settings_value' 		=> CONF_SHOP_NAME,
		 'settings_title' 			=> 'print_forms_enabled',
		 'settings_description' 	=> 'print_form_enabled_description',
		 'settings_html_function' 	=> 'text',
		 'sort_order' 			=> 1,
		 );*/

	}

	function display($strict = true)
	{
		@ini_set('memory_limit','32M');
		if($orderID = intval($_GET["orderID"])){
			$order = ordGetOrder($orderID);
		}else{
			$order = false;
		}
		if($order && $strict && !$this->verifyOrderData($order)){
			unset($order);
		}
		
		if($orderID&&!$order){
			die('Заказ не найден');
		}
		
		if($order){
			$ShippingModule = ShippingRateCalculator::getInstance($order['shipping_module_id']);
			/*@var $InvoiceModule RussianPost*/
			if(!$ShippingModule||!($ShippingModule instanceof RussianPost)){
				die ("печатная форма не применима");
			}
			$custom_currency = $ShippingModule->_getSettingValue('CONF_RUSSIANPOST_CURRENCY');
			if($custom_currency){
				$currencyEntry = new Currency();
				$currencyEntry->loadByCID($custom_currency);
			}else{
				$currencyEntry = Currency::getDefaultCurrencyInstance();
			}
			/*@var $currencyEntry Currency*/
			$RUR_rate = $currencyEntry->currency_value;
			
			$order['order_amount'] *= $RUR_rate;
	
			$order['rub'] = round(floor($order['order_amount']));
			$order['cop'] = round ($order['order_amount']*100 - round( floor($order['order_amount'])*100 ) );;
			
			$order['order_amount'] = Currency::stringView($order['order_amount']);
			
			
			foreach($order as $key=>&$value){
				if(isset($_GET[$key])){
					$value = $_GET[$key];
				}
			}
			
			foreach($order as $key=>&$value){
				if(isset($_POST[$key])){
					$value = $_POST[$key];
					$_GET[$key] = $_POST[$key];
				}
			}
		}
			
		
		$side = isset($_GET['side'])?$_GET['side']:($order?'':'print');	
		$image_path = dirname($this->template_path);
		$smarty = &Core::getSmarty();
		/*@var $smarty Smarty */
	
		switch($side){
			case 'front':
				$image_info = null;
				if($image = $this->read($image_path.'/f113en_front.gif',$image_info)){
					if($this->COLOR){
						if($image_stripe = $this->read($image_path.'/f113en_stripe.gif',$image_info)){
							imagecopy($image,$image_stripe,808,663,0,0,$image_info[0],$image_info[1]);
						}
					}
					$this->printOnImage($image,sprintf('%d',$order['rub']),1730,670);
					$this->printOnImage($image,sprintf('%02d',$order['cop']),1995,670);
					$this->printOnImage($image,$order['order_amount'],856,735,30);
					$this->printOnImage($image,$this->COMPANY_NAME,915,800);
					$this->printOnImage($image,$this->ADDRESS1,915,910);
					$this->printOnImage($image,$this->ADDRESS2,824,975);
					$this->printOnImagePersign($image,$this->POST_CODE, 1985, 1065);
					$this->printOnImagePersign($image,$this->INN, 920, 1135);
					$this->printOnImagePersign($image,$this->BANK_KOR_NUMBER, 1510, 1135);
					$this->printOnImage($image,$this->BANK_NAME, 1160, 1194);
					$this->printOnImagePersign($image,$this->BANK_ACCOUNT_NUMBER, 1018, 1250);
					$this->printOnImagePersign($image,$this->BIK, 1885, 1250);
					
					header("Content-type: image/gif");
					imagegif($image);
					exit;
				}
				break;
			case 'back':
				$image_info = null;
				if($image = $this->read($image_path.'/f113en_back.gif',$image_info)){
					header("Content-type: image/gif");
					imagegif($image);
					exit;
				}
				break;
			case 'print':
				if(!$strict&&!$order){
					$smarty->assign('action','preview');
				}
				$smarty->assign('editable',false);
				break;
			default:
				if(!$strict&&!$order){
					$smarty->assign('action','preview');
				}
				$smarty->assign('order',$order);
				$smarty->assign('editable',true);
				break;
		}
		
		parent::display($strict);
	}
	function printOnImage(&$image,$text,$x,$y,$font_size = 35)
	{
		$y += $font_size;
		static $font_path = null;
		static $text_color = null;
		if(is_null($font_path)){
			$font_path = dirname($this->template_path).'/arial.ttf';
			$font_path = (file_exists($font_path) && function_exists('imagettftext')) ? $font_path : false;
		}
		if(is_null($text_color)){
			
			$text_color	= ($this->COLOR&&false)?ImageColorAllocate($image, 32, 32, 96):ImageColorAllocate($image, 16, 16, 16);
		}
		if ($font_path){
			imagettftext($image, $font_size, 0, $x, $y, $text_color, $font_path, $text);
		}else{
			imagestring($image, $font_size, $x, $y, $text, $text_color);
		}
	}
	function printOnImagePersign(&$image,$text,$x,$y,$cell_size = 34,$font_size = 35)
	{
		$size = mb_strlen($text,'UTF-8');
		for($i=0;$i<$size;$i++){
			$this->printOnImage($image,mb_substr($text,$i,1,'UTF-8'),$x,$y,$font_size);
			$x += 	$cell_size;
		}
	}
	function read($fileName, &$info){

		$info = @getimagesize($fileName);
		if (!$info) return FALSE;
		switch ($info[2]) {
			case 1:
				// Create recource from gif image
				$srcIm = @imagecreatefromgif( $fileName );
				break;
			case 2:
				// Create recource from jpg image
				$srcIm = @imagecreatefromjpeg( $fileName );
				break;
			case 3:
				// Create resource from png image
				$srcIm = @imagecreatefrompng( $fileName );
				break;
			case 5:
				// Create resource from psd image
				break;
			case 6:
				// Create recource from bmp image imagecreatefromwbmp
				$srcIm = @imagecreatefromwbmp( $fileName );
				break;
			case 7:
				// Create resource from tiff image
				break;
			case 8:
				// Create resource from tiff image
				break;
			case 9:
				// Create resource from jpc image
				break;
			case 10:
				// Create resource from jp2 image
				break;
			default:
				break;
		}

		if (!$srcIm) return FALSE;
		else return $srcIm;
	}
}
?>