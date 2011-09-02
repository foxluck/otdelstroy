<?php

class Parser1CXml 
{
	private $currency = array();
	private $rate = 1;
	
	private $heartbeat;
	
	function __construct($heartbeat = true) {
		$this->heartbeat = $heartbeat;
		if ($heartbeat) {
			Heartbeat::start();
		}
	}
	
	public function parce($filename)
	{
		File::chmod($filename);
//		File::convert($filename, 'cp1251', 'utf-8');
		
		$valid = false;
		
		$reader = new XMLReader();
		$reader->open($filename);
		while ($reader->read()) {
			
			if ( $reader->name == "Классификатор" ) {
				$valid = true;
				$currentCatedoryId = 1;
				$categoryIdStack = array();
				$categoryMap = array();
				$reader->read();
				while ($reader->read() && 
				       !($reader->name == "Классификатор" && 
				       $reader->nodeType == XMLReader::END_ELEMENT)  ) 
				{
					if ($reader->name == "Группы" && 
						$reader->nodeType == XMLReader::ELEMENT &&
						str_replace(' ', '', $reader->readOuterXml()) == '<Группы/>') 
					{
							continue;
					}
					
					if ($reader->name == "Группы" && $reader->nodeType == XMLReader::ELEMENT) {
						array_unshift($categoryIdStack, $currentCatedoryId);
					}
					if ($reader->name == "Группы" && $reader->nodeType == XMLReader::END_ELEMENT) {
						array_shift($categoryIdStack);
					}
					
					if ($reader->name == "Группа" && $reader->nodeType == XMLReader::ELEMENT) 
					{
						$element = $reader->readOuterXml();
						$element = simplexml_load_string(trim($element));
						
						$catedory = db_phquery_fetch(DBRFETCH_ASSOC, "SELECT * FROM ?#CATEGORIES_TABLE WHERE id_1c = ?", $element->Ид);
						
						$categoryEntry = new Category;
						$categoryEntry->__use_cache = false;
						
						if ($catedory) {
							$categoryEntry->loadFromArray($catedory);
						}
						
						$parent = $categoryIdStack[0];
						// if flat categories structure
						if ( isset($element->Родитель) ) {
							$parent_uuid = (string)$element->Родитель;
							if ( !empty( $parent_uuid ) ) {
								$parent = (int)$categoryMap[(string)$element->Родитель];
							}
							else {
								$categoryEntry->categoryID = 1;
							}
						}
						
						if ($categoryEntry->categoryID != 1) {
							$categoryEntry->parent = $parent;
							$categoryEntry->{LanguagesManager::ml_getLangFieldName('name')} = $element->Наименование;
						}
						$categoryEntry->id_1c = $element->Ид;
						$categoryEntry->save();
						
						$currentCatedoryId = $categoryEntry->categoryID;
						$categoryMap[(string)$element->Ид] = $categoryEntry->categoryID;
						unset($categoryEntry);
						
						unset($element);
					}
					
				} 
			} // if $reader->name == "Классификатор"

			
			if ( $reader->name == "Товары" ) {
				$valid = true;
				
				$categories_ids = array();
				$categories = db_phquery_fetch(DBRFETCH_ASSOC_ALL, "SELECT categoryID, id_1c FROM ?#CATEGORIES_TABLE");
				foreach ($categories as $category) { 
					$categories_ids[$category['id_1c']] = $category['categoryID'];
				}
				unset($categories);
				
				while ($reader->read() && 
				       !($reader->name == "Товары" && 
				       $reader->nodeType == XMLReader::END_ELEMENT)  ) 
				{
					if ($reader->name == "Товар" && $reader->nodeType == XMLReader::ELEMENT) 
					{
						if ( $this->heartbeat && !Heartbeat::next("Товар", $reader) ){
							return false;
						}
						
						$element = $reader->readOuterXml();
						$element = simplexml_load_string(trim($element));
						
						$product = db_phquery_fetch(DBRFETCH_ASSOC, "SELECT * FROM ?#PRODUCTS_TABLE WHERE id_1c = ?", $element->Ид);
						
						$productEntry = new Product();
						$productEntry->__use_cache = false;
						
						if ($product) {
							$productEntry->loadFromArray($product);
						}
						
						$name = (string)$element->Наименование;
						
						if (isset($element->ХарактеристикиТовара) && isset($element->ХарактеристикиТовара->ХарактеристикаТовара)) {
							$properties_str = array();
							foreach ($element->ХарактеристикиТовара->ХарактеристикаТовара as $property) {
								$properties_str[] = (string)$property->Значение;
							}
							if (count($properties_str)>0) {
								$properties_str = implode(', ', $properties_str);
								$name .= " ({$properties_str})";
							}
						}
						
						$productEntry->categoryID = $categories_ids[ (string)$element->Группы->Ид ];
						$productEntry->product_code = (string)$element->Артикул;
						$productEntry->{LanguagesManager::ml_getLangFieldName('name')} = $name;
						$productEntry->id_1c = (string)$element->Ид;
						$productEntry->ordering_available= 1;
						
						$brief_description = htmlspecialchars((string)$element->Описание);
						
						$descs = (array)$element->ЗначенияРеквизитов;
						if (is_array($descs['ЗначениеРеквизита'])) {
							foreach ($descs['ЗначениеРеквизита'] as $desc) {
								$desc = (array)$desc;
								if ($desc['Наименование'] == "Полное наименование") {
									$brief_description = $desc['Значение'];
									break;
								}
								if ($desc['Наименование'] == "Вес") {
									$weight = $desc['Значение'];
									if ( defined('CONF_WEIGHT_UNIT') && constant('CONF_WEIGHT_UNIT') == 'g' ) {
										$weight *= 1000;
									}
									if (intval($weight) > 0) {
										$productEntry->weight = $weight;
									}
									break;
								}
							}
						}
						else {
							$desc = (array) $descs['ЗначениеРеквизита'];
							if ($desc['Наименование'] == "Полное наименование") {
								$brief_description = $desc['Значение'];
								
							}
							if ($desc['Наименование'] == "Вес") {
									$weight = $desc['Значение'];
									if ( defined('CONF_WEIGHT_UNIT') && constant('CONF_WEIGHT_UNIT') == 'g' ) {
										$weight *= 1000;
									}
									$productEntry->weight = $weight;
									break;
								}
						}
						
						$description = htmlspecialchars((string)$element->Описание);
						if (!empty($description)) {
							$productEntry->{LanguagesManager::ml_getLangFieldName('description')} = $description;
						}
						if (!empty($brief_description)) {
							$productEntry->{LanguagesManager::ml_getLangFieldName('brief_description')} = $brief_description;
						}
						
						$productEntry->correctData();
						$productEntry->save();
						
						if ((string)$element->Картинка) {
							$DIR_NAME = DIR_TEMP."/";
							$image_path = $DIR_NAME . (string)$element->Картинка;
							
							if ( file_exists($image_path) ) { 
								
								if(SystemSettings::is_hosted()){
									$fileEntry = new WbsFiles('SC');
								}
								else {
									$fileEntry = new FileWBS();
								}
								
								Functions::register($fileEntry, 'file_copy', 'copy');
								Functions::register($fileEntry, 'file_move', 'move');
								Functions::register($fileEntry, 'file_remove', 'remove');
								
								$file_name_ = explode("/", $image_path);
								$orig_file = $image_path;
								$file_name = array_pop($file_name_);
								
								/**
								 * Standard picture
								 */
								$temp_file = DIR_TEMP.'/'.getUnicFile(4, 'img.s.%s.temp', DIR_TEMP);
								$standard_file_name = $file_name;
					
								if(	!file_exists(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name) 
									|| (file_exists(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name) 
										&& md5_file($orig_file) == md5_file(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name)) ) 
								{
									//$standard_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $file_name), DIR_PRODUCTS_PICTURES);
								
									if(
									PEAR::isError($res = Functions::exec('img_resize', array($orig_file, CONF_PRDPICT_STANDARD_SIZE, CONF_PRDPICT_STANDARD_SIZE, $temp_file)))
									||
									PEAR::isError($res = Functions::exec('file_copy', array($temp_file, DIR_PRODUCTS_PICTURES.'/'.$standard_file_name)))
									){
										$error = $res;
										if(file_exists($temp_file)){
											unlink($temp_file);
										}
										Functions::exec('file_remove', array($orig_file));
										break;
									}
									if(file_exists($temp_file)){
										unlink($temp_file);
									}
									
									/**
									 * Thumbnail picture
									 */
									$temp_file = DIR_TEMP.'/'.getUnicFile(4, 'img.t.%s.temp', DIR_TEMP);
									$thumbnail_file_name = preg_replace('@\.([^\.]+)$@', '_thm.$1', $file_name);
//									if(file_exists(DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name))
//									$thumbnail_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $thumbnail_file_name), DIR_PRODUCTS_PICTURES);
						
									if(
									PEAR::isError($res = Functions::exec('img_resize', array($orig_file, CONF_PRDPICT_THUMBNAIL_SIZE, CONF_PRDPICT_THUMBNAIL_SIZE, $temp_file)))
									||
									PEAR::isError($res = Functions::exec('file_copy', array($temp_file, DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name)))
									){
										$error = $res;
										if(file_exists($temp_file)){
											unlink($temp_file);
										}
										Functions::exec('file_remove', array($orig_file));
										Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name));
										break;
									}
									if(file_exists($temp_file)){
										unlink($temp_file);
									}
						
									/**
									 * Enlarged picture
									 */
									$temp_file = DIR_TEMP.'/'.getUnicFile(4, 'img.e.%s.temp', DIR_TEMP);
									$orig_size = getimagesize($orig_file);
									$standard_size = getimagesize(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name);
									
									if(($orig_size[0]>$standard_size[0]) || ($orig_size[1]>$standard_size[1])){
						
										$enlarged_file_name = preg_replace('@\.([^\.]+)$@', '_enl.$1', $file_name);
//										if(file_exists(DIR_PRODUCTS_PICTURES.'/'.$enlarged_file_name))
//										$enlarged_file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $enlarged_file_name), DIR_PRODUCTS_PICTURES);
						
										if(
										PEAR::isError($res = Functions::exec('img_resize', array($orig_file, CONF_PRDPICT_ENLARGED_SIZE, CONF_PRDPICT_ENLARGED_SIZE, $temp_file)))
										||
										PEAR::isError($res = Functions::exec('file_copy', array($temp_file, DIR_PRODUCTS_PICTURES.'/'.$enlarged_file_name)))
										){
											$error = $res;
											if(file_exists($temp_file)){
												unlink($temp_file);
											}
											Functions::exec('file_remove', array($orig_file));
											Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES.'/'.$enlarged_file_name));
											Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES.'/'.$standard_file_name));
											Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES.'/'.$thumbnail_file_name));
											break;
										}
									}else {
						
										$enlarged_file_name = '';
									}
									if(file_exists($temp_file)){
										unlink($temp_file);
									}
						
									$productID = $productEntry->{$productEntry->__primary_key};
									
									if ( !db_phquery_fetch(DBRFETCH_FIRST, "SELECT 1 FROM ?#PRODUCT_PICTURES WHERE productID = ? AND filename = ?", $productID, $standard_file_name) )
									{
										db_phquery("
										INSERT ?#PRODUCT_PICTURES (productID, filename, thumbnail, enlarged, priority)
										VALUES( ?, ?, ?, ?,?)", $productID, $standard_file_name, $thumbnail_file_name, $enlarged_file_name, 0);
									}
								}
							}
						}
						
						unset($productEntry);
						unset($element);
					}
			    }
			} // end $reader->name == "Товары"
			
			if ( $reader->name == "ПакетПредложений" ) {
				$valid = true;
				
				
				while ($reader->read() && 
				       !($reader->name == "ПакетПредложений" && 
				       $reader->nodeType == XMLReader::END_ELEMENT)  ) 
				{
					if ($reader->name == "Предложение" && $reader->nodeType == XMLReader::ELEMENT) 
					{
						if ( $this->heartbeat && !Heartbeat::next("Предложение", $reader) ){
							return false;
						}
						
						$element = $reader->readOuterXml();
						$element = simplexml_load_string(trim($element));
						
						$productData = db_phquery_fetch(DBRFETCH_ASSOC, "SELECT * FROM ?#PRODUCTS_TABLE WHERE id_1c = ?", $element->Ид);
						
						if ($productData) {
							$productEntry = new Product();
							$productEntry->__use_cache = false;
							$productEntry->loadFromArray($productData);
							
							$priceValue = false;
							if ( $element->Цены->Цена[1] ) {
								foreach ( $element->Цены->Цена as $price) {
									$price = (array)$price;
									if ((string)$price['ИдТипаЦены'] == (string)$this->currency["Розничная"]["id"]) {
										$priceValue = (string)$price['ЦенаЗаЕдиницу'];
										break;
									}
								}
							}
							if ($priceValue == false) {
								$priceValue = (string) $element->Цены->Цена->ЦенаЗаЕдиницу;
							}
							
							$priceValue = (float)str_replace(array(' ', ','), array('', '.'), $priceValue);
							$priceValue = $priceValue / (float)$this->rate;
							if ($priceValue != 0) {
								$productEntry->Price = $priceValue;
							}
							$productEntry->in_stock = (string)$element->Количество;
							
							$productEntry->correctData();
							$productEntry->save();
				
							unset($productEntry);
						}
						unset($element);
					}
					if ($reader->name == "ТипЦены" && $reader->nodeType == XMLReader::ELEMENT) {
						$element = $reader->readOuterXml();
						$element = simplexml_load_string(trim($element));
						
						$this->currency[(string)$element->Наименование] = array(
							"id" => (string)$element->Ид, 
							"currency" => (string)$element->Валюта,
						);
					}
					if ($reader->name == "ТипыЦен" && $reader->nodeType == XMLReader::END_ELEMENT) {
						if ( !isset($this->currency["Розничная"]) ) {
							$this->currency["Розничная"] = array_shift($this->currency);
						}
						
						if (	$this->currency["Розничная"]["currency"] == Currency::getDefaultCurrencyInstance()->currency_iso_3 ||
								(in_array($this->currency["Розничная"]["currency"], array("руб", "RUR", "RUB") &&
								 in_array(Currency::getDefaultCurrencyInstance()->currency_iso_3, array("руб", "RUR", "RUB") )))
						) {
							$this->rate = 1;
						}
						else {
							$currency = currGetCurrencyByISO3($this->currency["Розничная"]["currency"]);
							if ($currency) {
								$this->rate = $currency['currency_value'];
							}
							else {
								$this->rate = 1;
							}
						}
					}
			    }
			} // end $reader->name == "Предложения"
		} // end $reader->read()
		
		return $valid;
	}
}


class Heartbeat
{
	static $step = array();
	static $start_time;
	static $max_time = 2; //sec
	
	static function next($type, $reader)
	{
		if ( !isset($_SESSION["SHOPSCRIPT_1C_IMPORT"]["heartbeat"] ))  {
			$_SESSION["SHOPSCRIPT_1C_IMPORT"]["heartbeat"] = array();
		}
		if ( !isset($_SESSION["SHOPSCRIPT_1C_IMPORT"]["heartbeat"][$type] ))  {
			$_SESSION["SHOPSCRIPT_1C_IMPORT"]["heartbeat"][$type] = 0;
		}
		if ( !isset(self::$step[$type] ))  {
			self::$step[$type] = 0;
		}
		
		if (self::$step[$type] < $_SESSION["SHOPSCRIPT_1C_IMPORT"]["heartbeat"][$type]) {
			
			for ($i = self::$step[$type] ; $i < $_SESSION["SHOPSCRIPT_1C_IMPORT"]["heartbeat"][$type]; $i++) {
				self::$step[$type]++;
				$reader->next();
			}
			$reader->read();
		}
		
		$_SESSION["SHOPSCRIPT_1C_IMPORT"]["heartbeat"][$type]++;
		self::$step[$type]++;
		
		if (self::getTime() - self::$start_time >= self::$max_time) {
			return false;
		}
		
		return true;
	}
	
	static function start()
	{
		self::$start_time = self::getTime();
	}
	
	static function getTime()
	{
		list($msec, $sec) = explode(chr(32), microtime());
    	return ($sec+$msec);
	}
}

?>