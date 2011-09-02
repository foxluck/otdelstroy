<?php
class News extends ComponentModule {
	
	var $DB_TABLE = '';
	var $NewsPerPage = 10;
	var $NewsInShortList = 5;
	var $CurrentPage = 1;
	var $TotalPages = 1;

	function __construct( $_ConfigID = 0 )
	{
		$this->ModuleType = 0;
		$this->SingleInstall = true;
		$this->ModuleVersion = 1.0;

		if(defined('NEWS_TABLE')){
			
			$this->DB_TABLE = NEWS_TABLE;
		}else{
			
			$this->DB_TABLE = 'SS_news_table';
		}
		
		$this->TotalPages = ceil($this->getNewsNumber()/$this->NewsPerPage);
		if(isset($_GET['news_page']) && intval($_GET['news_page'])>0 && $_GET['news_page']<=$this->TotalPages)
			$this->CurrentPage = (int)$_GET['news_page'];
		
		//Module::Module($_ModuleConfigID);
		parent::__construct($_ConfigID = 0);
	}
	
	function initSettings(){
		
		$this->Settings = array(
			'news_per_page' => array(
				'name' => 'news_per_page',
				'value' => 10,
				'type' => SETTING_NUMBER,
				'title' => 'Количество новостей на странице в магазине',
				'description' => '',
				),
			'news_in_shortlist' => array(
				'name' => 'news_in_shortlist',
				'value' => 5,
				'type' => SETTING_NUMBER,
				'title' => 'Количество новостей в коротком списке',
				'description' => '',
				),
		);
		parent::initSettings();
		$this->NewsPerPage = $this->getSettingValue('news_per_page');
		$this->NewsInShortList = $this->getSettingValue('news_in_shortlist');
	}
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'short news list' => array(
				'name' => 'Short list of news',
				'method' => 'methodShortList',
				),
			'news list frontend' => array(
				'name' => 'News list frontend',
				'method' => 'methodFList',
				),
			'add_news' => array(
				'name' => 'Добавить новость',
				),
			'bnews' => array(
				'name' => 'Управление новостями',
				'method' => 'methodBNews',
				),
			'b_subscribers' => array(
				'name' => 'Управление подписчиками',
				'type' => INTDIVAVAILABLE,
				),
		);
		
		$this->__registerComponent('news_short_list', 'cpt_lbl_news_short_list', array(TPLID_GENERAL_LAYOUT, TPLID_HOMEPAGE), 'methodShortList', array('news_num' => array('type' => 'text', 'params' => array('name' => 'news_num', 'title'=> 'News number', 'value'=>$this->NewsInShortList))));
		parent::initInterfaces();
	}
	
	function methodBNews(){
		$this->generatePage('admin news list');
	}
	
	function methodFList(){
		
		$this->generatePage('frontend news list');
	}
	
	function methodShortList($call_settings = null){

		$local_settings = isset($call_settings['local_settings'])?$call_settings['local_settings']:array();

		if(isset($local_settings['news_num'])&&$local_settings['news_num']){
			$this->NewsInShortList = $local_settings['news_num'];
		}
		
		/* @var $smarty Smarty */
		$smarty = &Core::getSmarty();
		
		if ( isset($_POST['subscribe']) ){
			
			$error = subscrVerifyEmailAddress($_POST['email']);
			if ( $error == '' ){
				$smarty->assign( 'subscribe', 1 );
				subscrAddUnRegisteredCustomerEmail( $_POST['email'] );
				RedirectSQ('email_subscribed='.urldecode($_POST['email']));
			}else{
				$smarty->assign( 'error_message', $error );
			}
		}
	
		if ( isset($_GET['email_subscribed']) ){
			$smarty->assign('subscribe',1);
			$smarty->hassign( 'email_to_subscribe', urldecode($_GET['email_subscribed']));
			set_query('email_subscribed','',true);
		}
		else{
			$smarty->assign( 'email_to_subscribe', 'Email' );
		}
		$this->generatePage('frontend news short list');
	}

	function generatePage($_PageName){
		
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/* @var $smarty Smarty */
		
		$smarty->assign( "current_date", Time::standartTime());
		
		switch($_PageName){
			
			case 'frontend news short list':
				$smarty->assign('news_array', xHtmlSpecialChars($this->getNews(null, 1, $this->NewsInShortList), array(), 'title') );

				$smarty->assign('news_rss_link','rssfeed.php?type=blog&amp;lang='.LanguagesManager::getCurrentLanguage()->iso2);
				print $smarty->fetch('news.frontend.shortlist.tpl.html');
				break;
				
			case 'frontend news list':
				
				$xRequestURI = set_query('&msg=');
				$smarty->assign('xRequestURI', $xRequestURI);
								
				$GV = $Register->get(VAR_GET);
				if(!array_key_exists('blog_id', $GV) or !$this->isNIDCorrect($GV['blog_id']))
				{
    				$lister = getListerRange($this->CurrentPage, $this->TotalPages);
    				$smarty->assign('ListerRange', range($lister['start'], $lister['end']));
    				$smarty->assign('CurrentPage', $this->CurrentPage);
    				$smarty->assign('TotalPages', $this->TotalPages);
    				$smarty->assign('LastPage', $this->TotalPages);
    				$smarty->assign('news_posts', xHtmlSpecialChars($this->getNews(), array(), 'title') );
    				$smarty->assign( 'main_content_template', 'news.frontend.list.tpl.html' );
				}
				else
				{
				    $posts = array($this->getNews($GV['blog_id']));
				    $smarty->assign('page_title', $posts[0]['title'].' ― '.CONF_SHOP_NAME);
				    $smarty->assign('news_posts',  xHtmlSpecialChars($posts, array(), 'title'));
				    $smarty->assign('main_content_template', 'news.frontend.post.tpl.html');
				};

				$smarty->assign('rss_link','rssfeed.php?type=blog&amp;lang='.LanguagesManager::getCurrentLanguage()->iso2);
				break;
			/**
			 * Should be called only from backoffice
			 */
			case 'admin news list':
				
				$rMsg = array();
			
				$usePOST = $this->ActionsHandler($rMsg);
				
				$msg = isset($_GET['msg'])?$_GET['msg']:'';
				switch ($msg){
					case 'delete_ok':
						$rMsg = array(
							'type' => 'ok',
							'text' => translate("blog_msg_post_deleted"),
						);
						break;
					case 'edit_ok':
						$rMsg = array(
							'type' => 'ok',
							'text' => translate("msg_saved_changes"),
						);
						break;
					case 'add_ok':
						$rMsg = array(
							'type' => 'ok',
							'text' => translate("blog_msg_post_added"),
						);
						break;
					case 'pctdelete_ok':
						$rMsg = array(
							'type' => 'ok',
							'text' => translate("blog_msg_picture_deleted"),
						);
						break;
					default:
						$msg = '';
				}
				
				$xRequestURI = set_query('&msg=&safemode=');
				$smarty->assign('xRequestURI', $xRequestURI);
				$smarty->assign('Message', $rMsg);
				
				if(isset($_GET['news_number'])){
					
					$News = $this->getNews($_GET['news_number']);
				}
				if(!isset($News)){
					
					$lister = getListerRange($this->CurrentPage, $this->TotalPages);
					$smarty->assign('ListerRange', range($lister['start'], $lister['end']));
					$smarty->assign('CurrentPage', $this->CurrentPage);
					$smarty->assign('TotalPages', $this->TotalPages);
					$smarty->assign('LastPage', $this->TotalPages);
					$smarty->assign( "news_posts", xHtmlSpecialChars($this->getNews(), array(), 'title') );
					if($usePOST)$smarty->hassign('NewsInfo', $_POST['DATA']);
				}else {
					
					$smarty->assign('NewsEdit',1);
					$_t = $usePOST?$_POST['DATA']:$News;
					$_e = $_t['textToMail'];
					$_t = xHtmlSpecialChars($_t);
					$_t['textToMail'] = $_e;
					$smarty->assign('NewsInfo', $_t);
				}
				$smarty->assign( "admin_sub_dpt", 'news.admin_list.tpl.html' );
				break;
		}
	}

	/**
	 * Handler for actions. Work with external data without checking admin mode. 
	 * Should be called only from backoffice
	 */
	function ActionsHandler(&$_rMsg){
		
		$ACTION = isset($_POST['fACTION'])?$_POST['fACTION']:'';
		
		if(isset($_GET['dlt_picture'])){
			
			$ACTION = 'DELETE_PICTURE_NEWS';
		}
		if (isset($_GET["delete"])){
			
			$ACTION = 'DELETE_NEWS';
		}
		
		if ($ACTION && CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
		{
			RedirectSQ("&safemode=yes&delete=&dlt_picture=");
		}

		switch ($ACTION){
			
			case 'DELETE_NEWS':
			
				$this->deleteNews($_GET["delete"]);
				Redirect(set_query('&delete=&msg=delete_ok'));
				break;
			case 'DELETE_PICTURE_NEWS':
			
				$News = $this->getNews($_GET['news_number']);
				if(!isset($News))break;
				Functions::exec('file_remove', array(DIR_PRODUCTS_PICTURES."/".$News['picture']));
				$this->saveNews(array('NID'=>$News['NID'], 'picture'=>''));
				Redirect(set_query('dlt_picture=&msg=pctdelete_ok'));
				break;
			case 'SAVE_NEWS':
				
				if(isset($_POST['DATA'])){
					
					$picture = "";
					if ( $_FILES["picture"]["size"]!=0 && is_image($_FILES["picture"]["name"])){
						
						$res = Functions::exec('file_move_uploaded', array($_FILES["picture"]["tmp_name"], 	DIR_PRODUCTS_PICTURES."/".$_FILES["picture"]["name"] ));
						if(PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());

						$picture = $_FILES["picture"]["name"];
						SetRightsToUploadedFile( DIR_PRODUCTS_PICTURES."/".$picture );
					}
		
					if ( !file_exists(DIR_PRODUCTS_PICTURES."/".$picture) )
						$picture = "";
						
					$_POST['DATA']['picture'] = $picture?$picture:'';
					if(!$_POST['DATA']['picture'])unset($_POST['DATA']['picture']);
					$def_language = LanguagesManager::getDefaultLanguage();
					if(!$_POST['DATA']['title_'.$def_language->iso2] && !$_POST['DATA']['textToPublication_'.$def_language->iso2]){
						$_POST['DATA']['picture'] = '';
						$_rMsg = array(
							'type' => 'error',
							'text' => translate("blog_err_empty_titletext"),
						);
						return true;
					}
					$this->saveNews($_POST['DATA']);
				}
				Redirect(set_query('?ukey=manage_news&msg=add_ok&news_number='));
				break;
			case 'ADD_NEWS':
		
				$def_language = LanguagesManager::getDefaultLanguage();	
				if(!$_POST['DATA']['title_'.$def_language->iso2] && !$_POST['DATA']['textToPublication_'.$def_language->iso2]){
					
					$_rMsg = array(
						'type' => 'error',
						'text' => translate("blog_err_empty_titletext"),
					);
					return true;
				}
				if(isset($_POST['DATA']['emailed'])&&!$_POST['DATA']['textToMail']){
						
					$_rMsg = array(
						'type' => 'error',
						'text' => translate("blog_err_empty_texttoemail"),
					);
					return true;
				}
				
				$stamp = microtime();
				$stamp = explode(" ", $stamp);
				$_POST['DATA']['add_stamp'] = $stamp[1];
				
				if ( !isset($_POST['DATA']['emailed']) )$_POST['DATA']['textToMail']='';

				
				$NID = $this->addNews( $_POST['DATA'] );
				if ( isset($_POST['DATA']['emailed']) ) {
					$this->sendNews($NID);
				}
		
				Redirect(set_query('?ukey=manage_news&msg=add_ok'));
		}
		return false;
	}
	
	function getNewsNumber(){
		
		$sql = '
			SELECT COUNT(*) FROM '.$this->DB_TABLE.'
		';

		@list($Number) = db_fetch_row(db_query($sql));
		return $Number;
	}
	
	function getNews($_ID = null, $CurrentPage = null, $NewsPerPage = null){
		
		if(!isset($_ID)){
			
			$News = array();
			$NewsPerPage = isset($NewsPerPage)?$NewsPerPage:$this->NewsPerPage;
			if($NewsPerPage<=0){
				$NewsPerPage = 10;
			}
			
			$CurrentPage = isset($CurrentPage)?$CurrentPage:$this->CurrentPage;
			
			$sql = '
				SELECT *,'.LanguagesManager::sql_prepareField('title',true).', '.LanguagesManager::sql_prepareField('textToPublication',true).' FROM '.$this->DB_TABLE.'
				ORDER BY priority DESC, add_stamp DESC
				LIMIT '.(($CurrentPage-1)*$NewsPerPage).', '.$NewsPerPage.'
			';
			$Result = db_query($sql);
			while($_Row = db_fetch_row($Result)){
				
				$_Row['picture_exists'] = $_Row['picture']?file_exists(DIR_PRODUCTS_PICTURES."/".$_Row['picture']):false;
				$News[] = $_Row;
			}
		}else {
			
			$sql = 'SELECT *,'.LanguagesManager::sql_prepareField('title',true).', '.LanguagesManager::sql_prepareField('textToPublication',true).' FROM '.$this->DB_TABLE.' WHERE NID='.intval($_ID);
			$News = db_fetch_row(db_query($sql));
			if(!(isset($News['NID']) && (int)$News['NID'])){
				$News = null;
			}
			$News['picture_exists'] = $News['picture']?file_exists(DIR_PRODUCTS_PICTURES."/".$News['picture']):false;
		}
		return $News;
	}
	
	function isNIDCorrect(&$nid)
	{
		$nid = intval($nid);
	    $sql = 'select count(*) as cnt from '.$this->DB_TABLE.' where NID = '.$nid;
	    $r = db_fetch_row(db_query($sql));
	    if($r['cnt'] == 1){
	    	return true;
	    }else{
	    	unset($nid);
	    	return false;
	    }
	}
	
	function saveNews($_Info){
		$FieldsSQL = array();
		foreach ($_Info as $_Key=>$_Val){
			
			$FieldsSQL[] = '`'.xEscapeSQLstring($_Key).'`="'.xEscapeSQLstring($_Val).'"';
		}
		if(!count($FieldsSQL))return false;
		
		$FieldsSQL = implode(', ', $FieldsSQL);
		
		$sql = '
			UPDATE '.$this->DB_TABLE.'
			SET '.$FieldsSQL.'
			WHERE NID='.intval($_Info['NID']);
		db_query($sql);
		$this->updateRSSFeed();
		return true;
	}
	
	function addNews($_Info){
		
		if(!count($_Info))return false;
		
		$sql = '
			INSERT INTO '.$this->DB_TABLE.'
			(`'.implode('`, `', xEscapeSQLstring(array_keys($_Info))).'`)
			VALUES("'.implode('", "', xEscapeSQLstring($_Info)).'")
		';
		db_query($sql);
		
		$this->updateRSSFeed();
		
		return db_insert_id();		
	}

	function sendNews($_ID){
		
		$sql = '
			SELECT '.LanguagesManager::ml_getLangFieldName('title').' AS title, textToMail FROM '.$this->DB_TABLE.' WHERE NID="'.xEscapeSQLstring($_ID).'"
		';
		$News = db_fetch_assoc(db_query($sql));
		if(!$News['title']){
			$News['title'] = translate("email_news_of")." ".CONF_SHOP_NAME;
		}
		$News["textToMail"] .= "<br /><br />".translate("email_bestregards").", ".CONF_SHOP_NAME."<br />".CONF_SHOP_URL;
	
		$callBackParam = '';
		$count_row = '';
		$Subscribers = subscrGetAllSubscriber( $callBackParam, $count_row);
		foreach ($Subscribers as $subscriber){
			if (preg_match("/^[a-z0-9_-]+(\.[a-z0-9_-]+)*@([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i", $subscriber["Email"])){
				$res = ss_mail($subscriber["Email"],
						$News['title'],
						$News["textToMail"],
						true);
				/*$mailer = new SSMailer();
				$mailer->AddAddress($subscriber["Email"]);
				$mailer->CharSet = translate("str_default_charset");
				$mailer->Subject = $News['title']?$News['title']:translate("email_news_of")." ".CONF_SHOP_NAME;
				$mailer->FromName = CONF_SHOP_NAME;
				$mailer->From = CONF_GENERAL_EMAIL;
				$mailer->Sender = CONF_GENERAL_EMAIL;
				$mailer->IsHTML(true);
				$mailer->Body = $News["textToMail"]."<br /><br />".translate("email_bestregards").", ".CONF_SHOP_NAME."<br />".CONF_SHOP_URL;
				$mailer->AltBody = strip_tags(preg_replace('@<br[^>]*>@msi', "\n", $mailer->Body));
				$mailer->Send();*/
			}
		}
	}
	
	function deleteNews($_ID){
		
		$sql = '
			DELETE FROM '.$this->DB_TABLE.'
			WHERE NID='.intval($_ID);
		db_query($sql);
		$this->updateRSSFeed();
		return true;
	}
	
	function updateRSSFeed(){
		if(file_exists(DIR_RSS."/blog-news.xml")){
			unlink(DIR_RSS."/blog-news.xml");
		}	
	
		$languages = LanguagesManager::getInstance()->getLanguages();
		foreach($languages as $language){
			$path = DIR_RSS."/blog-news-{$language->iso2}.xml";
			if(file_exists($path)){
				unlink($path);
			}
		}		
		return;
			
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		ClassManager::includeClass('URL');
		$urlEntry = new URL;
		$urlEntry->loadFromServerInfo();
		$urlEntry->Query="";
		$link = '';
		$get = array("ukey"=>"news","furl_enable"=>true);
		fURL::convertGetToPath($link,$get);
		$urlEntry->setPath($link);
		$urlEntry->constructUri();
			
		ClassManager::includeClass('RSSFeedGenerator');
		$RSSFeed = new RSSFeedGenerator();
		$RSSFeed->setChannel(translate('pgn_news').' ― '.CONF_SHOP_NAME,$urlEntry->getURI(),translate('pgn_news'));
		$RSSFeed->itemElements = array('title','description','content:encoded'=>'description','dc:creator'=>'author','pubDate','link','guid');
		$RSSFeed->additionalElementSource = array('xmlns:dc'=>'http://purl.org/dc/elements/1.1/',
													'xmlns:content'=>'http://purl.org/rss/1.0/modules/content/');
		//$RSSFeed->SQL = 'SELECT Topic as title,\''.$urlEntry->getURI().'\' as link, Body as description, Author as author, UNIX_TIMESTAMP(add_time) as pubDate, CONCAT(\''.$urlEntry->getURI().'\',\'#\',DID) as guid FROM '.DISCUSSIONS_TABLE.' WHERE productID='.$productID.' ORDER BY DID DESC';
		$RSSFeed->SQL = 'SELECT NID, add_stamp as pubDate, title, textToPublication as description FROM '.$this->DB_TABLE.' ORDER BY priority DESC, add_stamp DESC';
		//?ukey=news&blog_id 
		/*$urlEntry = new URL;
				$urlEntry->loadFromServerInfo();
				$urlEntry->setQuery(\'?ukey=news&blog_id=\'.$row[\'NID\']);
				$item[\'link\'] = $urlEntry->getURI();*/
		$RSSFeed->setItemHandler('
				static $urlEntry;
				if(!$urlEntry){
					$urlEntry = new URL;
					$urlEntry->loadFromServerInfo();
				}
				$urlEntry->Query="";
				$link = $urlEntry->getURI();
				$get = array("ukey"=>"news","blog_id"=>$item[\'NID\'],"furl_enable"=>true);
				fURL::convertGetToPath($item[\'link\'],$get);
				$urlEntry->setPath($item[\'link\']);
				$urlEntry->constructUri();
				$item[\'link\'] = $urlEntry->getURI();
				$item[\'guid\'] = $item[\'link\'];
				return $item;
				 
								');
		$RSSFeed->limit = 20;
		$RSSFeed->generateFeed(DIR_RSS."/blog-news.xml");
	}
}
?>