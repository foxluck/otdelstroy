<?php

class UGAjaxGroupsRightsAction extends UGAjaxAction
{
	public $user_id;

	public function __construct()
	{
		$this->group_id = Env::Post('id', Env::TYPE_INT, "");
		switch (Env::Post('action')) {
			case 'save' : {
				$this->save();
				break;
			}
			case 'get' : {
				$rights = new Rights($this->group_id, Rights::GROUP);
				$this->response = $rights->getAll();
				break;
			}
			case 'del' : {
				$app_id = Env::Post('application_id');
				$groups_rights_model = new GroupsRightsModel();
				$groups_rights_model->delete($this->group_id, $app_id);
				break;
			}
		}				
	}
	
	public function save()
	{
		$app_id = Env::Post('application_id', Env::TYPE_STRING_TRIM, "");
		$section = Env::Post('section', Env::TYPE_STRING_TRIM, "");
		$object_id = Env::Post('object_id', Env::TYPE_STRING_TRIM, "");
		$value = Env::Post('value');
		
		$groups_rights_model = new GroupsRightsModel();
		$path = "/ROOT/".$app_id."/".$section;
		
		$rights = new Rights($this->group_id, Rights::GROUP);
		if ($object_id == 'ALL') {
			$folders = $rights->getFolders($app_id, false, false);
			foreach ($folders as $folder) {
				$groups_rights_model->save($this->group_id, $path, $folder['ID'], $value, Env::Post('max'));
			}
		} elseif ($section == 'SCREENS') {
		    if ($value == 7) {
	    		$rights->set($app_id, Rights::FUNCTIONS, 'ADMIN', 1);
		    } else {
                $rights->set($app_id, Rights::FUNCTIONS, 'ADMIN', 0);
		    }
		    $rights->set($app_id, $section, $object_id, 1);
		} else {
			$groups_rights_model->save($this->group_id, $path, $object_id, $value);
		}
	}
	
	public function prepareData()
	{
	}
	
}

?>