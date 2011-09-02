<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* ����:     modifier.set_query.php
* ���:     modifier
* ���:     query
* ����������:  ������ � ����������� � ������ �������
* -------------------------------------------------------------
*/
function smarty_modifier_set_query($_vars, $_request = ''){
	
	return renderURL($_vars, $_request);
}
?>