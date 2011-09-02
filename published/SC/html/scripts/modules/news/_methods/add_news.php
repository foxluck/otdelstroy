<?php
$this->assignSubTemplate('backend/news_add.html');

$rMsg = array();
$usePOST = $this->ActionsHandler($rMsg);
				
$msg = isset($_GET['msg'])?$_GET['msg']:'';
switch ($msg){
	case 'add_ok':
		$rMsg = array(
			'type' => 'ok',
			'text' => translate("blog_msg_post_added"),
		);
		break;
	default:
		$msg = '';
}
				
$_t = xHtmlSpecialChars($usePOST?$_POST['DATA']:array('textToMail'=>''));
$_t['textToMail'] = nl2br($_t['textToMail']);
$this->assign2template('NewsInfo', $_t);
$this->assign2template('current_date', Time::standartTime());
?>