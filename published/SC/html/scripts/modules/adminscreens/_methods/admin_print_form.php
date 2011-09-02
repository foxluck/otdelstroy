<?php
$form_id = isset($_GET['form_id'])?$_GET['form_id']:false;
$form_class = isset($_GET['form_class'])?$_GET['form_class']:false;
$print_form = Forms::getInstance($form_id,$form_class,true);
/*@var $print_form Forms*/
if(is_object($print_form)){
	$print_form->display(false);
}else{
	print translate('print_form_not_found');exit;
}
?>