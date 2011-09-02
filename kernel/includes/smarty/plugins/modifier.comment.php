<?php
function smarty_modifier_comment($value, $comment_id = ''){

	if(!$comment_id)$comment_id = $value;
	
	$user_login = '';
	$user_login = sc_getSessionData('U_ID');
	$comment_id = "cmnt-{$user_login}-{$comment_id}";
	if(isset($_COOKIE[$comment_id])||!$value){
		setcookie($comment_id, 1, 60*24*60*60);
		return '';
	}
	$value = translate($value);
	return 
"
<p class='comment_block' id='{$comment_id}'>
{$value}
<br /><br />
<a href='#close_comment' onclick='closeComment(\"{$comment_id}\");'>".translate('btn_close')."</a>
</p>
";
}
?>