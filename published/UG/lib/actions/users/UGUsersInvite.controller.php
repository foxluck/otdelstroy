<?php	

class UGUsersInviteController extends UGController
{
	public $public = true;
	public function exec()
	{
		$this->layout = false;
        if ($this->ajax) {
            $this->actions[] = new UGAjaxUsersInviteAction();
        } else {
			$this->actions[] = new UGUsersInviteAction();
        }
	}
}
?>