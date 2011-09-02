<?php
function smarty_modifier_transcape($_str){
	
	return htmlspecialchars(translate($_str));
}
?>