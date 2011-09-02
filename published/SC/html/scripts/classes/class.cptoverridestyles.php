<?php
class CptOverrideStyles{
	
	/**
	 * @var Theme
	 */
	var $themeEntry;
	
	/**
	 * @return CptOverrideStyles
	 */
	function instance(){
		
		$Register = &Register::getInstance();

		$overrideStyles = new CptOverrideStyles();
		$overrideStyles = &$overrideStyles;
		$overrideStyles->themeEntry = $Register->get('CURRENT_THEME_ENTRY');
		
		return $overrideStyles;
	}
	
	function erase_cache(){
		
		unsetWData('__OVERRIDESTYLES_CACHE__');
	}
	
	function get_styles($style_id = null){
		
		if(issetWData('__OVERRIDESTYLES_CACHE__'))$styles = loadWData('__OVERRIDESTYLES_CACHE__');
		else{
			$php_styles_file = $this->themeEntry->getPath().'/overridestyles.php';
			$styles = array();
			
			if(!file_exists($php_styles_file))return $styles;
			
			$styles = unserialize(file_get_contents($php_styles_file));
			storeWData('__OVERRIDESTYLES_CACHE__', $styles);
		}
		return !is_null($style_id)&&isset($styles[$style_id])?$styles[$style_id]:$styles;
	}
	
	function save_cache(){
		
		if(!issetWData('__OVERRIDESTYLES_CACHE__'))return false;
		
		$this->_save_styles_php(loadWData('__OVERRIDESTYLES_CACHE__'));
		$this->_save_styles_css(loadWData('__OVERRIDESTYLES_CACHE__'));
	}
	
	function cache_styles($style, $style_id = null){

		if(!is_null($style_id)){
			$all_styles = $this->get_styles();
			$all_styles[$style_id] = $style;
		}else{
			$all_styles = $style;
		}
		
		storeWData('__OVERRIDESTYLES_CACHE__', $all_styles);
		/**
		 * TODO: test
		 */
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		if($Register->is_set('__OVERRIDESTYLES_CACHE_CSSFILE__')){
			$file_cache = $this->themeEntry->getPath().'/'.$Register->get('__OVERRIDESTYLES_CACHE_CSSFILE__');
		}else{
			delete_file($this->themeEntry->getPath().'/temp_overridestyles');
			checkPath($this->themeEntry->getPath().'/temp_overridestyles');
			$file_cache = '/temp_overridestyles/overridestyles_cache_'.time().'.css';
			$Register->assign('__OVERRIDESTYLES_CACHE_CSSFILE__', $file_cache);
			$file_cache = $this->themeEntry->getPath().'/'.$file_cache;
		}
		$this->_save_styles_css($all_styles, $file_cache);
		return $file_cache;
		/**/
	}
	
	function _save_styles_php($styles){
		
		$php_styles_file = $this->themeEntry->getPath().'/overridestyles.php';
		$fp = fopen($php_styles_file, 'w');
		fwrite($fp, serialize($styles));
		fclose($fp);
	}
	
	function _save_styles_css($styles, $file = null){
		
		$css_styles_file = is_null($file)?$this->themeEntry->getPath().'/overridestyles.css':$file;
		$fp = fopen($css_styles_file, 'w');
		
		if(is_array($styles))foreach ($styles as $style_id=>$style){
			if(!is_array($style))continue;
			$class_id = 'cptovst_'.preg_replace('/[^a-zA-Z0-9]/u','_',$style_id);
			foreach ($style as $property=>$val){
				
				$val = preg_replace('/[^a-zA-Z0-9]/u','',$val);
				if(!$val)continue;
				$string = '';
				switch ($property){
				case 'backgroundColor':
					$string = '.'.$class_id.'{background-color: #'.$val.'!important;}';
					break;
				case 'borderColor':
					$string = '.'.$class_id.'{border-color: #'.$val.'!important; border-style:solid;}';
					break;
				case 'borderWidth':
					$string = '.'.$class_id.'{border-width: '.$val.'px!important;}';
					break;
				case 'fontColor':
					$string = '.'.$class_id.' *,.'.$class_id.'{color: #'.$val.'!important;}';
					break;
				case 'textAlign':
					$string = '.'.$class_id.' *,.'.$class_id.'{text-align: '.$val.'!important;}';
					break;
				case 'linkColor':
					$string = '.'.$class_id.' * a,.'.$class_id.' a{color: #'.$val.'!important;}';
					break;
				case 'padding':
					$val = intval($val);
					$string = '.'.$class_id.'{padding: '.$val.'px!important;}';
					break;
				}
				if($string)fwrite($fp, $string."\n");
			}
			fwrite($fp, "\n\n");
		}
		fclose($fp);
	}
	
	function get_new_style_id(){
		
		$styles = $this->get_styles();
		do{
			$style_id = rand_name(6);
		}while(array_key_exists($style_id, $styles) && isset($styles[$style_id]));
		
//		$styles[$style_id] = array();
//		
//		$this->save_styles($styles);
		
		return $style_id;
	}
}
?>