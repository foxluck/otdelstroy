<?php
define('ACTCTRL_GET', 'get');
define('ACTCTRL_POST', 'post');
define('ACTCTRL_AJAX', 'ajax');
define('ACTCTRL_CUST', 'custom');

class ActionsController{
	
	var $__action_sources;
	var $__current_data;
	var $__action_source;
	var $__action;
	
	var $__params = array();
	
	function preAction($action){
		
		safeMode($action != 'main');
	}
	
	function postAction($action){
		;
	}
	
	function ActionsController(){
		
		$this->registerStandardSources();
	}
	
	function registerSource($source_id, &$source_data){
		
		$this->__action_sources[$source_id] = &$source_data;
	}
	
	function registerStandardSources(){
		
		$Register = &Register::getInstance();
		$GetVars = &$Register->get(VAR_GET);
	
		if(isset($GetVars['caller'])){
			
			ClassManager::includeClass('jshttprequest');
			$JsHttpRequest = new JsHttpRequest(translate("str_default_charset"));
		}
		
		global $_REQUEST;
		
		$this->registerSource(ACTCTRL_AJAX, $_REQUEST);
		$this->registerSource(ACTCTRL_POST, $Register->get(VAR_POST));
		$this->registerSource(ACTCTRL_GET, $Register->get(VAR_GET));
	}
	
	function exec($controller_name, $sources = array(ACTCTRL_POST, ACTCTRL_GET, ACTCTRL_AJAX, ACTCTRL_CUST), $params = array()){
		$controller = new $controller_name();
		/*@var $controller ActionsController*/
		$controller->__params = $params;
		$controller->__exec($sources);
	}
	
	function __exec_cust($data){
		
		$this->registerSource(ACTCTRL_CUST, $data);
		
		return $this->__exec(ACTCTRL_CUST);
	}
	
	function __exec($sources = null){

		if(!is_array($sources))$sources = array($sources);

		foreach ($sources as $source){
			
			if(!isset($this->__action_sources[$source]['action']))continue;
			
			if(!method_exists($this, $this->__action_sources[$source]['action'])){
				pear_dump('No action handler');die;
			}

			$this->__current_data = &$this->__action_sources[$source];
			$this->__action_source = $source;
			$this->__action = $this->__action_sources[$source]['action'];  
			
			switch ($source) {
				case ACTCTRL_GET:
					renderURL('action=', '', true);
					break;
			}
			
			$this->preAction($this->__action);
			
			$return = $this->{$this->__action}();
			
			$this->postAction($this->__action);
			
			return $return;
		}
		
		if(!$this->__action){
			$this->__current_data = &$this->__action_sources[ACTCTRL_GET];
			$this->__action_source = ACTCTRL_GET;
			$this->__action = 'main';
			
			$this->preAction($this->__action);
			
			$return = $this->{$this->__action}();
		}
	}
	
	function getData($key = null, $key2 = null){
		
		if(is_null($key))return $this->__current_data;
		
		if(is_null($key2))
			return isset($this->__current_data[$key])?$this->__current_data[$key]:'';
		else
			return isset($this->__current_data[$key][$key2])?$this->__current_data[$key][$key2]:'';
	}

	function existsData($key){
		return isset($this->__current_data[$key]);		
	}
	
	function setData($key, $value){
		
		$this->__current_data[$key] = $value;
	}

	function main(){
		;
	}
}
?>