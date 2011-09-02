<?php
class Pricelist extends Module
{

	function callFromInstallConfig()
	{

		$PricelistDivision = new Division();
		$PricelistDivision->setName('Pricelist');
		$PricelistDivision->setParentID(DivisionModule::getDivisionIDByUnicKey('TitlePage'));
		$PricelistDivision->setEnabled(1);
		$PricelistDivision->save();
		$PricelistDivision->addCustomSetting('Icon', 'icon');
		$PricelistDivision->loadCustomSettings();
		$PricelistDivision->setCustomSetting('icon', 'images/price.gif');
		$PricelistDivision->save();
		$PricelistDivision->addInterface($this->getConfigID().'_pricelist');
	}

	function initInterfaces()
	{

		$this->Interfaces['pricelist'] = array(
			'name' => 'Pricelist',
			'method' => 'methodPricelist',
		);
	}

	function methodPricelist()
	{

		global $smarty;

		$key = md5($this->buildOrderClause().LanguagesManager::getCurrentLanguage()->iso2);
		$cache_lifetime = 1800;//30 min content cache
		$cache_name = __CLASS__.'.'.$key;
		$cache = Cache::getInstance($cache_name,Cache::FILE);
		if((time() - $cache->get('timestamp',0) > $cache_lifetime)
		||
		(!($out = $cache->get('page')))
		){
			$sort_string = translate('prd_sort_pricelist_control_string');
			$sort_string = str_replace( '{ASC_NAME}', '<a rel="nofollow" href="'.xHtmlSetQuery('&sort=name&direction=ASC').'">'.translate('str_ascending').'</a>',$sort_string );
			$sort_string = str_replace( '{DESC_NAME}', '<a rel="nofollow" href="'.xHtmlSetQuery('&sort=name&direction=DESC').'">'.translate('str_descending').'</a>',$sort_string );
			$sort_string = str_replace( '{ASC_PRICE}', '<a rel="nofollow" href="'.xHtmlSetQuery('&sort=Price&direction=ASC').'">'.translate('str_ascending').'</a>',	$sort_string );
			$sort_string = str_replace( '{DESC_PRICE}', '<a rel="nofollow" href="'.xHtmlSetQuery('&sort=Price&direction=DESC').'">'.translate('str_descending').'</a>',	$sort_string );
			$sort_string = str_replace( '{ASC_RATING}', '<a rel="nofollow" href="'.xHtmlSetQuery('&sort=customers_rating&direction=ASC').'">'.translate('str_ascending').'</a>',	$sort_string );
			$sort_string = str_replace( '{DESC_RATING}', '<a rel="nofollow" href="'.xHtmlSetQuery('&sort=customers_rating&direction=DESC').'">'.translate('str_descending').'</a>',	$sort_string );
			$smarty->assign( 'string_product_sort', $sort_string );
			$smarty->assign('pricelist_elements', $this->pricessCategories());//use 10 minutes cache for data
			$out = $smarty->fetch('pricelist.tpl.html');
			$smarty->clear_assign('pricelist_elements');
			$cache->set('timestamp',time());
			$cache->set('page',function_exists('gzcompress')?gzcompress($out):$out);
			$cache->store();
			unset($cache);
			Cache::dropInstance($cache_name);
		}else{
			if(function_exists('gzuncompress')){
				$out = gzuncompress($out);
			}
		}
		$smarty->assign('cache_content',$out);
		$smarty->assign('main_content_template', 'cache.html');

	}

	private function pricessCategories()
	{
		$key = md5($this->buildOrderClause().LanguagesManager::getCurrentLanguage()->iso2);
		$cache_lifetime = 600;//30 min content cache
		$cache_name = __CLASS__.'.raw.'.$key;
		$cache = Cache::getInstance($cache_name,Cache::PHP);
		if((time() - $cache->get('timestamp',0) > $cache_lifetime)
		||
		(!($out = $cache->get('data')))
		){

			$out = array();
			$cnt = 0;

			$sql = 'SELECT categoryID, '.LanguagesManager::sql_prepareField('name',true)
			.', slug, parent, sort_order FROM ?#CATEGORIES_TABLE WHERE categoryID>1 ORDER BY parent,'.LanguagesManager::ml_getLangFieldName('name');//
			$q = db_phquery($sql);
			$priceList = new DataTree();
			while ($row = db_fetch_row($q))
			{
				$priceList->setData(array('is_category'=>'1','id'=>(int)$row['categoryID'],'slug'=>$row['slug'],'name'=>$row['name'],'sort_order'=>$row['sort_order']),
				(int)$row['categoryID'],(int)$row['parent']);
			}
			$sortfunction = create_function('$a,$b','{
if(isset($a["data"])&&isset($b["data"])){
	$a_val = intval($a["data"]["sort_order"]);
	$b_val = intval($b["data"]["sort_order"]);
	return $a_val>$b_val?1:(($a_val<$b_val)?-1:strcmp($a["data"]["name"],$b["data"]["name"]));
}else{
	return 0;
}}');

			$priceList->sortNodes($sortfunction,1);

			$order_clause = $this->buildOrderClause();


			$sql = 'SELECT productID, '.LanguagesManager::sql_prepareField('name',true).', Price, in_stock, slug, categoryID, product_code from ?#PRODUCTS_TABLE WHERE categoryID>1 and Price>0 and enabled=1 '.
			$order_clause.' 
				';
			//add products
			$q = db_phquery( $sql);
			while ($row = db_fetch_row($q))
			{
				$row['price'] = show_price($row['Price']);
				$priceList->setData(array('is_category'=>'0','id'=>(int)$row['productID'],'slug'=>$row['slug'],'name'=>$row['name'],'in_stock'=>$row['in_stock'],'price'=>$row['price'],'product_code'=>$row['product_code']),
				$priceList->getMaxNodeId()+1,(int)$row['categoryID']);
			}
			db_free_result($q);

			$sql = 'SELECT `product`.productID, '.LanguagesManager::sql_prepareField('name',true).', Price, in_stock, slug, `category`.categoryID as categoryID, product_code from ?#PRODUCTS_TABLE as `product` LEFT JOIN ?#CATEGORIY_PRODUCT_TABLE as `category` ON (`product`.productID=`category`.productID) WHERE `category`.categoryID>1 and Price>0 and enabled=1 '.
			$order_clause.' 
				';
			//add products
			$q = db_phquery( $sql);
			while ($row = db_fetch_row($q))
			{
				$row['price'] = show_price($row['Price']);
				$priceList->setData(array('is_category'=>'0',
										'id'=>(int)$row['productID'],
										'slug'=>$row['slug'],
										'name'=>$row['name'],
										'in_stock'=>$row['in_stock'],
										'price'=>$row['price'],
										'product_code'=>$row['product_code']),
				$priceList->getMaxNodeId()+1,
				(int)$row['categoryID']);
			}
			db_free_result($q);
			$out = $priceList->plainData(-2);
			unset($priceList);
			$cache->set('timestamp',time());
			$cache->set('data',&$out);
			$cache->store();
			unset($cache);
			Cache::dropInstance($cache_name);
		}
		return $out;
	}

	private function buildOrderClause()
	{
		if ( !isset($_GET['sort']) ){
			$order_clause = 'order by sort_order, name';
		}else{
			//verify $_GET['sort']
			switch ($_GET['sort']){

				case 'name':
				case 'Price':
				case 'customers_rating':
					break;
				default:
					$_GET['sort'] = 'name';
					break;
			}

			$order_clause = ' order by '.$_GET['sort'];
			if ( isset($_GET['direction']) )
			{
				if ( !strcmp( $_GET['direction'] , 'DESC' ) )
				$order_clause .= ' DESC ';
				else
				$order_clause .= ' ASC ';
			}
		}
		return $order_clause;
	}
}
?>