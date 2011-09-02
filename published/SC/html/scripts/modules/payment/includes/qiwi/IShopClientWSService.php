<?php
class updateBill {
	public $login; // string
	public $password; // string
	public $txn; // string
	public $status; // int
}

class updateBillResponse {
	public $updateBillResult; // int
}


/**
 * IShopClientWSService class
 *
 *
 *
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class IShopClientWSService extends nusoap_server// SoapServer
{
	private $callback = null;

	private static $classmap = array(
                                    'updateBill' => 'updateBill',
                                    'updateBillResponse' => 'updateBillResponse',
	);

	public function __construct($wsdl = "IShopClientWS.wsdl", $options = array())
	{
		foreach(self::$classmap as $key => $value) {
			if(!isset($options['classmap'][$key])) {
				$options['classmap'][$key] = $value;
			}
		}
		parent::__construct($wsdl, $options);
	}
}

?>