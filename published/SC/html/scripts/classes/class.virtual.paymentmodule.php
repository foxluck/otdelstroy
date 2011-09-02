<?php
require_once(DIR_CLASSES.'/class.virtual.module.php');
define('PAYMTD_TYPE_CC', 'cc');
define('PAYMTD_TYPE_ONLINE', 'online');
define('PAYMTD_TYPE_MANUAL', 'manual');
define('PAYMTD_TYPE_REPLACE', 'replace');

class PaymentModule extends virtualModule {

	var $type = '';
	var $language = '';
	/**
	 * Use for adding shipping methods
	 *
	 * @var string
	 */
	var $method_title = '';
	/**
	 * Use for adding shipping methods
	 *
	 * @var string
	 */
	var $method_description = '';

	function _initVars(){

		$this->method_title = translate('mdlc_'.strtolower(get_class($this)).'_title', false);
		$this->method_description = translate('mdlc_'.strtolower(get_class($this)).'_description', false);
		parent::_initVars();
	}

	function PaymentModule($_ModuleConfigID = 0){

		$this->LanguageDir = DIR_MODULES.'/payment/languages/';
		$this->ModuleType = PAYMENT_MODULE;
		$this->MethodsTable = PAYMENT_TYPES_TABLE;
		virtualModule::virtualModule($_ModuleConfigID);
	}

	// *****************************************************************************
	// Purpose	html form to get information from customer about payment,
	//			this functions does not return <form> </form> tags - these tags are already defined in
	//			the
	// Inputs
	// Remarks
	// Returns	nothing
	function payment_form_html()
	{
		return "";
	}

	// *****************************************************************************
	// Purpose	core payment processing routine
	// Inputs   $order is array with the following elements:
	//	"customer_email" - customer's email address
	//	"customer_ip" - customer IP address
	//	"order_amount" - total order amount (in conventional units)
	//	"currency_code" - currency ISO 3 code (e.g. USD, GBP, EUR)
	//	"currency_value" - currency exchange rate defined in the backend in 'Configuration' -> 'Currencies' section
	//	"shipping_info" - shipping information - array of the following data:
	//		"first_name", "last_name", "country_name", "state", "zip", "city", "address"
	//	"billing_info" - billing information - array of the following data:
	//		"first_name", "last_name", "country_name", "state", "zip", "city", "address"
	// Remarks
	function payment_process($order)
	{
		return 1;
	}

	/**
	 * PHP code executed after order has been placed
	 *
	 * @param int $orderID
	 * @return mixed
	 */
	function after_processing_php($orderID)
	{
		return "";
	}

	/**
	 * html code printed after order has been placed and after_processing_php has been executed
	 *
	 * @param int $orderID
	 * @return string
	 */
	function after_processing_html( $orderID )
	{
		return "";
	}

	/**
	 * Filter payment method info
	 *
	 * @param array $method_info: array(PID, Name, description, Enabled, sort_order, email_comments_text, module_id, calculate_tax)
	 * @return array: filtered method info
	 */
	function filterPaymentMethod($method_info){

		do{

			if(!$method_info['module_id'])break;

			$Module = modGetModuleObj($method_info['module_id'], PAYMENT_MODULE);
			if(!is_object($Module))break;

			$method_info = $Module->_filterPaymentMethod($method_info);
		}while (0);

		return $method_info;
	}

	function _filterPaymentMethod($method_info){

		return $method_info;
	}

	function getAllowedOrderActions($orderID){

		if(is_object($orderID)){
			$order = get_object_vars($orderID);
		}elseif(is_array($orderID)){
			$order = $orderID;
		}else{
			$order = ordGetOrder($orderID);
		}
		$orderID = $order['orderID'];
		$order_statusID = $order['statusID'];

		$availble_actions = array();

		if(ost_isPredefinedStatus($order_statusID)){
			if($order_statusID == CONF_ORDSTATUS_PENDING)
			$availble_actions[] = ORDACTION_PROCESS;
			if($order_statusID != CONF_ORDSTATUS_CANCELLED){
				if($order_statusID != CONF_ORDSTATUS_DELIVERED)$availble_actions[] = ORDACTION_DELIVER;
				$availble_actions[] = ORDACTION_CANCEL;
			}
			if($order_statusID == CONF_ORDSTATUS_CANCELLED)
			$availble_actions[] = ORDACTION_RESTORE;

		}else{
			$availble_actions = array(ORDACTION_CANCEL, ORDACTION_DELIVER, ORDACTION_PROCESS);
		}
		return $availble_actions;
	}

	function isAllowedOrderAction($action, $orderID){

		return in_array($action, $this->getAllowedOrderActions($orderID));
	}

	function transactionResultHandler($transaction_result = '',$message = ''){
		switch($transaction_result){
			case 'success':
			case 'failure':
			case 'result':
			case 'cancel':
				$Register = &Register::getInstance();
				/* @var $Register Register */
				$smarty = $Register->get(VAR_SMARTY);
				/* @var $smarty View*/
				$smarty->assign('additionalMessage', $message);
				$smarty->assign('TransactionResult', $transaction_result);
				$smarty->assign( 'main_content_template', 'transaction_result.tpl.html');

			default:
				break;
		}
	}

	function validateResultKey($params = null){
		$key = isset($_GET['secure_key'])?$_GET['secure_key']:'';
		if(!is_array($params)){
			$params = array($params);
		}
		$params = array_merge($params,$this->ModuleConfigID);
		$validKey = generateSecureKey($params);
		return ($key == $validKey);
	}

	function oncancel(){
		;
	}

	function onprocess(){
		;
	}

	function onrestore(){
		;
	}

	function ondeliver(){
		;
	}

	function onrefund(){
		;
	}

	function oncharge(){
		;
	}

	function uninstall($_ConfigID = 0){

		$_ConfigID = (int)$_ConfigID?(int)$_ConfigID:$this->ModuleConfigID;
		parent::uninstall($_ConfigID);

		db_phquery('UPDATE ?#ORDERS_TABLE SET payment_module_id=0 WHERE payment_module_id=?', $_ConfigID);
	}

	protected function inputCCYear($fieldName,$fieldDescription = 'Year',$defaultValue = null,$size = 4){
		$CurrYear4d = date('Y');
		$CurrYear2d = date('y');
		$ExpYears = "\n<select name=\"{$fieldName}\">";
		$ExpYears .= "\n<optgroup label=\"{$fieldDescription}\">\n";
		for($_Y = 0; $_Y<10; $_Y++){

			$_Selected = isset($defaultValue)?($defaultValue==(($size==2?$CurrYear2d:$CurrYear4d)+$_Y)):0;
			$ExpYears .= '<option value="'.(($size==2?$CurrYear2d:$CurrYear4d)+$_Y).'"'.($_Selected?' selected="selected"':'').'>'.($CurrYear4d+$_Y).'</option>';
		}
		$ExpYears .= "</select>\n";
		return $ExpYears;

	}

	protected function inputCCMonth($fieldName,$fieldDescription = 'Month',$defaultValue = null){
		global $rMonths;
		$ExpMonths = "\n<select name=\"{$fieldName}\">";
		$ExpMonths .= "\n<optgroup label=\"{$fieldDescription}\">\n";
		for($_M = 1; $_M<=12; $_M++){

			$_Selected = isset($defaultValue)?($defaultValue==($_M)):0;
			$ExpMonths .= '<option value="'.$_M.'"'.($_Selected?' selected="selected"':'').'>'.$rMonths[$_M].'</option>';
		}
		$ExpMonths .= "</select>\n";
		return $ExpMonths;
	}
}
?>