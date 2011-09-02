<?php
$Register = &Register::getInstance();
$smarty = &$Register->get(VAR_SMARTY);
/* @var $smarty Smarty */
$GetVars = &$Register->get(VAR_GET);
$PostVars = &$Register->get(VAR_POST);

if(isset($PostVars['rows_num']))renderURL('rows_num='.$PostVars['rows_num'], '', true);

$rows_show_all = isset($GetVars['rows_num']) && $GetVars['rows_num'] == 'show_all';
$rows_num = isset($GetVars['rows_num'])?intval($GetVars['rows_num']):0;
if(!$rows_num)$rows_num = 50;

$DBHandler = &$Register->get(VAR_DBHANDLER);
/* @var $DBHandler DataBase */

$DBRes = $DBHandler->ph_query('SELECT COUNT(*) FROM ?#CATEGORIES_TABLE WHERE categoryID!=1');
$TotalRows = $DBRes->fetchRow(0);

$TotalPages = ceil($TotalRows/$rows_num);

$page = isset($GetVars['page'])?intval($GetVars['page']):0;
if(!$page || $page >$TotalPages)$page = 1;

$category_report=GetCategoryViewedTimesReport($rows_show_all?null:$rows_num, ($page-1)*$rows_num);

$smarty->assign('rows_show_all', $rows_show_all);
$smarty->assign('Lister', getLister($page, $TotalPages, 10));
$smarty->assign('categories', $category_report );
$smarty->assign('rows', array(10 => 10, 50=>50, 100=>100));
$smarty->assign('rows_num', $rows_num);
$smarty->assign('admin_sub_dpt', 'reports_category_viewed_times.tpl.html');
?>