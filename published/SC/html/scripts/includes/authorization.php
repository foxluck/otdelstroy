<?php
	if (isset($_POST["enter"]) && !isset($_SESSION["log"])){

		if ( regAuthenticate($_POST["user_login"],$_POST["user_pw"]) ){
			
			Redirect( set_query('ukey=office') );
		}else{
			Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'err_wrong_password', '', array('Data'=>$_POST));
		}
	}


	if (isset($_POST["forgotpw"])) //forgot password?
	{
		$smarty->hassign("forgotpw", $_POST["forgotpw"]);
		$res = regSendPasswordToUser( $_POST["forgotpw"], $smarty_mail );
		if ( $res )
			$smarty->assign("login_was_found", 1);
		else
			$smarty->assign("login_wasnt_found", 1);
		$show_password_form = 1;
	}
?>