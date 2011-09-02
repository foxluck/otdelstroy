<?php

class UGUsersImageChangeController extends UGController
{
	
	public function exec()
	{
		$this->layout = false;		
	    $this->actions[] = new UGUsersImageChangeAction();
		
	}	
}
?>