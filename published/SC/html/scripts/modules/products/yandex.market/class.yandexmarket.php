<?php
require_once(DIR_FUNC.'/export_products_function.php');

class YandexMarket extends Module {
	const YANDEX_FILE = "/yandex.xml";

	function initInterfaces(){

		$this->Interfaces = array(
		'export_page' => array(
		'name' => 'Страница экспорта продуктов в Yandex.Маркет',
		'method' => 'methodExport',
		),
		'xml_file_access' => array(
		'name' => 'Доступ к файлу Yandex.Маркет',
		'method' => 'methodXMLFileAccess',
		),
		);
	}

	function methodXMLFileAccess(){

		//доступ к файлу для Яндекс.Маркет
		$fileToDownLoad = DIR_TEMP.YandexMarket::YANDEX_FILE;

		if (file_exists( $fileToDownLoad )){
			if (isset($_GET["download"])){
				header('Content-type: application/force-download');
				header('Content-Transfer-Encoding: Binary');
				header('Content-length: '.filesize($fileToDownLoad));
				header('Content-disposition: attachment; filename='.basename($fileToDownLoad) );
				readfile($fileToDownLoad);
			}else{
				echo implode( "", file( $fileToDownLoad ) );
			}
			exit(1);
		}else{
			if(function_exists('error404page'))error404page();
		}
	}

	function _exportToYandexMarket( $f, $rate, $export_product_name )
	{
		$spArray = array(
		'exprtUNIC'=>array(
		'mode' 				=>'toarrays',
		'expProducts' 		=>array()
		)
		);
		$exportCategories = array(array(),array());
		export_exportSubcategories(0, $exportCategories, $spArray);
		$this->_exportBegin( $f );
		$this->_exportAllCategories( $f, $spArray['exprtUNIC']['expProducts'] );
		$local_delivery_cost = isset($_POST['yandex_export_local_delivery_cost'])?floatval(str_replace(',','.',$_POST['yandex_export_local_delivery_cost'])):false;
		if(isset($_POST['yandex_export_local_delivery_cost_enabled'])&&$_POST['yandex_export_local_delivery_cost_enabled']){
			fputs( $f, "				<local_delivery_cost>".$local_delivery_cost."</local_delivery_cost>\n");
		}
		$this->_exportProducts( $f, $rate, $export_product_name, $spArray['exprtUNIC']['expProducts'] );
		$this->_exportEnd( $f );
	}


	function _deleteHTML_Elements( $str, $strip_tags = true )
	{
		if($strip_tags){
			$str = strip_tags($str);
		}
		$str = str_replace('&nbsp;',	' ',	$str);
		$str = str_replace( "&",	"&amp;",	$str );
		$str = str_replace( "<",	"&lt;",		$str );
		$str = str_replace( ">",	"&gt;",		$str );
		$str = str_replace( "\"",	"&quot;",	$str );
		$str = str_replace( "'",	"&apos;",	$str );
		$str = str_replace( "\r",	"",			$str );
		return $str;
	}

	function _exportBegin( $f )
	{
		fputs( $f, "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n" );
		fputs( $f, "	<!DOCTYPE yml_catalog SYSTEM \"shops.dtd\">\n" );
		fputs( $f, "		<yml_catalog date=\"".date("Y-m-d H:i")."\">\n" );
		fputs( $f, "			<shop>\n" );
		fputs( $f, "				<name>".$this->_deleteHTML_Elements(CONF_SHOP_NAME)."</name>\n");
		fputs( $f, "				<company>".$this->_deleteHTML_Elements(CONF_SHOP_NAME)."</company>\n");
		fputs( $f, "				<url>".$this->getStoreUrl()."</url>\n");
		fputs( $f, "				<currencies>\n");
		fputs( $f, "					<currency id=\"RUR\" rate=\"1\"/>\n");
		fputs( $f, " 					<currency id=\"USD\" rate=\"CBRF\"/>\n");
		fputs( $f, " 					<currency id=\"EUR\" rate=\"CBRF\"/>\n");
		fputs( $f, " 					<currency id=\"UAH\" rate=\"CBRF\"/>\n"); 
		fputs( $f, "				</currencies>\n");
	}


	function _exportAllCategories( $f, &$_ProductIDs )
	{
		if(!count($_ProductIDs))return 0;
		$Cats = array();
		$execCats = array();
		$sql = "
					SELECT catt.categoryID, ".LanguagesManager::sql_prepareField('catt.name')." AS name, catt.parent, catt.slug FROM ".CATEGORIES_TABLE." as catt
					LEFT JOIN ".PRODUCTS_TABLE." as prot ON catt.categoryID=prot.categoryID
					WHERE prot.productID IN (".implode(", ", $_ProductIDs).")
					GROUP BY prot.categoryID
				";
		$q = db_query($sql);
		fputs($f,"				<categories>\n");
		while ($row = db_fetch_row($q))
		{
			if(!in_array($row[0], $execCats)){

				$execCats[] = $row[0];
			}
			if(!in_array($row[2], $Cats) && $row[2]>1){

				$Cats[] = $row[2];
			}
			$row[1] = $this->_deleteHTML_Elements( $row[1] );
			if ($row[2] <= 1)
			{
				fputs($f,"					<category id=\"".$row[0]."\">".$row[1].
				"</category>\n");
			}
			else
			{
				fputs($f,"					<category id=\"".$row[0]."\" parentId=\"".$row[2]."\">".$row[1]."</category>\n");
			}
		}
		db_free_result($q);

		while (count($Cats)) {

			$sql = "
						SELECT categoryID, ".LanguagesManager::sql_prepareField('name')." AS name, parent FROM ".CATEGORIES_TABLE." WHERE categoryID IN (".implode(", ", $Cats).")
						";
			$q = db_query($sql);
			$Cats = array();
			while ($row = db_fetch_row($q))
			{
				$Disp = false;
				if(!in_array($row[0], $execCats)){

					$execCats[] = $row[0];
					$Disp = true;
				}
				if( !in_array($row[2], $execCats) && !in_array($row[2], $Cats) && $row[2]>1){

					$Cats[] = $row[2];
				}
				$row[1] = $this->_deleteHTML_Elements( $row[1] );
				if ($row[2] <= 1 && $Disp)
				{
					fputs($f,"					<category id=\"".$row[0]."\">".$row[1].
					"</category>\n");
				}
				elseif($Disp)
				{
					fputs($f,"					<category id=\"".$row[0]."\" parentId=\"".$row[2]."\">".$row[1]."</category>\n");
				}
			}
			db_free_result($q);
		}

		fputs($f,"				</categories>\n");
	}


	function _exportProducts( $f, $rate, $export_product_name, &$_ProductIDs )
	{

		fputs( $f, "				<offers>\n");

		//товары с нулевым остатком на складе
		$clause = isset($_POST["yandex_dont_export_negative_stock"])?" and in_stock>0":"";
		//комментарии к товарам
		$sales_notes = isset($_POST['yandex_export_sales_notes'])?$this->_deleteHTML_Elements($_POST['yandex_export_sales_notes'],false):false;
		//
		$local_delivery_cost_enabled = (isset($_POST['yandex_export_local_delivery_cost_override'])&&$_POST['yandex_export_local_delivery_cost_override'])?true:false;
		//какое описание экспортировать
		if ($_POST["yandex_export_description"] == 1)
		{
			$dsc = "description";
			$dsc_q = ", ".LanguagesManager::sql_prepareField($dsc)." as ".$dsc;
		}
		else if ($_POST["yandex_export_description"] == 2)
		{
			$dsc = "brief_description";
			$dsc_q = ", ".LanguagesManager::sql_prepareField($dsc)." as ".$dsc;
		}
		else
		{
			$dsc = "";
			$dsc_q = "";
		}

		//выбрать товары
		$proCount = count($_ProductIDs);
		$chunk_size = 100;
		$iter = 0;
		for (; $iter<$proCount;$iter+=$chunk_size){

			$sql = "select productID, ".LanguagesManager::sql_prepareField('name')." AS name, Price, categoryID, default_picture".$dsc_q.", in_stock, slug, eproduct_filename, min_order_amount".($local_delivery_cost_enabled?', free_shipping, shipping_freight':'')." from ".PRODUCTS_TABLE."
					where ".(count($_ProductIDs)?"productID IN(".implode(", ", array_slice($_ProductIDs, $iter, $chunk_size)).") AND ":"")."enabled=1 AND ordering_available>0 ".$clause;

			$q = db_query($sql);

			$store_url = $this->getStoreUrl();
			
			//$picture_url = (MOD_REWRITE_SUPPORT&&false)?$store_url.'products_pictures/':BASE_URL.URL_PRODUCTS_PICTURES.'/';
			$picture_url = (SystemSettings::is_hosted())?$store_url.'products_pictures/':BASE_URL.URL_PRODUCTS_PICTURES.'/';
			$picture_url = preg_replace('@([^:]{1})//@','\\1/',$picture_url);

			while ($product = db_fetch_row($q))
			{

				fputs( $f, "					<offer available=\"".(($product['in_stock'] || !CONF_CHECKSTOCK)?'true':'false')."\" id=\"".$product["productID"]."\">\n");
				fputs( $f, "						<url>".str_replace('&','&amp;',set_query('ukey=product&furl_enable=1&product_slug='.$product['slug'].'&productID='.$product['productID'].'&from=ya',$store_url))."</url>\n" );
				fputs( $f, "						<price>".RoundFloatValueStr($product["Price"]*$rate)."</price>\n" );
				fputs( $f, "						<currencyId>RUR</currencyId>\n" );
				fputs( $f, "						<categoryId>".$product["categoryID"]."</categoryId>\n" );

				if ($product["default_picture"] != NULL)
				{
					$pic_clause = " and photoID=".((int)$product["default_picture"]);
				}
				else
				$pic_clause = "";

				$q1 = db_query("select filename, thumbnail from ".PRODUCT_PICTURES." where productID=".$product["productID"] . $pic_clause.' ORDER BY priority');//.' ORDER BY priority');
				$pic_row = db_fetch_row($q1);
				if($pic_row){
					if ( strlen($pic_row["filename"]) && file_exists(DIR_PRODUCTS_PICTURES."/".$pic_row["filename"]) )
					fputs( $f, "						<picture>".$picture_url.str_replace(' ', '%20',$this->_deleteHTML_Elements($pic_row["filename"]))."</picture>\n" );
					else
					if ( strlen($pic_row["thumbnail"]) && file_exists(DIR_PRODUCTS_PICTURES."/".$pic_row["thumbnail"]) )
					fputs( $f, "						<picture>".$picture_url.str_replace(' ', '%20',$this->_deleteHTML_Elements($pic_row["thumbnail"]))."</picture>\n" );

				}


				switch ($export_product_name){
					default:
					case 'only_name':
						$_NameAddi = '';
						break;
					case 'path_and_name':
						$_NameAddi = '';
						$_t = catCalculatePathToCategory( $product['categoryID'] );
						foreach ($_t as $__t)
						if($__t['categoryID']!=1)
						$_NameAddi .= $__t['name'].':';
						break;
				}
				
				if($local_delivery_cost_enabled&&($product['free_shipping']||$product['shipping_freight'])){
					fputs( $f, "						<local_delivery_cost>".($product['free_shipping']?'0':(float)$product['shipping_freight'])."</local_delivery_cost>\n" );
				}
				
				$product["name"]		= $this->_deleteHTML_Elements( $_NameAddi.$product["name"] );

				fputs( $f, "						<name>".$product["name"]."</name>\n" );

				if ( strlen($dsc)>0 )
				{
					$product[$dsc] = $this->_deleteHTML_Elements( $product[$dsc] );
					fputs( $f, "						<description>".$product[ $dsc ]."</description>\n" );
				}
				else
				{
					fputs( $f, "						<description></description>\n" );
				}
				
				if($sales_notes){
					fputs( $f, "						<sales_notes>".$sales_notes."</sales_notes>\n" );
				}elseif($product["min_order_amount"]>1){
					fputs( $f, "						<sales_notes>Минимальный заказ: ".$product["min_order_amount"]." шт.</sales_notes>\n" );
				}
				if (trim($product["eproduct_filename"]) != "") {
					
					fputs( $f, "						<downloadable>true</downloadable>\n");
				}	
				else {			
					fputs( $f, "						<downloadable>false</downloadable>\n");
				}
				fputs( $f, "					</offer>\n");

			}
			db_free_result($q);

		}
		fputs( $f, "				</offers>\n");
	}

	function _exportEnd( $f )
	{
		fputs( $f, "			</shop>\n" );
		fputs( $f, "		</yml_catalog>\n" );
	}

	function methodExport(){

		global $smarty;
		//show successful save confirmation message
		if (file_exists(DIR_TEMP.YandexMarket::YANDEX_FILE)){
			$file_info = array(
				'size'=>(string) round( filesize(DIR_TEMP.YandexMarket::YANDEX_FILE) / 1024 ),
				'mtime'=>Time::standartTime(filemtime(DIR_TEMP.YandexMarket::YANDEX_FILE)),
			);
			$smarty->assign("yandex_file", $file_info);
			if (isset($_GET["yandex_export_successful"])) {
				set_query('yandex_export_successful=yes','',true);
				$smarty->assign("yandex_export_successful", 1);
				$smarty->assign('base_url',$this->getStoreUrl());
			}
		}
		
		if (!isset($_POST["yandex_export"]))$_POST["yandex_export"] = '';
		if ($_POST["yandex_export"]) //save payment gateways_settings
		{
			$rurrate = (float)$_POST["yandex_rur_rate"];
			$yandex_export_product_name = isset($_POST['yandex_export_product_name'])?$_POST['yandex_export_product_name']:'only_name';

			if ($rurrate <= 0)
			{
				$smarty->assign( "yandex_errormsg", "Курс рубля указан неверно. Пожалуйста, вводите положительное число" );
			}else{//экспортировать товары
				$f = @fopen(DIR_TEMP.YandexMarket::YANDEX_FILE,"wb");
				if ($f)
				{
					$this->_exportToYandexMarket( $f, $rurrate, $yandex_export_product_name );
					fclose($f);
					setlocale(LC_CTYPE, 'ru_RU.CP-1251', 'ru_RU.CP1251', 'ru_RU.win');
					iconv_file('utf-8','cp1251',DIR_TEMP.YandexMarket::YANDEX_FILE,true);
					RedirectSQ('yandex_export_successful=yes');
				}else{
					$smarty->assign( "yandex_errormsg", "Ошибка при создании файла ".YandexMarket::YANDEX_FILE);
				}
			}
		}

		require(DIR_ROOT.'/includes/modules.export_products.php');

		$smarty->assign("admin_sub_dpt", "modules_yandex.tpl.html");
	}
	
	private function getStoreUrl()
	{
		static $store_url = null;
		if(!is_null($store_url)){
			return $store_url;
		}
		$store_url = correct_URL(isset($_POST['base_url'])?$_POST['base_url']:CONF_FULL_SHOP_URL);
		return $store_url;
	}
}
?>