<?php

class UGSettingsSaveAction extends UGAjaxAction
{

	public function prepareData() 
	{
	    $items = Env::Post('itemsOnPage', Env::TYPE_INT, 10);
		if ($items) {
			User::setSetting("ITEMSONPAGE", $items);
		}	
	}	
}
?>