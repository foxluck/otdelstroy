<?php
//require_once(DIR_CLASSES.'/class.virtualmodule.php');
define('PAYMTD_TYPE_CC', 'cc');
define('PAYMTD_TYPE_DIRECT_CC', 'cc_direct');
define('PAYMTD_TYPE_ONLINE', 'online');
define('PAYMTD_TYPE_MANUAL', 'manual');
define('PAYMTD_TYPE_REPLACE', 'replace');
define('PAYMTD_TYPE_OBSOLETE', 'obsolete');

/**
 * @package DynamicModules
 */
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
	
	/**
	 * Path to logo image
	 *
	 * @var string
	 */
	var $default_logo = '';
	
	/**
	 * array of classnames connected printforms
	 *
	 * @var array(string)
	 */
	protected $connected_printforms = array();
	
	/**
	 * 
	 * @param $_ModuleConfigID
	 * @return PaymentModule
	 */
	static function &getInstance($_ModuleConfigID)
	{
		return parent::getInstance($_ModuleConfigID,PAYMENT_MODULE);
	}
	
	static function &getClassInstance($class = null,$_ModuleConfigID = 0)
	{
		$payment_module = parent::getClassInstance($class,$_ModuleConfigID,self::getModuleDirectory(PAYMENT_MODULE));
		return $payment_module;
	}

	static function &getModules($language = null, $method_type = null,$class = null)
	{
		$payment_modules = array();
		if($class){
			if($payment_module = self::getClassInstance($class)){
				/*@var $objectModule PaymentModule*/
				$payment_module->className = $class;
				$payment_modules[] = $payment_module;
			}
		}
		
		if(!count($payment_modules)){
			$payment_modules = parent::getModulesInDirectory(GetFilesInDirectory( DIR_MODULES.'/payment', 'php'/*,'class\..+' */));			
		}
		//for($j_max = count($payment_modules)-1; $j_max>=0; $j_max--){
		foreach($payment_modules as $id=>&$payment_module){
			/*@var $payment_module PaymentModule*/
			if( !is_null($method_type) && $payment_module->type != $method_type){
				unset($payment_modules[$id]);
			}elseif(!is_null($language)&&($payment_module->language)&&($payment_module->language!=$language)){
				unset($payment_modules[$id]);
			}elseif(!is_null($class)&&(get_class($payment_module)!=$class)){
				unset($payment_modules[$id]);
			}
			
		}
			
		$sort_function = create_function('$a, $b','
			if(strtolower(get_class($a)) == \'manualpayment\')return 1;
			if(strtolower(get_class($b)) == \'manualpayment\')return -1;
			if(strtolower(get_class($a)) == \'cmanualccprocessing\')return -1;
			if(strtolower(get_class($b)) == \'cmanualccprocessing\')return 1;
			return strcmp($a->title, $b->title);');
		usort($payment_modules, $sort_function);
		return $payment_modules;

	}
	
	function _initVars(){

		$this->method_title = translate('mdlc_'.strtolower(get_class($this)).'_title', false);
		$this->method_description = translate('mdlc_'.strtolower(get_class($this)).'_description', false);
		parent::_initVars();
	}

	function __construct($_ModuleConfigID = 0){

		$this->LanguageDir = DIR_MODULES.'/payment/languages/';
		$this->ModuleType = PAYMENT_MODULE;
		$this->MethodsTable = PAYMENT_TYPES_TABLE;
		parent::__construct($_ModuleConfigID);
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
	function after_processing_html( $orderID, $active = true )
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

			$Module = PaymentModule::getInstance($method_info['module_id']);
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

	function transactionResultHandler($transaction_result = '',$message = '',$source = 'frontend'){
		switch($transaction_result){
			case 'success':
			case 'failure':
			case 'result':
			case 'cancel':
			case 'decline':
			case 'check':
				if($source == 'frontend'){
					$Register = &Register::getInstance();
					/* @var $Register Register */
					$smarty = $Register->get(VAR_SMARTY);
					/* @var $smarty View*/
					$smarty->assign('additionalMessage', $message);
					$smarty->assign('TransactionResult', $transaction_result);
					$smarty->assign( 'main_content_template', 'transaction_result.tpl.html');
				}elseif($source == 'handler'){
					print "TransactionResult: {$transaction_result}\n";
					print "additionalMessage: {$message}\n";
				}else{
					print "unknown source: {$source}\n";
					print "TransactionResult: {$transaction_result}\n";
					print "additionalMessage: {$message}\n";
				}

			default:
				break;
		}
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

	function getTransactionResultURL($transaction_result,$params = array()){

		$scURL = preg_replace(array("@^https?://@","@/.+$@"),array('',''), trim( CONF_FULL_SHOP_URL ));
		$scURL = "http://".$scURL;

		if(count($params)){
			$secret_key = $this->generateSecureKey($params);

		}else{
			$secret_key = null;
		}
		$scURL .= set_query('?ukey=transaction_result&transaction_result='.$transaction_result.
		($this->ModuleConfigID?'&modConfID='.$this->ModuleConfigID:'').
		($secret_key?'&secure_key='.$secret_key:'').'&view=&step=&did=');
		$scURL = preg_replace('@([^:]{1})//@','\\1/',$scURL);
		return $scURL;
		return set_query('ukey=transaction_result&transaction_result='.$transaction_result.
		($this->ModuleConfigID?'&modConfID='.$this->ModuleConfigID:'').
		($secret_key?'&secure_key='.$secret_key:''),
		$scURL);
	}

	function getDirectTransactionResultURL($transaction_result,$params = array(),$https = false){
		$scURL = preg_replace("@^https?://@",'', trim( BASE_WA_URL ));
		if(SystemSettings::is_hosted()){
			$scURL .= 'shop/';
		}else{
			$scURL .= 'published/SC/html/scripts/';
		}
		$scURL = "http".($https?'s':'')."://".$scURL.'callbackhandlers/paymenthandler.php';

		if(count($params)){
			$secret_key = $this->generateSecureKey($params);

		}else{
			$secret_key = null;
		}
		$get = array();
		if($this->ModuleConfigID){
			$get[] = 'modConfID='.$this->ModuleConfigID;
		}
		$get[] = 'transaction_result='.$transaction_result;
		if($secret_key){
			$get[] = 'secure_key='.$secret_key;
		}
		if(count($get)){
			$scURL .= '?'.implode('&',$get);
		}
		return $scURL;
		return set_query(($this->ModuleConfigID?'&modConfID='.$this->ModuleConfigID:'').
		'&transaction_result='.$transaction_result.
		($secret_key?'&secure_key='.$secret_key:''),
		$scURL);//,false,true);

	}

	/**
	 * validate secure_key for transaction handler
	 *
	 * @param array $params
	 * @return boolean
	 */
	function validateResultKey($params = array()){
		$key = isset($_GET['secure_key'])?$_GET['secure_key']:'';
		$validKey = $this->generateSecureKey($params);
		//log_error(1024,var_export(array($validKey,$key),true),__FILE__,__LINE__);
		return ($key == $validKey)?true:false;
	}

	function generateSecureKey($params){
		if(!is_array($params)){
			$params = array($params);
		}

		$params['config_id'] = $this->ModuleConfigID;
		$params[] = SystemSettings::get('DB_USER');
		$params[] = SystemSettings::get('DB_PASS');
		//log_error(1024,var_export($params,true),__FILE__,__LINE__);
		return md5(implode('%',$params));
	}
	
	function getConnectedPrintforms()
	{
		return $this->connected_printforms;
	}
	
	
	static function _getStatuses(){

		$OStatuses = ostGetOrderStatues();
		$_OSt = array(
		' '.':-1'
				);
		$TC = count($OStatuses);
		for($_j = 0; $_j<$TC;$_j++){
				
			$_OSt[] = xHtmlSpecialChars($OStatuses[$_j]['status_name']).':'.$OStatuses[$_j]['statusID'];
		}
		return implode(',',$_OSt);
	}
}
?>