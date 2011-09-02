<?php
class Functions extends Singleton {

	var $__functions = array();

	static function register(&$object, $exec_name, $method_name){

		$instance = &Functions::getInstance();
		$instance->__functions[$exec_name] = array('method' => $method_name, 'object' => &$object);
	}

	static function exec($name, $params){

		$instance = &Functions::getInstance();
		if(!isset($instance->__functions[$name]))return null;

		try{
			return call_user_func_array(array(&$instance->__functions[$name]['object'],$instance->__functions[$name]['method']),$params);
		}catch (Exception $e){
			if(SystemSettings::is_hosted()){
				$message = explode(':',$e->getMessage());
				return PEAR::raiseError($message[0]);
			}else{
				return PEAR::raiseError($e->getMessage());
			}
		}
		//call_user_func_array(array(&$obj,$method),array(&$arg1,$arg2,$arg3))
		//			call_user_method_array  ( string $method_name  , object $obj  [, array $paramarr  ] )
		//
		//4.10 PHP
		//return call_user_method_array($instance->__functions[$name]['method'], $instance->__functions[$name]['object'], $params);
	}
}
?>