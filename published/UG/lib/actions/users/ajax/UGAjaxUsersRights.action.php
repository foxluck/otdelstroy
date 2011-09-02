<?php

/**
 * Getting, saving and deleting of the rights of users and groups by ajax
 * 
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id: UGAjaxUsersRights.action.php 4438 2009-04-21 09:17:15Z alexmuz $
 */
class UGAjaxUsersRightsAction extends UGAjaxAction
{
	public $user_id;

	public function __construct()
	{
		$this->user_id = Env::Post('id', Env::TYPE_BASE64, "");
		switch (Env::Post('action')) {
			case 'save' : {
				$this->save();
				break;
			}
			case 'get' : {
				$rights = new Rights($this->user_id);
				$this->response = $rights->getAll();
				break;
			}
			case 'del' : {
				$app_id = Env::Post('application_id');
				$rights = new Rights($this->user_id);
				$rights->delete($app_id);
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
		
		
		if ($app_id == 'DD' && $section == 'QUOTA') {
			$value = (int)$value;
			if ($value < 0) {
				$value = 0;
			}
			$disk_quota_model = new DiskQuotaModel();
			$disk_quota_model->set($this->user_id, 'DD', $value);
			$this->response['value'] = $value;			
			return true;
		}
		
		if ($app_id == 'PM' && $section == 'FOLDERS') {
		    $section = 'PROJECTS';
		}
		
		$rights = new Rights($this->user_id);		
		if ($object_id == 'ALL') {
			$folders = $rights->getFolders($app_id, false, false);
			foreach ($folders as $folder) {
				$rights->set($app_id, $section, $folder['ID'], $value, Env::Post('max'));
			}
		}
		// Administrator 
		elseif ($section == 'SCREENS') {
		    if ($value == 7) {
	    		$rights->set($app_id, Rights::FUNCTIONS, 'ADMIN', 1);
		    } else {
                $rights->set($app_id, Rights::FUNCTIONS, 'ADMIN', 0);
		    }
		    $rights->set($app_id, $section, $object_id, 1);
		} else {
		    $rights->set($app_id, $section, $object_id, $value);
		}
	}
	
	public function prepareData()
	{
	}
	
}

?>