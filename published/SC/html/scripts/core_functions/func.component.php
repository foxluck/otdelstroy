<?php
	define('__CPT_RAW_TPL', '__CPT_RAW_TPL');
	define('__CPT_SMARTY', '__CPT_SMARTY_');
	define('__CPT_PARAMS', '__CPT_PARAMS_');

	function cpt_getNameInForm($param_name, $field_name = ''){
		
		if(!$field_name)$field_name = $param_name;
		return 'cpts_'.$param_name.'_'.$field_name;
	}

//----------------------------------------------------
// PRODUCT LIST SELECT
//----------------------------------------------------

	function cptsettingview_product_list_select($params){

		$params['options'] = array();
		$product_lists = ProductList::stc_getLists(false);
		foreach ($product_lists as $_list){
			$params['options'][$_list['id']] = $_list['name'];
		}
		
		return cptsettingview_select($params);
	}
	
	function cptsettingserializer_product_list_select($params, $post){
		
		return cptsettingserializer_select($params, $post);
	}
//----------------------------------------------------
// CUSTOM HTML
//----------------------------------------------------

	function cptsettingview_custom_html($params){

		$value = '';
		if($params['value']){
			
			$code_info = htmlCodesManager::getCodeInfo($params['value']);
			if(isset($code_info['code']))$value = $code_info['code'];
		}
		
		$html = 'html-code
<br />
<input type="hidden" name="cpts_'.$params['name'].'_code_id" value="'.xHtmlSpecialChars($params['value']).'" />
<textarea rows="5" cols="25" name="cpts_'.$params['name'].'_rawcode">'.xHtmlSpecialChars($value).'</textarea>
		';
		return $html;
	}
	
	function cptsettingserializer_custom_html($params, $post){
		
		if(isset($post['rawcode'])){
			$theme_id = isset($_GET['theme_id'])?$_GET['theme_id']:CONF_CURRENT_THEME;
			
			if(isset($post['code_id']) && $post['code_id']){
				
				$code_key = $post['code_id'];
				htmlCodesManager::updateCode($code_key, array('key' => $code_key, 'title' => $theme_id, 'code' => $post['rawcode']),$theme_id);
			}else{
				
				$code_key = htmlCodesManager::renderCodeKey();
				htmlCodesManager::addCode(array('key' => $code_key, 'title' => $theme_id, 'code' => $post['rawcode']),$theme_id);
			}
		}else {
			$code_key = $post[$params['name']];
		}
		return $code_key;
	}
//----------------------------------------------------
// IMAGE FILE
//----------------------------------------------------
	function cptsettingview_image_file($params){
		
		$logo_dir = DIR_IMG;
		
		$params['value'] = preg_replace('@.*(\/|\\\)([^\/\\\]+)$@', '$2', $params['value']);
		$params['value'] = $params['value']&&file_exists($logo_dir.'/'.$params['value'])?$params['value']:'';
		
		if($params['value']){
			
			$log_size = getimagesize($logo_dir.'/'.$params['value']);
		}
		
		$html = '
		<input type="hidden" name="'.cpt_getNameInForm($params['name']).'" value="'.xHtmlSpecialChars($params['value']).'" />
		'.($params['value']?'
		'.xHtmlSpecialChars($params['current_file_title']).'<br />
		<a href="'.URL_IMAGES.'/'.$params['value'].'" class="cpt_dontblock" onclick="open_window(\''.URL_IMAGES.'/'.$params['value'].'\', '.$log_size[0].', '.$log_size[1].');return false;">'.xHtmlSpecialChars($params['value']).'</a> ('.$log_size[0].'x'.$log_size[1].')<br /><br />
		':'').'
		'.xHtmlSpecialChars($params['upload_file_title']).'<br />
		<input type="file" class="cpt_dontblock" name="'.cpt_getNameInForm($params['name'], 'upload_file').'" />
		';
		return $html;
	}

	function cptsettingserializer_image_file($params, $post){

		$Register = &Register::getInstance();
		$logo_dir = DIR_IMG;
		$upload_param_name = cpt_getNameInForm($params['name'], 'upload_file');

		if (isset($_FILES[$upload_param_name]) && $_FILES[$upload_param_name]['name']) {

			$upload_file = $_FILES[$upload_param_name];
			if($upload_file['error'] || !file_exists($upload_file['tmp_name']))return PEAR::raiseError('thm_msg_error_upload');
			if(!preg_match('/\.(jpg|jpeg|gif|jpe|pcx|bmp|png|tif)$/i', $upload_file['name']))return PEAR::raiseError('thm_msg_logo_only_imgs');
			
			if(file_exists($logo_dir.'/'.$upload_file['name']) && $post[$params['name']] != $upload_file['name'])return PEAR::raiseError('thm_msg_logo_exists', null, null, null, array($logo_dir.'/'.$upload_file['name'], $post[$params['name']], $upload_file['name']));
			
			if(!copy($upload_file['tmp_name'], $logo_dir.'/'.$upload_file['name']))return PEAR::raiseError('thm_msg_error_upload');
		
			$post[$params['name']] = $upload_file['name'];
			
			unset($_FILES[$upload_param_name]);

			$Register->set('__CPT_SERPARAM_VAL-'.$params['name'], $post[$params['name']]);
			
		}elseif($Register->is_set('__CPT_SERPARAM_VAL-'.$params['name'])) {
			
			$post[$params['name']] = $Register->get('__CPT_SERPARAM_VAL-'.$params['name']);
		}
		return $post[$params['name']];
	}
//----------------------------------------------------
// TEXT
//----------------------------------------------------
	function cptsettingview_text($params){
		return (isset($params['title'])&&$params['title']?translate($params['title']).'<br>':'')."<input type='text' size='".(isset($params['size'])?$params['size']:10)."' name='cpts_{$params['name']}_{$params['name']}' value='".xHtmlSpecialChars($params['value']?$params['value']:$params['default_value'])."' />";
	}

	function cptsettingserializer_text($params, $post){
		
		return $post[$params['name']];
	}
//----------------------------------------------------
// OVERRIDESTYLE
//----------------------------------------------------
	function cptsettingview_overridestyle($params){
		
		$Register = &Register::getInstance();
		$currentDivision = &$Register->get(VAR_CURRENTDIVISION);
		/*@var $currentDivision Division*/
		if($currentDivision->UnicKey != 'cpt_settings')return '';
		$overrideStyles = &CptOverrideStyles::instance();

		$p = explode(':', $params['value']);
		$enable = $p[0];
		
		if(isset($p[1])){
			
			$style_id = $p[1];
			$style = $overrideStyles->get_styles($style_id);
		}else{
			$style_id = '';
			$style = array();
		}
		
		$html = 
'
<fieldset>
	<legend><input type="checkbox" name="'.cpt_getNameInForm('overridestyle', 'enable').'" '.($enable?'checked="checked"':'').' id="enabled-override" /><label for="enabled-override">'.translate('cpt_ovst_title').'</label></legend>
	<input name="'.cpt_getNameInForm('overridestyle', 'style_id').'" value="'.$style_id.'" type="hidden" />
	
	<p id="ovst-description" '.($enable?'style="display: none;"':'').'>'.translate('cpt_ovst_description').'</p>
	
	<div id="styles-block" '.(!$enable?'style="display: none;"':'').'>
	<table><tr>
	<td>
	'.translate('cpt_ovst_backgroundColor').':
	</td>
	<td width="1%">
	<input type="text" size="8" id="id_'.cpt_getNameInForm('overridestyle', 'cssprop_backgroundColor').'" name="'.cpt_getNameInForm('overridestyle', 'cssprop_backgroundColor').'" value="'.(isset($style['backgroundColor'])?xHtmlSpecialChars($style['backgroundColor']):'').'" />
	</td>
	<td><img src="images_common/color_picker/select_arrow.gif" onmouseover="this.src=\'images_common/color_picker/select_arrow_over.gif\'" onmouseout="this.src=\'images_common/color_picker/select_arrow.gif\'" onclick="showColorPicker(getLayer(\'enabled-override\'),getLayer(\'id_'.cpt_getNameInForm('overridestyle', 'cssprop_backgroundColor').'\'))"></td>
	</tr>
	<tr>
	<td>
	'.translate('cpt_ovst_borderColor').':
	</td>
	<td>
	<input type="text" size="8" id="id_'.cpt_getNameInForm('overridestyle', 'cssprop_borderColor').'" name="'.cpt_getNameInForm('overridestyle', 'cssprop_borderColor').'" value="'.(isset($style['borderColor'])?xHtmlSpecialChars($style['borderColor']):'').'" />
	</td>
	<td><img src="images_common/color_picker/select_arrow.gif" onmouseover="this.src=\'images_common/color_picker/select_arrow_over.gif\'" onmouseout="this.src=\'images_common/color_picker/select_arrow.gif\'" onclick="showColorPicker(getLayer(\'enabled-override\'),getLayer(\'id_'.cpt_getNameInForm('overridestyle', 'cssprop_borderColor').'\'))"></td>
	</tr>
	<tr>
	<td>
	'.translate('cpt_ovst_borderWidth').':
	</td>
	<td>
	<input type="text" size="8" name="'.cpt_getNameInForm('overridestyle', 'cssprop_borderWidth').'" value="'.(isset($style['borderWidth'])?xHtmlSpecialChars($style['borderWidth']):'').'" />
	</td>
	<td></td>
	</tr>
	<tr>
	<td>
	'.translate('cpt_ovst_fontColor').':
	</td>
	<td>
	<input type="text" size="8" id="id_'.cpt_getNameInForm('overridestyle', 'cssprop_fontColor').'" name="'.cpt_getNameInForm('overridestyle', 'cssprop_fontColor').'" value="'.(isset($style['fontColor'])?xHtmlSpecialChars($style['fontColor']):'').'" />
	</td>
	<td><img src="images_common/color_picker/select_arrow.gif" onmouseover="this.src=\'images_common/color_picker/select_arrow_over.gif\'" onmouseout="this.src=\'images_common/color_picker/select_arrow.gif\'" onclick="showColorPicker(getLayer(\'enabled-override\'),getLayer(\'id_'.cpt_getNameInForm('overridestyle', 'cssprop_fontColor').'\'))"></td>
	</tr>
	<tr>
	<td>
	'.translate('cpt_ovst_textAlign').':
	</td>
	<td colspan="2">
		<select name="'.cpt_getNameInForm('overridestyle', 'cssprop_textAlign').'">
		<option value="left"'.(isset($style['textAlign'])&&$style['textAlign']=='left'?' selected':'').'>'.translate('cpt_align_left').'</option>
		<option value="center"'.(isset($style['textAlign'])&&$style['textAlign']=='center'?' selected':'').'>'.translate('cpt_align_center').'</option>
		<option value="right"'.(isset($style['textAlign'])&&$style['textAlign']=='right'?' selected':'').'>'.translate('cpt_align_right').'</option>
		</select>
	</td>
	</tr>
	<tr>
	<td>
	'.translate('cpt_ovst_linkColor').':
	</td>
	<td>
	<input type="text" size="8" id="id_'.cpt_getNameInForm('overridestyle', 'cssprop_linkColor').'" name="'.cpt_getNameInForm('overridestyle', 'cssprop_linkColor').'" value="'.(isset($style['linkColor'])?xHtmlSpecialChars($style['linkColor']):'').'" />
	</td>
	<td><img src="images_common/color_picker/select_arrow.gif" onmouseover="this.src=\'images_common/color_picker/select_arrow_over.gif\'" onmouseout="this.src=\'images_common/color_picker/select_arrow.gif\'" onclick="showColorPicker(getLayer(\'enabled-override\'),getLayer(\'id_'.cpt_getNameInForm('overridestyle', 'cssprop_linkColor').'\'))"></td>
	</tr>
	<tr>
	<td>
	'.translate('cpt_ovst_padding').':
	</td>
	<td>
	<input type="text" size="8" name="'.cpt_getNameInForm('overridestyle', 'cssprop_padding').'" value="'.(isset($style['padding'])?xHtmlSpecialChars($style['padding']):'').'" />
	</td>
	<td></td>
	</tr>
	</table>
	</div>
</fieldset>		
';
		return $html;
	}

	function cptsettingserializer_overridestyle($params, $post, $from_cptsmarty = false){

		if($from_cptsmarty)return $post[$params['name']];
		
		$Register = &Register::getInstance();

		if($Register->is_set('__OVERRIDESTYLE__')&&$Register->get('__OVERRIDESTYLE__')){
			$style_id = $Register->get('__OVERRIDESTYLE__');
		}else{

			$style = array();
			foreach ($post as $key=>$val){
				
				if(strpos($key, 'cssprop_')!==0)continue;
				$style[str_replace('cssprop_', '', $key)] = $val;
			}
			
			$overrideStyles = &CptOverrideStyles::instance();
			
			$style_id = !$post['style_id']?$overrideStyles->get_new_style_id():$post['style_id'];
	
			$overrideStyles->cache_styles($style, $style_id);
			
			$Register->assign('__OVERRIDESTYLE__', $style_id);
		}
		$serdata = (isset($post['enable'])?'1':'').':'.$style_id;
		return $serdata;
	}
//----------------------------------------------------
// RADIOGROUP
//----------------------------------------------------

	function cptsettingview_radiogroup($params){

		$html = translate(isset($params['title'])&&$params['title']?translate($params['title']):'');
		$html .= $html?'<br />':'';
		$cnt = 0;
		foreach ($params['options'] as $opt_value=>$opt_title){
			$rnd = rand_name();
			$cnt++;
			$html .= "<input class='cpt_dontblock' type='radio' name='cpts_{$params['name']}_{$params['name']}' id='cpts-{$params['name']}-{$params['name']}-{$cnt}-{$rnd}' value='".xHtmlSpecialChars($opt_value)."' ".(($params['value']?$params['value']:$params['default_value'])==$opt_value?'checked':'')." />&nbsp;<label for='cpts-{$params['name']}-{$params['name']}-{$cnt}-{$rnd}'>".xHtmlSpecialChars(translate($opt_title))."</label><br />";
		}
		return $html;
	}

	function cptsettingserializer_radiogroup($params, $post){
		
		return $post[$params['name']];
	}
//----------------------------------------------------
// CHECKBOXGROUP
//----------------------------------------------------

	function cptsettingview_checkboxgroup($params){
		if(is_string($params['value']))$params['value'] = explode(':', $params['value']);
		$html = translate(isset($params['title'])&&$params['title']?translate($params['title']):'');
		$html .= $html?'<br />':'';
		$cnt = 0;
		foreach ($params['options'] as $opt_value=>$opt_title){
			$rnd = rand_name();
			$cnt++;
			$checked = in_array($opt_value, is_array($params['value'])?$params['value']:array());
			$html .= "<input class='cpt_dontblock' type='checkbox' name='cpts_{$params['name']}_{$params['name']}[]' id='cpts-{$params['name']}-{$params['name']}-{$cnt}-{$rnd}' value='".xHtmlSpecialChars($opt_value)."' ".($checked?'checked':'')." />&nbsp;<label for='cpts-{$params['name']}-{$params['name']}-{$cnt}-{$rnd}'>".xHtmlSpecialChars(translate($opt_title))."</label><br />";
		}
		return (isset($params['before_load'])?$params['before_load']:'').$html;
	}

	function cptsettingserializer_checkboxgroup($params, $post){
		if(is_array($post[$params['name']]))
			$post[$params['name']] = implode(':', $post[$params['name']]);
		return $post[$params['name']];
	}
//----------------------------------------------------
// SELECT
//----------------------------------------------------

	function cptsettingview_select($params){

		$html = '';
		$cnt = 0;
		if(is_string($params['options'])){
			$__t = explode('||', $params['options']);
			$params['options'] = array();
			foreach ($__t as $__v){
				$__v = explode('::', $__v);
				$params['options'][$__v[0]] = isset($__v[1])?$__v[1]:'';
			}
		}
		foreach ($params['options'] as $opt_value=>$opt_title){

			$html .= "<option value='".xHtmlSpecialChars($opt_value)."' ".(($params['value']?$params['value']:$params['default_value'])==$opt_value?'selected':'').">".xHtmlSpecialChars(translate($opt_title))."</option>";
		}

		return (isset($params['title'])&&$params['title']?translate($params['title']).'<br />':'')."<select".(isset($params['onchange'])?" onchange='".str_replace('\'', "\\\\'",$params['onchange'])."'":'')." id='cpts-{$params['name']}-{$params['name']}' name='cpts_{$params['name']}_{$params['name']}' class='cpt_dontblock'>{$html}</select>".(isset($params['onload'])?$params['onload']:'');
	}

	function cptsettingserializer_select($params, $post){
		
		return $post[$params['name']];
	}
//----------------------------------------------------
	
	function cptsettingview_uknown($params){
		
		return "Uknown setting type - {$params}";
	}
	
	/**
	 * Enter description here...
	 *
	 * @param array $component_info
	 * @param Theme $themeEntry
	 * @return string
	 */
	function cpt_getForm($settings_type, $component_info, &$themeEntry, $values = array()){
		
		$component_settings_descr = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getSettingsVDescr', $component_info['id'], $settings_type);
		if(!isset($component_info[$settings_type])||!is_array($component_info[$settings_type])){
			$component_info[$settings_type] = array();
		}
		$settings = array_merge($component_info[$settings_type], $themeEntry->getComponentSettingsValues($component_info['id']));
		
		$html = '';
		
		if(is_array($component_settings_descr)){
			foreach ($component_settings_descr as $name=>$vdescr){
				if(!function_exists('cptsettingview_'.$vdescr['type'])){
					$html .= '<div id="cpt-layer-'.$name.'">'.cptsettingview_uknown($vdescr['type']).'</div>';
					continue;
				}
				
				if(isset($settings[$name]))$vdescr['params']['value'] = $settings[$name];
				if(isset($values[$name]))$vdescr['params']['value'] = $values[$name];
				$html .= '<div id="cpt-layer-'.$name.'">'.call_user_func('cptsettingview_'.$vdescr['type'], $vdescr['params']).'</div>';
			}
		}
		
		return $html;
	}

	function cpt_parseParamsString($params_str){
		
		$params = array();
		
		$params_str = explode(";", $params_str);
		for ($i=0, $len=count($params_str);$i<$len;$i++){
			
			$params_str[$i] = explode(':', $params_str[$i], 2);
			$params[$params_str[$i][0]] = isset($params_str[$i][1])?$params_str[$i][1]:null;
		}
		
		return $params;
	}
	
	function cpt_getSmarty($component_info, $params){
		
		$component_lsettings_descr = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getSettingsVDescr', $component_info['id'], 'local_settings');
		
		$params_string = '';
		
		foreach ($component_lsettings_descr as $name=>$vdescr){
			
			if(!function_exists('cptsettingserializer_'.$vdescr['type'])){
				print 'Uknown setting handler - cptsettingserializer_'.$vdescr['type'];die;
				continue;
			}
			
			$ser_val = call_user_func_array('cptsettingserializer_'.$vdescr['type'], array($vdescr['params'], isset($params[$name])?$params[$name]:array()));
			if(PEAR::isError($ser_val))return $ser_val;

			$params_string .= " $name='".str_replace("'","\\'", $ser_val)."'";
		}

		return "{cpt_{$component_info['id']}{$params_string}}";
	}
	
	function cpt_getParamsFromForm($component_info, &$raw_data, $params_type = 'local_settings'){
		
		$component_lsettings_descr = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getSettingsVDescr', $component_info['id'], $params_type);
		
		$params = array();
		
		foreach ($component_lsettings_descr as $name=>$vdescr){
			
			if(!function_exists('cptsettingserializer_'.$vdescr['type'])){
				print 'Uknown setting handler - cptsettingserializer_'.$vdescr['type'];die;
				continue;
			}
			
			$post = array();
			
			foreach ($raw_data as $key=>$val){
				
				if(!preg_match("@^cpts_{$name}_(.*)$@", $key, $sp))continue;
				
				$post[$sp[1]] = $val;
			}
			
			$params[$name] = $post;
		}
		
		return $params;
	}
	
	function cpt_getParamsFromFormExt($component_info, &$raw_data, $params_type = 'local_settings'){
		
		$component_lsettings_descr = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getSettingsVDescr', $component_info['id'], $params_type);
		
		$params = array();
		
		foreach ($component_lsettings_descr as $name=>$vdescr){
			
			if(!function_exists('cptsettingserializer_'.$vdescr['type'])){
				print 'Uknown setting handler - cptsettingserializer_'.$vdescr['type'];die;
				continue;
			}
			
			$post = array();
			
			foreach ($raw_data as $key=>$val){
				
				if(!preg_match("@^cpts_{$name}_(.*)$@", $key, $sp))continue;
				
				$post[$sp[1]] = $val;
			}
			
			$params[$name] = array('description' => $vdescr, 'data' => $post);
		}
		
		return $params;
	}

	/**
	 * For cpt_getParamsFromFormExt
	 *
	 * @param mixed $data
	 */
	function cpt_serializeData($data){

		$ser_data = array();
		foreach ($data as $name=>$info){
			
			if(!function_exists('cptsettingserializer_'.$info['description']['type'])){
				print 'Uknown setting handler - cptsettingserializer_'.$info['description']['type'];die;
				continue;
			}
			
			$ser_data[$name] = call_user_func_array('cptsettingserializer_'.$info['description']['type'], array($info['description']['params'], $info['data']));
			if(PEAR::isError($ser_data[$name]))return $ser_data[$name];
		}
		
		return $ser_data;
	}
?>