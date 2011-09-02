<?php

class UGUsersEmailController extends UGController 
{
 
	public function exec()
	{
		if (Env::Get('ajax')) {
			$this->layout = false;
			$this->actions[] = new UGAjaxUsersEmailAction();
		} elseif(Env::Post('upload_action')) {
			$this->layout = false;
			$this->actions[] = new UGUsersEmailUploadAction();
		} else {
			$this->layout = 'Empty';
			$this->actions[] = new UGUsersEmailSendAction();
		}
	}

}

?>