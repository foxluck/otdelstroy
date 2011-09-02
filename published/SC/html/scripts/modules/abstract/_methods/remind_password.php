<?php
	class RemindPasswordController extends ActionsController {
		
		function remind(){
			
			global $smarty_mail;
			if(!trim($this->getData('email')))Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'usr_enter_loginemail', '', array('name' => 'error'));
			$res = regSendPasswordToUser( $this->getData('email'), $smarty_mail );
			if($res)Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'usr_password_sent', '', array('name' => 'success'));
			else Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'usr_cant_find_user_in_db', '', array('name' => 'error', 'email' => $this->getData('email')));
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			$Message = &$Register->get(VAR_MESSAGE);
			/*@var $Message Message*/
			if(Message::isMessage($Message) && $Message->is_set() && isset($Message->email)){
				$smarty->assign('email', $Message->email);
			}
			$smarty->assign('main_content_template', 'remind_password.html');
		}
	}
	
	ActionsController::exec('RemindPasswordController');
?>