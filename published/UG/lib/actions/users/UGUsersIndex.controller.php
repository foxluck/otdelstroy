<?php
 
class UGUsersIndexController extends UGController 
{
    
    public function exec()
    {
        $this->layout = "User";
        if (!$this->ajax && Env::Get('tab') == 'settings') {
        	$this->actions[] = new UGUsersSettingsAction(false);
        } elseif (!$this->ajax && Env::Get('tab') == 'notes') {
        	$this->actions[] = new UGUsersNotesAction(false);
        } else {
        	$this->actions[] = new UGUsersIndexAction(false);
        }
    }
        
}

?>