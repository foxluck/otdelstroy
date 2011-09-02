<?php
	class Theme{
		
		var $id;
		/**
		 * @var xmlNodeX
		 */
		var $xnTheme;
		var $templates_info;
		
		/**
		 * Load template
		 *
		 * @param mixed $id
		 * @return PEAR_Error | null
		 */
		function load($id){
			
			if(!$id)return PEAR::raiseError('thm_notheme_msg');

			$this->id = $id;
			
			if(!file_exists($this->getPath())){
				
				$this->id = null;
				return PEAR::raiseError('thm_notheme_msg');
			}
		
			$this->xnTheme = new xmlNodeX();
			$this->xnTheme->renderTreeFromFile($this->getPath().'/theme.xml');
			
			$this->templates_info = array(
				TPLID_GENERAL_LAYOUT=> array(
					'name' => 'thm_generallayout_link',
					'file' => 'index.html',
					'simple_editor' => true,
					'advanced_editor' => true,
					),
				TPLID_HOMEPAGE => array(
					'name' => 'thm_homepage_link',
					'file' => 'home.html',
					'simple_editor' => true,
					'advanced_editor' => true,
					),
				TPLID_PRODUCT_INFO => array(
					'name' => 'thm_productinfo_link',
					'file' => 'product_info.html',
					'simple_editor' => true,
					'advanced_editor' => true,
					),
				TPLID_CSS => array(
					'name' => 'thm_css_link',
					'file' => 'main.css',
					'simple_editor' => false,
					'advanced_editor' => true,
					),
				TPLID_HEAD => array(
					'name' => 'thm_head_link',
					'file' => 'head.html',
					'simple_editor' => false,
					'advanced_editor' => true,
					),
			);
		}
		
		/**
		 * Get information about templates for specified editor type
		 *
		 * @param mixed $editor_type - simple_editor, advanced_editor, null
		 * @return array
		 */
		function getTemplatesInfo($editor_type = null){
			
			if(is_null($editor_type))return $this->templates_info;
			
			$templates_info = array();
			foreach ($this->templates_info as $id=>$info){
				
				if(isset($info[$editor_type]) && $info[$editor_type] == true)$templates_info[$id] = $info;
			}
			
			return $templates_info;
		}
		
		/**
		 * Get template directory path
		 * 
		 * @return string
		 */
		function getPath(){

			return (file_exists(DIR_THEMES."/{$this->id}")?DIR_THEMES:DIR_REPOTHEMES)."/{$this->id}";
		}
		
		function getURLOffset(){
			
			return (file_exists(DIR_THEMES."/{$this->id}")?URL_THEMES:URL_REPOTHEMES)."/{$this->id}";
		}
		
		/**
		 * Save template
		 *
		 * @param string $template_file
		 * @param string $template_content
		 * @return PEAR_Error | null
		 */
		function saveTemplate($template_id, $template_content, $temp_saving = false, $content_changed = false){
			
			if(!$temp_saving){
				$this->__copyFromRepo();
				
				$fp = fopen($this->getPath()."/".$this->__getTemplateFile($template_id), 'w');
				fwrite($fp, $template_content);
				fclose($fp);
				
				$this->xnTheme->attribute('last_modified', date('Y-m-d H:i:s'));
				$this->xnTheme->saveToFile($this->getPath().'/theme.xml', true);
				
				$this->__cleaupTempSaving($template_id);
				self::cleanUpCache();
			}else{
				
				storeWData($this->__getTempSavingKey($template_id), array($template_content, $content_changed));
			}
		}
		
		function __cleaupTempSaving($template_id){
			
			unsetWData($this->__getTempSavingKey($template_id));
		}
		
		function __getTempSavingKey($template_id){
			
			return 'THMTPLTEMPSAVING::'.$this->id.'::'.$template_id;
		}
		
		function __copyFromRepo(){
			
			if(!file_exists(DIR_THEMES."/{$this->id}")){
				
				copy_dir(DIR_REPOTHEMES."/{$this->id}", DIR_THEMES."/{$this->id}");
			}
		}
		
		/**
		 * @param string $template_id
		 * @return string
		 */
		function getTemplateContent($template_id, $use_temp_saving = false){
			
			if(!$use_temp_saving){
				return file_get_contents($this->getPath()."/".$this->__getTemplateFile($template_id));
			}
				
			$temp_saving_key = $this->__getTempSavingKey($template_id);
			if(issetWData($temp_saving_key)){
				list($content, $content_changed) = popWData($temp_saving_key);
				if($content_changed){
					$Register = &Register::getInstance();
					/*@var $Register Register*/
					$Register->assign('__THMTPL_CONTENTCHANGED', 1);
				}
				return $content;
			}else{
				return file_get_contents($this->getPath()."/".$this->__getTemplateFile($template_id));
			}
		}
		
		function getTemplatePath($template_id){
			
			return $this->getPath()."/".$this->__getTemplateFile($template_id);
		}
		
		/**
		 * @param string $template_id
		 * @return string
		 */
		function __getTemplateFile($template_id){
			
			return $this->templates_info[$template_id]['file'];
		}
	
		function getComponentSettingsValues($cpt_id){
			
			$settings = array();
			$r_xnSetting = $this->xnTheme->xPath('/theme/components_settings/component[@id="'.$cpt_id.'"]/setting');
			for ($j=0, $max_j=count($r_xnSetting); $j<$max_j; $j++){
				
				$xnSetting = &$r_xnSetting[$j];
				/*@var $xnSetting xmlNodeX*/
				$settings[$xnSetting->attribute('name')] = $xnSetting->attribute('value');
			}
			
			return $settings;
		}
	
		function setComponentSettingValue($cpt_id, $setting_name, $setting_value){
			
			$xnComponent = $this->xnTheme->xPath('/theme/components_settings/component[@id="'.$cpt_id.'"]');
			@$xnComponent = &$xnComponent[0];
			if(!is_object($xnComponent)){
				
				$xnComponentsSettings = &$this->xnTheme->getFirstChildByName('components_settings');
				$xnComponent = &$xnComponentsSettings->child('component', array('id'=>$cpt_id));
			}
			/*@var $xnComponent xmlNodeX*/
			$xnSetting = $xnComponent->xPath('/component/setting[@name="'.$setting_name.'"]');
			@$xnSetting = &$xnSetting[0];
			if(!is_object($xnSetting)){
				
				$xnSetting = &$xnComponent->child('setting', array('name'=>$setting_name));
			}
			/*@var $xnSetting xmlNodeX*/
			$xnSetting->attribute('value', $setting_value);
		}
		
		function saveTheme(){
			
			$this->xnTheme->attribute('last_modified', date('Y-m-d H:i:s'));
			$this->xnTheme->saveToFile($this->getPath().'/theme.xml', true);
			self::cleanUpCache();
		}
	
		function reset(){
			
			delete_file(DIR_THEMES."/{$this->id}");
			delete_file(DIR_COMPILEDTEMPLATES);
		}
		
		static function cleanUpCache(){
			if(SystemSettings::is_hosted()) {
				$old = DIR_COMPILEDTEMPLATES.'.old';
				if(file_exists($old)) {
					delete_file($old);	
				}
				rename(DIR_COMPILEDTEMPLATES,$old);
			}else {
				delete_file(DIR_COMPILEDTEMPLATES);
			}
		}
		
		function isLocal(){
			
			return ($this->getPath() == DIR_THEMES."/{$this->id}");
		}
	
		function getTitle(){
			
			return $this->xnTheme->getChildData('title');
		}
		
		function installTheme(){
			
		}
		
		function exportTheme(){
			
		}
		
		function importTheme(){
			
		}
	}
?>