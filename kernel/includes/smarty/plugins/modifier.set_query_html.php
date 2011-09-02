<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* ����:     modifier.set_query_html.php
* ���:     modifier
* ���:     query
* ����������:  ������ � ����������� � ������ �������
* -------------------------------------------------------------
*/
function smarty_modifier_set_query_html($_vars, $_request = ''){
	
	return xHtmlSetQuery($_vars, $_request);
}
?>