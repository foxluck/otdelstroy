<?php

class UGSettingsSaveController extends UGController
{
	public function exec()
	{
		$this->layout = false;
		$this->actions[] = new UGSettingsSaveAction();
	}
}
?>