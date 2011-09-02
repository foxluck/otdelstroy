<?php
//require_once(DIR_CLASSES.'/class.virtual.module.php');

/**
 * Parent for all rate calculators modules
 * @package DynamicModules
 */
class ShippingRateCalculator extends virtualModule {

	/**
	 * Use for filtering modules by administration language in hosted
	 *
	 * @var 'rus'|'eng'
	 */
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
	 * @return ShippingRateCalculator
	 */
	static function getInstance($_ModuleConfigID)
	{
		return parent::getInstance($_ModuleConfigID, SHIPPING_RATE_MODULE);
	}

	function _initVars(){

		$this->method_title = translate('mdlc_'.strtolower(get_class($this)).'_title', false);
		$this->method_description = translate('mdlc_'.strtolower(get_class($this)).'_description', false);
		parent::_initVars();
	}

	function __construct($_ModuleConfigID = 0){

		$this->LanguageDir = DIR_MODULES.'/shipping/languages/';
		$this->ModuleType = SHIPPING_RATE_MODULE;
		$this->MethodsTable = SHIPPING_METHODS_TABLE;
		parent::__construct($_ModuleConfigID);
	}

	function _getServiceType($_ServiceID){

		$ShippingTypes = $this->_getShippingTypes();
		foreach ($ShippingTypes as $_Type=>$_Services)
		if(in_array($_ServiceID, $_Services))
		return $_Type;
		return '';
	}

	function _convertDecLBStoPoundsOunces($_Dec){

		return array(
		'lbs' => floor($_Dec),
		'oz' => ceil(16*($_Dec - floor($_Dec))),
		);
	}

	/**
	 * Return list of rates for services
	 *
	 * @param array $_Services
	 * @param array $order
	 * @param array $address
	 */
	function _getRates(&$_Services,  $order, $address){

		$Query 		= $this->_prepareQuery($_Services,  $order, $address);
		$Answer 		= $this->_sendQuery($Query);
		$parsedAnswer 	= $this->_parseAnswer($Answer);
		if(!is_array($parsedAnswer)){
			$_Services = null;
			
			return $parsedAnswer;
		}
		$newServices 		= array();

		foreach($_Services as $_ind=>&$_Service){
			$s_id = $_Service['id'];
			if(isset($parsedAnswer[$s_id])){
				foreach ($parsedAnswer[$_Service['id']] as $_indV=>$_Variant){
					$newServices[] = array(
					'id' => sprintf("%02d%02d", $s_id, $_indV),
					'name' => $_Variant['name'],
					'rate' => $_Variant['rate'],
					);
				}
			}
			unset($_Service);
		}
		$_Services = $newServices;
	}

	/**
	 * Return information by available shipping services
	 * The same for all shipping modules
	 *
	 * @param array $order
	 * @param array $address
	 * @param integer $_shServiceID
	 * @return array 'name'=>'<Service name>', 'id'=><Service ID>, 'rate'=>'<Service Rate>'
	 */
	function calculate_shipping_rate($order, $address, $_shServiceID = 0){

		$_shServiceID = (int)$_shServiceID;
		if($_shServiceID>99){
				
			if(strlen($_shServiceID)<4)$_shServiceID = sprintf("%04d", $_shServiceID);
			$_orinServiceID = $_shServiceID;
			list($_shServiceID, $_serviceOffset) = sscanf($_shServiceID, "%02d%02d");
		}
		$Rates = array();
		if($_shServiceID){
				
			$AvailableServices = $this->getShippingServices();
			$Rates[] = array(
			'name' 		=> (isset($AvailableServices[$_shServiceID]['name'])?$AvailableServices[$_shServiceID]['name']:''),
			'code' 		=> (isset($AvailableServices[$_shServiceID]['code'])?$AvailableServices[$_shServiceID]['code']:''),
			'id' 	=> $_shServiceID,
			'rate' 		=> 0,
			);
		}else {

			$AvailableServices = $this->_getServicesByCountry($address['countryID']);
			foreach ($AvailableServices as $_Service){

				$_Service['rate'] = 0;
				$Rates[] = $_Service;
			}
		}

		$this->_getRates($Rates, $order, $address);
		
		if(isset($_orinServiceID)){
				
			if(isset($Rates[$_serviceOffset])){
				$Rates = array($Rates[$_serviceOffset]);
			}else {
				$Rates = array(array(
				'name' 		=> '',
				'id' 	=> 0,
				'rate' 		=> 0,
				));
			}
		}
		if(is_array($Rates) && !count($Rates)){
			$Rates = array(array(
			'name' 		=> '',
			'id' 	=> 0,
			'rate' 		=> 0,
			));
		}
		return $Rates;
	}

	#заглушка
	function allow_shipping_to_address(){

		return true;
	}

	/**
	 * Convert from one Measurement to another Measurement
	 *
	 * @param unknown_type $_Units
	 * @param unknown_type $_From
	 * @param unknown_type $_To
	 */
	function _convertMeasurement($_Units, $_From, $_To){

		switch (strtolower($_From).'_'.strtolower($_To)){
				
			case 'lb_kg':
			case 'lbs_kgs':
			case 'lbs_kg':
			case 'lb_kgs':
				$_Units = $_Units/2.2046;
				break;
			case 'kg_lb':
			case 'kg_lbs':
			case 'kgs_lb':
			case 'kgs_lbs':
				$_Units = $_Units*2.2046;
				break;
			case 'g_lb':
			case 'g_lbs':
				$_Units = $_Units/1000*2.2046;
				break;
			case 'lb_g':
			case 'lbs_g':
				$_Units = $_Units/2.2046*1000;
				break;
			case 'g_kg':
			case 'g_kgs':
				$_Units = $_Units/1000;
		}

		return $_Units;
	}

	function _getOrderWeight(&$Order){

		$TC = count($Order['orderContent']['cart_content']);
		$OrderWeight = 0;
		$ShippingProducts = 0;

		for( $i = 0; $i<$TC; $i++ ){
				
			$Product = GetProduct($Order['orderContent']['cart_content'][$i]['productID']);
			if($Product['free_shipping'])continue;
			$ShippingProducts++;
			if(!isset($Product['weight']))continue;
			if(!$Product['weight'])continue;
			$OrderWeight += $Order['orderContent']['cart_content'][$i]['quantity']*$Product['weight'];
		}
		if($OrderWeight<=0 && $ShippingProducts)$OrderWeight=0.1;

		return $OrderWeight;
	}

	function _getShippingProducts($_Order){

		$Products = array();
		$_TC = count($_Order['orderContent']['cart_content'])-1;
		for (; $_TC>=0;$_TC--){
				
			if($_Order['orderContent']['cart_content'][$_TC]['free_shipping'])continue;
			$Products[] = $_Order['orderContent']['cart_content'][$_TC];
		}
		return $Products;
	}

	/*
	 abstract methods
	 */

	/**
	 * Return array of shipping types
	 */
	function _getShippingTypes(){

		return array();
	}

	/**
	 * Return services for country
	 *
	 * @param integer $_CountryID - country id
	 */
	function _getServicesByCountry(){

		return $this->getShippingServices();
	}

	/**
	 * Return list of shipping services
	 *
	 * @param string $_Type shipping type (Domestic, Inrenational)
	 * @return array
	 */
	function getShippingServices(){return array();}

	/**
	 * Return list of  enabled shipping services
	 *
	 * @param string $_Type shipping type
	 * @param bool $classes_combined
	 * @return array
	 */
	function getAvailableServices($_Type = '', $classes_combined = false){

		$services = $this->getShippingServices($_Type);
		$available_services = array();
		foreach ($services as $k=>$service){
				
			if($this->serviceIsAvailable($service)){

				if($classes_combined){
						
					$classes = $this->getAvailableClasses();
					if(count($classes)){

						foreach ($classes as $class){
								
							$available_services[] = array(
							'id' => sprintf('%02d%02d',$service['id'],$class['id']),
							'name' => $service['name'].': '.$class['descr']
							);
						}
					}else{

						$service['id'] = sprintf('%02d00',$service['id']);
						$available_services[] = $service;
					}
				}else{
						
					$available_services[] = $service;
				}
			}
		}
		return $available_services;
	}

	function serviceIsAvailable($service){

		return true;
	}

	function getAvailableClasses(){

		$classes = $this->_getClasses();
		$available_classes = array();
		foreach ($classes as $k=>$class){
				
			if($this->classIsAvailable($class)){
				$available_classes[] = $class;
			}
		}
		return $available_classes;
	}

	function _getClasses(){

		return array();
	}

	function classIsAvailable($class){

		return true;
	}

	function _prepareQuery(&$_Services,  $order, $address){

		return $this->_prepareXMLQuery($_Services,  $order, $address);
	}

	function _sendQuery($_Query){

		return $this->_sendXMLQuery($_Query);
	}

	function _parseAnswer($_Answer){

		return $this->_parseXMLAnswer($_Answer);
	}

	function _sendXMLQuery(){

	}

	function _prepareXMLQuery(){
	}

	function _parseXMLAnswer(){;}

	function uninstall($_ConfigID = 0){

		$_ConfigID = (int)$_ConfigID?(int)$_ConfigID:$this->ModuleConfigID;
		parent::uninstall($_ConfigID);

		db_phquery('UPDATE ?#ORDERS_TABLE SET shipping_module_id=0 WHERE shipping_module_id=?', $_ConfigID);
	}
	
	function getConnectedPrintforms()
	{
		return $this->connected_printforms;
	}
}
?>