<?php
/**
 * @connect_module_class_name RussianpostLabel116
 * @package DynamicModules
 * @subpackage PrintForms
 * @_type module
 * @_sub_type russianpost
 * @_language rus
 * @_side backend
 * @_name Бланк Почты России ф. 116 (отправление) 
 * @_description  ф. 116 (отправление)
 * @__no_settings -
 */
class RussianpostLabel116 extends Forms
{
	function _initSettingFields()
	{
		parent::_initSettingFields();
		
		 
		 $this->SettingsFields['COMPANY_NAME'] = array(
		 'settings_value' 		=> '',
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
		if($order&&$strict&&!$this->verifyOrderData($order)){
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
	
			$order['order_amount_d'] = round(floor($order['order_amount']));
			$order['order_price_d'] = $order['order_amount_d'];
			$side = isset($_GET['side'])?$_GET['side']:'';
			$order['shipping_address'] = str_replace(array("\n","\r"),' ',$order['shipping_address']);
			$order['shipping_address'] = preg_replace('/\s+/msi',' ',$order['shipping_address']);
			$order['shipping_address_1'] = $order['shipping_city'].($order['shipping_state']?", {$order['shipping_state']}":'');
			$order['shipping_address_2'] = $order['shipping_address'];
			$order['shipping_name'] = implode(' ',array($order['shipping_firstname'],$order['shipping_lastname']));
			$order['order_amount'] = Currency::stringView($order['order_amount']);
			$order['order_price'] = $order['order_amount'];
			
			$order['COMPANY_NAME'] = $this->COMPANY_NAME;
			$order['ADDRESS_1'] = $this->ADDRESS1;
			$order['ADDRESS_2'] = $this->ADDRESS2;
			$order['POST_CODE'] = $this->POST_CODE;
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
			
			$order['POST_CODE'] = substr($order['POST_CODE'],0,6);
			$order['shipping_zip'] = substr($order['shipping_zip'],0,6);
		}
			
		$side = isset($_GET['side'])?$_GET['side']:($order?'':'print');	
		
		$image_path = dirname($this->template_path);
		$smarty = &Core::getSmarty();
		/*@var $smarty Smarty */
		switch($side){
			case 'front':
				$image_info = null;
				if($image = $this->read($image_path.'/f116_front.gif',$image_info)){
					
					$this->printOnImage($image,$order['order_amount'],294, 845,18);
					$this->printOnImage($image,$order['order_price'],294, 747,18);
					//customer
					$this->printOnImage($image,$order['shipping_name'],390, 915);
					$this->printOnImage($image,$order['shipping_address_1'],390, 975);
					$this->printOnImage($image,$order['shipping_address_2'],300, 1040);
											
					$this->printOnImagePersign($image,$order['shipping_zip'],860, 1105);
					
					//company
					$this->printOnImage($image,$this->COMPANY_NAME, 420, 1170);
					$this->printOnImage($image,$this->ADDRESS1, 400, 1237);
					$this->printOnImage($image,$this->ADDRESS2, 300, 1304);
					$this->printOnImagePersign($image,$this->POST_CODE, 1230, 1304);
					
					//additional
					$this->printOnImage($image,$order['order_price_d'],590, 2003);
					$this->printOnImage($image,$order['order_amount_d'],1280, 2003);
					
					$this->printOnImage($image,$order['shipping_name'],390, 2085);
					
					$this->printOnImage($image,$order['shipping_address_1'],390, 2170);
					$this->printOnImage($image,$order['shipping_address_2'],300, 2260);
											
					$this->printOnImagePersign($image,$order['shipping_zip'],1230, 2260);
					
					
					header("Content-type: image/gif");
					imagegif($image);
					exit;
				}
				break;
			case 'back':
				$image_info = null;
				if($image = $this->read($image_path.'/f116_back.gif',$image_info)){
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
				$smarty->assign('order',$order);
				break;
			default:
				if(!$strict&&!$order){
					$smarty->assign('action','preview');
				}
				$smarty->assign('editable',true);
				$smarty->assign('order',$order);
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
	function printOnImagePersign(&$image,$text,$x,$y,$cell_size = 55,$font_size = 35)
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