<?php
define('DIR_ROOT', str_replace("\\","/",realpath(dirname(__FILE__))));
include(DIR_ROOT.'/includes/init.php');
include_once DIR_CFG.'/connect.inc.wa.php';

//GET INFORMATION ABOUT REQUESTED RSS

$type = isset($_GET['type'])?$_GET['type']:-1;
$productID = isset($_GET['id'])?$_GET['id']:0;

$RSSfilePath = '';
$RSStype = '';
$RSSfileLang = null;
if(isset($_GET['lang'])&&preg_match('/^[a-z]{2}$/',$_GET['lang'])){
	$RSSfileLang = $_GET['lang'];
}
foreach($_GET as $key=>$val){
	unset($_GET[$key]);
}
switch($type){
	case 'product_reviews':

		switch($productID){
			case 'all':
				$RSSfilePath = DIR_RSS.'/all-reviews.xml';
				$RSStype = 'all_product';
				break;
			default:
				$productID = intval($productID);
				if($productID<=0){
					header("HTTP/1.0 404 Not Found");
					echo ("<font color=red><b>Not Found</b></font>" );
					exit;
				}
				$RSSfilePath = sprintf(DIR_RSS.'/%d/%d.xml',$productID/10000,$productID%10000);
				$RSStype = 'product';
				break;
				break;
		}

		break;
			case 'blog':
				$RSSfilePath = DIR_RSS.'/blog-news.xml';
				if($RSSfileLang){
					$RSSfilePath = DIR_RSS.'/blog-news-'.$RSSfileLang.'.xml';	
				}
				$RSStype = 'blog';
				break;
			default:
				if($productID<=0){
					header("HTTP/1.0 404 Not Found");
					echo ("<font color=red><b>Not Found</b></font>" );
					exit;
				}
				break;

}

if(file_exists($RSSfilePath)){
	header('Content-type: application/xml');
	readfile($RSSfilePath);
	exit;
}else{
	if(!file_exists(DIR_RSS)){
		mkdir(DIR_RSS,0777,true);
	}
	$directory = dirname($RSSfilePath);
	if(!file_exists($directory)){
		mkdir($directory,0777,true);
	}
}


include_once(DIR_FUNC.'/db_functions.php' );
include_once(DIR_FUNC.'/setting_functions.php' );
//include_once(DIR_CLASSES.'/classmanager.php');
include_once(DIR_FUNC.'/setting_functions.php' );

$DB_tree = new DataBase();
$DB_tree->connect(SystemSettings::get('DB_HOST'), SystemSettings::get('DB_USER'), SystemSettings::get('DB_PASS'));
$DB_tree->selectDB(SystemSettings::get('DB_NAME'));

$DB_tree->query("SET character_set_client='".MYSQL_CHARSET."'");
$DB_tree->query("SET character_set_connection='".MYSQL_CHARSET."'");
$DB_tree->query("SET character_set_results='".MYSQL_CHARSET."'");
settingDefineConstants();
define('VAR_DBHANDLER','DBHandler');

$Register = &Register::getInstance();
/*@var $Register Register*/
$Register->set(VAR_DBHANDLER, $DB_tree);

$LanguageEntry = &LanguagesManager::getCurrentLanguage();
$locals = $LanguageEntry->getLocals(array(LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_HIDDEN), false, false);
$Register->set('CURRLANG_LOCALS', $locals);
$Register->set('CURR_LANGUAGE', $LanguageEntry);
ClassManager::includeClass('URL');
$urlEntry = new URL;
$urlEntry->loadFromServerInfo();

$urlEntry->Query='';
$urlEntry->setPath('');
unset($_SERVER['REQUEST_URI']);
$urlEntry->Path = str_replace('published/SC/html/scripts/','',$urlEntry->Path);
$urlEntry->URI = str_replace('published/SC/html/scripts/','',$urlEntry->URI);
$Register->set('URL_ENTRY', $urlEntry);

ClassManager::includeClass('RSSFeedGenerator');
$RSSFeed = new RSSFeedGenerator();
settingDefineMLConstants();


//NEWS
switch($RSStype){
	case 'blog':
		$_urlEntry = clone $urlEntry;
		$_urlEntry->setPath(set_query('?ukey=news'));
		if($RSSfileLang){
			if($language = LanguagesManager::getLanguageByISO2($RSSfileLang,true)){
				LanguagesManager::setCurrentLanguage($language->id,false);
			}else{
				$RSSfilePath = DIR_RSS.'/blog-news.xml';
			}
		}
		
		$RSSFeed->setChannel(translate('pgn_news').' ― '.CONF_SHOP_NAME,$_urlEntry->getURI(),translate('pgn_news'));
		$RSSFeed->itemElements = array('title','description','content:encoded'=>'description','dc:creator'=>'author','pubDate','link','guid');
		$RSSFeed->additionalElementSource = array('xmlns:dc'=>'http://purl.org/dc/elements/1.1/',
		'xmlns:content'=>'http://purl.org/rss/1.0/modules/content/');
		$RSSFeed->SQL = 'SELECT NID, add_stamp as pubDate, '.LanguagesManager::sql_prepareField('title',true).', '.LanguagesManager::sql_prepareField('textToPublication',false).' as description FROM '.NEWS_TABLE.' ORDER BY priority DESC, add_stamp DESC';

		$RSSFeed->setItemHandler('
				static $urlEntry;
				if(!$urlEntry){
					$Register = &Register::getInstance();
					$urlEntry = $Register->get(\'URL_ENTRY\');
				}
				$_urlEntry = clone $urlEntry;
				$_urlEntry->Query="";
				$_urlEntry->setPath(set_query(\'?ukey=news&blog_id=\'.$item[\'NID\']));
				$item[\'link\'] = $_urlEntry->getURI();
				$item[\'guid\'] = $item[\'link\'];
				return $item;');
		$RSSFeed->limit = 20;
		$RSSFeed->generateFeed($RSSfilePath);
		break;
	case 'product':

		//PRODUCTS

		if($productID>0){
			$product = new Product();
			$product->loadByID($productID);

			$urlEntry->Query='';
			$urlEntry->setPath('');
			unset($_SERVER['REQUEST_URI']);
			$urlEntry->Path = str_replace('published/SC/html/scripts/','',$urlEntry->Path);
			$urlEntry->URI = str_replace('published/SC/html/scripts/','',$urlEntry->URI);
			$urlEntry->setPath(set_query("?ukey=discuss_product&productID={$productID}&product_slug={$product->slug}"));

			$RSSFeed->setChannel(strip_tags(str_replace('%PRODUCT_NAME%',$product->name,translate('prddiscussion_title'))).' ― '.CONF_SHOP_NAME,$urlEntry->getURI(),'');
			$RSSFeed->itemElements = array('title','description','dc:creator'=>'author','pubDate','link','guid');
			$RSSFeed->additionalElementSource = array('xmlns:dc'=>'http://purl.org/dc/elements/1.1/');
			$RSSFeed->SQL = 'SELECT Topic as title,\''.$urlEntry->getURI().'\' as link, Body as description, Author as author, UNIX_TIMESTAMP(add_time) as pubDate, CONCAT(\''.$urlEntry->getURI().'\',\'#\',DID) as guid FROM '.DISCUSSIONS_TABLE.' WHERE productID='.$productID.' ORDER BY DID DESC';
			$RSSFeed->limit = 10;
			$RSSFeed->setItemHandler('$item[\'description\'] = htmlspecialchars($item[\'description\']);return $item;');
			$RSSFeed->generateFeed($RSSfilePath);
		}else{
			header("HTTP/1.0 404 Not Found");
			echo ("<font color=red><b>Not Found</b></font>" );
		}
		break;
	case 'all_product':
		$_urlEntry = clone $urlEntry;
		$_urlEntry->Query = '';
		$_urlEntry->setQuery('?did=20');
		$_urlEntry->setPath('frame.php');



		$RSSFeed->setChannel(translate('pgn_product_reviews').' ― '.CONF_SHOP_NAME,$_urlEntry->getURI(),'');

		$RSSFeed->itemElements = array('title','description','dc:creator'=>'author','pubDate','link','guid isPermaLink="false"'=>'guid');
		$RSSFeed->additionalElementSource = array('xmlns:dc'=>'http://purl.org/dc/elements/1.1/');
		$RSSFeed->SQL = 'SELECT t2.slug as slug,t2.'.LanguagesManager::ml_getLangFieldName('name').' as name,t1.Topic as subject, t1.Body as review, t1.Author as author,t1.productID as productID, UNIX_TIMESTAMP(t1.add_time) as pubDate, CONCAT(t1.productID,\'#\',t1.DID) as guid, t1.DID as newsID FROM '.DISCUSSIONS_TABLE.' as t1 LEFT JOIN '.PRODUCTS_TABLE.' as t2 ON(t1.productID = t2.productID) ORDER BY DID DESC';
		$RSSFeed->limit = 20;
		$RSSFeed->setItemHandler('
			static $urlEntry;
			if(!$urlEntry){
				$Register = &Register::getInstance();
				$urlEntry = $Register->get(\'URL_ENTRY\');
			}
			$_urlEntry = clone $urlEntry;
			$_urlEntry->Query="";
			$_urlEntry->setPath(set_query(\'?ukey=discuss_product&product_slug=\'.$item[\'slug\'].\'&productID=\'.$item[\'productID\'].\'#\'.$item[\'newsID\']));

			$item[\'link\'] = $_urlEntry->getURI();
			$item[\'title\'] = $item[\'name\'].\' — \'.translate(\'review from\').\' \'.$item[\'author\'];
			$item[\'subject\'] = htmlspecialchars($item[\'subject\']);
			$item[\'review\'] = htmlspecialchars($item[\'review\']);
			$item[\'description\'] = "<p><strong>{$item[\'subject\']}</strong></p><p>{$item[\'review\']}</p>";
			return $item;');
		$RSSFeed->generateFeed($RSSfilePath);//.'.tmp');
		break;
}
if(file_exists($RSSfilePath)){
	header('Content-type: application/xml');
	readfile($RSSfilePath);
	exit;
}else{
	header("HTTP/1.0 404 Not Found");
	echo ("<font color=red><b>Not Found</b></font>" );
	exit;
}
?>