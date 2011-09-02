<?php
class Feedback extends Module
{

	function initInterfaces()
	{

		$this->Interfaces = array();
		$this->Interfaces['feedback'] = array(
			'name'	 => 'Feed back',
			'method' => 'methodFeedback',
		);
	}

	function methodFeedback()
	{

		global $smarty;
		$Register = &Register::getInstance();
		/*@var $Register Register*/
		$Message = $Register->get(VAR_MESSAGE);
		/*@var $Message Message*/

		if(isset($_POST['feedback'])) {

			$customer_name = trim( $_POST['customer_name'] );
			$customer_email = trim( $_POST['customer_email'] ) ;
			$message_subject = trim( $_POST['message_subject'] ) ;
			$message_text = trim( $_POST['message_text'] );
		}else {

			$customer_name = '';
			$customer_email = '';
			$message_subject = '';
			$message_text = '';
		}

		if(isset($_POST['feedback']) && !valid_email($customer_email)){
			Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'msg_error_wrong_email', '', array('feedback_data' => $_POST));
		}

		//validate input data
		if (isset($_POST['send']) && $customer_email && $customer_name && $message_subject && $message_text && valid_email($customer_email)){

			if(CONF_ENABLE_CONFIRMATION_CODE){
				$iVal = new IValidator();
				if(!$iVal->checkCode($_POST['fConfirmationCode'])) {

					Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'err_wrong_ccode', '', array('feedback_data' => $_POST));
				}
			}

			$customer_name = str_replace(array('@','<','\n'), array('[at]', '', ''), $customer_name);
			$customer_email = str_replace(array('\n','<'), '', $customer_email);
			$message_text = "{$customer_name} ({$customer_email}):\n{$message_text}";
			$headers = array('From'=>$customer_email,'Sender'=>$customer_email,'FromName'=>$customer_name);
			//send a message to store administrator
			ss_mail(CONF_GENERAL_EMAIL, $message_subject,$message_text,false,$headers);
			RedirectSQ('sent=1');
		}elseif(isset($_POST['feedback'])) {
			Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'err_input_all_required_fields', '', array('feedback_data' => $_POST));
		}

		//extract input to Smarty
		if(Message::isMessage($Message) && $Message->is_set() && isset($Message->feedback_data)) {
			$smarty->assign('feedback_data', $Message->feedback_data);
		}

		if (isset($_GET['sent'])) {
			$smarty->assign('sent',1);
		}
		set_query('sent=','',true);

		$smarty->assign('conf_image', URL_ROOT.'/imgval.php?'.generateRndCode(4).'=1');

		$smarty->assign('main_content_template', 'feedback.tpl.html');
	}
}
//EOF
