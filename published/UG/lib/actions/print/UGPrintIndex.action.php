<?php

class UGPrintIndexAction extends UGViewAction
{	
	public function prepareData()
	{
		$params = Env::Get();
		if (!isset($params['offset'])) {
			$params['offset'] = 0;
		}

		$this->smarty->assign('offset', $params['offset']);
		$this->smarty->assign('limit', isset($params['limit']) ? $params['limit'] : 0);

		unset($params['offset']);
		unset($params['limit']);
		
		
		$res = array();
		foreach ($params as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					if (is_array($v)) {
						foreach ($v as $subk => $subv) {
							$res[$key."[".$k."][".$subk."]"] = $subv;	
						}
					} else {
						$res[$key."[".$k."]"] = $v;
					}
				}
			} else {
				$res[$key] = $value;
			}
		}
		
		$this->smarty->assign('params', json_encode($res));
		
		$url = Env::Server('REQUEST_URI');
		$url = preg_replace("!&params\[(limit|offset)\]=[0-9]+!ui", "", $url);
		$this->smarty->assign('url_all', $url);
		$this->getFields();
	}
	
    public function getFields()
    {
    	$config = array();
    	
        $all_fields = ContactType::getAllFields(User::getLang(), false, false, true);        
       
        // dbfields
        $dbfields = array();
		foreach ($all_fields as $id => $field) {
			if ($field['type'] != 'SECTION') {
				$dbfields[$field['dbname']] = $field['type'];
			}
		}
		$config['dbfields'] = $dbfields;

		// types
		$type_sections = array();
		$photo_field = array();
		$types = ContactType::getTypes(User::getLang());
		foreach ($types as $type_id => $type_info) {
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
			
		$config['fields'] = $type_sections;
		$config['photo'] = $photo_field;
		
		$this->smarty->assign('config', json_encode($config));
    }	
}