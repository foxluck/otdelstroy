<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();
global $smarty_mail;

if ( !cartCheckMinOrderAmount() )RedirectSQ('?ukey=cart');

if ( isset($_GET['remind_password']) ){
	
	set_query('remind_password=','',true);
	$smarty->assign('remind_password' , 1);
}

if ( isset($_POST['user_login'])  ){
	
	$smarty->hassign( 'user_login', $_POST['user_login'] );
	$smarty->hassign( 'login_to_remind_password', $_POST['user_login'] );
}

if ( isset($_POST['remind_password']) ){	
	
	$Reminded = regSendPasswordToUser( $_POST['login_to_remind_password'], $smarty_mail )?'yes':'no';
	if($Reminded=='no')$smarty->hassign('remind_user_login', $_POST['login_to_remind_password']);
	$smarty->assign( 'password_sent_notifycation',  $Reminded);
}

if ( isset($_POST['login']) ){
	
	if ( trim($_POST['user_login']) != '' ){	
		
		$cartIsEmpty = cartCartIsEmpty($_POST['user_login']);
		if ( regAuthenticate( $_POST['user_login'], $_POST['user_pw'], false ) ){
			
			if ( $cartIsEmpty )RedirectSQ('?ukey=order2_shipping&shippingAddressID='.regGetDefaultAddressIDByLogin($_SESSION['log']) );
			else RedirectSQ( '?ukey=shopping_cart&make_more_exact_cart_content=yes' );
		}else{
			
			$smarty->hassign('remind_user_login', $_POST['user_login']);
			$smarty->assign( 'password_sent_notifycation',  'no');
			$smarty->assign('remind_password' , 1);
		}
	}
}

$smarty->assign('check_order', 'yes');
$smarty->assign('main_content_template', 'register_authorization.tpl.html');
?>