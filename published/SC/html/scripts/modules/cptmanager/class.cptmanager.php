<?php
class CptManager extends ComponentModule {
	
	var $SingleInstallation = true;
	
	function initInterfaces(){
		
		$this->__registerInterface('cpt_connector', 'Component connector', INTHIDDEN, 'mtd_cptConnector');
		$this->__registerInterface('cpt_installModuleComponent', 'Install module component', INTHIDDEN, 'mtd_installModuleComponent');
		$this->__registerInterface('cpt_installStandaloneComponent', 'Install standalone component', INTHIDDEN, 'mtd_installStandaloneComponent');
		$this->__registerInterface('cpt_getComponents', 'Get components', INTHIDDEN, 'mtd_getComponents');
		$this->__registerInterface('cpt_getSettingsVDescr', 'Get component settings (global or local) view description', INTHIDDEN, 'cpt_getSettingsVDescr');
		$this->__registerInterface('cpt_getComponentInfo', 'Get component info', INTHIDDEN, 'cpt_getComponentInfo');
		$this->__registerInterface('cpt_callComponent', 'Call component info', INTHIDDEN, 'cpt_callComponent');
		$this->__registerInterface('cpt_getComponentOutput', 'Get component html', INTHIDDEN, 'cpt_getComponentOutput');
		$this->__registerInterface('cpt_getComponentSmarty', 'Get component smarty', INTHIDDEN, 'cpt_getComponentSmarty');
	}
	
	function cpt_getComponentSmarty($cpt_id, $params){
		
		$component_info = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponentInfo', $cpt_id);
		$component_lsettings_descr = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getSettingsVDescr', $cpt_id, 'local_settings');

		$params_string = '';
		foreach ($component_lsettings_descr as $name=>$vdescr){
			
			if(!function_exists('cptsettingserializer_'.$vdescr['type'])){
				print 'Uknown setting handler - cptsettingserializer_'.$vdescr['type'];die;
				continue;
			}
			
			$ser_val = call_user_func_array('cptsettingserializer_'.$vdescr['type'], array($vdescr['params'], $params, true));
			if (PEAR::isError($ser_val))return $ser_val;
			$params_string .= " $name='".str_replace("'","\\'", $ser_val)."'";
		}
		return '{cpt_'.$component_info['id'].$params_string.'}';
	}
	
	function cpt_getComponentOutput($cpt_id, $params, $wrap = false){
		
		ob_start();
		$this->cpt_callComponent($cpt_id, $params, $wrap);
		$cpt_output = ob_get_contents();
		ob_end_clean();
		
		return $cpt_output;
	}
	
	function cpt_callComponent($cpt_id, $params, $wrap = false){
		static $counter = array(
			'maincontent'=>0,
		);
		if(isset($counter[$cpt_id])){
			if($counter[$cpt_id]++){
				return "<!-- not allowed component {$cpt_id} , please remove it in design editor-->";
				//TODO add optional message
				//"<h2 style=\"color:red;background:black;\">INVALID COMPONENT '{$cpt_id}', please remove it in design editor</h2>";
			}
		}
		
		
		if($wrap){
			
			$params['id'] = $cpt_id;
			$cpt_params_str = '';
			foreach ($params as $key=>$val){
				$cpt_params_str .= ";$key:".htmlspecialchars($val);
			}
			$cpt_params_str = substr($cpt_params_str, 1);
			$cpt_tpl_id = 0;
			if(defined(__CPT_TPL_ID_CONST)){

				$cpt_tpl_id = __CPT_TPL_ID_CONST;
			}else{
				
				if(issetWData(__CPT_TPL_N))$cpt_tpl_id = loadWData(__CPT_TPL_N);
				$cpt_tpl_id++;
				storeWData(__CPT_TPL_N, $cpt_tpl_id);
			}

			storeWData(__CPT_SMARTY.$cpt_tpl_id, ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponentSmarty', $cpt_id, $params));
			storeWData(__CPT_PARAMS.$cpt_tpl_id, $params);
			print '<div id="cpt-tpl-id-'.$cpt_tpl_id.'" class="cpt_wrapper" listDoubleClickHandler="listDoubleClickHandler(ev, id);"><!-- cpt_wrapper_start['.$cpt_params_str.'] -->';
		}
		
		$css_id = null;
		if(isset($params['overridestyle'])){
			$ovst = explode(':',$params['overridestyle'],2);
			$css_id = isset($ovst[1])&&intval($ovst[0])?$ovst[1]:null;
		}
		print "\n".'<div class="cpt_'.$cpt_id.(is_null($css_id)?'':" cptovst_{$css_id}").'">';
		$interface_info = $this->__getCptInterfaceInfo($cpt_id);
		$return = ModulesFabric::callInterface($interface_info, $params);
		print '</div>'."\n";

		if($wrap){
			print '<!-- cpt_wrapper_end --></div>';
		}
		return $return;
	}
	
	function __getCptInterfacesInfo(){
		
		$sql = '
			SELECT xInterfaceCalled FROM ?#TBL_INTERFACE_INTERFACES WHERE xInterfaceCaller=?
		';
		$Result = $this->dbHandler->ph_query($sql, $this->getConfigID().'_cpt_connector');

		$cpt_interfaces = array();
		while($row = $Result->fetchAssoc()){

			if(!preg_match('/^(\d+)_(.+)$/', $row['xInterfaceCalled'], $_T))continue;
				
			$cpt_interfaces[$_T[2]] = array(
				'module_config_id' => $_T[1],
				'key' => $_T[2],
			);
		}
		return $cpt_interfaces;
	}
	
	/**
	 * @return array || null
	 */
	function __getCptInterfaceInfo($cpt_id){
		
		static $cpt_interfaces;
		if(!is_array($cpt_interfaces))$cpt_interfaces = $this->__getCptInterfacesInfo();
		
		return isset($cpt_interfaces[$cpt_id])?$cpt_interfaces[$cpt_id]:null;
	}
	
	function cpt_getSettingsVDescr($cpt_id, $settings_type){
		
		$interface_info = $this->__getCptInterfaceInfo($cpt_id);
		$moduleEntry = &ModulesFabric::getModuleObj($interface_info['module_config_id']);
		/*@var $moduleEntry ComponentModule*/
		if(!is_object($moduleEntry))return array();
		return $moduleEntry->getComponentVDescription($interface_info['key'], $settings_type=='global_settings'?'global':'local');
	}
	
	function mtd_componentsConnector(){
		
	}
	
	/**
	 * Install module component to component manager
	 *
	 * @param int $module_id - module instance id
	 * @param mixed $cpt_id - component id
	 */
	function mtd_installModuleComponent($module_id, $cpt_id){

		$moduleInstance = &ModulesFabric::getModuleObj($module_id);
		$this->registerInterface2Interface('cpt_connector', $moduleInstance->getConfigID(), $cpt_id);
	}
	
	function mtd_installStandaloneComponent($id){
		
		$this->registerInterface2Interface('cpt_connector', $this->getConfigID(), $id);
	}
	
	function mtd_getComponents($availability_zone = null){

		$cpt_interfaces = $this->getInterfaceInterfaces('cpt_connector');
		$components = array();
		
		for($i=0, $cnt = count($cpt_interfaces['main']);$i<$cnt;$i++){
			
			$moduleEntry = &ModulesFabric::getModuleObj($cpt_interfaces['main'][$i]['module_config_id']);
			if($moduleEntry&&method_exists($moduleEntry,'getComponentSettings')){
				$components[] = $moduleEntry->getComponentSettings($cpt_interfaces['main'][$i]['key']);
			}else{
				trigger_error('not found module at SC for this data: '.var_export($cpt_interfaces['main'][$i],true),E_USER_WARNING);
			}
		}

		if(is_null($availability_zone))return $components;
		
		$all_components = $components;
		$components = array();
		for ($i=0, $cnt=count($all_components); $i<$cnt; $i++){
			
			if(!is_array($all_components[$i]) || (!in_array($availability_zone, $all_components[$i]['allowed_templates']) && !in_array('everywhere', $all_components[$i]['allowed_templates']))) continue;
			
			$components[] = $all_components[$i];
		}
		return $components;
	}

	function cpt_getComponentInfo($cpt_id){
			
		$interface_info = $this->__getCptInterfaceInfo($cpt_id);
		$moduleEntry = &ModulesFabric::getModuleObj($interface_info['module_config_id']);
		/*@var $moduleEntry ComponentModule*/
		if(!is_object($moduleEntry))return array();
		return $moduleEntry->getComponentSettings($interface_info['key']);
	}
}
?>