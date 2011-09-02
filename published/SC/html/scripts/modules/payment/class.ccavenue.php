<?php
// ccavenue payment module

/**
 * @connect_module_class_name CCAvenue
 * @package DynamicModules
 * @subpackage Payment
 */

class CCAvenue extends PaymentModule 
{
	var $type = PAYMTD_TYPE_CC;
	var $language = 'eng';
	
	function _initVars(){
		
		parent::_initVars();
		$this->title 		= CCAVENUE_TTL;
		$this->description 	= CCAVENUE_DSCR;
		$this->Settings = array(
			'CONF_CCAVENUE_MERCHANT_ID',
			'CONF_CCAVENUE_WORKING_KEY',
			'CONF_CCAVENUE_INR_CURRENCY',
			);
	}

	function _initSettingFields(){
		
		$this->SettingsFields['CONF_CCAVENUE_MERCHANT_ID'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CCAVENUE_CFG_MERCHANT_ID_TTL, 
			'settings_description' 	=> CCAVENUE_CFG_MERCHANT_ID_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CCAVENUE_WORKING_KEY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CCAVENUE_CFG_WORKING_KEY_TTL, 
			'settings_description' 	=> CCAVENUE_CFG_WORKING_KEY_DSCR, 
			'settings_html_function' 	=> 'setting_TEXT_BOX(0,', 
			'sort_order' 			=> 1,
		);
		$this->SettingsFields['CONF_CCAVENUE_INR_CURRENCY'] = array(
			'settings_value' 		=> '', 
			'settings_title' 			=> CCAVENUE_CFG_INR_CURRENCY_TTL, 
			'settings_description' 	=> CCAVENUE_CFG_INR_CURRENCY_DSCR, 
			'settings_html_function' 	=> 'setting_CURRENCY_SELECT(', 
			'sort_order' 			=> 1,
		);
	}

	function after_processing_html( $orderID ) 
	{
		$order = ordGetOrder( $orderID );

		$Amount = $this->_convertCurrency($order['order_amount'], 0, $this->_getSettingValue('CONF_CCAVENUE_INR_CURRENCY'));
		$Checksum = $this->_getCheckSum($this->_getSettingValue('CONF_CCAVENUE_MERCHANT_ID'),$Amount,$orderID ,getTransactionResultURL('success'),$this->_getSettingValue('CONF_CCAVENUE_WORKING_KEY'));
//For new version SS			
//		$Checksum = $this->_getCheckSum($this->_getSettingValue('CONF_CCAVENUE_MERCHANT_ID'),$Amount,$orderID ,getTransactionResultURL('module_handler', $this->ModuleConfigID),$this->_getSettingValue('CONF_CCAVENUE_WORKING_KEY'));
		$res = '
			<form method="post" action="https://www.ccavenue.com/shopzone/cc_details.jsp" style="text-align:center">
			<input type=hidden name=Merchant_Id value="'.$this->_getSettingValue('CONF_CCAVENUE_MERCHANT_ID').'">
			<input type=hidden name=Amount value="'. $Amount.'">
			<input type=hidden name=Order_Id value="'. $orderID.'">'.
			'<input type=hidden name=Redirect_Url value="'.getTransactionResultURL('success').'">'.
//For new version SS			
//			'<input type=hidden name=Redirect_Url value="'.getTransactionResultURL('module_handler', $this->ModuleConfigID).'">'.
			'<input type=hidden name=Checksum value="'. $Checksum.'">
			<input type="hidden" name="billing_cust_name" value="'.$order['billing_firstname'].' '.$order['billing_lastname'].'"> 
			<input type="hidden" name="billing_cust_address" value="'. $order['billing_address'].'"> 
			<input type="hidden" name="billing_cust_country" value="'. $order['billing_country'].'"> 
			<input type="hidden" name="billing_cust_tel" value=""> 
			<input type="hidden" name="billing_cust_email" value="'. $order['customer_email'].'"> 
			<input type="hidden" name="delivery_cust_name" value="'. $order['shipping_firstname'].' '.$order['shipping_lastname'].'"> 
			<input type="hidden" name="delivery_cust_address" value="'. $order['shipping_address'].'"> 
			<input type="hidden" name="delivery_cust_tel" value=""> 
			<input type="hidden" name="delivery_cust_notes" value=""> 
			<input type="hidden" name="Merchant_Param" value=""> 
			<input type="submit" value="'.CCAVENUE_TXT_SUBMIT.'">
			</form>
		';

		return $res;
	}
	
//For new version SS			
	function transactionResultHandler(){
		
		global $smarty;
/*

	This is the sample RedirectURL PHP script. It can be directly used for integration with CCAvenue if your application is developed in PHP. You need to simply change the variables to match your variables as well as insert routines for handling a successful or unsuccessful transaction.

	return values i.e the parameters namely Merchant_Id,Order_Id,Amount,AuthDesc,Checksum,billing_cust_name,billing_cust_address,billing_cust_country,billing_cust_tel,billing_cust_email,delivery_cust_name,delivery_cust_address,delivery_cust_tel,billing_cust_notes,Merchant_Param POSTED to this page by CCAvenue. 

*/

		foreach ($_POST as $_Var=>$_Val){
			
			$$_Var = $_Val;
		}
		
		$WorkingKey = "" ; //put in the 32 bit working key in the quotes provided here
		
		$Checksum = $this->_verifyChecksum($Merchant_Id, $Order_Id , $Amount,$AuthDesc,$Checksum,$WorkingKey);
			
	
		if($Checksum=="true" && $AuthDesc=="Y")
		{
			$Message = CCAVENUE_TXT_1;
			
			//Here you need to put in the routines for a successful 
			//transaction such as sending an email to customer,
			//setting database status, informing logistics etc etc
		}
		else if($Checksum=="true" && $AuthDesc=="B")
		{
			$Message = CCAVENUE_TXT_2;
			
			//Here you need to put in the routines/e-mail for a  "Batch Processing" order
			//This is only if payment for this transaction has been made by an American Express Card
			//since American Express authorisation status is available only after 5-6 hours by mail from ccavenue and at the "View Pending Orders"
		}
		else if($Checksum=="true" && $AuthDesc=="N")
		{
			$Message = CCAVENUE_TXT_3;
			
			//Here you need to put in the routines for a failed
			//transaction such as sending an email to customer
			//setting database status etc etc
		}
		else
		{
			$Message = CCAVENUE_TXT_4;
			
			//Here you need to simply ignore this and dont need
			//to perform any operation in this condition
		}
		$smarty->assign('TransactionResult', $_GET['transaction_result']);
		$smarty->assign('Message', $Message);
		$smarty->assign('main_content_template', 'transaction_result.tpl.html');
	}
	
	function _getCheckSum($MerchantId,$Amount,$OrderId ,$URL,$WorkingKey){
		
		$str ="$MerchantId|$OrderId|$Amount|$URL|$WorkingKey";
		$adler = 1;
		$adler = $this->_adler32($adler,$str);
		return $adler;
	}

	function _verifyChecksum($MerchantId,$OrderId,$Amount,$AuthDesc,$CheckSum,$WorkingKey)
	{
		$str = "$MerchantId|$OrderId|$Amount|$AuthDesc|$WorkingKey";
		$adler = 1;
		$adler = $this->_adler32($adler,$str);
		
		if($adler == $CheckSum)
			return "true" ;
		else
			return "false" ;
	}

	function _adler32($adler , $str)
	{
		$BASE =  65521 ;
	
		$s1 = $adler & 0xffff ;
		$s2 = ($adler >> 16) & 0xffff;
		for($i = 0 ; $i < strlen($str) ; $i++)
		{
			$s1 = ($s1 + Ord($str[$i])) % $BASE ;
			$s2 = ($s2 + $s1) % $BASE ;
				//echo "s1 : $s1 <BR> s2 : $s2 <BR>";
	
		}
		return $this->_leftshift($s2 , 16) + $s1;
	}

	function _leftshift($str , $num)
	{
	
		$str = DecBin($str);
	
		for( $i = 0 ; $i < (64 - strlen($str)) ; $i++)
			$str = "0".$str ;
	
		for($i = 0 ; $i < $num ; $i++) 
		{
			$str = $str."0";
			$str = substr($str , 1 ) ;
			//echo "str : $str <BR>";
		}
		return $this->_cdec($str) ;
	}

	function _cdec($num)
	{
	
		for ($n = 0 ; $n < strlen($num) ; $n++)
		{
		   $temp = $num[$n] ;
		   $dec =  $dec + $temp*pow(2 , strlen($num) - $n - 1);
		}
	
		return $dec;
	}
}
?>