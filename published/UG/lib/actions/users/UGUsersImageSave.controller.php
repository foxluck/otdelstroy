<?php

class UGUsersImageSaveController extends UGController
{
	
	public function exec()
	{
		$this->layout = false;
		$this->actions[] = new UGUsersImageSaveAction();
	}	
}
?>