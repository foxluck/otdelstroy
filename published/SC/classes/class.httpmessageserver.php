<?php
	class HttpMessageServer{
		
		var $__data = array();
		var $__operation_result = null;
		var $__request_data = array();
		var $__debug = false;
		
		function HttpMessageServer(){
			
			ob_start(array(&$this, "__obHandler"));
			
			if(isset($_GET['request_data']) && substr($_GET['request_data'],0,8) == 'MSGCLT::'){
				
				$this->__request_data = unserialize(base64_decode(substr($_GET['request_data'], 8)));
			}
			
			$this->__operation_result = true;
		}
		
		function __obHandler($text){
			
			if(strlen($text)){
				return $this->__terminate($text);
			}

			return $this->__envelope();
		}
		
		function __envelope(){
			
			if(!$this->__debug){
				$result = array(
					'operation_result' => $this->__operation_result,
					'data' => $this->__data,
				);
				
				return 'MSGSRV::'.base64_encode(serialize($result));
			}else{
				return $this->__data['__server_message'];
			}
		}
		
		function putData($key, $val){
			
			$this->__data[$key] = $val;
		}
		
		function setOperationResult($result){
			
			$this->__operation_result = $result;
		}
		
		function end(){
			
			die;
		}
		
		function terminate($msg = ''){
			
			$this->setOperationResult(false);
			$this->putData('__server_message', $msg);
			$this->end();
		}
		
		function __terminate($msg){
			
			$this->setOperationResult(false);
			$this->putData('__server_message', $msg);
			return $this->__envelope();
		}
		
		function getRequest($key){
			
			return isset($this->__request_data[$key])?$this->__request_data[$key]:'';
		}
	}
?>