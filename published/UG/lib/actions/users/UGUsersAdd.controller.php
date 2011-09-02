<?php

class UGUsersAddController extends UGController
{
    public function exec() 
    {
        $this->title = _('Add a new user');
        if ($this->ajax) {
            $this->actions[] = new UGAjaxUsersAddAction();
        } else {
            $this->layout = 'Popup';
            $this->actions[] = new UGUsersAddAction();
        }
    }
}

?>