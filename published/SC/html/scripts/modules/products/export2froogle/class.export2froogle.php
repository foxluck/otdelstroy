<?php
class Export2Froogle extends Module 
{
	public static $custom_fields = array(
		'Brand'	=>'Brand',
		'MPN'	=>'Manufacturer Part Number (MPN)',
		'ISBN'	=>'International Standard Book Number (ISBN)',
		'shipping_weight'	=>'The weight of the product at shipping (shipping_weight)',
	);
	function initInterfaces()
	{
		
		$this->Interfaces = array(
			'export_page' => array(
				'name' => 'Export product list to Froogle(www.froogle.com)',
				'method' => 'methodExport2Froogle',
				),
		);
	}
	function methodExport2Froogle()
	{
		
		global $smarty;
		
		require_once(DIR_FUNC.'/export_products_function.php');
		
			function _exportToFroogle( $f, $rate, $mapping)
			{
				_exportHeader( $f, array_keys($mapping) );
				_exportProducts( $f, $rate,$mapping );
			}

			function _deleteInvalid_Elements( $str )
			{
				$str = str_replace( "\t"," ", $str );
				$str = str_replace( "\r"," ", $str );
				$str = str_replace( "\n"," ", $str );
				return $str;
			}

			function _exportHeader( $f, $extra_columns )
			{
				fputs( $f, "link\ttitle\tdescription\timage_link\tproduct_type\tprice\tid\tquantity\tshipping\tcondition\tpayment_notes".($extra_columns?"\t":'').implode("\t",$extra_columns)."\n" );
			}

			function _exportProducts( $f, $rate, $mapping)
			{

				//which description should be exported?
				switch($_POST["froogle_export_description"]){
					case 1:		$dsc = "description";		break;
					case 2:		$dsc = "brief_description";	break;
					default:	$dsc = "meta_description";	break;
				}
				$iso3 = 'USD';
				if(isset($_POST['froogle_currency_iso3']) && preg_match('/^[A-Z]{3}$/i',$_POST['froogle_currency_iso3'])){
					$iso3 = strtoupper($_POST['froogle_currency_iso3']);
				}
				$options = array();
				$fields = array();
				foreach($mapping as $field=>$sources){
					if($sources['source'] == 'option'){
						$options[] = intval($sources['optionID']);
					}elseif($sources['source'] == 'product'){
						$fields[] = $sources['field'];
					}
				}
				$options = array_unique($options);
				$fields = array_unique($fields);
				
				
				//export all active products

				/**
				 * 
				 * @param $ProductID
				 * @param $params array()
				 * @return void
				 */
				function __exportProduct( $ProductID, $params)
				{
					static $store_url;
					static $picture_url;
					static $weight_unit;
					
					$f 		= $params['f'];
					$rate 	= $params['rate'];
					$dsc 	= $params['dsc'];
					$iso3	= $params['iso3'];
					$mapping= $params['mapping'];
					$options= $params['options'];
					$fields = $params['fields'];
					
					if(!$store_url){
						$store_url = correct_URL(CONF_FULL_SHOP_URL);
					}
					if(!$picture_url){
						$picture_url = (SystemSettings::is_hosted())?$store_url.'products_pictures/':BASE_URL.URL_PRODUCTS_PICTURES.'/';
					}
					if(!$weight_unit){
						$weight_unit = preg_replace('/s$/','',strtolower(CONF_WEIGHT_UNIT),1);
					}
					$name = LanguagesManager::sql_prepareField('name',true);
					$description = LanguagesManager::sql_prepareField($dsc,true);
					if($fields){
						$_fields = ', `'.implode('`, `',$fields).'`';
					}else{
						$_fields = '';
					}
					$sql = <<<SQL
					SELECT 
						`?#PRODUCTS_TABLE`.`productID`, {$name}, `Price`, `categoryID`,
						`default_picture`, `in_stock`, {$description},
						`shipping_freight`, `slug`, `free_shipping`{$_fields}, `filename`
					FROM
						`?#PRODUCTS_TABLE`
					LEFT JOIN 
						`?#PRODUCT_PICTURES`
					ON 
						(`?#PRODUCTS_TABLE`.`default_picture`=`?#PRODUCT_PICTURES`.`photoID`)
					WHERE
						`?#PRODUCTS_TABLE`.`productID`=?
SQL;
					$q = db_phquery($sql,$ProductID);
					$product = db_fetch_row($q);
					
					//format data
					$rate = (float)$rate;
					if ($rate <= 0) $rate = 1;
					$product = array_map('_deleteInvalid_Elements',$product);
					$product["Price"] = RoundFloatValue( $product["Price"] * $rate );
					$product["shipping_freight"] = RoundFloatValue( $product["shipping_freight"] * $rate );
					$product["in_stock"] = (!CONF_CHECKSTOCK)?'1000':(($product["in_stock"] > 0 ) ? $product["in_stock"] : 0);
					$product[$dsc] = str_replace('&nbsp;',' ',strip_tags($product[$dsc]));
					$product['link'] = set_query('ukey=product&furl_enable=1&product_slug='.$product['slug'].'&productID='.$product['productID'],$store_url);

					//create categories string
					$product['category'] = "";
					if ($cpath = catCalculatePathToCategory($product["categoryID"]))	{
						array_shift($cpath);
						foreach($cpath as $category){
							$product['category'] .= $product['category']?" > ":'';
							$product['category'] .= $category['name'];
						}
					}

					
					if ( strlen($product["filename"]) && file_exists(DIR_PRODUCTS_PICTURES."/".$product["filename"]) ) {
						$product['image_link'] = $picture_url.$product["filename"];
					}elseif ( strlen($product["thumbnail"]) && file_exists(DIR_PRODUCTS_PICTURES."/".$product["thumbnail"]) ) {
						$product['image_link'] =  $picture_url.$product["thumbnail"];
					}else{
						$product['image_link'] = '';
					}
					
					$option_values = array();
					if($options){
						$sql = <<<SQL
					SELECT 
						*
					FROM
						`?#PRODUCT_OPTIONS_VALUES_TABLE`
					WHERE 
						(`productID`=?)
						AND
						(`optionID` IN (?@))
SQL;
						$qr = db_phquery($sql,$ProductID,$options);
						while($row = db_fetch_assoc($qr)){
							$option_values[$row['optionID']] = $row; 
						}
					}
					$extra_fields = array();
					foreach($mapping as $field=>$source){
						switch($source['source']){
							case 'option':{
								$optionID = $source['optionID'];
								$value_field = 'option_value_'.$source['iso2'];
								if(isset($option_values[$optionID]) && isset($option_values[$optionID][$value_field]) && ($option_values[$optionID]['option_type'] != 1)){
									$extra_fields[$field] = $option_values[$optionID][$value_field];
								}else{
									$extra_fields[$field] = '';
								}
								break;
							}
							case 'product':{
								$extra_fields[$field] = isset($product[$source['field']])?$product[$source['field']]:'';
								if($extra_fields[$field] && ($source['field'] == 'categoryID')){
									if($cpath && ($category = array_pop($cpath))){
										$extra_fields[$field] = $category['name'];
									}else{
										$extra_fields[$field] = '';
									}
								}elseif($field == 'shipping_weight'){
									if($extra_fields[$field]){
										$extra_fields[$field] .= ' '.$weight_unit;
									}else{
										$extra_fields[$field] = '1';
									} 
								}
								break;
							}
							default:{
								$extra_fields[$field] = '';
								break;
							}
						}
					}
					
					$product = array_map('_deleteInvalid_Elements',$product);
					$extra_fields = array_map('_deleteInvalid_Elements',$extra_fields);

					fputs( $f, $product['link']."\t"
								.$product["name"]."\t"
								.$product[$dsc]."\t"
								.$product['image_link']."\t"
								.$product['category']."\t"
								.$product["Price"]." {$iso3}"."\t"
								.$product["productID"]."\t"
								.$product["in_stock"]."\t"
								.":::".( $product["free_shipping"] ? "": $product["shipping_freight"])."\t"
								."new"."\t"
								."Google Checkout"
								.($extra_fields?"\t":'').implode("\t",$extra_fields)
								."\n" );
				}

				$exportCategories = array(array(),array());
				
				$_spArray = array(
					'f'=>$f,
					'rate'=>$rate,
					'dsc'=>$dsc,
					'iso3'=>$iso3,
					'mapping'=>$mapping,
					'options'=>$options,
					'fields'=>$fields,
					'exprtUNIC'=>array('mode'=>'simple')
				);
				export_exportSubcategories(0, $exportCategories, $_spArray);
			}

		if (file_exists(DIR_TEMP.'/froogle.txt')){
			$file_info = array(
				'size'=>(string) round( filesize(DIR_TEMP.'/froogle.txt') / 1024 ),
				'mtime'=>Time::standartTime(filemtime(DIR_TEMP.'/froogle.txt')),
			);
			$getFileParam = Crypt::FileParamCrypt( 'GetFroogleFeed', null );
			$smarty->assign('getFileParam', $getFileParam );
			$smarty->assign("froogle_file", $file_info);
		}

		if (isset($_GET['froogle_export_successful'])) {//show successful save confirmation message
			set_query('&froogle_export_successful=','',true);
			$smarty->assign("froogle_export_successful", 1);
		}

		//export products
		if (isset($_POST['froogle_export']) && $_POST['froogle_export']) {
			
			$currency = currGetCurrencyByID ( (int)$_POST['froogle_currency'] );

			if (!$currency) {
				$smarty->assign( 'froogle_errormsg', translate("gglbase_err_select_currency") );
			}else {//do export
				if ($f = @fopen(DIR_TEMP.'/froogle.txt','w')) {
					$mapping = array();
					if(isset($_POST['data_mapping']) && is_array($_POST['data_mapping'])){
						foreach($_POST['data_mapping'] as $field=>$source){
							if(isset(self::$custom_fields[$field])){
								if(preg_match('/^([a-z]+)_+(\d+)_+([a-z]{2})$/',$source,$matches)){
									$mapping[$field] = array(
										'source'=>$matches[1],
										'optionID'=>$matches[2],
										'iso2'=>$matches[3],
										'raw'=>$source,
									);
								}elseif(preg_match('/^[a-z_0-9]+$/i',$source)){
									$mapping[$field] = array(
										'source'=>'product',
										'field'=>$source,
										'raw'=>$source,
									);
								}
							}
						}	
					}
					_exportToFroogle( $f, $currency['currency_value'], $mapping);
					fclose($f);
					RedirectSQ('froogle_export_successful=yes');
				}else {
					$smarty->assign( 'froogle_errormsg', translate("gglbase_err_cant_create_file") );
				}
			}
		}
		
		require(DIR_ROOT.'/includes/modules.export_products.php');
		
		$product_fields = _getProductFields();
		$product_fields['categoryID'] = translate('prdset_str_category');
		$options_fields = _getOptionFields();
		
		$smarty->assign('product_fields', $product_fields);
		$smarty->assign('options_fields', $options_fields);
		$smarty->assign('google_fields',self::$custom_fields);

		$currencies = currGetAllCurrencies();
		$smarty->assign('currencies', $currencies);

		//set sub-department template
		$smarty->assign('admin_sub_dpt', 'modules_froogle.tpl.html');
	}
}
?>