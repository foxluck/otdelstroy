<?php
	class HttpMessageClient{

		var $__server_url = '';
		var $__data = array();
		var $__result_data = array();
		var $__raw_response = '';

		var $debug = false;

		function HttpMessageClient($server_url){

			$this->__server_url = $server_url;
		}

		function putData($key, $data){

			$this->__data[$key] = $data;
		}
		function switchRealURL ($url) {
			// Replace realname with hostname
			if (isset($_SERVER['HTTP_X_REAL_HOST']))
				$url = str_replace($_SERVER['HTTP_X_REAL_HOST'], $_SERVER['HTTP_HOST'], $url);
      return $url;
    }

		function send($direct = false){
			if(!extension_loaded('curl')){
				if(isset($_GET['test'])){
 					print 'php extension Curl isn\'t loaded';
 					print "<br><pre>".var_export(debug_backtrace(),true)."</pre>";
 					die();
				}	
			return false;
			}

			$request = 'MSGCLT::'.base64_encode(serialize($this->__data));

			if($direct){
				if($this->debug)print '<br>Method 1';
				$url = $this->__server_url.'&request_data='.$request;
				$url = $this->switchRealURL($url);
				if($_SERVER['HTTP_HOST'] == 'webasyst.webasyst.net' || $_SERVER['HTTP_HOST'] == 'webasyst.qa.webasyst.net'){

					if(!preg_match('/https?:\/\/([^\.]+)\./', $url, $sp))return false;
					$url .= '&__account_name='.$sp[1];
					$url = preg_replace('/(https?:\/\/)[^\.]+\./', '$1webasyst.', $url);
				}
			}else{
				ClassManager::includeClass('url');
				$serverURL = new URL();
				$serverURL->loadFromServerInfo();

				$serverURL->setPath('/SC/html/scripts/'.$this->__server_url);
				$serverURL->setQuery('?request_data='.$request);

				if(!(isset($_GET['__account_name']) && ($_SERVER['HTTP_HOST'] == 'webasyst.webasyst.net' || $_SERVER['HTTP_HOST'] == 'webasyst.qa.webasyst.net'))){
					if($this->debug)print '<br>Method 2.1';
					if(WBS_MSGSERVER_OVERRIDE){

						// --- Switch hostname back to *.webasyst.net
						if (isset($_SERVER['HTTP_X_REAL_HOST']))
							$uri = $this->switchRealURL($serverURL->getURI());
						else
							$uri = $serverURL->getURI();
						// ---

						if(!preg_match('/https?:\/\/([^\.]+)\./', $uri ,$sp))return false;
						//if(!preg_match('/https?:\/\/([^\.]+)\./', $serverURL->getURI() ,$sp))return false;
						$serverURL->setQuery('?request_data='.$request.'&__account_name='.$sp[1]);

						$url = $this->switchRealURL($serverURL->getURI());
						//print SystemSettings::is_hosted()?'HOSTED':'LOCAL';
						if (!SystemSettings::is_hosted()) {
							$url = preg_replace("@SC/html/@", (defined('WBS_INSTALL_PATH')?WBS_INSTALL_PATH:'')."published/SC/html/", $url);
							if(isset($this->__data['dbkey']))$url .= "&DB_KEY=" .$this->__data['dbkey'];
						} else {
							$url = preg_replace('/(https?:\/\/)[^\.]+\./', '$1webasyst.', $url);
						}
 				}else{
						$url = $serverURL->getURI();
						
					}
				}else{
					if($this->debug)print '<br>Method 2.2';
					$serverURL->setQuery('?request_data='.$request.'&__account_name='.$_GET['__account_name']);
					$url = $this->switchRealURL($serverURL->getURI());
					$url = preg_replace('/(https?:\/\/)[^\.]+\./', '$1webasyst.', $url);
				}
			}

			if(isset($_GET['test'])){
 				print $url;
 				print "<br><pre>".var_export(debug_backtrace(),true)."</pre>";
 				die();
			}
			if($this->debug)print '<br>Destination: '.$url;
			
			$ch = curl_init();
			@curl_setopt( $ch, CURLOPT_URL, $url );
			@curl_setopt($ch, CURLOPT_VERBOSE, 0);
			@curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			@curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
			@curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			@curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			@curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
			initCurlProxySettings($ch);

			$this->__raw_response = curl_exec($ch);

			curl_close($ch);

			if(substr($this->__raw_response, 0, 8)!='MSGSRV::')return false;

			$result = unserialize(base64_decode(substr($this->__raw_response, 8)));

//			if(!$result['operation_result'])return false;

			$this->__result_data = $result['data'];
			return true;
		}

		function getResult($key){

			return isset($this->__result_data[$key])?$this->__result_data[$key]:'';
		}
	}
?>