<?php

class UGGroupsEditController extends UGController
{
    public function exec()
    {
        if (Env::Get('tab') == 'rights') {
            if (Env::Get('ajax')) {
                $this->actions[] = new UGGroupsRightsAction();
            } else {
                $this->actions[] = new UGGroupsEditAccessAction();
            }
        } elseif (Env::Get('tab') == 'users') {
            $this->actions[] = new UGGroupsUsersAction();
        } else {
            $this->actions[] = new UGGroupsEditAction();
        }
    }
}

?>