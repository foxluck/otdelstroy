<?php
class checkBill {
	public $login; // string
	public $password; // string
	public $txn; // string
}

class checkBillResponse {
	public $user; // string
	public $amount; // string
	public $date; // string
	public $lifetime; // string
	public $status; // int
}

class getBillList {
	public $login; // string
	public $password; // string
	public $dateFrom; // string
	public $dateTo; // string
	public $status; // int
}

class getBillListResponse {
	public $txns; // string
	public $count; // int
}

class cancelBill {
	public $login; // string
	public $password; // string
	public $txn; // string
}

class cancelBillResponse {
	public $cancelBillResult; // int
}

class createBill {
	public $login; // string
	public $password; // string
	public $user; // string
	public $amount; // string
	public $comment; // string
	public $txn; // string
	public $lifetime; // string
	public $alarm; // int
	public $create; // boolean
}

class createBillResponse {
	public $createBillResult; // int
}


/**
 * IShopServerWSService class
 *
 *
 *
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class IShopServerWSService extends nusoap_client// SoapClient
{
	private static $classmap = array(
                                    'checkBill' => 'checkBill',
                                    'checkBillResponse' => 'checkBillResponse',
                                    'getBillList' => 'getBillList',
                                    'getBillListResponse' => 'getBillListResponse',
                                    'cancelBill' => 'cancelBill',
                                    'cancelBillResponse' => 'cancelBillResponse',
                                    'createBill' => 'createBill',
                                    'createBillResponse' => 'createBillResponse',
	);

	public function IShopServerWSService($wsdl = "IShopServerWS.wsdl", $options = array())
	{
		foreach(self::$classmap as $key => $value) {
			if(!isset($options['classmap'][$key])) {
				$options['classmap'][$key] = $value;
			}
		}
		parent::__construct($wsdl, $options);
	}

	public function getDebug()
	{
		$result = null;
		$class = get_parent_class($this);
		switch($class){
			case 'SoapClient':{
				$result =  var_export(array(
				$this->__getLastRequestHeaders(),
				$this->__getLastRequest(),
				$this->__getLastResponse(),
				$this->__getLastResponseHeaders(),
				));
				break;
			}
			case 'nusoap_client':{
				$result = parent::getDebug();
				break;
			}
			default:{
				$result = __METHOD__;
				break;
			}
		}
		return $result;
	}

	/**
	 *
	 *
	 * @param checkBill $parameters
	 * @return checkBillResponse
	 */
	public function checkBill(checkBill $parameters)
	{
		return $this->castCall(__FUNCTION__, array($parameters));
	}

	/**
	 *
	 *
	 * @param getBillList $parameters
	 * @return getBillListResponse
	 */
	public function getBillList(getBillList $parameters)
	{
		return $this->castCall(__FUNCTION__, array($parameters));
	}

	/**
	 *
	 *
	 * @param cancelBill $parameters
	 * @return cancelBillResponse
	 */
	public function cancelBill(cancelBill $parameters)
	{
		return $this->castCall(__FUNCTION__, array($parameters));
	}

	/**
	 *
	 *
	 * @param createBill $parameters
	 * @return createBillResponse
	 */
	public function createBill(createBill $parameters)
	{
		return $this->castCall(__FUNCTION__, array($parameters));
	}

	private function castCall($method,$parameters)
	{
		$call_result = $this->call($method,$parameters);
		$class = $method.'Response';
		$result = new $class();
		$vars = get_class_vars($class);
		foreach($vars as $key=>$var){
			if(isset($call_result[$key])){
				$result->{$key} = $call_result[$key];
			}
		}
		return $result;
	}

}

?>
