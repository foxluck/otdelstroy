<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
class GoogleSitemap extends ActionsController
{
	private $sitemaps = array();
	protected $sitemap_names = array(
	'index'=>'sitemap_index_description',
	'pages'=>'cpt_lbl_divisions_navigation',
	'categories'=>'pgn_category_tree',
	'products'=>'pgn_catalog',
	'auxpages'=>'cpt_lbl_auxpages_navigation',
	'news'=>'blog_post_list',
	);
	protected $sitemap_properties = array(
	'index'		=>array('priority'=>0.5,'changefreq'=>null),
	'pages'		=>array('priority'=>0.5,'changefreq'=>null),
	'categories'=>array('priority'=>0.5,'changefreq'=>null),
	'products'	=>array('priority'=>0.5,'changefreq'=>null),
	'auxpages'	=>array('priority'=>0.5,'changefreq'=>null),
	'news'		=>array('priority'=>0.5,'changefreq'=>null),
	);
	function update()
	{
		$sitemaps = $this->getData('sitemap');
		$update_count = 0;

		foreach($this->sitemap_names as $sitemap=>$name){//remove old files
			$path = DIR_SITEMAP.DIRECTORY_SEPARATOR.$sitemap.'.xml';
			if(file_exists($path)){
				unlink($path);
			}
		}
		foreach($sitemaps as $sitemap=>$update){
			if($update){
				if(PEAR::isError($res = $this->__sitemap($sitemap))){

				}else{
					$update_count++;
				}
			}
		}
		$this->__makeIndex();
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_update_successful');
	}

	function setup()
	{
		$settings = array('pages'=>array(),'auxpages'=>array());

		$pages = $this->getData('page');
		$page_list = $this->__getCustomPages();
		foreach($page_list as $page_item){
			if(isset($pages[$page_item['ukey']])&&$pages[$page_item['ukey']]){
				$settings['pages'][] = $page_item['ukey'];
			}
		}
		$auxpages = $this->getData('auxpage');
		$auxpage_list = $this->__getAuxPages();
		foreach($auxpage_list as $auxpage_item){
			if(isset($auxpages[$auxpage_item['aux_page_slug']])&&$auxpages[$auxpage_item['aux_page_slug']]){
				$settings['auxpages'][] = $auxpage_item['aux_page_slug'];
			}
		}


	}

	function delete()
	{
		$res = false;
		$sitemap = $this->getData('sitemap');
		if(preg_match('/[a-zA-Z_]+/',$sitemap)){
			$path = DIR_SITEMAP.DIRECTORY_SEPARATOR.$sitemap.'.xml';
			if(file_exists($path)){
				$res = unlink($path);
			}
		}
		if($res){
			$this->__makeIndex();
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'sitemap=', 'sitemap_deleted_success');
		}else{
			Message::raiseMessageRedirectSQ(MSG_ERROR, 'sitemap=', 'sitemap_delete_failed');
		}
	}

	function main()
	{
		if(!file_exists(DIR_SITEMAP)){
			mkdir(DIR_SITEMAP);
		}
		foreach($this->sitemap_names as $sitemap_id=>$sitemap_name){
			$path = DIR_SITEMAP.DIRECTORY_SEPARATOR.$sitemap_id.'.xml';
			if(file_exists($path)){
				$this->sitemaps[$sitemap_id] = array(
				'name'=>$sitemap_name,
				'file_size'=>filesize($path),
				'file_time'=>Time::standartTime(filemtime($path)),
				);
			}else{
				$this->sitemaps[$sitemap_id] = array(
				'name'=>$sitemap_name,
				'file_size'=>0,
				'file_time'=>0,
				);
			}
		}

		/*@var $smarty Smarty */
		$smarty = &Core::getSmarty();
		/*@var $smarty Smarty */

		$smarty->assign('sitemaps',$this->sitemaps);
		$smarty->assign('pages',$this->__getCustomPages());
	}

	private function __makeIndex()
	{
		$count = 0;
		if(!isset($_POST['base_url'])){
			$_POST['base_url'] = CONF_FULL_SHOP_URL;
		}else{
			if(!preg_match('|/$|',$_POST['base_url'])){
				$_POST['base_url'] .= '/';
			}
		}
		$index = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
	http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd">';
		foreach($this->sitemap_names as $sitemap_id=>$description){
			$path = DIR_SITEMAP.DIRECTORY_SEPARATOR.$sitemap_id.'.xml';
			if(file_exists($path)&&$sitemap_id!='index'){
				$count++;
				$index .= '
	<sitemap>
		<loc>'.$_POST['base_url'].'sitemap.php?app=SC&amp;section='.$sitemap_id.'</loc>
		<lastmod>'.date("c",filemtime($path)).'</lastmod>
	</sitemap>';
			}
		}
		$index .= '</sitemapindex>';
		$index_path = DIR_SITEMAP.DIRECTORY_SEPARATOR.'index.xml';
		if($count&&($fp = fopen($index_path,'w'))){
			fwrite($fp,$index);
			fclose($fp);
		}else{
			if(file_exists($index_path)){
				unlink($index_path);
			}
		}
	}

	private function __sitemap($name)
	{
		global $___base_path;
		if(SystemSettings::is_hosted()){
			$___base_path = WBS_INSTALL_PATH.'SC/html/scripts/';
			if(preg_match('@^/webasyst/@i',$_SERVER['REQUEST_URI'])){
				$___base_path = '/webasyst'.$___base_path;
			}
			//$___base_path = '/SC/html/scripts/';
		}else{
			$___base_path = WBS_INSTALL_PATH.'published/SC/html/scripts/';
			//$___base_path = '/published/SC/html/scripts/';
		}
		$exportData = new ExportData();
		/*@var $exportData ExportData*/
		$exportData->sqlOrderClause = '';
		$exportData->sqlWhereClause = '';
			
		$exportData->charset = 'utf-8';
		$exportData->format='xml';
		$exportData->format_params = array(
		//'header'=>'<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">',
		'header'=>'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
			    http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">',

		'footer'=>'</urlset>',
		'row_name'=>'url',
		);
		$exportData->setHeaders(array('loc'));

		switch($name){
			case 'products':
				$exportData->setHeaders(array('loc','lastmod','priority'));
				if(!isset($_POST['base_url'])){
					$_POST['base_url'] = CONF_FULL_SHOP_URL;
				}else{
					if(!preg_match('|/$|',$_POST['base_url'])){
						$_POST['base_url'] .= '/';
					}
				}
				$exportData->sqlWhereClause = 'pr.categoryID>1 AND pr.Price>0 AND pr.enabled=1 GROUP BY pr.productID';
				$exportData->sqlQuery = 'SELECT pr.productID as url, pr.slug as slug, UNIX_TIMESTAMP(pr.date_modified) as date_modified, UNIX_TIMESTAMP(MAX(disc.add_time)) as date_discuss FROM ?#PRODUCTS_TABLE AS pr LEFT JOIN ?#DISCUSSIONS_TABLE as disc ON (pr.productID = disc.productID)';
				//?ukey=product&productID=`$element.data.id`&product_slug=`$element.data.slug
				$exportData->setRowHandler('
				global $___base_path;
				$explain = true;
				$rows = array();
				$rows[]=array(\'url\' => $_POST[\'base_url\'].str_replace($___base_path,\'\',
					set_query("?ukey=product&productID={$row[\'url\']}&product_slug={$row[\'slug\']}".(MOD_REWRITE_SUPPORT?"&furl_enable=1":""))),
								\'lastmod\'=>date("c",$row[\'date_modified\']),
								\'priority\'=>0.8);
				if($row[\'date_discuss\']){
				$rows[]=array(\'url\' => $_POST[\'base_url\'].str_replace($___base_path,\'\',
					set_query("?ukey=discuss_product&productID={$row[\'url\']}&product_slug={$row[\'slug\']}".(MOD_REWRITE_SUPPORT?"&furl_enable=1":""))),
								\'lastmod\'=>date("c",$row[\'date_discuss\']),
								\'priority\'=>0.5);
				}
				return $rows;');
				break;
			case 'news':
				$exportData->setHeaders(array('loc','lastmod','priority'));
				$exportData->sqlQuery = 'SELECT NID as url, add_stamp FROM ?#NEWS_TABLE';
				$exportData->setRowHandler('
				global $___base_path;
				$row[\'priority\'] = 0.5;
				$row[\'add_stamp\']	= date("c",$row[\'add_stamp\']);
				$row[\'url\'] = $_POST[\'base_url\'].str_replace($___base_path,\'\',
					set_query("?ukey=news&blog_id={$row[\'url\']}"
					.(MOD_REWRITE_SUPPORT?"&furl_enable=1":"")));
				return $row;');
				break;
			case 'categories':
				$exportData->setHeaders(array('loc','priority','changefreq'));
				$exportData->sqlWhereClause = 't2.categoryID>1';
				$exportData->sqlGroupClause = 't2.categoryID';
				$exportData->sqlQuery = 'SELECT t1.categoryID as url, COUNT(t1.categoryID) as cat_count, t1.slug as slug FROM ?#CATEGORIES_TABLE t1 JOIN ?#PRODUCTS_TABLE t2 ON(t1.categoryID = t2.categoryID)';
				$exportData->setRowHandler('
				global $___base_path;
				$explain = true;
				$rows = array();
				$rows[] = array(\'url\' => $_POST[\'base_url\'].str_replace($___base_path,\'\',set_query("?categoryID={$row[\'url\']}&category_slug={$row[\'slug\']}".(MOD_REWRITE_SUPPORT?"&furl_enable=1":""))),
								\'priority\'=>0.8,
								\'changefreq\'=>\'daily\');
				if($row[\'cat_count\']>10){
					$offset = 10;
					while($offset<$row[\'cat_count\']){
						$rows[] = array(\'url\' => $_POST[\'base_url\'].str_replace($___base_path,\'\',set_query("?categoryID={$row[\'url\']}&category_slug={$row[\'slug\']}&offset={$offset}".(MOD_REWRITE_SUPPORT?"&furl_enable=1":""))),
										\'priority\'=>0.8,
										\'changefreq\'=>\'daily\');
						$offset += 10;												
					}
				}
				return $rows;');
				//$exportData->setRowHandler('$row[\'url\'] = $_POST[\'base_url\'].str_replace(\'/published/SC/html/scripts/\',\'\',set_query("?categoryID={$row[\'url\']}&category_slug={$row[\'slug\']}".(MOD_REWRITE_SUPPORT?"&furl_enable=1":"")));unset($row[\'slug\']);unset($row[\'cat_count\']);return $row;');
				break;
			case 'auxpages':
				$exportData->setHeaders(array('loc','changefreq','priority'));
				$exportData->sqlWhereClause = 'aux_page_enabled=1';
				$exportData->sqlQuery = 'SELECT `aux_page_slug` as \'url\', `aux_page_ID` as \'url2\', \'daily\' as \'changefreq\', 0.7 as \'priority\' FROM ?#AUX_PAGES_TABLE';
				$exportData->setRowHandler('global $___base_path;if(!$row[\'url\'])$row[\'url\'] = $row[\'url2\'];unset($row[\'url2\']);$row[\'url\'] = $_POST[\'base_url\'].str_replace($___base_path,\'\',set_query("?ukey=auxpage_{$row[\'url\']}".(MOD_REWRITE_SUPPORT?"&furl_enable=1":"")));return $row;');
				break;
			case 'pages':
				$exportData->setHeaders(array('loc','priority'));
				$exportData->sqlWhereClause = 'xParentID=1 AND xEnabled=1';
				$exportData->sqlQuery = 'SELECT xUnicKey as \'url\' FROM ?#DIVISIONS_TBL';
				$exportData->setRowHandler('
				global $___base_path;
				if(isset($_POST[\'page\'])&&isset($_POST[\'page\'][$row[\'url\']])&&$_POST[\'page\'][$row[\'url\']]){
				switch($row[\'url\']){
					case \'home\':$row[\'priority\'] = \'1.0\';break;
					case \'news\':$row[\'priority\'] = 0.5;break;
					case \'pricelist\':$row[\'priority\'] = 0.5;break;
					default:$row[\'priority\'] = 0.2;break;
				}
				$row[\'url\'] = $_POST[\'base_url\'].str_replace($___base_path,\'\',
					set_query("?ukey={$row[\'url\']}".(MOD_REWRITE_SUPPORT?"&furl_enable=1":"")));
					
				}else{
					unset($row);
				}
				return $row;');
				break;


			default:
				return false;
		}
		return $exportData->exportDataToFile(DIR_SITEMAP.'/'.$name.'.xml');
			
	}

	private function __getCustomPages()
	{
		$pages = array();
		$SubDivs = &DivisionModule::getBranchDivisions(DivisionModule::getDivisionIDByUnicKey('TitlePage'), array('xEnabled'=>1));
		foreach ($SubDivs as $_SubDiv){
			if($_SubDiv->UnicKey == 'order_status' && isset($_SESSION["log"])){
				continue;
			}
			if($_SubDiv->UnicKey == 'auth' && isset($_SESSION["log"])){
				$login_id = $_SubDiv->ID;
				$_SubDiv = DivisionModule::getDivisionByUnicKey('logout');
				$_SubDiv->ID = $login_id;
			}
			if($_SubDiv->UnicKey == 'register' && isset($_SESSION["log"])){

				$register_id = $_SubDiv->ID;
				$_SubDiv = DivisionModule::getDivisionByUnicKey('office');
				$_SubDiv->ID = $register_id;
			}
			/* @var $_SubDiv Division*/
			$pages[] = array(
			'id' => $_SubDiv->ID,
			'name' => translate($_SubDiv->Name),
			'ukey' => $_SubDiv->UnicKey,
			);

			if(isset($local_settings['divisions']) && $local_settings['divisions']=='mobile' && ($_SubDiv->UnicKey=='office'||$_SubDiv->UnicKey=='register')){
				$_SubDiv = DivisionModule::getDivisionByUnicKey('cart');
				$pages[] = array(
				'id' => $_SubDiv->ID,
				'name' => translate(translate($_SubDiv->Name)),
				'ukey' => $_SubDiv->UnicKey,
				);
			}
		}

		return $pages;
	}

	private function __getAuxPages()
	{
		$sql = 'SELECT '.LanguagesManager::sql_prepareField('aux_page_name').' AS name, `aux_page_ID` AS `id`, `aux_page_slug` FROM ?#AUX_PAGES_TABLE WHERE aux_page_enabled=1 ORDER BY `aux_page_priority` ASC';
		$Register = &Register::getInstance();
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/* @var $DBHandler DataBase */

		$DBRes = $DBHandler->ph_query($sql);

		$pages = $DBRes->fetchArrayAssoc();

		return $pages;
	}
}
/*@var $smarty Smarty */
$smarty = &Core::getSmarty();
/*@var $smarty Smarty */
ActionsController::exec('GoogleSitemap');
$smarty->assign('sub_template', $this->getTemplatePath('backend/google_sitemap.html'));
?>