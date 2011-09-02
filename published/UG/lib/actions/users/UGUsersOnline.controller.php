<?php

class UGUsersOnlineController extends UGController
{
    public function exec()
    {
        $this->actions[] = new UGAjaxUsersOnlineAction();
    }
}
?>