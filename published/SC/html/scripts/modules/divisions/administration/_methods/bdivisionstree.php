<?php
	$smarty = &Core::getSmarty();
	
	DivisionModule::ActionsHandler();
	
	$smarty->assign('Tree', $this->_buildTree(
			$this->_getAllSubDivisions(0), 
			array('Name'=>'name', 'Enabled' => 'enabled', 'ID'=>'id', 'Level'=>'level'), 
			array('Name'=>
			'<img align="left" alt="" src="images_common/tpl_una.gif" vspace="2" /><a class="%ENABLED%templates" title="Настройки раздела" href="'.set_query('ukey=div_settings&edid=%ID%').'" target="_self">%NAME%</a>'.
			'&nbsp;<a href="'.set_query('ukey=div_settings&edid=%ID%&sub=add_div').'" title="Добавить подраздел"><img src="images_common/add.gif" border="0" alt="Добавить подраздел" /></a>'.
			'&nbsp;<a href="'.set_query('fACTION=DIVISION_UP&divisionID=%ID%').'" title="Вверх"><img src="images_common/asc_img.gif" border="0" alt="Вверх" /></a>'.
			'&nbsp;<a href="'.set_query('fACTION=DIVISION_DOWN&divisionID=%ID%').'" title="Вниз"><img src="images_common/desc_img.gif" border="0" alt="Вниз" /></a>'
			)));
			
	$smarty->assign( 'sub_template', $this->getTemplatePath('backend/division_tree.html'));
?>