<?php
// *****************************************************************************
// Purpose	gets all discussion
// Inputs   $navigatorParams - item
//					"offset"			- count row from begin to place being shown
//					"CountRowOnPage"	- count row on page to show on page
// Remarks		
// Returns	
//				returns array of discussion
//				$count_row is set to count(discussion)
function discGetAllDiscussion( $callBackParam, &$count_row, $navigatorParams = null )
{
	$data = array();

	$orderClause = "";
	if ( isset($callBackParam["sort"]) )
	{
		$orderClause = " order by ".$callBackParam["sort"];
		if ( isset($callBackParam["direction"]) )
		{
			if ( $callBackParam["direction"] == "ASC" )
				$orderClause .= " ASC ";
			else
				$orderClause .= " DESC ";
		}
	}

	$filter = "";
	if ( isset($callBackParam["productID"]) )
	{
		if ( $callBackParam["productID"] != 0 )
			$filter = " AND ".PRODUCTS_TABLE.".productID=".$callBackParam["productID"];
	}

	$q = db_query("select DID, Author, Body, add_time, Topic, ".LanguagesManager::sql_prepareField('name')." AS product_name from ".
		DISCUSSIONS_TABLE.", ".PRODUCTS_TABLE.
		" where ".DISCUSSIONS_TABLE.".productID=".PRODUCTS_TABLE.".productID ".$filter." ".
		$orderClause );

 	if ( $navigatorParams != null )
	{
		$offset			= $navigatorParams["offset"];
		$CountRowOnPage	= $navigatorParams["CountRowOnPage"];
	}
	else
	{
		$offset = 0;
		$CountRowOnPage = 0;
	}
	$i=0;
	while( $row = db_fetch_row($q) )
	{
		if ( ($i >= $offset && $i < $offset + $CountRowOnPage) || 
				$navigatorParams == null  )
		{
			$row['add_time']	= Time::standartTime( $row['add_time'] );
			$data[] = $row;
		}
		$i ++;
	}
	$count_row = $i;
	return $data;
}

function discGetAllDiscussedProducts()
{
	$q = db_query(
		"select ".LanguagesManager::sql_prepareField('name')." AS product_name, ".PRODUCTS_TABLE.".productID AS productID from ".
			DISCUSSIONS_TABLE.", ".PRODUCTS_TABLE.
			" where ".DISCUSSIONS_TABLE.".productID=".PRODUCTS_TABLE.".productID ".
			" group by ".PRODUCTS_TABLE.".productID, product_name order by product_name" );
	$data = array();
	while( $row = db_fetch_row($q) )
		$data[] = $row;
	return $data;
}

function discGetDiscussion( $DID )
{
	$q = db_query("select DID, Author, Body, add_time, Topic, ".LanguagesManager::sql_prepareField('name')." AS product_name, ".
		" ".PRODUCTS_TABLE.".productID AS productID from ".
		DISCUSSIONS_TABLE.", ".PRODUCTS_TABLE.
		" where ".DISCUSSIONS_TABLE.".productID=".PRODUCTS_TABLE.".productID AND DID=$DID" );
	$row = db_fetch_row( $q );
	$row["add_time"] = Time::standartTime( $row["add_time"] );
 	return $row;
}


function discAddDiscussion( $productID, $Author, $Topic, $Body){
	
	$sql = '
		INSERT ?#DISCUSSIONS_TABLE (productID, Author, Body, add_time, Topic) VALUES(?,?,?,?,?)
	';
	db_phquery($sql,$productID,$Author,$Body,Time::dateTime(),$Topic);
	discUpdateRSSFeed($productID);
}

function discDeleteDiscusion( $DID ){
	
	db_phquery('DELETE FROM ?#DISCUSSIONS_TABLE WHERE DID=?',$DID);
	discUpdateRSSFeed();
}

function discGetLastDiscussions($productID, $n){
	
	$reviews = array();
	$productID = (int)$productID;
	$dbres = db_phquery('SELECT * FROM ?#DISCUSSIONS_TABLE WHERE productID=? ORDER BY DID DESC LIMIT 0, '.intval($n), $productID);
	while($row = db_fetch_assoc($dbres)){
		
		$row["add_time_str"] = Time::standartTime( $row["add_time"] );
		$reviews[] = $row;
	}
	/*
	if(count($reviews)&&!file_exists(DIR_RSS."/{$productID}.xml")){
		discUpdateRSSFeed($productID);		
	}*/
	return $reviews;
}

function discUpdateRSSFeed($productID = 0){
	
	if(file_exists(DIR_RSS."/all-reviews.xml")){
		unlink(DIR_RSS."/all-reviews.xml");
	}
	if($productID>0){
		$RSSfilePath = sprintf(DIR_RSS.'/%d/%d.xml',$productID/10000,$productID%10000);
		if(file_exists($RSSfilePath)){
			unlink($RSSfilePath);
		}
	}
	
	return;
	
	/*$Register = &Register::getInstance();*/
	/*@var $Register Register*/
/*
	$urlEntry = &$Register->get(VAR_URL);
	if(!is_object($urlEntry)){
		ClassManager::includeClass('URL');
		$urlEntry = new URL;
		$urlEntry->loadFromServerInfo();
	}
	$urlEntry->setQuery();*/

	ClassManager::includeClass('URL');
	$urlEntry = new URL;
	
	ClassManager::includeClass('RSSFeedGenerator');
	$RSSFeed = new RSSFeedGenerator();

	if($productID>0){
		$product = new Product();
		$product->loadByID($productID);
		
		$Register = &Register::getInstance();
		/*@var $Register Register*/
	
		$urlEntry->loadFromServerInfo();
		$urlEntry->Query="";
		$link = '';
		$get = array("productID"=>$productID,'product_slug'=>$product->slug,"furl_enable"=>true,'ukey'=>'discuss_product');
		fURL::convertGetToPath($link,$get);
		$urlEntry->setPath($link);
		$urlEntry->constructUri();
		
		$RSSFeed->setChannel(strip_tags(str_replace('%PRODUCT_NAME%',$product->name,translate('prddiscussion_title'))).' ― '.CONF_SHOP_NAME,$urlEntry->getURI(),'test');
		$RSSFeed->itemElements = array('title','description','dc:creator'=>'author','pubDate','link','guid');
		$RSSFeed->additionalElementSource = array('xmlns:dc'=>'http://purl.org/dc/elements/1.1/');
		$RSSFeed->SQL = 'SELECT Topic as title,\''.$urlEntry->getURI().'\' as link, Body as description, Author as author, UNIX_TIMESTAMP(add_time) as pubDate, CONCAT(\''.$urlEntry->getURI().'\',\'#\',DID) as guid FROM '.DISCUSSIONS_TABLE.' WHERE productID='.$productID.' ORDER BY DID DESC';
		$RSSFeed->limit = 10;
		$RSSfilePath = sprintf(DIR_RSS.'/%d/%d.xml',$productID/10000,$productID%10000);
		$RSSFeed->generateFeed($RSSfilePath);
	}
	
	//http://merge.dev.webasyst.net/SC/html/scripts/frame.php?did=20
	$urlEntry->loadFromServerInfo();
	$urlEntry->Query = '';
	$urlEntry->setQuery('?did=20');
	$urlEntry->setPath('/SC/html/scripts/frame.php');
	
	$RSSFeed->setChannel(translate('pgn_product_reviews').' ― '.CONF_SHOP_NAME,$urlEntry->getURI(),'test');	
	$RSSFeed->itemElements = array('title','description','dc:creator'=>'author','pubDate','guid isPermaLink="false"'=>'guid');	
	$RSSFeed->SQL = 'SELECT Topic as title, Body as description, Author as author, UNIX_TIMESTAMP(add_time) as pubDate, CONCAT(productID,\'#\',DID) as guid FROM '.DISCUSSIONS_TABLE.' ORDER BY DID DESC';
	$RSSFeed->limit = 20;
	$RSSFeed->generateFeed(DIR_RSS."/all-reviews.xml");
	
	
	
}
?>