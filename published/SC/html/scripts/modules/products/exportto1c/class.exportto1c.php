<?php
function textMessage($message, $params = '') {
	return $message;
}

function logwrite($_Message, $level) {
	$debug_level = 0;
	
	if ($debug_level >= $level) {
		$_Message = var_export($_Message  ,true);
		$fp = fopen(DIR_LOG.'/SC.1C.level-'.$level.'.'.date('Y.m.d').'.log', 'a');
		fwrite($fp, "\r\n".date("Y-m-d H:i:s ")."\r\n".$_Message."\r\n");
		fclose($fp);
	}
	return true;
}


class ExportTo1c extends Module {
	const _1C_FILE = "/exportto1c.xml";
	const _1C_IMPORT_FILE = "/importfrom1c.xml";

	function initInterfaces(){

		$this->Interfaces = array(
			'export_page' => array(
				'name' => 'Страница экспорта продуктов в 1C',
				'method' => 'export_page',
			),
			'exchange_1c' => array(
				'name' => 'Точка входа для синхронизации с 1c управление торговлей',
				'method' => 'exchange_1c',
			)
		);
	}
	
	function initSettings()
	{
		parent::initSettings();
	}
	
	function exchange_1c()
	{
		if ( !CONF_1C_ON ) {
			echo "failure\n".textMessage("error_not_enabled");
		}
		
		logwrite($_REQUEST, 1);
		
		if(!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])){
		  /*
		   * Add support on FastCGI mode
		   * RewriteCond %{HTTP:Authorization} !^$
		   * RewriteRule ^(.*)$ $1?http_auth=%{HTTP:Authorization} [QSA]
		   */
			if(isset($_GET['http_auth'])){
				$d = base64_decode(substr($_GET['http_auth'],6) );
				list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $d);
			}
		}
		$user = $_SERVER['PHP_AUTH_USER'];
		$password = $_SERVER['PHP_AUTH_PW'];
		
		if ( ($user != "webasyst" || $password != $this->getSecureKey()) ) {
			echo "failure\n".textMessage("error_authorize");
			exit;
		}
		
		@set_time_limit(0);
		
		$DIR_NAME = DIR_TEMP;
		
//		$post_max_size = ini_get("post_max_size")? ini_get("post_max_size") : 8388608;
//		$memory_limit = ini_get("memory_limit")? ini_get("memory_limit")-5000000 : 8388608;
		
		$FILE_SIZE_LIMIT = 1000000;//max($post_max_size, $memory_limit);
		$USE_ZIP =  false && function_exists("zip_open");
	
		if(isset($_GET["filename"]) && (strlen($_GET["filename"])>0))
		{
			$filename = trim(str_replace("\\", "/", trim($_GET["filename"])), "/");
			$filename = $DIR_NAME."/".$filename;
			
			if ( !file_exists( dirname($filename) ) ) {
				mkdir(dirname($filename), 0775, true);
			}
			
		}
		
		if( $_GET['type'] == "catalog" ) {
			
			if ( $_GET['mode'] == 'checkauth' ) {
				echo "success\n";
				echo session_name()."\n";
				echo session_id() ."\n";
				
			}
			else {
				
				if($_GET["mode"]=="init")
				{
					if(!is_dir($DIR_NAME))
					{
						echo "failure\n",textMessage("error_init");
					}
					else
					{
						echo "zip=".($USE_ZIP? "yes": "no")."\n";
						echo "file_limit=".$FILE_SIZE_LIMIT."\n";
					}
				}		
				elseif(($_GET["mode"] == "file"))
				{
					if(function_exists("file_get_contents")) {
						$data = file_get_contents("php://input");
					}
					elseif(isset($GLOBALS["HTTP_RAW_POST_DATA"])) {
						$data = &$GLOBALS["HTTP_RAW_POST_DATA"];
					}
					else {
						$data = false;
					}
		
					if($data !== false)
					{
						if($fp = fopen($filename, "ab"))
						{
							$result = fwrite($fp, $data);
							if($result === mb_strlen($data, 'latin1'))
							{
								echo "success\n";
							}
							else
							{
								echo "failure\n",textMessage("error_file_write");
							}
						}
						else
						{
							echo "failure\n",textMessage("error_file_open ".$filename);
						}
					}
					else
					{
						echo "failure\n",textMessage("error_http_read");
					}
				}
				elseif(($_GET["mode"] == "import"))
				{
					$registry = &$_SESSION["SHOPSCRIPT_1C_IMPORT"]["registry"];
					
					if (!isset($registry["STEP"]) || $registry["STEP"] == 0) { 
						$registry["STEP"] = 1;
					}
		
					if ($registry["STEP"] == 1) {
						
						//TODO: unzip
						$strMessage = textMessage("zip_done");
						$registry["STEP"] = 2;
					}
					if ($registry["STEP"] == 2) 
					{
						try {
							$Parser1CXml = new Parser1CXml();
							if($Parser1CXml->parce($filename))
							{
								update_products_Count_Value_For_Categories(1);
								$registry["STEP"] = 3;
								$strMessage = textMessage("file_read");
								unlink($filename);
							}
						}
						catch (Exception $e) {							
						}
					}
					else {
						$registry["STEP"]++;
					}
					
					if($strError)
					{
						echo "failure\n".$strError;
					}
					elseif($registry["STEP"] < 3)
					{
						echo "progress\n",$strMessage;
					}
					else
					{
						$registry["STEP"] = 0;
						echo "success\n",textMessage("import_success");
					}
				}
				
			}
		}
		elseif ($_GET['type'] == "sale"){
			
			
			if ( $_GET['mode'] == 'checkauth' ) {
				echo "success\n";
				echo session_name()."\n";
				echo session_id() ."\n";
			}
			else {
				
				if($_GET["mode"]=="init")
				{
					if(!is_dir($DIR_NAME))
					{
						echo "failure\n",textMessage("error_init");
					}
					else
					{
						echo "zip=".($USE_ZIP? "yes": "no")."\n";
						echo "file_limit=".$FILE_SIZE_LIMIT."\n";
					}
				}		
				else if ($_GET["mode"]=="query") {
					
					echo "<?xml version=\"1.0\" encoding=\"windows-1251\"?>\n";
					
					$orders = db_phquery( "
					
					SELECT * FROM ( SELECT
						 ?#ORDER_STATUS_CHANGE_LOG_TABLE.orderID ,
						 ?#ORDER_STATUS_CHANGE_LOG_TABLE.status_name ,
						 ?#ORDER_STATUS_CHANGE_LOG_TABLE.status_change_time ,
						 ?#ORDER_STATUS_CHANGE_LOG_TABLE.status_comment
						FROM ?#ORDER_STATUS_CHANGE_LOG_TABLE 
						WHERE status_change_time > ? ORDER BY ?#ORDER_STATUS_CHANGE_LOG_TABLE.status_change_time DESC) AS tbl
						LEFT JOIN ?#ORDERS_TABLE ON  ?#ORDERS_TABLE.orderID = tbl.orderID 
						 GROUP BY tbl.orderID", CONF_1C_TIME_LASTEXPORT);
					
$xml = '<КоммерческаяИнформация ВерсияСхемы="2.03" ДатаФормирования="'.date("Y-m-d H:i").'">';
					
					while($order = db_fetch_assoc($orders)) {
						
						$date_ = date("Y-m-d H:i", strtotime( Time::standartTime($order["order_time"])));
						list($date, $time) = explode(" ", $date_);
					
						$currency_code = $order['currency_code'];
						if ( $currency_code == 'RUR' || $currency_code == 'RUB') {
							$currency_code = "руб";
						}

						$shipping_address = $order['shipping_address'];
						if (trim($order['shipping_city'])) {
							$shipping_address .= ', '.$order['shipping_city'];
						}
						if (trim($order['shipping_zip'])) {
							$shipping_address .= ', '.$order['shipping_zip'];
						}
						if (trim($order['shipping_country'])) {
							$shipping_address .= ', '.$order['shipping_country'];
						}
						$billing_address = $order['billing_address'];
						if (trim($order['billing_city'])) {
							$billing_address .= ', '.$order['billing_city'];
						}
						if (trim($order['billing_zip'])) {
							$billing_address .= ', '.$order['billing_zip'];
						}
						if (trim($order['billing_country'])) {
							$billing_address .= ', '.$order['billing_country'];
						}
						
					$xml .= '
	<Документ>
		<Ид>'.$order['orderID'].'</Ид>
		<Номер>'.CONF_ORDERID_PREFIX.$order['orderID'].'</Номер>
		<Дата>'.$date.'</Дата>
		<ХозОперация>Заказ товара</ХозОперация>
		<Роль>Продавец</Роль>
		<Валюта>'.$currency_code.'</Валюта>
		<Курс>'.$order['currency_value'].'</Курс>
		<Контрагенты>
			<Контрагент>
				<Ид>'.$order['customerID'].'</Ид>
				<Наименование>'.htmlspecialchars($order['customer_firstname']).' '.htmlspecialchars($order['customer_lastname']).'</Наименование>
				<Роль>Покупатель</Роль>
				<ПолноеНаименование>'.htmlspecialchars($order['customer_firstname']).' '.htmlspecialchars($order['customer_lastname']).'</ПолноеНаименование>
				<Фамилия>'.$order['customer_lastname'].'</Фамилия>
				<Имя>'.$order['customer_firstname'].'</Имя>
				<АдресРегистрации>
					<Вид>Адрес доставки</Вид>
					<Представление>'.htmlspecialchars($shipping_address).'</Представление>
					<АдресноеПоле>
						<Тип>Почтовый индекс</Тип>
						<Значение>'.$order['shipping_zip'].'</Значение>
					</АдресноеПоле>
					<АдресноеПоле>
						<Тип>Регион</Тип>
						<Значение>'.$order['shipping_state'].'</Значение>
					</АдресноеПоле>
					<АдресноеПоле>
						<Тип>Город</Тип>
						<Значение>'.$order['shipping_city'].'</Значение>
					</АдресноеПоле>
					<АдресноеПоле>
						<Тип>Улица</Тип>
						<Значение>'.$order['shipping_address'].'</Значение>
					</АдресноеПоле>
				</АдресРегистрации>
				<Контакты>
					<Контакт>
						<Тип>Почта</Тип>
						<Значение>'.$order['customer_email'].'</Значение>
					</Контакт>';

					$phone = db_phquery_fetch(DBRFETCH_FIRST, "SELECT reg_field_value FROM SC_customer_reg_fields_values WHERE customerID = ? AND reg_field_ID = 1 LIMIT 1", $order['customerID']);
					if ($phone) {
						$phone = htmlspecialchars($phone);
						$xml .= '
						<Контакт>
							<Тип>ТелефонРабочий</Тип>
							<Представление>'.$phone.'</Представление>
							<Значение>'.$phone.'</Значение>
					</Контакт>';
					}
					
					$xml .= '				
				</Контакты>
			</Контрагент>
		</Контрагенты>
		<Время>'.$time.'</Время>
		<Комментарий>'.htmlspecialchars($order['status_comment']).'</Комментарий>';

		if ($order['order_discount'] > 0) {
			$xml .='	
			<Скидки>
				<Скидка>
					<Наименование>Скидка</Наименование>
					<Сумма>'.$order['order_discount']*$order['currency_value'].'</Сумма>
					<УчтеноВСумме>true</УчтеноВСумме>
				</Скидка>
			</Скидки>';
		}
		$xml .='
		<Товары>';					

		$products = db_phquery_fetch(DBRFETCH_ASSOC_ALL, "SELECT *, ?#ORDERED_CARTS_TABLE.Price as PriceInOrder FROM ?#ORDERED_CARTS_TABLE
			LEFT JOIN ?#SHOPPING_CART_ITEMS_TABLE
				ON ?#ORDERED_CARTS_TABLE.itemID = ?#SHOPPING_CART_ITEMS_TABLE.itemID
			LEFT JOIN ?#PRODUCTS_TABLE 
				ON ?#SHOPPING_CART_ITEMS_TABLE.productID = ?#PRODUCTS_TABLE.productID
			 
			WHERE orderID = ?", $order["orderID"]);
		foreach ($products as $product) {
				
			$xml .=
'			<Товар>
				<Ид>'.$product['id_1c'].'</Ид>
				<Наименование>'.htmlspecialchars($product[LanguagesManager::ml_getLangFieldName('name')], ENT_NOQUOTES).'</Наименование>
				<БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE">шт</БазоваяЕдиница>
				<ЦенаЗаЕдиницу>'.($product['PriceInOrder']*$order['currency_value']+$product['PriceInOrder']*$order['currency_value']*$product['tax']/100).'</ЦенаЗаЕдиницу>
				<Количество>'.$product['Quantity'].'</Количество>
				<Сумма>'.$product['Quantity'] * ($product['PriceInOrder']*$order['currency_value']+$product['PriceInOrder']*$order['currency_value']*$product['tax']/100).'</Сумма>
				<ЗначенияРеквизитов>
					<ЗначениеРеквизита>
						<Наименование>ВидНоменклатуры</Наименование>
						<Значение>Товар</Значение>
					</ЗначениеРеквизита>
					<ЗначениеРеквизита>
						<Наименование>ТипНоменклатуры</Наименование>
						<Значение>Товар</Значение>
					</ЗначениеРеквизита>
				</ЗначенияРеквизитов>
			</Товар>';
		}
		if ($order['shipping_cost']) {
			$xml .=
'			<Товар>
				<Ид>ORDER_DELIVERY</Ид>
				<Наименование>Доставка заказа</Наименование>
				<БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE">шт</БазоваяЕдиница>
				<ЦенаЗаЕдиницу>'.$order['shipping_cost'].'</ЦенаЗаЕдиницу>
				<Количество>1</Количество>
				<Сумма>'.$order['shipping_cost']*$order['currency_value'].'</Сумма>
				<ЗначенияРеквизитов>
					<ЗначениеРеквизита>
						<Наименование>ВидНоменклатуры</Наименование>
						<Значение>Услуга</Значение>
					</ЗначениеРеквизита>
					<ЗначениеРеквизита>
						<Наименование>ТипНоменклатуры</Наименование>
						<Значение>Услуга</Значение>
					</ЗначениеРеквизита>
				</ЗначенияРеквизитов>
			</Товар>';
		}
			
	$xml .=		
'		</Товары>
		<Сумма>'.$order['order_amount']*$order['currency_value'].'</Сумма>
		<ЗначенияРеквизитов>
			<ЗначениеРеквизита>
				<Наименование>Способ оплаты</Наименование>
				<Значение>'.htmlspecialchars($order['payment_type']).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Статус заказа</Наименование>
				<Значение>'.htmlspecialchars( $order['status_name']).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Дата изменения статуса</Наименование>
				<Значение>'.date("Y-m-d H:i:s",  Time::timeToServerTime( strtotime($order['status_change_time']))).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Способ доставки</Наименование>
				<Значение>'.htmlspecialchars($order['shipping_type']).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Адрес доставки</Наименование>
				<Значение>'.htmlspecialchars($shipping_address).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Адрес платильщика</Наименование>
				<Значение>'.htmlspecialchars($billing_address).'</Значение>
			</ЗначениеРеквизита>
		</ЗначенияРеквизитов>
	</Документ>';		
			logwrite($xml, 4);	
			echo iconv('utf-8', 'cp1251', $xml);
			$xml = '';
		}
		db_free_result($orders);
$xml .= '</КоммерческаяИнформация>';
		echo iconv('utf-8', 'cp1251', $xml);
		
		_setSettingOptionValue('CONF_1C_TIME_LASTEXPORT', date("Y-m-d H:i:s"));
					
				} // if ($_GET["mode"]=="query") 
				elseif ($_GET["mode"]=="success") {
					echo "success\n";
				}
				elseif(($_GET["mode"] == "file"))
				{
					echo "success\n";
				}
			}
		}
		
		exit;
	}

	public function change_module_state()
	{
		_setSettingOptionValue('CONF_1C_ON', ($_GET['enable'] == 1)?1:0);
		if ($_GET['enable'] == 1) {
		 	if(isset($_GET['caller'])){
				$JsHttpRequest = new JsHttpRequest(translate("str_default_charset"));
			}
			$GLOBALS['_RESULT'] = array("key" => $this->creatSecureKey());
			die;
		}
		return "";
	}
	
	public function import_action()
	{
		$smarty = &Core::getSmarty();
		do{
			if(
				PEAR::isError($res = File::checkUpload($_FILES['xml']))||
				PEAR::isError($res = File::move_uploaded($_FILES['xml']['tmp_name'], DIR_TEMP.self::_1C_IMPORT_FILE))
			){
				$error = $res;
				
			}
			$smarty->assign("file_excel_name", DIR_TEMP.self::_1C_IMPORT_FILE);				
			$parser1CXml = new Parser1CXml(false);
			if ($parser1CXml->parce(DIR_TEMP.self::_1C_IMPORT_FILE)) {
				update_products_Count_Value_For_Categories(1);
				RedirectSQ('importfrom1c_successful=yes');
			}
			else {
				$smarty->assign("exportto1c_errormsg", translate('lbl_error'));
				$smarty->assign("admin_sub_dpt", "modules_exportto1c.tpl.html");
			}
			
		} while(false);
	}

	const MODE_1C_PRODUCTS = 1;
	const MODE_1C_ORDERS = 2;
	
	private $MODE = array(
		1 => array( 'name' => "Продукты"),
		2 => array( 'name' => "Заказы"),
	);
	
	function export_page(){
		$smarty = &Core::getSmarty();
		$smarty->assign("admin_sub_dpt", "modules_exportto1c.tpl.html");
		
		if (!extension_loaded ('xmlreader')) {
			$smarty->assign("not_extension", "1");
			return ;
		}
		
		if ($_GET["action"] == 'change_module_state') {
			$this->change_module_state();
			return;
		}
		
		$html =  setting1_CHECK_BOX( 'CONF_1C_EXPORT_ORDERS');
		$smarty->assign( 'export_orders', $html );
		
		$html =  setting1_CHECK_BOX( 'CONF_1C_EXPORT_PRODUCTS');
		$smarty->assign( 'export_products', $html );
		
		//show successful save confirmation message
		if (file_exists(DIR_TEMP.self::_1C_FILE)){
			$file_info = array(
				'size'=>(string) round( filesize(DIR_TEMP.self::_1C_FILE) / 1024 ),
				'mtime'=>Time::standartTime(filemtime(DIR_TEMP.self::_1C_FILE)),
			);
			$smarty->assign("exportto1c_file", $file_info);
			if (isset($_GET["exportto1c_successful"])) {
				set_query('exportto1c_successful=yes','',true);
				$smarty->assign("exportto1c_successful", 1);
				$smarty->assign('base_url',$this->getStoreUrl());
			}
			if (isset($_GET["importfrom1c_successful"])) {
				set_query('importfrom1c_successful=yes','',true);
				$smarty->assign("importfrom1c_successful", 1);
				$smarty->assign('base_url',$this->getStoreUrl());
			}
		}
		
		$smarty->assign('user', 'webasyst');
		$smarty->assign('password', $this->getSecureKey());
		
		$smarty->assign('url_from_1c', $this->getCallbackUrl());
		
		if ($_POST["importfrom1c"]) {
			$this->import_action();
			return;
		}
		
		if (!isset($_POST["exportto1c"]))$_POST["exportto1c"] = '';
		if ($_POST["exportto1c"]) //save payment gateways_settings
		{

			$f = @fopen(DIR_TEMP.self::_1C_FILE,"wb");
			if ($f)
			{
				@set_time_limit(0);
				$this->_exportTo1C( $f);
				fclose($f);
				RedirectSQ('exportto1c_successful=yes');
				
			}else{
				$smarty->assign( "export1c_errormsg", "Ошибка при создании файла ".self::_1C_FILE);
			}
		}
		
	}
	
	private $categories_ids = array();
	
	function _exportTo1C( $f )
	{
		$categories = db_phquery_fetch(DBRFETCH_ASSOC_ALL, "SELECT categoryID, parent, 
																id_1c, ".LanguagesManager::ml_getLangFieldName('name')." as name 
															FROM ?#CATEGORIES_TABLE ");
		foreach ($categories as $category) { 
			$this->categories_ids[$category['categoryID']] = $category['id_1c'];
		}
		
		$spArray = array(
			'exprtUNIC'=>array(
			'mode' 				=>'toarrays',
			'expProducts' 		=>array()
		)
		);
		
		session_write_close();
		$this->_exportBegin( $f, $spArray['exprtUNIC']['expProducts'], $categories  );
		session_start();
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

	function _exportBegin( $f, &$_ProductIDs, $categories )
	{
		fputs( $f, "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n" );
		fputs( $f, '	<КоммерческаяИнформация ВерсияСхемы="2.03" ДатаФормирования="'.date("Y-m-d H:i")."\">\n" );
		
		
		
		if ( $this->is_products_mode() ) {
			$uuid = new uuid();
			$classifierID = $uuid->get();
			fputs( $f, "		<Классификатор>\n" );
			fputs( $f, "			<Ид>".$classifierID."</Ид>\n" );
			fputs( $f, "			<Наименование>Классификатор (Каталог товаров)</Наименование>\n" );
			fputs( $f, "			<Владелец>\n" );
			fputs( $f, "				<Ид>bd72d900-55bc-11d9-848a-00112f43529a</Ид>\n" );
			fputs( $f, "				<ПолноеНаименование>".$this->_deleteHTML_Elements(CONF_SHOP_NAME, false)."</ПолноеНаименование>\n" );
			fputs( $f, "				<Наименование>".$this->_deleteHTML_Elements(CONF_SHOP_NAME, false)."</Наименование>\n" );
			fputs( $f, "			</Владелец>\n" );		
			
			$level = 0;
			$categories = catGetCategoryCList();
			
			fputs($f,"				<Группы>\n");
			foreach ($categories as $category) {
				if ($category['categoryID'] == 1) {
					$category['name'] = "Корень";
				}
				
				if ( !$category['id_1c'] ) {
					$category['id_1c'] = $uuid->get();
					db_phquery("UPDATE ?#CATEGORIES_TABLE SET id_1c = ? WHERE categoryID =? ", $category['id_1c'], $category['categoryID']);
					$this->categories_ids[$category['categoryID']] = $category['id_1c'];
				}
				
				if ($category['level'] > $level) {
					fputs($f,"					<Группы>\n");
					$level = $category['level'];
				}
				else if($category['level'] < $level)
				{
					for ($i = 0; $i < $level-$category['level']; $i++) {
						fputs($f,"					</Группы>\n");
						fputs($f,"				</Группа>\n");
					}
					$level = $category['level'];
				}
				
				fputs($f,"					<Группа>\n");
				fputs($f,"						<Ид>".$category['id_1c']."</Ид>\n");
				fputs($f,"						<Наименование>".$this->_deleteHTML_Elements($category['name'], false)."</Наименование>\n");
				fputs($f,"						<Родитель>".$this->categories_ids[$category['parent']]."</Родитель>\n");

				if (!($category['ExistSubCategories'] && $category['ExistSubCategories'] > 0)) {
					fputs($f,"					</Группа>\n");
				}
			}
			
			if ($level > 0) {
				for ($i = 0; $i < $level; $i++) {
					fputs($f,"					</Группы>\n");
					fputs($f,"				</Группа>\n");
				}
			}
			
			fputs($f,"				</Группы>\n");		
			fputs( $f, "		</Классификатор>\n" );
		
			$catalogID = $uuid->get();
			fputs( $f, "		<Каталог>\n" );
			fputs( $f, "			<Ид>".$catalogID."</Ид>\n" );
			fputs( $f, "			<ИдКлассификатора>".$classifierID."</ИдКлассификатора>\n" );
			fputs( $f, "			<Наименование>Каталог товаров от ".date("Y-m-d H:i")."</Наименование>\n" );
			fputs( $f, "			<Владелец>\n" );
			fputs( $f, "				<Ид>bd72d900-55bc-11d9-848a-00112f43529a</Ид>\n" );
			fputs( $f, "				<ПолноеНаименование>".$this->_deleteHTML_Elements(CONF_SHOP_NAME, false)."</ПолноеНаименование>\n" );
			fputs( $f, "				<Наименование>".$this->_deleteHTML_Elements(CONF_SHOP_NAME, false)."</Наименование>\n" );
			fputs( $f, "			</Владелец>\n" );
			fputs( $f, "			<Товары>\n" );
	
		
			$sql = "select productID, ".LanguagesManager::sql_prepareField('name')." AS name, Price, categoryID, description_ru, in_stock, slug, id_1c, product_code from ".PRODUCTS_TABLE." WHERE enabled=1";
			$products = db_phquery($sql);
			$store_url = $this->getStoreUrl();
			
			$product_cache = array();
			while ( $product = db_fetch_assoc($products) )	{	
				if (!$product["name"]) { continue;}
				
				if ( !trim($product['id_1c']) ) {
					$product['id_1c'] = $uuid->get();
					$product_cache[ $product['productID'] ] = $product['id_1c'];
					db_phquery("UPDATE ?#PRODUCTS_TABLE SET id_1c = ? WHERE productID =? ", $product['id_1c'], $product['productID']);
				}
				fputs( $f, "					<Товар>\n" );
				fputs( $f, "						<Ид>".$product['id_1c']."</Ид>\n" );
				if ($product['product_code']) {
					fputs( $f, "						<Артикул>".htmlspecialchars($product['product_code'])."</Артикул>" );
				}
				if (isset($this->categories_ids[$product['categoryID']]) && $this->categories_ids[$product['categoryID']] != '') {
					fputs( $f, "						<Группы><Ид>".$this->categories_ids[$product['categoryID']]."</Ид></Группы>\n" );
				}
	
				$product["name"]		= $this->_deleteHTML_Elements( $product["name"] , false);
				
				fputs( $f, "						<Наименование>".htmlspecialchars($product["name"])."</Наименование>\n" );
				fputs( $f, "					    <БазоваяЕдиница Код=\"796\" НаименованиеПолное=\"Штука\" МеждународноеСокращение=\"PCE\">шт</БазоваяЕдиница>\n" );
				fputs( $f, "						<Описание>".htmlspecialchars($product["description_ru"])."</Описание>\n" );
				
				fputs( $f, "      	<ЗначенияРеквизитов>\n" );
                fputs( $f, "            <ЗначениеРеквизита>\n" );
                fputs( $f, "    	        <Наименование>ВидНоменклатуры</Наименование>\n" );
                fputs( $f, "              	<Значение>Товар</Значение>\n" );
                fputs( $f, "            </ЗначениеРеквизита>\n" );
                fputs( $f, "            <ЗначениеРеквизита>\n" );
                fputs( $f, "            	<Наименование>ТипНоменклатуры</Наименование>\n" );
                fputs( $f, "                <Значение>Товар</Значение>\n" );
                fputs( $f, "            </ЗначениеРеквизита>\n" );
                fputs( $f, "        </ЗначенияРеквизитов>\n" );
				fputs( $f, "					</Товар>\n");
			}
			
			fputs( $f, "				</Товары>\n");
			fputs( $f, "			</Каталог>\n" );
			
			fputs( $f, "			<ПакетПредложений СодержитТолькоИзменения=\"false\">\n" );
			fputs( $f, "				<Ид>bd72d8f9-55bc-11d9-848a-00112f43529a#</Ид>\n" );
			fputs( $f, "				<Наименование>Пакет предложений</Наименование>\n" );
			fputs( $f, "				<ИдКаталога>".$catalogID."</ИдКаталога>\n" );
			fputs( $f, "				<ИдКлассификатора>".$classifierID."</ИдКлассификатора>\n" );
			fputs( $f, "			<Владелец>\n" );
			fputs( $f, "				<Ид>bd72d900-55bc-11d9-848a-00112f43529a</Ид>\n" );
			fputs( $f, "				<ПолноеНаименование>".$this->_deleteHTML_Elements(CONF_SHOP_NAME, false)."</ПолноеНаименование>\n" );
			fputs( $f, "				<Наименование>".$this->_deleteHTML_Elements(CONF_SHOP_NAME, false)."</Наименование>\n" );
			fputs( $f, "			</Владелец>\n" );			
			fputs( $f, "				<ТипыЦен>\n" );
			fputs( $f, "					<ТипЦены>\n" );
			fputs( $f, "					<Ид>cbcf493b-55bc-11d9-848a-00112f43529a</Ид>\n" );
			fputs( $f, "					<Наименование>Розничная</Наименование>\n" );
			$currency = Currency::getDefaultCurrencyInstance()->currency_iso_3;
			if ($currency == "RUB" || $currency == "RUR") { 
				$currency = "руб";
			}
			fputs( $f, "					<Валюта>".$currency."</Валюта>\n" );
			fputs( $f, "					</ТипЦены>\n" );
			fputs( $f, "				</ТипыЦен>\n" );
			fputs( $f, "				<Предложения>\n" );
			
			if (!mysql_data_seek($products["resource"],0) ) {
				db_free_result($products);
				$sql = "select productID, ".LanguagesManager::sql_prepareField('name')." AS name, Price, categoryID, in_stock, slug, id_1c from ".PRODUCTS_TABLE." WHERE enabled=1";
				$products = db_phquery($sql);
			}
			while ( $product = db_fetch_assoc($products) )	{
				if (!$product["name"]) {continue;}
				
				fputs( $f, "				<Предложение>\n" );
				$product["name"]		= $this->_deleteHTML_Elements( $product["name"] , false);
				if (!$product['id_1c']) {
					$product['id_1c'] = $product_cache[ $product['productID'] ];
					unset( $product_cache[ $product['productID'] ] );
				}
				fputs( $f, "					<Ид>".$product['id_1c']."</Ид>\n" );
				fputs( $f, "					<Наименование>".htmlspecialchars($product["name"])."</Наименование>\n" );
				fputs( $f, "					<БазоваяЕдиница Код=\"796\" НаименованиеПолное=\"Штука\" МеждународноеСокращение=\"PCE\">шт</БазоваяЕдиница>\n" );
				fputs( $f, "					<Цены>\n" );
				fputs( $f, "						<Цена>\n" );
//				fputs( $f, "						<Представление>4 USD за шт</Представление>\n" );
				fputs( $f, "						<ИдТипаЦены>cbcf493b-55bc-11d9-848a-00112f43529a</ИдТипаЦены>\n" );
				fputs( $f, "						<ЦенаЗаЕдиницу>".$product['Price']."</ЦенаЗаЕдиницу>\n" );
				fputs( $f, "						<Валюта>".$currency."</Валюта>\n" );
				fputs( $f, "						<Единица>шт</Единица>\n" );
				fputs( $f, "						<Коэффициент>1</Коэффициент>\n" );
				fputs( $f, "						</Цена>\n" );
				fputs( $f, "					</Цены>\n" );
				fputs( $f, "					<Количество>".$product["in_stock"]."</Количество>\n" );
				fputs( $f, "				</Предложение>\n" );
			}
			db_free_result($products);
			unset($product_cache);
			
			fputs( $f, "				</Предложения>\n" );
			fputs( $f, "			</ПакетПредложений>\n" );
			
		}
		
		
		if ($this->is_orders_mode()) {		
			
			$where = '';
			if ( !$this->is_export_ordersall() ) { 
				$where = "WHERE status_change_time > '".CONF_1C_TIME_LASTEXPORT."'";
			}
			
			$orders = db_phquery("
					
					SELECT * FROM ( SELECT
						 ?#ORDER_STATUS_CHANGE_LOG_TABLE.orderID ,
						 ?#ORDER_STATUS_CHANGE_LOG_TABLE.status_name ,
						 ?#ORDER_STATUS_CHANGE_LOG_TABLE.status_change_time ,
						 ?#ORDER_STATUS_CHANGE_LOG_TABLE.status_comment
						FROM ?#ORDER_STATUS_CHANGE_LOG_TABLE 
						".$where." ORDER BY ?#ORDER_STATUS_CHANGE_LOG_TABLE.status_change_time DESC) AS tbl
						LEFT JOIN ?#ORDERS_TABLE ON  ?#ORDERS_TABLE.orderID = tbl.orderID 
						 GROUP BY tbl.orderID", CONF_1C_TIME_LASTEXPORT);
					
				while ($order = db_fetch_assoc($orders) )
				{
						$date_ = date("Y-m-d H:i", strtotime( Time::standartTime($order["order_time"])));
						list($date, $time) = explode(" ", $date_);
					
						$currency_code = $order['currency_code'];
						if ( $currency_code == 'RUR' || $currency_code == 'RUB') {
							$currency_code = "руб";
						}

						$shipping_address = $order['shipping_address'];
						if (trim($order['shipping_city'])) {
							$shipping_address .= ', '.$order['shipping_city'];
						}
						if (trim($order['shipping_zip'])) {
							$shipping_address .= ', '.$order['shipping_zip'];
						}
						if (trim($order['shipping_country'])) {
							$shipping_address .= ', '.$order['shipping_country'];
						}
						$billing_address = $order['billing_address'];
						if (trim($order['billing_city'])) {
							$billing_address .= ', '.$order['billing_city'];
						}
						if (trim($order['billing_zip'])) {
							$billing_address .= ', '.$order['billing_zip'];
						}
						if (trim($order['billing_country'])) {
							$billing_address .= ', '.$order['billing_country'];
						}
						
					$xml .= '
	<Документ>
		<Ид>'.$order['orderID'].'</Ид>
		<Номер>'.CONF_ORDERID_PREFIX.$order['orderID'].'</Номер>
		<Дата>'.$date.'</Дата>
		<ХозОперация>Заказ товара</ХозОперация>
		<Роль>Продавец</Роль>
		<Валюта>'.$currency_code.'</Валюта>
		<Курс>'.$order['currency_value'].'</Курс>
		<Контрагенты>
			<Контрагент>
				<Ид>'.$order['customerID'].'</Ид>
				<Наименование>'.htmlspecialchars($order['customer_firstname']).' '.htmlspecialchars($order['customer_lastname']).'</Наименование>
				<Роль>Покупатель</Роль>
				<ПолноеНаименование>'.htmlspecialchars($order['customer_firstname']).' '.htmlspecialchars($order['customer_lastname']).'</ПолноеНаименование>
				<Фамилия>'.$order['customer_lastname'].'</Фамилия>
				<Имя>'.$order['customer_firstname'].'</Имя>
				<АдресРегистрации>
					<Вид>Адрес доставки</Вид>
					<Представление>'.htmlspecialchars($shipping_address).'</Представление>
					<АдресноеПоле>
						<Тип>Почтовый индекс</Тип>
						<Значение>'.$order['shipping_zip'].'</Значение>
					</АдресноеПоле>
					<АдресноеПоле>
						<Тип>Регион</Тип>
						<Значение>'.$order['shipping_state'].'</Значение>
					</АдресноеПоле>
					<АдресноеПоле>
						<Тип>Город</Тип>
						<Значение>'.$order['shipping_city'].'</Значение>
					</АдресноеПоле>
					<АдресноеПоле>
						<Тип>Улица</Тип>
						<Значение>'.$order['shipping_address'].'</Значение>
					</АдресноеПоле>
				</АдресРегистрации>
				<Контакты>
					<Контакт>
						<Тип>Почта</Тип>
						<Значение>'.$order['customer_email'].'</Значение>
					</Контакт>';

					$phone = db_phquery_fetch(DBRFETCH_FIRST, "SELECT reg_field_value FROM SC_customer_reg_fields_values WHERE customerID = ? AND reg_field_ID = 1 LIMIT 1", $order['customerID']);
					if ($phone) {
						$phone = htmlspecialchars($phone);
						$xml .= '
						<Контакт>
							<Тип>ТелефонРабочий</Тип>
							<Представление>'.$phone.'</Представление>
							<Значение>'.$phone.'</Значение>
					</Контакт>';
					}
					
					$xml .= '				
				</Контакты>
			</Контрагент>
		</Контрагенты>
		<Время>'.$time.'</Время>
		<Комментарий>'.htmlspecialchars($order['status_comment']).'</Комментарий>';
					
		if ($order['order_discount'] > 0) {
			$xml .='	
			<Скидки>
				<Скидка>
					<Наименование>Скидка</Наименование>
					<Сумма>'.$order['order_discount']*$order['currency_value'].'</Сумма>
					<УчтеноВСумме>true</УчтеноВСумме>
				</Скидка>
			</Скидки>';
		}
		$xml .='
		<Товары>';					

		$products = db_phquery_fetch(DBRFETCH_ASSOC_ALL, "SELECT *, ?#ORDERED_CARTS_TABLE.Price as PriceInOrder FROM ?#ORDERED_CARTS_TABLE
			LEFT JOIN ?#SHOPPING_CART_ITEMS_TABLE
				ON ?#ORDERED_CARTS_TABLE.itemID = ?#SHOPPING_CART_ITEMS_TABLE.itemID
			LEFT JOIN ?#PRODUCTS_TABLE 
				ON ?#SHOPPING_CART_ITEMS_TABLE.productID = ?#PRODUCTS_TABLE.productID
			 
			WHERE orderID = ?", $order["orderID"]);
		foreach ($products as $product) {
				
			$xml .=
'			<Товар>
				<Ид>'.$product['id_1c'].'</Ид>
				<Наименование>'.htmlspecialchars($product[LanguagesManager::ml_getLangFieldName('name')], ENT_NOQUOTES).'</Наименование>
				<БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE">шт</БазоваяЕдиница>
				<ЦенаЗаЕдиницу>'.($product['PriceInOrder']*$order['currency_value']+$product['PriceInOrder']*$order['currency_value']*$product['tax']/100).'</ЦенаЗаЕдиницу>
				<Количество>'.$product['Quantity'].'</Количество>
				<Сумма>'.$product['Quantity'] * ($product['PriceInOrder']*$order['currency_value']+$product['PriceInOrder']*$order['currency_value']*$product['tax']/100).'</Сумма>
				<ЗначенияРеквизитов>
					<ЗначениеРеквизита>
						<Наименование>ВидНоменклатуры</Наименование>
						<Значение>Товар</Значение>
					</ЗначениеРеквизита>
					<ЗначениеРеквизита>
						<Наименование>ТипНоменклатуры</Наименование>
						<Значение>Товар</Значение>
					</ЗначениеРеквизита>
				</ЗначенияРеквизитов>
			</Товар>';
		}
		if ($order['shipping_cost']) {
			$xml .=
'			<Товар>
				<Ид>ORDER_DELIVERY</Ид>
				<Наименование>Доставка заказа</Наименование>
				<БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE">шт</БазоваяЕдиница>
				<ЦенаЗаЕдиницу>'.$order['shipping_cost'].'</ЦенаЗаЕдиницу>
				<Количество>1</Количество>
				<Сумма>'.$order['shipping_cost']*$order['currency_value'].'</Сумма>
				<ЗначенияРеквизитов>
					<ЗначениеРеквизита>
						<Наименование>ВидНоменклатуры</Наименование>
						<Значение>Услуга</Значение>
					</ЗначениеРеквизита>
					<ЗначениеРеквизита>
						<Наименование>ТипНоменклатуры</Наименование>
						<Значение>Услуга</Значение>
					</ЗначениеРеквизита>
				</ЗначенияРеквизитов>
			</Товар>';
		}
		
	$xml .=		
'		</Товары>
		<Сумма>'.$order['order_amount']*$order['currency_value'].'</Сумма>
		<ЗначенияРеквизитов>
			<ЗначениеРеквизита>
				<Наименование>Способ оплаты</Наименование>
				<Значение>'.htmlspecialchars($order['payment_type']).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Статус заказа</Наименование>
				<Значение>'.htmlspecialchars( $order['status_name']).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Дата изменения статуса</Наименование>
				<Значение>'.date("Y-m-d H:i:s", Time::timeToServerTime( strtotime($order['status_change_time']))).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Способ доставки</Наименование>
				<Значение>'.htmlspecialchars($order['shipping_type']).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Адрес доставки</Наименование>
				<Значение>'.htmlspecialchars($shipping_address).'</Значение>
			</ЗначениеРеквизита>
			<ЗначениеРеквизита>
				<Наименование>Адрес платильщика</Наименование>
				<Значение>'.htmlspecialchars($billing_address).'</Значение>
			</ЗначениеРеквизита>
		</ЗначенияРеквизитов>
	</Документ>';	
			fputs( $f, $xml);
			$xml = '';
		}			
		db_free_result($orders);
			_setSettingOptionValue('CONF_1C_TIME_LASTEXPORT', date("Y-m-d H:i:s"));
		}
		fputs( $f, "		</КоммерческаяИнформация>\n" );		
	}

	
	
	private function getStoreUrl()
	{
		$scURL = str_replace(array("http://","https://"),array('',''), trim( BASE_WA_URL ));
		if(SystemSettings::is_hosted()){
			$scURL .= 'SC/html/scripts/';
		}else{
			$scURL .= 'published/SC/html/scripts/';
		}
		return $scURL;
	}
	
	private function getCallbackUrl()
	{
		$scURL = str_replace(array("http://","https://"),array('',''), trim( BASE_WA_URL ));
		if(SystemSettings::is_hosted()){
			$scURL .= 'SC/html/scripts/';
		}else{
			$scURL .= 'published/SC/html/scripts/';
		}
		$scURL = "http".($https?'s':'')."://".$scURL.'callbackhandlers/1c_exchange.php';
		return $scURL;
	}
	
	function creatSecureKey() {
		$params[] = __FILE__;
		$params[] = time();
		$key = md5(implode('%',$params));
		_setSettingOptionValue('CONF_1C_PASSWORD', $key);
		return $key;
	}
	function getSecureKey(){
		
		if ( defined("CONF_1C_PASSWORD") ) {
			return constant("CONF_1C_PASSWORD");
		}
		else {
			$this->creatSecureKey();
			return _getSettingOptionValue("CONF_1C_PASSWORD");
		}
		
	}
	
	private function is_products_mode()
	{
		return _getSettingOptionValue('CONF_1C_EXPORT_PRODUCTS') == 1;
	}
	
	private function is_orders_mode()
	{
		return _getSettingOptionValue('CONF_1C_EXPORT_ORDERS') == 1;
	}
	
	private function is_export_ordersall()
	{
		return (isset($_POST['export_orders_mode'])&& $_POST['export_orders_mode'] == 2)? true : false;
	}
}

class log {
	static function text($name, $dump = null)
	{
		$handle = fopen('/tmp/1c.log', 'a');
		fwrite($handle, date("d-m-Y h:i:s").' call: '.print_r(array($name, $dump),true)."\n\n");
		fclose($handle);
	}
}

function setting1_CHECK_BOX($settingsID){

	$settings_constant_name = $settingsID;

	if ( isset($_POST["save"]) )
	_setSettingOptionValue( $settings_constant_name,
	isset($_POST["setting".$settings_constant_name])?1:0 );
	$res = '<input type="checkbox" name="setting'.$settings_constant_name.'" value="1" ';
	if ( _getSettingOptionValue($settings_constant_name) )
	$res .= ' checked="checked" ';
	$res .= " />";
	return $res;
}
?>