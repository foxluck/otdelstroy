<?php
$Register = &Register::getInstance();
/*@var $Register Register*/

$smarty = &$Register->get(VAR_SMARTY);
/*@var $smarty Smarty*/

$divisionEntry = &$Register->get(VAR_CURRENTDIVISION);
/*@var $divisionEntry Division*/
$smarty->assign('main_content_template', $divisionEntry->UnicKey.'.html');
?>