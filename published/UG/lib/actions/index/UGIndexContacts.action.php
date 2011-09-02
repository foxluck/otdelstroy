<?php

class UGIndexContactsAction extends UGIndexUsersAction
{
    public function getFolders()
    {
        $rights = new Rights(CurrentUser::getId(), Rights::USER);
        $folders = $rights->getFolders('CM', false, true, Rights::FLAG_RIGHTS_INT | Rights::FLAG_NOT_PARENT | Rights::FLAG_NOT_EMPTY);

        // Advanced folders
        $add_folders = array();
        if (User::hasAccess('CM', Rights::FOLDERS, 'PRIVATE')) {
	        $add_folders[] = array(
	        	'PRIVATE'.User::getContactId(), 
	            _s("Private"),
	            3,
	            array()
	        );
        }
        if (User::getSetting('PUBLIC', 'CM', '')) {
	        $add_folders[] = array(
	        	'PUBLIC', 
	            _s("Public"),
	             3,
	            array()
	        );
        }        
        $folders = array_merge($add_folders, $folders);
               
        $this->smarty->assign('add_contact', $this->getWriteRights($folders));
        
        return array(
            	'ROOT', 
                _s('Available folders'), 
                User::hasAccess('CM', Rights::FOLDERS, 'ROOT'), 
                $folders
        );
    }
    
    
    public function getWriteRights($folders)
    {
    	foreach ($folders as $folder) {
    		if ($folder[2] >= Rights::RIGHT_WRITE) {
    			return true;
    		}	
    		if ($folder[3] && $this->getWriteRights($folder[3])) {
    			return true;
    		}
    	}
    	return false;
    }
    
    public function getWidgets()
    {
    	$widgets_model = new WidgetsModel();
    	$widgets = $widgets_model->getByType("SBSC");
    	$result = array();
    	foreach ($widgets as $widget) {
    		$result[] = array(
    			$widget['WG_ID'],
    			$widget['WG_DESC'],
    			array(),
    			base64_encode($widget['WG_ID']),
    		);
    	}
    	$this->smarty->assign('widgets_count', count($result));
    	return array(
    		'ROOT',
    		'',
    		$result
    	);
    }
    
    public function getLists()
    {
    	$lists_model = new ListsModel();
    	$lists = $lists_model->getByContactId(User::getContactId());
    	$result = array();
    	           	
    	if (Wbs::getDbkeyObj()->appExists('SC') && User::isAdmin('CM')) {
    	    $result[] = array(
    	        -1,
    	        _s('Store customers'),
    	        array(),
    	        1,
    	        0
    	    );
    	}
    	
    	foreach ($lists as $id => $f) {
    		$result[] = array(
    			$id,
    			$f['CL_NAME'],
    			array(),
    			$f['CL_SQL'] == 2 ? 2 : ($f['CL_SQL'] ? 1 : 0),
    			$f['CL_SHARED'] ? 1 : 0 
    		);    		
    	}
    	return array(
    		'ROOT',
    		'',
    		$result
    	);
    }
    
    public function getRights()
    {
        $app = User::getAppId();
    	return array(
    		'contacts' => $app == 'CM' ? User::hasAccess('CM') : false,
    	    'createRootFolder' => $app == 'CM' ? User::hasAccess('CM', Rights::FOLDERS, 'ROOT') : false, 
    		'users' => User::hasAccess('UG'),
    		'admin' => $app == 'CM' ? User::isAdmin('CM') : false,
    	    'private' => $app == 'CM' ? User::hasAccess('CM', Rights::FOLDERS, 'PRIVATE') : false
    	);
    }
    
    public function prepareData() 
    {
        if (Env::Get('folder_id')) {
            $folder_id = Env::Get('folder_id'); 
        } else {
            $folder_id = User::getSetting("LASTFOLDER", 'CM');
        }
        $viewParams = array(	    	
	    	"currentFolderId" => $folder_id,
	    	"currentListId" => User::getSetting("LASTLIST", 'CM'),
        	"currentFormId" => User::getSetting("LASTFORM", 'CM'),
	   	);
   	        
        $this->smarty->assign('page', Env::Get('p', Env::TYPE_INT, User::getSetting('LASTPAGE')));
		$this->smarty->assign('contact_types', json_encode(ContactType::getTypeNames()));

        $this->smarty->assign("folders", json_encode($this->getFolders()));
        $this->smarty->assign("lists", json_encode($this->getLists()));
        $this->smarty->assign("widgets", json_encode($this->getWidgets()));

        $this->smarty->assign('manage_users', User::hasAccess('UG'));
		
        $this->smarty->assign("viewParams", $viewParams);
        $viewSettings = array(
        	'itemsOnPage' => User::getSetting('ITEMSONPAGE', 'UG'),
        	'viewmodeApplyTo' => 'local'
        );
        $this->smarty->assign("viewSettings", $viewSettings);
 
		if (Env::Get('name')) {
		    $this->smarty->assign('search_string', Env::Get('name'));
		}

		if (Env::Session('MESSAGE')) {
		    $this->smarty->assign('message', Env::Session('MESSAGE'));
			Env::unsetSession('MESSAGE');
		}
		
		$this->smarty->assign('contact_id', User::getContactId());
		$right = $this->getRights();
		$this->smarty->assign('right', $right);
		$this->smarty->assign('right_js', json_encode($right));
				    
		$this->smarty->assign('app', User::getAppId());
		
		$this->getFields();
    }        
}

?>