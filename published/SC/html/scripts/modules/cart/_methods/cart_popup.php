<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

//should we add products to cart?
if ( isset($_GET['addproduct']) ){
	
	$variants=array();
	foreach( $_GET as $key => $val )
	{
		if(strstr($key, 'option_select_hidden_'))
			$variants[]=$val;
	}
	unset( $_SESSION['variants'] );
	$_SESSION['variants']=$variants;
	RedirectSQ('addproduct=&add2cart='.$_GET['addproduct']);
}


//specify that this is a popup window
$this_is_a_popup_cart_window = true;
$smarty->assign('this_is_a_popup_cart_window', 1);

//include core shopping cart routine
include(DIR_MODULES.'/'.$this->ModuleDir.'/scripts/shopping_cart.php');
?>