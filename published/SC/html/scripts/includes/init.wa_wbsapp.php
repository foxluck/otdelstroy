<?php
	function wbs_getWBSHost(){
		
		if(preg_match('@(?:(?:^dev\.|^test\.|^qa\.|^www\.|(?<=\.)dev\.|(?<=\.)test\.|(?<=\.)qa\.|)webasyst\.net)@ui', $_SERVER['HTTP_HOST'], $p_results)){
	
			if($p_results[0] == 'webasyst.net')$p_results[0] = 'www.'.$p_results[0];
			return $p_results[0];
		}else{
			return 'www.webasyst.net';
		}
	}

	function wbs_getSoapConnectData(){
		
		return array(
			"WBS_SOAP_USER" => "WEBASYST_SHOPPINGCART",
			"WBS_SOAP_PWD" => "32ba3e9w01b7f33w31e4c3d6701b7f3"
		);
	}
	
	switch (wbs_getWBSHost()){
		
		case 'dev.webasyst.net':
			define('SCCONF_LOC_SWITCHER', 1);
			define('SCCONF_PROGRAMMER_MODE', 1);
			define('WBS_MSGSERVER_OVERRIDE' ,0);
			define( "WBS_HOST_URL", "http://dev.webasyst.net/wbs/" );
			define( "WBS_HOSTED_URL", "http://dev.webasyst.net/wbs/" );
			define( "WBS_DB_KEY", "WEBASYST" );
			ini_set('include_path', ini_get('include_path').PATH_DELIMITER.'./modules/payment/pppro/pear');
			set_include_path('.'.PATH_DELIMITER.'..'.PATH_DELIMITER.'./modules/payment/pppro/pear');
			break;
		case 'test.webasyst.net':
			define('SCCONF_PROGRAMMER_MODE', 1);
			define('WBS_MSGSERVER_OVERRIDE' ,0);
			define( "WBS_HOST_URL", "http://test.webasyst.net/wbs/" );
			define( "WBS_HOSTED_URL", "http://test.webasyst.net/wbs/" );
			define( "WBS_DB_KEY", "STDBTEST" );
			break;
		case 'qa.webasyst.net':
			define('SCCONF_PROGRAMMER_MODE', 1);
			define('WBS_MSGSERVER_OVERRIDE' ,1);
			define( "WBS_HOST_URL", "http://qa.webasyst.net/wbs/" );
			define( "WBS_HOSTED_URL", "http://qa.webasyst.net/wbs/" );
			define( "WBS_DB_KEY", "WEBASYST" );
			
			set_include_path('.'.PATH_DELIMITER.'..'.PATH_DELIMITER.'./modules/payment/pppro/pear');
			break;
		case 'www.webasyst.net':
			define('SCCONF_PROGRAMMER_MODE', 1);
			define('WBS_MSGSERVER_OVERRIDE' ,1);
			define( "WBS_HOST_URL", "http://webasyst.webasyst.net/wbs/" );
			define( "WBS_HOSTED_URL", "http://webasyst.net/wbs/" );
			define( "WBS_DB_KEY", "WEBASYST" );
			
			$set_names = true;
			set_include_path('.'.PATH_DELIMITER.'..'.PATH_DELIMITER.'./modules/payment/pppro/pear');
			break;
	}
	
	define( "WBS_MT_ENDPOINT", WBS_HOST_URL."/MT/soap/mt_webservice.php?DB_KEY=".WBS_DB_KEY );

	function wbs_auth(){
		
		if(!isset($_SESSION['WBS_ACCESS_SC'])){
			Redirect('auth.php?redirect='.base64_encode(renderURL()));
		}
		
		return $_SESSION['WBS_ACCESS_SC'];
	}

	function sc_issetSessionData($key){
		
		return isset($_SESSION['__WBS_SC_DATA'][$key]);
	}

	function sc_getSessionData($key){
		
		return isset($_SESSION['__WBS_SC_DATA'][$key])?$_SESSION['__WBS_SC_DATA'][$key]:'';
	}
	
	function sc_setSessionData($key, $val){
		
		$_SESSION['__WBS_SC_DATA'][$key] = $val;
	}
	
	/**
	 * @param Division
	 * @param array - array of divisions where last element is current division
	 */
	function sc_checkLoggedUserAccess2Division($CurrDivision, $BreadDivs = array()){

		static $accesses;
	
		$U_ID = sc_getSessionData('U_ID');
		$UG_IDs = sc_getSessionData('UG_IDs');
		if(!is_array($UG_IDs) || !count($UG_IDs))$UG_IDs = null;

		if(!is_array($accesses)){

			$dbres = db_phquery('SELECT xDivisionID FROM ?#TBL_DIVISION_ACCESS WHERE (xU_ID=? AND xID_TYPE=0)'.(is_null($UG_IDs)?'':' OR (xID_TYPE=1 AND xU_ID IN (?@))'), $U_ID, $UG_IDs);
			$accesses = array();
			while($row = db_fetch_row($dbres))
				$accesses[$row[0]] = 1;
		}

		if(isset($accesses[$CurrDivision->getID()]))return true;
		for($k = count($BreadDivs)-1; $k>=0; $k--){
			if(isset($accesses[$BreadDivs[$k]->getID()]))return true;
		}
		
		print translate('forbidden_page');
		die();
	}

	function sc_registerOrder2MT($orderID){

		$mt_service_options = array( 'namespace' => 'urn:SOAP_MT_Server' );
		
		if(!class_exists('SOAP_Client'))include_once('Services/PayPal/SOAP/Client.php');
		$soapclient = new SOAP_Client( WBS_MT_ENDPOINT );
		if( PEAR::isError($soapclient) )
			return $soapclient;
		
		$soapclient->setOpt( 'timeout', 30 );
	
		$orderEntry = new Order;
		$res = $orderEntry->loadByID($orderID);
		if ( PEAR::isError($res) )return $res;
		
		$order_info = array(
			'DB_KEY' => sc_getSessionData('DB_KEY'),
			'MT_SCO_AMOUNT' => round($orderEntry->order_amount*$orderEntry->currency_value, 2),
			'MT_SCO_CURRENCY' => $orderEntry->currency_code,
			'MT_SCO_PAYMENT_TYPE' => $orderEntry->payment_type,
			'MT_SCO_SHIPPING_TYPE' => $orderEntry->shipping_type,
			'MT_SCO_SHIPPING_COUNTRY' => $orderEntry->shipping_country,
			'MT_SCO_SHIPPING_ZIP' => $orderEntry->shipping_zip,
			'MT_SCO_SHIPPING_CITY' => $orderEntry->shipping_city,
			'MT_SCO_SOURCE' => $orderEntry->source,
		);
		$connect_data = wbs_getSoapConnectData();

		$parameters['sc_order'] = encodeArray( $order_info);
		$parameters['U_ID'] = base64_encode($connect_data['WBS_SOAP_USER']);
		$parameters['PASSWORD'] = base64_encode($connect_data['WBS_SOAP_PWD']);
		$res = $soapclient->call( "mt_registerSCOrder", $parameters, $mt_service_options );

		if ( PEAR::isError($res) )return $res;
		
		if($res->error==1)return PEAR::raiseError('err_failure_order_registration', $res->errorCode);
	}
?>