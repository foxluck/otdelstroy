<?php

class UGUsersItemsController extends UGController
{
	public function exec()
	{
		$this->actions[] = new UGAjaxUsersItemsAction();
	}
}
?>