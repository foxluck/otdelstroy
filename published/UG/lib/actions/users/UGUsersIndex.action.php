<?php 

class UGUsersIndexAction extends UGUsersAction 
{
    
    public static function getBackTitle()
    {
    	$mode = Env::Get('mode');
    	$id = Env::Get('id');
    	switch ($mode) {
    		case 'groups':
    			$group = Groups::get($id);
    			return $group['UG_NAME'];
    		case 'folders':
    			$contac_folders_model = new ContactFolderModel();
    			$folder = $contac_folders_model->get($id);
    			return $folder['NAME'];
    		case 'lists':
    			$lists_model = new ListsModel();
    			$list = $lists_model->get($id);
    			return $list['CL_NAME'];
    		case 'search':
    			return _('Search results');
    		default:
    			return false;
    	}
    }
    
    public function getFolders() 
    {
        $folders = Contact::getFolders();
        $is_folder = false;
        $result = array();
        $folder_id = $this->contact_info['CF_ID'];
        if (substr($folder_id, 0, 7) == 'PRIVATE') {
            $contact_id = substr($folder_id, 7);
            // private folder of the other user
            if ($contact_id != User::getContactId() || !$folders) {
                $result[] = array(
                    'key' => $folder_id,
                    'value' => _s('Private').' ('.Contact::getName($contact_id).')',
                    'disabled' => true,
                    'offset' => 1
                );
                $is_folder = true;
            }
        }
        
    	foreach ($folders as $folder) {
    	    if ($folder['ID'] == $folder_id) {
    	        $is_folder = true;
    	    } 
    		$result[] = array(
    			'key' => $folder['ID'],
    			'value' => $folder['NAME'],
    		    'disabled' => $folder['RIGHTS'] < Rights::RIGHT_WRITE,
    		    'offset' => $folder['OFFSET']
    		);
    	}	
    	if ($folder_id && !$is_folder) {
    	    $contact_folder_model = new ContactFolderModel();
    	    $folder = $contact_folder_model->get($folder_id);
    	    $n = explode(".", $folder_id);
    	    $result[] = array(
    	        'key' => $folder_id,
    	        'value' => $folder['NAME'],
    	        'disabled' => true,
    	        'offset' => count($n)
    	    );
    	}
    	return $result;
    }
    
       
    public function prepareData()
    {	
		$contact_type = new ContactType($this->contact_info['CT_ID']);
		$type = $contact_type->getType();
		$js = array();
		
		$photo_field = $contact_type->getPhotoField();
		$main_fields = $contact_type->getMainFields();
		$main_section = ContactType::getMainSection();
        $type['fields'] = array_values($type['fields']);				
		foreach($type['fields'] as $x => &$group) {
			$js_f = array();
			if ($group['fields']) {
				foreach ($group['fields'] as $f) {
					 $field_info = array(
						$f['id'],
						$f['name'],
						$f['type'],
						$f['type'] == 'EMAIL' ? $this->contact_info[$f['dbname']] : "{$this->contact_info[$f['dbname']]}",
						$f['required']
					); 
					
					if ($f["options"]) {
						$field_info[] = $f["options"];
					}
					$js_f[] = $field_info;
				}
			}
			if ($group['id'] == $main_section) {			
				$main = array();
				$other = array();
				foreach ($js_f as $field_info) {
					if (in_array($field_info[0], $main_fields)) {
						$main[] = $field_info;
					} else {
						$other[] = $field_info;
					}
				}
				$js_f = array_merge($main, $other);
			}
			$js[] = array($group['id'] != $main_section ? "group".$x : "CONTACT", $js_f);	
		}
		
		if (User::getAppId() != 'MW') {
		$js[] = array('FOLDER', array(
    		        array(
    		            'CF_ID',
    		            _('Folder'),
    		            'MENU',
    		            $this->contact_info['CF_ID'] ? $this->contact_info['CF_ID'] : '&lt;'._('none folder').'&gt;',
    		            0,
    		            $this->getFolders()
			        )
			    ));
		}
		$last_time = User::getLastTime($this->user_id);
		$status = (time() - $last_time <= User::ONLINE_TIMEOUT) ? _("online") : _("offline");
		
		if ($last_time) {
			$last_time = WbsDateTime::getTime($last_time). " " . WbsDateTime::ago($last_time);	
		}
		
						
		$this->smarty->assign('js', json_encode($js));
		$this->smarty->assign('type', $this->contact_info['CT_ID']);
    	if ($this->contact_info['C_MODIFYCID']) {
	        $this->contact_info['C_MODIFYNAME'] = Contact::getName($this->contact_info['C_MODIFYCID']);
	        if ($this->contact_info['C_MODIFYDATETIME']) {
	            $this->contact_info['C_MODIFYDATETIME'] = WbsDateTime::getTime(strtotime($this->contact_info['C_MODIFYDATETIME']));
	        }	           
		}		
				
		$this->smarty->assign('contact_info', $this->contact_info);
		$this->smarty->assign('fields', $type['fields']);
		$this->smarty->assign("photo_field", $photo_field);
		$this->smarty->assign('main_fields', json_encode($main_fields));
		$this->smarty->assign('super_main_fields', json_encode($contact_type->getMainFields()));
		
		$this->smarty->assign('back_title', htmlspecialchars(self::getBackTitle()));
		$this->smarty->assign('mode', Env::Get('mode'));
		
		$date_format = mb_strtolower(Wbs::getDbkeyObj()->getDateFormat());

		$this->smarty->assign("dateFormat", mb_substr($date_format, 0, -2));

		$countries = Wbs::getCountries();
		$this->smarty->assign('countries', json_encode($countries));
		
		$this->smarty->assign('link',  Contact::getSubscribeLink($this->contact_id)."&i");
		parent::prepareData();
    }
}

?>