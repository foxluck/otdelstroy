<?php
	$Register = &Register::getInstance();
	$GetVars = &$Register->get(VAR_GET);

	$MessageEntry = new Message();
	$Register->set(VAR_MESSAGE, $MessageEntry);

	$class = '';
	$string = '';

	if(isset($GetVars['msg'])){

		$MessageEntry = loadWData($GetVars['msg']);
		/* @var $MessageEntry Message */

		if(Message::isMessage($MessageEntry) && $MessageEntry->is_set()){

			$Register->set(VAR_MESSAGE, $MessageEntry);
			switch ($MessageEntry->Type){
				case MSG_ERROR:
					$class = "error_block";
					$string = '<span class="error_flag">Error: </span><span class="error_message">'.translate($MessageEntry->getMessage()).'</span>';
					break;
				case MSG_SUCCESS:
					$class = "success_block";
					$string = '<span class="success_message">'.translate($MessageEntry->getMessage()).'</span>';
					break;
				case MSG_NOTIFY:
					$class = "comment_block";
					$string = '<span class="success_message">'.translate($MessageEntry->getMessage()).'</span>';
					break;
			}
		}

		renderURL('msg=', '', true);
	}

	define('MESSAGE_BLOCK',$string? "<div id='message-block' class='{$class}'>{$string}</div>":'');
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	if(isset($MessageEntry->name))
		$smarty->assign('MessageBlock__'.$MessageEntry->name, MESSAGE_BLOCK);
	else
		$smarty->assign('MessageBlock', MESSAGE_BLOCK);
?>