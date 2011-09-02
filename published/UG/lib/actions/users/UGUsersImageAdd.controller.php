<?php

class UGUsersImageAddController extends UGController
{
	
	public function exec()
	{
		$this->layout = false;		
	    $this->actions[] = new UGUsersImageAddAction();
		
	}	
}
?>