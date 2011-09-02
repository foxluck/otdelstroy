<?php
	$local_settings = &$Args[0]['local_settings'];

	if(!$local_settings['list_id'])return;
	
	$productList = new ProductList();
	$res = $productList->loadByID($local_settings['list_id']);
	if(!$res)return;
	
	$products = $productList->getProducts(true);
	
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */

	$smarty->assign('__products', $products);
	$smarty->assign('__block_height', intval($local_settings['block_height']));
	$smarty->display('product_list.html');
?>
