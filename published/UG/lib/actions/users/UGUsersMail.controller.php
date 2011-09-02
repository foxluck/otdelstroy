<?php

class UGUsersMailController extends UGController
{
    public function exec()
    {
        $this->layout = 'Empty';
        $this->actions[] = new UGUsersMailAction();
    }
}

?>