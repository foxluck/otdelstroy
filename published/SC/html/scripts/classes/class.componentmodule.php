<?php
	class ComponentModule extends AbstractModule {

		var $cpt_settings = array();
		var $cpt_settings_vdescription = array();

		function __registerComponent($id, $name, $allowed_templates = array(), $method = null, $local_settings = array()){
			
			$this->Interfaces[$id] = array(
					'id' => $id,
					'name' => translate($name),
					'key' => $id,
					'type' => INTCOMPONENT,
					'allowed_templates' => $allowed_templates,
					'method' => is_null($method)?'cpt_'.$id:$method,
					'local' => $local_settings,
				);
		}
		
		function __prepend_interface($interface_key, &$params){
			
			$interface_params = $this->getInterfaceParams($interface_key);

			if(isset($interface_params['type']) && $interface_params['type'] == INTCOMPONENT){
				
				$tparams = $params;
				
				$Register = &Register::getInstance();
				$themeEntry = &$Register->get('CURRENT_THEME_ENTRY');
				/*@var $themeEntry Theme*/
				
				$params = array(
					'local_settings' => $params[0],
					'global_settings' => $themeEntry->getComponentSettingsValues($interface_key)
				);
				$cpt_params = $this->getComponentSettings($interface_key);
				if(isset($cpt_params['local_settings'])&&is_array($cpt_params['local_settings']))foreach ($cpt_params['local_settings'] as $key=>$val){
					
					if(isset($params['local_settings'][$key]))continue;
					
					$params['local_settings'][$key] = $val;
				}
				if(isset($cpt_params['global_settings'])&&is_array($cpt_params['global_settings']))foreach ($cpt_params['global_settings'] as $key=>$val){
					
					if(isset($params['global_settings'][$key]))continue;
					
					$params['global_settings'][$key] = $val;
				}
				
				$params = array($params);
			}
		}
		
		function getComponentSettings($component_id){
			
			$general_settings = array(
				'overridestyle' => array( 'type' => 'overridestyle', 'params' => array('name' => 'overridestyle')),
			);
			
			if(isset($this->cpt_settings[$component_id])){
				if(!isset($this->cpt_settings[$component_id]['local'])){
					$this->cpt_settings[$component_id]['local'] = array();
				}
				if(!is_array($this->cpt_settings[$component_id]['local'])){
					$this->cpt_settings[$component_id]['local'] = array($this->cpt_settings[$component_id]['local']);
				}
				
				$this->cpt_settings[$component_id]['local'] = array_merge($this->cpt_settings[$component_id]['local'], $general_settings);
				return $this->cpt_settings[$component_id];
			}
			if(isset($this->Interfaces[$component_id])){
				if(!isset($this->Interfaces[$component_id]['local'])){
					$this->Interfaces[$component_id]['local'] = array();
				}
				if(!is_array($this->Interfaces[$component_id]['local'])){
					$this->Interfaces[$component_id]['local'] = array($this->Interfaces[$component_id]['local']);
				}
				$this->Interfaces[$component_id]['local'] = array_merge($this->Interfaces[$component_id]['local'], $general_settings);
				return $this->Interfaces[$component_id];
			}

			$xnInterfaces = new xmlNodeX();
			$xnInterfaces->renderTreeFromFile(DIR_MODULES.'/'.$this->ModuleDir.'/_methods/'.$component_id.'.xml');
			$r_xnInterface = $xnInterfaces->getChildrenByName('interface');
			for($j=0, $cnt = count($r_xnInterface);$j<$cnt;$j++){
				
				$xnInterface = &$r_xnInterface[$j];
				/*@var $xnInterface xmlNodeX*/
				
				$xnKey = &$xnInterface->getFirstChildByName('key');

				if($xnKey->getData()==$component_id){

					$cpt_settings =  array(
						'id' => $xnKey->getData(),
						'name' => $xnInterface->getChildData('name'),
						'type' => $xnInterface->getChildData('type'),
						'allowed_templates' => explode(',', $xnInterface->getChildData('allowed_templates')),
						'local_settings' => array(),
						'global_settings' => array(),
					);
					
					$cpt_settings['type'] = !is_null($cpt_settings['type'])?constant($cpt_settings['type']):'';
					
					$r_xnSettings = $xnInterface->getChildrenByName('settings');
					for ($i=0,$max_i = count($r_xnSettings); $i<$max_i; $i++){
						
						$xnSettings = &$r_xnSettings[$i];
						/*@var $xnSettings xmlNodeX*/
						$vsbl_key = $xnSettings->attribute('visibility')=='global'?'global_settings':'local_settings';
						
						for ($j=0, $max_j=count($xnSettings->ChildNodes); $j<$max_j; $j++){
							
							$xnSetting = &$xnSettings->ChildNodes[$j];
							/*@var $xnSetting xmlNodeX*/
							$cpt_settings[$vsbl_key][$xnSetting->attribute('name')] = $xnSetting->attribute('default_value');
						}
					}

					$this->cpt_settings[$component_id] = $cpt_settings;
					if(!is_array($this->cpt_settings[$component_id]['local'])){
						$this->cpt_settings[$component_id]['local'] = array();
					}
					$this->cpt_settings[$component_id]['local'] = array_merge($this->cpt_settings[$component_id]['local'], $general_settings);
					return $this->cpt_settings[$component_id];
				}
			}
			return null;
		}
	
		function getComponentVDescription($component_id, $settings_visibility){
			
			$general_settings = array(
				'overridestyle' => array( 'type' => 'overridestyle', 'params' => array('name' => 'overridestyle')),
			);
			
			if(isset($this->cpt_settings_vdescription[$settings_visibility][$component_id])){
			
				$r_settings = isset($this->cpt_settings_vdescription[$settings_visibility][$component_id])?$this->cpt_settings_vdescription[$settings_visibility][$component_id]:array();
				return array_merge($r_settings, $general_settings);
			}
			if(isset($this->Interfaces[$component_id])){
				
				$r_settings = isset($this->Interfaces[$component_id][$settings_visibility])?$this->Interfaces[$component_id][$settings_visibility]:array();
				return array_merge($r_settings, $general_settings);
			}
			
			$xnInterfaces = new xmlNodeX();
			$xnInterfaces->renderTreeFromFile(DIR_MODULES.'/'.$this->ModuleDir.'/_methods/'.$component_id.'.xml');
			list($xnSettings) = $xnInterfaces->xPath('/interfaces/interface/settings[@visibility="'.$settings_visibility.'"]');
			/*@var $xnSettings xmlNodeX*/
			if(!is_object($xnSettings))return null;

			$vdescription = array();
			
			for ($j=0,$max_j=count($xnSettings->ChildNodes); $j<$max_j; $j++){
				
				$xnSetting = &$xnSettings->ChildNodes[$j];
				/*@var $xnSetting xmlNodeX */
				$vdescription[$xnSetting->attribute('name')] = array('type' => $xnSetting->Name, 'params' => $xnSetting->Attributes);
			}
			
			$this->cpt_settings_vdescription[$settings_visibility][$component_id] = $vdescription;
			
			return array_merge($this->cpt_settings_vdescription[$settings_visibility][$component_id], $general_settings);
		}
	}
?>