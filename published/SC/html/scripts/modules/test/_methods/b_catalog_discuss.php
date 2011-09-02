<?php
$smarty = &Core::getSmarty();
/* @var $smarty Smarty */

if( isset($_GET['answer']) ){
	
	$discussion = discGetDiscussion( $_GET['answer'] );

	if(isset($_POST['add'])){
		
		discAddDiscussion( $discussion['productID'], $_POST['newAuthor'], $_POST['newTopic'], $_POST['newBody'] );
		RedirectSQ('answer=');
	}

	$smarty->assign( 'discussion', $discussion );
	$smarty->assign( 'answer', 1);
}else{

	if ( isset($_GET['delete']) ){
		
		safeMode(true, 'safemode=yes&delete=');
		discDeleteDiscusion( $_GET['delete'] );
		RedirectSQ('delete=');
	}
	
	$gridEntry = new Grid();
	
	$gridEntry->rows_num = 20;
	$gridEntry->show_rows_num_select = false;
	
	$gridEntry->registerHeader('prdreview_postaddtime', 'add_time', true, 'desc');
	$gridEntry->registerHeader('prdset_product_name');
	$gridEntry->registerHeader('str_name');
	$gridEntry->registerHeader('str_subject');
	$gridEntry->registerHeader('prddiscussion_body');
	$gridEntry->registerHeader('btn_delete');
	
	$gridEntry->setRowHandler('
		$row["add_time"] = Time::standartTime($row["add_time"]);
		return $row;
	');
	
	$gridEntry->show_rows_num_select = false;
	
	$gridEntry->query_total_rows_num = 'SELECT COUNT(*) FROM ?#DISCUSSIONS_TABLE';
	$gridEntry->query_select_rows = '
		SELECT t1.*, '.LanguagesManager::sql_prepareField('name').' AS product_name 
		FROM ?#DISCUSSIONS_TABLE t1, ?#PRODUCTS_TABLE t2 
		WHERE t1.productID=t2.productID
		';
	
	$gridEntry->prepare();
}
/*
if($gridEntry->total_rows_num&&!file_exists(DIR_RSS.'/all-reviews.xml')){
	discUpdateRSSFeed();
}

if(file_exists(DIR_RSS.'/all-reviews.xml')){
	$smarty->assign('rss_link',URL_RSS.'/all-reviews.xml');
}
*/
$smarty->assign('rss_link','rssfeed.php?type=product_reviews&amp;id=all');
$smarty->assign('admin_sub_dpt', 'catalog_discuss.tpl.html');
?>