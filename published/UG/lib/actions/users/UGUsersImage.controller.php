<?php

class UGUsersImageController extends UGController
{
	
	public function exec()
	{
		$this->layout = false;
		if (Env::Get('ajax')) {
		    $this->actions[] = new UGUsersImageUploadAction();
		} else {
		    $this->actions[] = new UGUsersImageAction();
		}
	}	
}
?>