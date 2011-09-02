<?php
ClassManager::includeClass('HttpMessageClient');

class WbsHttpMessageClient extends HttpMessageClient {

	function WbsHttpMessageClient($dbkey, $server_url){

		$this->putData('dbkey', $dbkey);
		$this->putData('session.name',ini_get('session.name'));
		parent::HttpMessageClient($server_url);
	}
}
?>