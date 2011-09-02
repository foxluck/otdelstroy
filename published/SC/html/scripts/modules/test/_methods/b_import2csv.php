<?php
/* @vars $smarty Smarty */
$smarty = &Core::getSmarty();
//products and categories catalog import from MS Excel .CSV files

//validate $_POST["delimeter"] data
if (isset($_POST["delimeter"]) && strlen($_POST["delimeter"])==0)
$_POST["delimeter"] = ";";


if (isset($_POST["proceed"])){ //upload file and show import configurator

	safeMode(true);
	$res = 0;
	$Register = &Register::getInstance();
	$FilesVar = &$Register->get(VAR_FILES);
	$file_excel_name = DIR_TEMP."/file.csv";

	//upload CSV-file
	if (isset($FilesVar["csv"]) && $FilesVar["csv"]["name"])
	{
		do{
				
			//check file upload result
			$res = File::checkUpload($FilesVar["csv"]);
			if(PEAR::isError($res)){
				$error = $res;
				break;
			}
				
			//move file to work directory
			if(
			PEAR::isError($res = File::checkUpload($FilesVar['csv']))||
			PEAR::isError($res = File::move_uploaded($FilesVar['csv']['tmp_name'],$file_excel_name))
			){
			/*$res = File::move_uploaded($FilesVar['csv']['tmp_name'],$file_excel_name);
			//$res = Functions::exec('file_move_uploaded', array($FilesVar['csv']['tmp_name'], $file_excel_name));
			if(PEAR::isError($res)){*/
				$error = $res;
				break;
			}
				
			//change file encoding if nessasary
			if($_POST['charset'] && strtoupper($_POST['charset']) != strtoupper(DEFAULT_CHARSET)){
				if(!File::convert($file_excel_name,$_POST['charset'],DEFAULT_CHARSET,true)){
					$error = PEAR::raiseError('error_convert_file_encoding');
					break;
				}
			}
				
			//change file permission to allow ftp managment
			File::chmod($file_excel_name);
				
				
			$smarty->assign("file_excel_name", $file_excel_name);
				
			$catalogImport = new ImportCatalog($file_excel_name,$_POST["delimeter"]);
				
			$product_fields = _getProductFields();
			$options_fields = _getOptionFields();
			$unique_columns = _getUniqueColumns();
			//read head line of csv file
			if(!$catalogImport->readCsvLine()){
				$error = PEAR::raiseError('error_read_csv_file');
				break;
			}
			
			$catalogImport->setTargetColumns(array('main'=>$product_fields,'customparams'=>$options_fields));
			$catalogImport->setPrimaryCols($unique_columns);
			$catalogImport->setConfiguratorHeader(array('prdimport_source_column','&nbsp;','prdimport_target_column','prdimport_primary_column'));//

			//var_dump($catalogImport);
			
			$excel_configurator = $catalogImport->getDataMappingHtmlConfigurator(true,false,'name');
			$smarty->assign("excel_import_configurator", $excel_configurator);
			$smarty->assign("source_column_count", sprintf(translate('prdimport_found_n_columns'),$catalogImport->getSourceColumnCount()));
			$smarty->assign("source_columns", $catalogImport->getSourceColumns());
			$smarty->assign("delimeter", $_POST["delimeter"]);
		}while(false);
	}
	if (isset($error)) //uploaded successfully
	{
		Message::raiseMessageRedirectSQ(MSG_ERROR,'',$error->getMessage());
		//$smarty->assign("message",$error->getMessage());// "upload_file_error");
	}
}

//last step of import = fill database with new content
//configuration finished - update database
if (isset($_POST["do_excel_import"]) && isset($_POST["filename"])){

	if (CONF_BACKEND_SAFEMODE){ //this action is forbidden when SAFE MODE is ON
		Redirect(set_query('safemode=yes'));
	}

	@set_time_limit(0);

	//import file content

	$importCatalog = new ImportCatalog($_POST["filename"],$_POST["delimeter"]);

	$line = $importCatalog->readCsvLine();
	$importCatalog->parseDataMapping();
	$data = $importCatalog->applyDataMapping($line);
	
	if (!$importCatalog->primary_col||!$importCatalog->isValidData($data['main'])) //not set update column
	{
		$smarty->assign("excel_import_result", "update_column_error");
		//go to the previous step
		$proceed = 1;
		$file_excel = "";
		$file_excel_name = $_POST["filename"];
		$res = 1;
	}else{
		$use_structure = isset($_POST['use_structure'])?$_POST['use_structure']:false;
		$session_id = session_id();
		session_write_close();
		$maxCount=0;
		$msg='';

		$limitExceed=false;
		if(SystemSettings::is_hosted()){
			$messageClient = new WbsHttpMessageClient($db_key, 'wbs_msgserver.php');
			$messageClient->putData('action', 'ALLOW_ADD_PRODUCT');
			$messageClient->putData('language',LanguagesManager::getCurrentLanguage()->iso2);
			$messageClient->putData('session_id',$session_id);
			$res=$messageClient->send();
		}else{
			$res = false;
		}
		
		if($res&&($messageClient->getResult('max')>0)){
			$maxCount=$messageClient->getResult('max')-$messageClient->getResult('current');
			$msg=$messageClient->getResult('msg');
			if($messageClient->getResult('success')!==true)
			{
				//$maxCount=0;
				$limitExceed=true;
			}
				
			while($line = $importCatalog->readCsvLine()){
				$data = $importCatalog->applyDataMapping($line);
				$statistic = $importCatalog->import($data,$use_structure,($maxCount>0));
				if($statistic['insert']){
					$maxCount--;
				}
			}


			$messageClient = new WbsHttpMessageClient($db_key, 'wbs_msgserver.php');
			$messageClient->putData('action', 'ALLOW_ADD_PRODUCT');
			$messageClient->putData('language',LanguagesManager::getCurrentLanguage()->iso2);
			$messageClient->putData('session_id',$session_id);
			$messageClient->send();
			$msg=$messageClient->getResult('msg');
			$limitExceed=!$messageClient->getResult('success');

			session_id($session_id);
			session_start();
				
			if(strlen($msg)&&$limitExceed){
				$msg='<div class="error_block" ><span class="error_message">'.$msg.'</span></div>';
			}elseif(strlen($msg)){
				$msg='<div class="comment_block" ><span class="success_message">'.$msg.'</span></div>';
			}

			$smarty->assign('limit_msg',$msg);
		}else{

			$line_counter = 0;	
			while($line = $importCatalog->readCsvLine()){
				$line_counter++;
				$data = $importCatalog->applyDataMapping($line);
				$statistic = $importCatalog->import($data,$use_structure);
				//$statistic = importCatalogData($data,$importCatalog->primary_col,$use_structure);
			}
		}
		
		//update products count value if defined
		update_products_Count_Value_For_Categories(1);
		if(SystemSettings::is_hosted()&&file_exists(WBS_DIR.'/kernel/classes/class.metric.php')){
			include_once(WBS_DIR.'/kernel/classes/class.metric.php');
				
			$DB_KEY=SystemSettings::get('DB_KEY');
			$U_ID = sc_getSessionData('U_ID');
				
			$metric = metric::getInstance();
			$metric->addAction($DB_KEY, $U_ID, 'SC','IMPORTPRODUCTNEW','ACCOUNT', $statistic['product_added']);
			$metric->addAction($DB_KEY, $U_ID, 'SC','IMPORTPRODUCTMOD','ACCOUNT', $statistic['product_modify']);
				
		}

		$smarty->assign("excel_import_result", "ok");

		$smarty->assign('category_added',$statistic['category_added']);
		$smarty->assign('category_modify',$statistic['category_modify']);
		$smarty->assign('product_added',$statistic['product_added']);
		$smarty->assign('product_modify',$statistic['product_modify']);
	}
}

global $file_encoding_charsets;
$smarty->assign('charsets', $file_encoding_charsets);
$smarty->assign('default_charset', translate('prdine_default_charset'));
//$smarty->assign('unique_columns', _getUniqueColumns());
$smarty->assign("admin_sub_dpt", "catalog_excel_import.tpl.html");
?>