<?php


class ImportCatalog extends ImportData
{
	private $statistic = array(
	'category_added'=>0,
	'category_modify'=>0,
	'product_added'=>0,
	'product_modify'=>0,
	'insert'=>false
	);
	protected $parents = array();
	protected $name_fields = null;
	protected $primary_category_col = null;
	private $currentCategoryID;

	function __construct($csv_file_name,$delimeter = ';')
	{
		$this->name_fields = LanguagesManager::ml_getLangFieldNames('name');
		$this->currentCategoryID = 1;
		parent::__construct($csv_file_name,$delimeter);
	}

	function ApplyDataMapping($line)
	{
		$data = parent::applyDataMapping($line);
		$customparams = array();

		//fix custom params
		if(isset($data['customparams'])&&is_array($data['customparams'])){
			foreach($data['customparams'] as $key=>$value){
				if(preg_match('/^([\d]+)__(.{2})$/',$key,$matches)){
					$optionID = intval($matches[1]);
					$optionISO2 = $matches[2];
					if(!isset($customparams[$matches[1]])){
						$customparams[$optionID] = array();
					}
					$customparams[$optionID][$optionISO2] = $value;
				}
			}
		}
		$data['customparams'] = $customparams;

		//fix pictures
		$data['pictures'] = array();
		if(isset($data['main']['picture'])&&$data['main']['picture']){
			$data['pictures'] = is_array($data['main']['picture'])?$data['main']['picture']:array($data['main']['picture']);
			$data['main']['picture'] = count($data['pictures'])?$data['pictures'][0]:'';
		}

		return $data;
	}

	function parseDataMapping()
	{
		parent::parseDataMapping();
		$this->primary_category_col = ($this->primary_col =='product_code')?LanguagesManager::ml_getLangFieldName('name'):$this->primary_col;
	}

	function import($data,$use_structure = true,$allow_insert = true)
	{
		$this->statistic['insert'] = false;
		if($this->isProduct($data['main'])){
			//import product
			$this->importProductData($data['main'],$data['pictures'],$data['customparams'],$use_structure,$allow_insert);
		}elseif($this->isCategory()){//if category
			//import category
			$this->importCategoryData($data['main'],$use_structure);
		}
		return $this->statistic;
	}

	protected function importProductData($product_data,$pictures,$customparams,$use_structure = true,$allow_insert = true)
	{
		if(isset($product_data['slug'])){
			$product_data['slug'] = make_slug($product_data['slug']);
			if(!$product_data['slug']){
				unset($product_data['slug']);
			}
		}
		if($use_structure){//search for product within current category
			$currentCategoryID = $this->currentCategoryID;
			$sql = 'SELECT productID FROM ?#PRODUCTS_TABLE WHERE categoryID=? AND '.xEscapeSQLstring($this->primary_col).' = ?';
			$productID = db_phquery_fetch(DBRFETCH_FIRST, $sql, $currentCategoryID,(string)$product_data[$this->primary_col]);


		}else{//or use global search
			$sql = 'SELECT productID, categoryID FROM ?#PRODUCTS_TABLE WHERE `'.xEscapeSQLstring($this->primary_col).'` = ?';
			list($productID,$currentCategoryID) = db_phquery_fetch(DBRFETCH_ROW, $sql, (string)$product_data[$this->primary_col]);
		}

		if(isset($product_data['free_shipping'])){
			$product_data['free_shipping'] = intval($product_data['free_shipping'])||(trim($product_data['free_shipping'])==="+")?1:0;
		}
		if(isset($product_data["Price"])){
			$product_data["Price"] = trim(str_replace( array(' ',','),array('','.'), $product_data["Price"]));
		}
		if (isset($product_data['eproduct_filename']) && !$product_data['eproduct_filename']){
			unset($product_data['eproduct_filename']);
		}

		if(isset($product_data['classID'])){
			$classID = taxGetTaxClassByName($product_data['classID']);
			if($classID){
				$product_data['classID'] = $classID;
			}else{
				unset($product_data['classID']);
			}
		}

		$productEntry = new Product();
		$productEntry->__use_cache = false;
		if($productID){ //update product info
			$this->statistic['product_modify']++;

			$productEntry->loadByID($productID);
			$old_classID = $productEntry->classID;
			if(isset($product_data['min_order_amount']) && intval($product_data['min_order_amount'])<1){

				$product_data['min_order_amount'] = 1;
			}
		}elseif($allow_insert){
			$this->statistic['product_added']++;
			$this->statistic['insert'] = true;
			$product_data['invisible'] = isset($product_data['invisible'])?($product_data['invisible']?1:0):0;
			$product_data['ordering_available'] = isset($product_data['ordering_available'])?($product_data['ordering_available']?1:0):1;

			$product_data['min_order_amount'] = $product_data['min_order_amount']==0?1:$product_data['min_order_amount'];
			$product_data['classID'] = CONF_DEFAULT_TAX_CLASS;
		}

		if(isset($product_data['invisible'])){
			$product_data['enabled'] = !$product_data['invisible'];
		}
		if($productID||$allow_insert){
			$productEntry->loadFromArray($product_data);
			$productEntry->categoryID = intval($currentCategoryID)?$currentCategoryID:1;
			$productEntry->correctData();
			$productEntry->save();

			$productID = $productEntry->productID;
			$this->importExtraOptionValues($productID,$customparams);
			//_importExtraOptionValues( $row, $productID, $updated_extra_option );
			if(isset($product_data['picture'])&&$product_data['picture']){
				$this->importProductPictures($productID,$pictures);
			}
			//TODO: add extra categories support
			//TODO: add extra options multiply selection
			//TODO: add "See also" support
			//TODO: add tags (and export too)
		}


	}

	protected function importCategoryData($category_data,$use_structure = true)
	{
		if(isset($category_data['slug'])){
			$category_data['slug'] = make_slug($category_data['slug']);
			if(!$category_data['slug']){
				unset($category_data['slug']);
			}
		}
		if(isset($category_data['picture'])&&is_array($category_data['picture'])){
			$category_data['picture'] = $category_data['picture'][0];
		}
		if($use_structure){
			$parentCategoryID = $this->getParentCategory($category_data);
			$this->fixCategoryName($category_data);

			$sql = 'SELECT categoryID FROM ?#CATEGORIES_TABLE'
			.	' WHERE categoryID>1 AND `'.$this->primary_category_col.'` = ? AND parent=?';
			$currentCategoryID = db_phquery_fetch(DBRFETCH_FIRST, $sql, $category_data[$this->primary_category_col],$parentCategoryID);
		}else{
			$parentCategoryID = 1;
			$this->fixCategoryName($category_data);
			$sql = 'SELECT categoryID, parent FROM ?#CATEGORIES_TABLE'
			.	' WHERE categoryID>1 AND `'.$this->primary_category_col.'` = ?';
			$row = db_phquery_fetch(DBRFETCH_ROW, $sql, $category_data[$this->primary_category_col]);
			
			if($currentCategoryID = array_shift($row)) {
				$parentCategoryID = array_shift($row);
			}
		}

		$categoryEntry = new Category;
		$categoryEntry->__use_cache = false;
		if ($currentCategoryID){//update category
			$categoryEntry->loadByID($currentCategoryID);
			$fields_names = array_merge(array('sort_order','picture','slug'),
			LanguagesManager::ml_getLangFieldNames('description'),
			LanguagesManager::ml_getLangFieldNames('meta_title'),
			LanguagesManager::ml_getLangFieldNames('meta_description'),
			LanguagesManager::ml_getLangFieldNames('meta_keywords'),
			LanguagesManager::ml_getLangFieldNames('name'));
			foreach($fields_names as $f_name){
				if(!isset($category_data[$f_name]) || !$category_data[$f_name])continue;
				$categoryEntry->{$f_name} = $category_data[$f_name];
			}
			$categoryEntry->save();
			$this->statistic['category_modify']++;
		}else{//insert
			$category_data['sort_order'] = isset($category_data['sort_order'])?intval($category_data['sort_order']):0;
			$categoryEntry->loadFromArray($category_data);
			$categoryEntry->allow_products_comparison = 1;
			$categoryEntry->allow_products_search = 1;
			$categoryEntry->show_subcategories_products = 1;
			$categoryEntry->parent = $parentCategoryID;
			$categoryEntry->save();
			$this->statistic['category_added']++;

			$currentCategoryID = $categoryEntry->categoryID;
		}

		$this->setParentCategory($currentCategoryID);
	}

	static function importProductPictures($productID,$pictures)
	{
		$first_picture = true;
		if(count($pictures)){
			db_phquery( "delete from ?#PRODUCT_PICTURES where productID=?",$productID );
			foreach($pictures as $order=>$picture){
				$picture = explode(',',$picture);
				$count = count($picture);
				if($count>3){
					$picture = array_slice($picture,0,3);
				}elseif($count<3){
					$picture = array_merge($picture,array_fill($count-1,3-$count,''));
				}
				$picture = array_map('trim',$picture);
				if(strlen(implode('',$picture))){
					$sql = 'INSERT ?#PRODUCT_PICTURES (productID, filename, thumbnail, enlarged, priority) VALUES( ?,?,?,?,?)';
					db_phquery($sql, $productID,$picture[0],$picture[1],$picture[2],$order);
					if ($first_picture){// update DEFAULT PICTURE information
						$photoID = db_insert_id();
						$sql = 'update ?#PRODUCTS_TABLE set default_picture =? where productID=?';
						db_phquery($sql,$photoID,$productID );
						$first_picture = false;
					}
				}
			}
		}
	}

	protected function importExtraOptionValues($productID, $customparams = array())
	{

		//global $errors_options;
		static $defaultLanguage = null;
		if(!$defaultLanguage){
			$defaultLanguage = &LanguagesManager::getDefaultLanguage();
		}

		if(!$customparams){
			$customparams = array();
		}

		foreach($customparams as $optionID=>$lang_variants){

			/**
			 * Ignore option if no default value
			 */
			if(!isset($lang_variants[$defaultLanguage->iso2]))continue;
			$values = $lang_variants[$defaultLanguage->iso2];
			$matches = null;
			if(preg_match('/^\{(.*)\}$/',$values,$matches)){
				$mode = 'predefined';
				$values = $matches[1];
			}else{
				$mode = 'custom';
				$values = $lang_variants;
			}
			
			switch ($mode){
				case 'custom':
					$sql = 'DELETE FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE optionID=? and productID=?';
					db_phquery($sql,$optionID,$productID);
						
					if(!strlen(implode('',$values))){
						break;
					}
					$option_values = LanguagesManager::ml_getLangFieldNames('option_value');
					$sql = "INSERT `?#PRODUCT_OPTIONS_VALUES_TABLE` (`optionID`, `productID`";
					foreach($option_values as $field){
						$sql .= ", `{$field}`";
					}
					$sql .= ') VALUES (?optionID,?productID';
					$data = array();
					foreach($option_values as $key=>$field){
						if(preg_match('/_([a-z]{2})$/',$field,$iso_matches)){
							$data[$field] = $values[$iso_matches[1]];
							if(is_array($data[$field])){
								$data[$field] = trim(implode(' ',$data[$field]));
							}
							$sql .= ", ?{$field}";
						}
					}
					$sql .= ')';
					
					$data['optionID'] = $optionID;
					$data['productID'] = $productID;
					db_phquery($sql,$data);
					break;
				case 'predefined':
					if(!$values){

						//					$errors_options[]
						break;
					}
					$values_options = explode(',',$values);
					//delete all current product option configuration
					db_phquery("DELETE FROM ?#PRODUCT_OPTIONS_VALUES_TABLE WHERE optionID=? AND productID=?", $optionID, $productID);
					db_phquery("DELETE FROM ?#PRODUCTS_OPTIONS_SET_TABLE WHERE optionID=? AND productID=?", $optionID, $productID);

					$default_variantID = 0;
					foreach ($values_options as $key => $val){
						$val = trim($val);
						// current value is "OPTION_NAME=SURCHARGE", e.g. red=3, xl=1, s=-1, m=0
						// or red, X1 ETC,
						$pattern = '/^([^=]+)(\s*=\s*([+\-]?\d+(\.\d*)?))?$/';
						if (preg_match($pattern,$val,$matches))
						{
							$val_name = trim($matches[1]);
							$val_surcharge = (float)(isset($matches[3])?$matches[3]:0);
							if($val_name == null)break;

							//print "<br>{$optionID} {$val_name} ++ {$val_surcharge}<br>\n";

							//search for a specified option value in the database
							$variantID = optOptionValueExists($optionID, $val_name);
							//print "<br>{$optionID}:{$variantID} {$val_name} ++ {$val_surcharge}<br>\n";
							if ( !$variantID ) //does not exist => add new variant value
							{
								$variantID = optAddOptionValue(array('optionID' => $optionID, 'sort_order'=>0, 'option_value_'.$defaultLanguage->iso2 => $val_name));
							}
							if ($default_variantID==0){
								$default_variantID = $variantID;
							}

							//now append this variant value to the product
							db_phquery("INSERT ?#PRODUCTS_OPTIONS_SET_TABLE (productID, optionID, variantID, price_surplus) VALUES(?,?,?,?)", $productID, $optionID, $variantID, $val_surcharge);

						}elseif($key == 0){
							$default_variantID = -1;
						}
					}

					//assign default variant ID - first option in the variants list is default
					if ($default_variantID>0 || ($default_variantID == -1)){

						$sql = '
						INSERT ?#PRODUCT_OPTIONS_VALUES_TABLE (optionID, productID, option_type, option_show_times, variantID) VALUES(?,?,?,?,?)
					';
						db_phquery($sql, $optionID, $productID, 1, 1, max(0,$default_variantID));
					}
					break;
			}
		}
	}

	protected function getParentCategory($category_data)
	{
		static $level_field = null;
		if(!$level_field){
			$level_field = in_array($this->primary_col,$this->name_fields)?$this->primary_col:LanguagesManager::ml_getLangFieldName('name');
		}
		$matches = null;
		$level=0;
		if(preg_match('/^([!]*)/',$category_data[$level_field],$matches)){
			$level = strlen($matches[1]);
		}

		if(count($this->parents)>$level){
			array_splice($this->parents,$level);
		}
		$count=count($this->parents);
		$parentCategoryID = ($count)?$this->parents[$count-1]:1;
		return $parentCategoryID;
	}

	protected function setParentCategory($currentCategoryID)
	{
		$this->currentCategoryID = $currentCategoryID;
		$this->parents[] = $this->currentCategoryID;
	}

	protected function fixCategoryName(&$category_data)
	{
		foreach($this->name_fields as $name_field){
			if(preg_match('/^([!]*)(.+)$/',$category_data[$name_field],$matches)){
				$category_data[$name_field] = $matches[2];
			}
		}
	}


	protected function isProduct($data)
	{
		static $noncategory_fields = array("product_code", "Price", "in_stock", "list_price", "items_sold", "brief_description");
		if(!isset($data[$this->primary_col])||$data[$this->primary_col]===""){
			return false;
		}
		$res = false;
		foreach($noncategory_fields as $field){
			if(isset($data[$field])&&$data[$field]!=""){
				$res = true;
				break;
			}
		}
		return $res;
	}

	protected function isCategory()
	{
		static $result = null;
		if(is_null($result)){
			$allowed_fields = array_merge(array('slug'),$this->name_fields);
			$result = in_array($this->primary_category_col,$allowed_fields)?true:false;
		}
		return $result;
	}
	function isValidData($data)
	{
		if(!isset($data[$this->primary_col])||$data[$this->primary_col]===""){
			return false;
		}else{
			return true;
		}
	}
}
?>