<?php
$smarty = &Core::getSmarty();
$this->_initUserInfo();
set_query('show_all=','',true);

$callBackParam = array();
$visits	= array();
$callBackParam['log'] = regGetLoginById( $_GET['userID'] );
$count = 0;
$navigatorHtml = GetNavigatorHtml( set_query('__tt='), 20, 'stGetVisitsByLogin', $callBackParam, 	$visits, $offset, $count );
$smarty->assign( 'navigator', $navigatorHtml );
$smarty->assign( 'visits', $visits );
$smarty->assign('UserInfoFile',$this->getTemplatePath('backend/bvisits_log.tpl.html'));
?>