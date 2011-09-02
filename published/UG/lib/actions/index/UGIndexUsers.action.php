<?php

class UGIndexUsersAction extends UGViewAction
{
    
    public function getGroups()
    {
        $result = array();
        $groups_model = new GroupsModel();
        $groups = $groups_model->getAll();
        foreach ($groups as $group) {
            $result[] = array($group['UG_ID'], $group['UG_NAME'], array());        
        }
        return $result;
    }

    
    public function getFields()
    {
        $all_fields = ContactType::getAllFields(User::getLang(), false, false, true);        
       
        // dbfields
        $dbfields = array();
		foreach ($all_fields as $id => $field) {
			if ($field['type'] != 'SECTION') {
				$dbfields[$field['dbname']] = $field['type'];
			}
		}
		$this->smarty->assign('dbfields', json_encode($dbfields));

		// types
		$type_sections = array();
		$photo_field = array();
		$types = ContactType::getTypes(User::getLang());
		$type_names = array();
		foreach ($types as $type_id => $type_info) {
		    $type_names[$type_id] = $type_info['name'];
			$contact_type = new ContactType($type_id);
			$main_fields = $contact_type->getMainFields();
			$main_section = $contact_type->getMainSection();
			$list_sections = array();
			$type = $contact_type->getType(User::getLang());
			foreach ($type['fields'] as $section) {
				if ($section['id'] != $main_section) {
					$list_sections[$section['id']] = array('name' => $section['name'], 'fields' => array());
				}
				foreach ($section['fields'] as $field) {
					if ($field['type'] == 'IMAGE') {
						continue;
					}
					if ($section['id'] == $main_section && !in_array($field['id'], $main_fields)) {
						$list_sections[$section['id']."_".$field['id']] = array(
							'name' => $field['name'], 
							'fields' => array(
								array($field['dbname'], $field['name']) 
							) 
						);
					} else if ($section['id'] != $main_section) {
						$list_sections[$section['id']]['fields'][] = array($field['dbname'], $field['name']);		
					}
				}	
			}
			$pf = ContactType::getField($contact_type->getPhotoField());
			$photo_field[$type_id] = $pf ? $pf['dbname'] : '';
			$type_sections[$type_id] = array_values($list_sections);
		}
		
        $this->smarty->assign('contact_types', json_encode($type_names));		

		$this->smarty->assign('list_fields', json_encode($type_sections));
		$this->smarty->assign('photoField', json_encode($photo_field));		
    }

    public function prepareData()
    {
        $this->smarty->assign('page', Env::Get('p', Env::TYPE_INT, User::getSetting('LASTPAGE')));

		$groups = $this->getGroups();
		$this->smarty->assign('groups_count', count($groups));
        $this->smarty->assign("groups", json_encode(array('ROOT', '', $groups)));
		
        $group_id = User::getSetting("LASTGROUP", 'UG');
        $this->smarty->assign("group_id", $group_id ? $group_id : 'all');
        $viewSettings = array(
        	'itemsOnPage' => User::getSetting('ITEMSONPAGE', 'UG'),
        	'viewmodeApplyTo' => 'local'
        );
        $this->smarty->assign("viewSettings", $viewSettings);

		if (Env::Session('MESSAGE')) {
		    $this->smarty->assign('message', Env::Session('MESSAGE'));
		    Env::unsetSession('MESSAGE');
		}
		
		$this->getFields();

		$this->smarty->assign('right_js', json_encode(array('users' => 1)));		
		$this->smarty->assign('app', User::getAppId());
		$this->smarty->assign('online', Groups::countUsers(Groups::ONLINE));

		$this->smarty->assign('time', microtime(true) - Registry::get('time'));
    }
}

?>