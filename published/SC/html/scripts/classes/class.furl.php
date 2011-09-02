<?php
//	ClassManager::includeClass('Category');
//	ClassManager::includeClass('Product');

	class fURL{

		var $__path = '';
		var $__path_settings = array();

		/**
		 * Enter description here...
		 *
		 * @param URL $urlEntry
		 * @return unknown
		 */
		static function get_real_path($urlEntry = null){
			
			$Register = &Register::getInstance();
			/*@var $Register Register*/
			$path = $Register->get('FURL_PATH');
			if(!$path && isset($_GET['__furl_path']))$path = $_GET['__furl_path'];
			
			if(is_null($urlEntry)){
				$urlEntry = &$Register->get(VAR_URL);
				/*@var $Register URL*/
				if(!is_object($urlEntry)){
					ClassManager::includeClass('URL');
					$urlEntry = new URL;
					$urlEntry->loadFromServerInfo();
				}
			}
			return preg_replace('/\/[^\/]+$/u', '/', str_replace($path, '', $urlEntry->getPath()));
		}
		
		static function exec($path = null){

			if(is_null($path))$path = isset($_GET['__furl_path'])?$_GET['__furl_path']:'';
						
			$fURL = new fURL();
			$fURL->__path = $path;
			$fURL->__parsePath();
			$fURL->__updateSystemVars();
		}
		
		function __updateSystemVars(){
			
			$get_vars = $this->__renderGetString();
			$_SERVER['REQUEST_URI'] = fURL::get_real_path();
			
			renderURL($get_vars.'&__furl_path='.(isset($this->__path_settings['language_iso2'])?'&lang_iso2='.$this->__path_settings['language_iso2']:''), '', true);
		}
		
		function __renderGetString(){
			
			if(!isset($this->__path_settings['update_sys_handler']))$this->__path_settings['update_sys_handler'] = '';
			$get_vars = '';
			switch ($this->__path_settings['update_sys_handler']){
				case 'category':
					$categoryEntry = new Category();
					$categoryEntry->loadBySlug($this->__path_settings['category_slug']);
					if($categoryEntry->categoryID){
						$get_vars .= '&categoryID='.$categoryEntry->categoryID.'&category_slug='.$this->__path_settings['category_slug'];
					}else{
						$get_vars .= '&categoryID='.$this->__path_settings['category_slug'];
					}
					if($this->__path_settings['category_search']){
						$get_vars .= '&ukey=category_search';
					}else{
						$get_vars .= '&ukey=category';
					}
					if(isset($this->__path_settings['page']))$get_vars .= '&page='.$this->__path_settings['page'];
					break;
				case 'product':
					$productEntry = new Product();
					$productEntry->loadBySlug($this->__path_settings['product_slug']);
					if($productEntry->productID){
						
						$categoryID = $productEntry->categoryID;
						$get_vars .= '&productID='.$productEntry->productID.'&product_slug='.$this->__path_settings['product_slug'];
					}else{
						
						$productEntry->loadByID($this->__path_settings['product_slug']);
						$categoryID = $productEntry->categoryID;
						$get_vars .= '&productID='.$this->__path_settings['product_slug'];
					}
					
					$Register = &Register::getInstance();
					/*@var $Register Register*/
					$Register->set('categoryID', $categoryID);
					if(isset($this->__path_settings['product_widget'])){
						$get_vars .= '&ukey=product_widget';
					}else{
						$get_vars .= $this->__path_settings['ukey']!='reviews'?'&ukey=product':'&ukey=discuss_product';
					}
					break;
				default:
					if(isset($this->__path_settings['ukey']))$get_vars .= '&ukey='.$this->__path_settings['ukey'];
					$get_vars .= isset($this->__path_settings['page'])?'&news_page='.$this->__path_settings['page']:'';
					break;
			}
			
			if(isset($this->__path_settings['get'])){
				
				$get_vars .= $this->__path_settings['get'];
			}
			return $get_vars;
		}
		
		static function convertGetToPath(&$request, &$get){
				    
		    if(!MOD_REWRITE_SUPPORT) return;
			unset($get['__furl_path']);
//			if(!count($get))return;
			$Register = &Register::getInstance();
			/*@var $Register Register*/
			if(($Register->get('admin_mode') && !isset($get['furl_enable']))
				|| isset($get['furl_disable'])){
				unset($get['furl_disable']);
				return;
			}
			if(isset($get['furl_enable'])){
				unset($get['furl_enable']);
			}

			$rest_part = '';
			if(preg_match('@([^\?\&]+)([\?\&].*)$@msi', $request, $sp)){
				list($sp, $request, $rest_part) = $sp;
			}
			$request = str_replace('?', '', $request);
			
			$urlEntry = $Register->get(VAR_URL);
			/*@var $urlEntry URL*/
			if((strpos($request, 'http') === 0 && $urlEntry->getHost() == preg_replace('/https?:\/\/([^\/]*).*$/u', '$1', $request)
				|| strpos($request, 'http') === false)
				){
				$_fURL = new fURL();
				$real_path = fURL::get_real_path();
				if($real_path != '/'){
					$_fURL->__path = str_replace($real_path, '', $request);
				}else{
					$_fURL->__path = (substr($request,0,1)=='/')?substr($request,1):$request;
				}
				$_fURL->__path = preg_replace('/[^\/]*$/', '', $_fURL->__path);
				$_fURL->__path = str_replace('http://', '', $_fURL->__path);
				if($_fURL->__path){
					$_fURL->__parsePath();
					$rget_string = $_fURL->__renderGetString();
					$r_TokenStrs = explode('&', $rget_string);
					$rget = array();
					foreach ($r_TokenStrs as $TokenStr){
						
						$r_Token = explode('=', $TokenStr,2);
						if(isset($r_Token[1])){
							$rget[$r_Token[0]] = $r_Token[1];
						}
					}
					$get = array_merge($rget, $get);
				}
				if(strpos($request, 'http') === 0 && $urlEntry->getHost() == preg_replace('/https?:\/\/([^\/]*).*$/u', '$1', $request)){
					
					$urlEntry->set($request);
					$urlEntry->setPath(fURL::get_real_path($urlEntry));
					$urlEntry->setQuery('?');
					$request = $urlEntry->getURI();
				}else{
					
					$request = fURL::get_real_path();
				}
			}
			
			if(!isset($get['ukey'])||($get['ukey'] != 'category_search')){
				$request = str_replace('category_search/', '', $request);
			}
			
			//storefront mode workaround
			if(isset($get['store_mode'])&&$get['store_mode']){
				$request .= $get['store_mode'].'/';
				unset($get['store_mode']);
			}
			
			if(isset($get['lang_iso2'])){

				$defaultLanguage = &LanguagesManager::getDefaultLanguage();
				$request .= $defaultLanguage->iso2 == $get['lang_iso2']?'':$get['lang_iso2'].'/';
				unset($get['lang_iso2']);
			}
			if(isset($get['refid'])){
				$request .= 'referral/'.$get['refid'].'/';
				unset($get['refid']);
				unset($get['ukey']);
			}elseif(isset($get['categoryID'])){

				$request .= 'category/'.(isset($get['category_slug'])?$get['category_slug']:$get['categoryID']).'/';
				if(isset($get['ukey']) && $get['ukey'] == 'product_comparison'){
					$request .= 'compare/';
				}
				if(isset($get['ukey']) && $get['ukey'] == 'category_search'){
					$request = str_replace('category_search/', '', $request);
					$request .= 'search/';
				}
				unset($get['category_slug']);
				unset($get['categoryID']);
				unset($get['ukey']);
				unset($get['did']);
				if(isset($get['page'])){
					
					$request .= "page{$get['page']}/";
					unset($get['page']);
				}
			}elseif(isset($get['product_slug']) || isset($get['productID']) && $get['ukey'] != 'cart'){
				
				if(isset($get['ukey'])&&($get['ukey'] == 'product_widget')){
					$request .= "product_widget/";
				}else{
					$request .= "product/";
				}
				$request .= (isset($get['product_slug'])?$get['product_slug']:$get['productID']).(isset($get['ukey'])&&$get['ukey']=='discuss_product'?'/reviews/':'/');
				unset($get['product_slug']);
				unset($get['productID']);
				unset($get['ukey']);
				unset($get['did']);
			}elseif(isset($get['ukey'])){
				
				switch ($get['ukey']){
					case 'transaction_result':
						$request .= 'transaction/'.(isset($get['transaction_result'])?$get['transaction_result'].'/':'');
						unset($get['transaction_result']);
						break;
					case 'product_comparison':
						$request .= "compare/";
						break;
					case 'office':
						$request .= "myaccount/";
						break;
					case 'news':
						$get['ukey'] = 'blog';
					case 'blog':
					case 'cart':
						$request .= "{$get['ukey']}/";
						if(($get['ukey']=='news' || $get['ukey']=='blog') && isset($get['news_page'])){
							$request .= "page{$get['news_page']}/";
							unset($get['news_page']);
							unset($get['page']);
						};
						if(array_key_exists('blog_id', $get))
						{
						    $request .= $get['blog_id'].'/';
						    unset($get['blog_id']);
						};
						break;
					default:
						$request .= "{$get['ukey']}/";
				}
				unset($get['ukey']);
				unset($get['did']);
			}
			if(isset($get['show_all'])){
				
				$request .= 'all/';
				unset($get['show_all']);
				unset($get['page']);
			}elseif(isset($get['page'])){
				
				$request .= 'page'.$get['page'].'/';
				unset($get['page']);
			}elseif(isset($get['offset'])){
				
				$request .= 'offset'.$get['offset'].'/';
				unset($get['offset']);
			}
			$request .= $rest_part;
			$request = str_replace('TitlePage.html', '', $request);
			$request = str_replace('TitlePage/', '', $request);
			$request = str_replace(array('home/', 'page1/', 'offset0/'), '', $request);
			//DEBUG:
			if(false&&($fp = fopen(DIR_TEMP.'/request.log','a'))){
				fwrite($fp,"{$request}\t".str_replace('/',"\t",$request)."\n");
				fclose($fp);
			}
		}
		
		function __parsePath(){
			$path_parts = explode('/', $this->__path);
			$this->__path_settings = array();
			$parts_num = count($path_parts);
			$i = 0;
			
			while($parts_num > $i++){
			
				$part_value = array_shift($path_parts);
				if(!$part_value)break;
				switch ($i){
					case 1:
						if(strlen($part_value) == 2){//It is language iso2
							$languageEntry = LanguagesManager::getLanguageByISO2($part_value);
							if(!is_null($languageEntry)&&$languageEntry->enabled){
							
								LanguagesManager::setCurrentLanguage($languageEntry->id, false);
								$this->__path_settings['language_iso2'] = $part_value;
								$this->__path_settings['get'] .= '&lang_iso2='.$part_value;
							}
							break;
						}
						if(in_array($part_value,array('facebook','vkontakte'))){
							$this->__path_settings['store_mode'] = $part_value;
							$this->__path_settings['get'] .= '&store_mode='.$part_value;
							break;
						}
					default:
						switch ($part_value){
							case 'referral':
								$this->__path_settings['get'] = '&refid='.array_shift($path_parts);
								continue;
							case 'category':
								$this->__path_settings['category_slug'] = str_replace('.html', '', array_shift($path_parts));
								$this->__path_settings['category_search'] = false;
								if(isset($path_parts[0]) && $path_parts[0] == 'search'){
									$this->__path_settings['category_search'] = true;
								}
								$this->__path_settings['update_sys_handler'] = 'category';
								continue;												
							case 'product_widget':
								$this->__path_settings['product_widget'] = true;
							case 'product':
								$this->__path_settings['update_sys_handler'] = 'product';
								$this->__path_settings['product_slug'] = str_replace('.html', '', array_shift($path_parts)); 
								continue;
							case 'blog':
								$part_value = 'news';
							case 'news':
								$this->__path_settings['ukey'] = str_replace('.html', '', $part_value);
								if(isset($path_parts[0]) && strpos($path_parts[0], 'page')===0){
									
									$this->__path_settings['page'] = intval(substr($path_parts[0], 4));
								}
								if(intval($path_parts[0]) > 0)
								{
								    $this->__path_settings['get'] = '&blog_id='.intval($path_parts[0]);
								}
								continue;
							case 'compare':
								$this->__path_settings['ukey'] = 'product_comparison';
								$this->__path_settings['update_sys_handler'] = '';
								continue;
							case 'myaccount':
								$this->__path_settings['ukey'] = 'office';
								continue;
							case 'transaction':
								$this->__path_settings['ukey'] = 'transaction_result';
								$this->__path_settings['get'] .= '&transaction_result='.array_shift($path_parts);
								continue;
							default:
								if(strpos($part_value, 'page')===0){
									
									$part_value = str_replace('.html', '', $part_value);
									$this->__path_settings['get'] .= '&page='.intval(substr($part_value, 4));
								}elseif(strpos($part_value, 'offset') === 0){
									
									$part_value = str_replace('.html', '', $part_value);
									$this->__path_settings['get'] .= '&offset='.intval(substr($part_value, 6));
								}elseif(strpos($part_value, 'all') === 0){
									
									$this->__path_settings['get'] .= '&show_all=1';
								}elseif(!array_key_exists('ukey', $this->__path_settings)){
									
									$this->__path_settings['ukey'] = str_replace(array('.html','.php'), '', $part_value);
								}
								continue;
						}
						
						break;
				}
			}
		}
	}
?>