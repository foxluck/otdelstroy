<?php 

class UGUsersListsAction extends UGAjaxAction 
{
    protected $sort;
    protected $order;
    protected $limit;
    protected $offset; 
    protected $page = 0;
    
    protected $group_id;
    protected $folder_id;
    
	public static $view_modes = array(
		0 => 'columns',
		1 => 'detail',
		2 => 'tile' 
	);
    
    public function __construct()
    {
        parent::__construct();
        $this->show = false;
        
        $this->mode = Env::Post('mode', Env::TYPE_STRING_TRIM, "folders");
             
        // Get Sort params
        $this->sort = Env::Post("sortColumn");
        if ($this->sort) {
            $this->order = Env::Post("sortDirection");
        }
        
        if ($this->sort == 'C_NAME') {
        	$this->sort = 'C_FULLNAME';
        } elseif ($this->sort == 'C_CREATENAME') {
            $this->sort = 'C_CREATECID';
        }
        
        // Get Limits params
        $this->limit = Env::Post('limit', Env::TYPE_INT, 0);
        if ($this->limit) {
            $this->offset = Env::Post("offset", Env::TYPE_INT, 0);
            $this->page = (int)($this->offset / $this->limit);
        }
        User::setSetting('LASTPAGE', $this->page);
    }
    
    public function getSort($name)
    {
    	$sort = User::getSetting($name);
    	if ($sort) {
    		$sort = explode(":", $sort);
    		if ($sort[0] == 'C_NAME') {
    			$sort[0] = "C_FULLNAME";
    		} elseif ($sort[0] == 'C_CREATENAME') {
    			$sort[0] = "C_CREATECID";
    		} else {
    			if (!ContactType::getFieldId($sort[0])) {
    				return "C_FULLNAME ASC";
    			}
    		}
    		return $sort[0]." ".$sort[1];
    	} else {
    		return "C_FULLNAME ASC";
    	}
    }
    
    public function getUsers()
    {
    	if ($this->sort == 'C_ID') {
    		$sort = "C.".$this->sort ." ".$this->order;
    	}
    	elseif ($this->sort) {
    		$sort = $this->sort." ".$this->order;
    	} else {
    		$sort = "";
    	}
    	
    	if ($this->limit) {
    		$limit = ($this->offset ? $this->offset . "," : "" ) . $this->limit;
    	} else {
    		$limit = "";
    	}
    	switch ($this->mode) {
    	    case "analytics":
    	        $field = Env::Post('query');
    	        $value = Env::Post('value');
    	        $contacts_model = new ContactsModel();
                $title = htmlspecialchars_decode(Env::Post('title'));
                
                if ($field == 'C_EMAILADDRESS' && !$value) {
                    $query = "C_EMAILADDRESS != ''";
                } elseif (!strlen($value)) {
                    if ($field == 'C_CREATECID') {
                        $query = $field." IS NULL";
                    } else {
                        $query = $field." IS NOT NULL AND ".$field." != ''";
                    }
                } else {
                    $query = $field." = '".$contacts_model->escape($value)."'";
                }
                if ($field_id = ContactType::getFieldId($field)) {
                    Registry::set('ADD_SHOW_FIELD', $field_id); 
                }
    	        $users = $contacts_model->getBySQL($query, $sort, $limit);
    	        if (Env::Post('save')) {
    	            $lists_model = new ListsModel();
    	            $list_id = $lists_model->add($title, $query);
			        $this->response['list'] = array(
						'id' => $list_id,
						'name' => $title,
			            'search' => $title
    	            );
    	            $this->response['add'] = 1;
    	        }
    	        $this->response['folder'] = array(
    				'ID' => '',
    				'NAME' => $title,
    				'RIGHTS' => 3,
    	            'ANALYTICS' => 1
    			);
    			
    			$this->response['total'] = $contacts_model->countBySQL($query);
                 break;
    		case "folders":
		    	$folder_id = Env::Post('folderId', Env::TYPE_STRING_TRIM, false);
		    	User::setSetting('LASTFOLDER', $folder_id, 'CM');
    			$contact_folder_model = new ContactFolderModel();
    			$folder_info  = $contact_folder_model->get($folder_id, User::getId());
    			
    			$this->response['folder'] = array(
    				'ID' => $folder_info['ID'],
    				'NAME' => $folder_info['NAME'],
    				'RIGHTS' => $folder_info['RIGHTS']
    			);
    			
    			if (!$sort) {
    				$sort = $this->getSort('SORTINGfolders'.$folder_id);
    			}

    			$contacts_model = new ContactsModel();
    			$users = $contacts_model->getByFolderId($folder_id, User::getContactId(), $sort, $limit);
    			if ($this->sort == 'C_EMAILADDRESS') {
    				$this->response['total'] = $contacts_model->countByFolderId($folder_id, User::getContactId(), true);
    				$this->response['total_advanced'] = $contacts_model->countByFolderId($folder_id, User::getContactId());    				
    			} else {
    				$this->response['total'] = $contacts_model->countByFolderId($folder_id, User::getContactId());
    			}
    			break;	
    		case "groups":
    			$group_id = Env::Post('groupId', false, 'all');
    			User::setSetting('LASTGROUP', $group_id);
    			$group_info = Groups::get($group_id);
				$this->response['folder'] = array(
				    'ID' => $group_id,
				    'NAME' => $group_info['UG_NAME']
				);

    			$users = Groups::getUsers($group_id, $sort, $limit);
    			$this->response['total'] = Groups::countUsers($group_id);
    			break;
    			
    		case "lists":
    			$list_id = Env::Post('listId', false, 0);
				User::setSetting('LASTLIST', $list_id, 'CM');
				if ((int)$list_id) {
	    			$lists_model = new ListsModel();
	    			$list_info = $lists_model->get($list_id);
	    			if ($list_info['CL_C_ID'] == User::getContactId()) {
	    				$r = "7";
	    			} elseif ($list_info['CL_SHARED'] && User::isAdmin('CM')) {
	    				$r = "7";
	    			} else {
	    				$r = "1";
	    			}
					$this->response['folder'] = array(
					    'ID' => $list_id,
					    'NAME' => $list_info['CL_NAME'],
					    'RIGHTS' => $r,
					    'SEARCH' => $list_info['CL_SQL'] ? ListsModel::getSearchDescription($list_info['CL_SEARCH'], true, true, $list_id) : 0,
						'SHARED' => $list_info['CL_SHARED'] ? 1 : 0
						
					);
				} else {
					$list_info = CMPlugins::getInstance()->getData('list_info', array('id' => $list_id));
					$this->response['folder'] = array(
						'ID' => $list_id,
						'NAME' => $list_info['name'],
						'RIGHTS' => 1,
						'SEARCH' => $list_info['desc'],
						'SHARED' => 0 						
					);
				}
				
    			if (!$sort) {
    				$sort = $this->getSort('SORTINGlists'.$list_id);
    			}
    			if ((int)$list_id) {
    				$result = Contact::getByList($list_id, $sort, $limit);
    			} else {
    				$result = CMPlugins::getInstance()->getData('list_contacts', array('id' => $list_id, 'sort' => $sort, 'limit' => $limit));
    			}
    			$users = $result['users'];
    			$this->response['total'] = $result['count'];
				
    			break;
    		case "search":
    			$search_string = Env::Post('searchString');
    			$list_id = Env::Post('list', Env::TYPE_INT, 0);
    			if ($list_id) {
    				$lists_model = new ListsModel();
    				$list_info = $lists_model->get($list_id);
    			} else {        			
	    			$this->response['folder'] = array(
	    				'ID' => 0,
	    				'NAME' => _('Search results for ').'"'.$search_string.'"',
	    				'RIGHTS' => 0
	    			);
    			}
    	    	if (Env::Post('noSave') == '-1') {
    				$list_id = Env::Post('list', Env::TYPE_INT);
    				if (!$list_id) {
    					$list_id = true;
    				}
    			} else {
    				$list_id = Env::Post('list', Env::TYPE_INT);
    			}    			
    			$result = Contact::searchByName($search_string, $sort, $limit, $list_id, Env::Post('list_title'));
    			$users = $result['users'];
    			$this->response['total'] = $result['count'];
    	    	if ($list_id) {
    	    		if ($list_id === true) {
    	    			$this->response['add'] = Env::Post('list_title') ? 1 : 2;
    	    		}
    	    		User::setSetting('LASTLIST', $result['list']['id'], 'CM');
    				$this->response['list'] = $result['list'];
	    			$this->response['folder'] = array(
	    				'ID' => $result['list']['id'],
	    				'NAME' => $result['list']['name'],
	    				'RIGHTS' => 7,
	    				'SEARCH' => $result['list']['search']
	    			);   			    				
    			}
    			break;
    		case "smartsearch":
    			$info = Env::Post('info');
				$list_id = Env::Post('list', Env::TYPE_INT, 0);
    			if ($list_id) {
    				$lists_model = new ListsModel();
    				$list_info = $lists_model->get($list_id);
    				$this->mode = "lists";
	    			$this->response['folder'] = array(
	    				'ID' => $list_id,
	    				'NAME' => $list_info['CL_NAME'],
	    				'RIGHTS' => 1,
	    				'SEARCH' => $list_info['CL_SEARCH'] ? 1 : 0
	    			);
    			} else {        			
	    			$this->response['folder'] = array(
	    				'ID' => 0,
	    				'NAME' => '',
	    				'RIGHTS' => 0
	    			);
    			}
	
    			if (Env::Post('noSave') == '-1') {
    				$list_id = Env::Post('list', Env::TYPE_INT);
    				if (!$list_id) {
    					$list_id = true;
    				}
    			} else {
    				$list_id = false;
    			}
    			$result = Contact::searchByFields($info, $sort, $limit, false, $list_id, Env::Post('list_title'));
    			$users = $result['users'];
    			if ($list_id) {
    	    		if ($list_id === true) {
    	    			$this->response['add'] = Env::Post('list_title') ? 1 : 2;
    	    		}
    	    		User::setSetting('LASTLIST', $result['list']['id'], 'CM');
	    			$this->response['folder'] = array(
	    				'ID' => $result['list']['id'],
	    				'NAME' => $result['list']['name'],
	    				'RIGHTS' => 7,
	    				'SEARCH' => $result['list']['search']
	    			);    				
    				$this->response['list'] = $result['list'];
    			}
    			$this->response['total'] = $result['count'];
    			break;
    			
    		case "advancedsearch":
    			$info = Env::Post('info', false, array());
    			$this->response['folder'] = array(
    				'ID' => 0,
    				'NAME' => '',
    				'RIGHTS' => 0    			
    			);

    			foreach ($info as &$cond) {
    				$cond['link'] = $cond['cond'] = 1;
    			} 
    			
   				// Subscribe 
    			if ( Env::Post('added') == 1) {
					$info[] = array('field' => 'added', 'val' => '1');
    			}
    			// Added by users 
    			elseif ($create_cid = Env::Post('createcid', Env::TYPE_INT, 0)) {
    				$info[] = array('field' => 'createcid', 'val' => $create_cid);
    			}
    			
    			// When
    			$when = Env::Post('when', Env::TYPE_INT, 0);
    			if ($when == 1 && ($days = Env::Post('days', Env::TYPE_INT, 0))) {
    				$info[] = array('field' => 'days', 'val' => $days);
    			} elseif ($when == 2) {
    				$from = Env::Post('from', Env::TYPE_INT, false);
    				if ($from && $time = WbsDateTime::unixtime($from)) {
			    		$value = date("Y-m-d", $time);
			    		$info[] = array('field' => 'from', 'val' => $value);	
					}

    			    $to = Env::Post('to', Env::TYPE_INT, false);
    				if ($from && $time = WbsDateTime::unixtime($from)) {
			    		$value = date("Y-m-d", $time);
			    		$info[] = array('field' => 'to', 'val' => $value);	
					}					
    			}
    			
    			// Folder id
    			$folder_id = Env::Post('folder_id');
    			if ($folder_id) {
    			    if (Env::Post('subfolders')) {
    			        $folder_id .= "%";
    			    }
    				$info[] = array('field' => 'folder_id', 'val' => $folder_id);
    			}
    			
    			$type_id = Env::Post('type_id', Env::TYPE_INT, 0);
    			if ($type_id) {
    				$info[] = array('field' => 'type_id', 'val' => $type_id, 'link' => 1);
    			}
    			    			
    	    	if (Env::Post('noSave') == '-1') {
    				$list_id = Env::Post('list', Env::TYPE_INT);
    				if (!$list_id) {
    					$list_id = true;
    				}
    			} else {
    				$list_id = false;
    			}    			
    			
    			$result = Contact::searchByFields($info, $sort, $limit, false, $list_id, Env::Post('list_title'), 'advanced');
    			if ($list_id) {
    	    		if ($list_id === true) {
    	    			$this->response['add'] = Env::Post('list_title') ? 1 : 2;
    	    		}
    	    		User::setSetting('LASTLIST', $result['list']['id'], 'CM');
	    			$this->response['folder'] = array(
	    				'ID' => $result['list']['id'],
	    				'NAME' => $result['list']['name'],
	    				'RIGHTS' => 7,
	    				'SEARCH' => $result['list']['search']
	    			);    				
    				$this->response['list'] = $result['list'];
    			}
    			$users = $result['users'];
    			$this->response['total'] = $result['count'];
    			break;
    			
    	}
    	$this->response['users'] = array();
    	if ($users) {
	    	Contact::useStore(false);
	        foreach ($users as $user) {
	        	$user_info = Contact::getInfo($user['C_ID'], $user, false, true);
	        	if ($this->sort == 'C_FIRSTNAME' || $this->sort == 'C_MIDDLENAME' || $this->sort == 'C_LASTNAME') {
	        		$first = ContactType::getFieldId($this->sort);
	        	} else {
	        		$first = false;
	        	}
	        	if (is_array($user_info["C_EMAILADDRESS"])) {
	        	    $user_info["C_EMAILADDRESS"] = array_shift($user_info["C_EMAILADDRESS"]);
	        	} 
	            $user_info['C_NAME'] = Contact::getName($user['C_ID'], false, $user, true, $first);
	            $user_info["ENC_ID"] = base64_encode($user['C_ID']);
	            $user_info['U_ID'] = (string) $user_info['U_ID'];
	            $this->response['users'][] = $user_info; 
	        }
            Contact::useStore(true); 
    	}
    }
   
    public function getSetting($name, $id) 
    {
    	$val = User::getSetting($name.$id);
    	if (!$val) {
    		$val = User::getSetting($name);
    	}	
    	return $val;
    }
    
    public function prepareData()
    {
   	    	
    	$this->response = array();    	
    	$this->getUsers();
    	
        if (substr($this->mode, -6) == 'search') {
        	if ($this->response['folder']['ID']) {
        		$mode = "lists";
        	} else {
    			$mode = "search";
        	}
    	} else {
    		$mode = $this->mode;	
    	}    	
    	
    	if ($viewMode = $this->getSetting('VIEWMODE', $mode.$this->response['folder']['ID'])) {
    		$this->response['viewMode'] = self::$view_modes[$viewMode];
    	} else {
    		$this->response['viewMode'] = 'columns';
    	}

    	if ($pages = $this->getSetting('ITEMSONPAGE', $mode.$this->response['folder']['ID'])) {
    		$this->response['itemsOnPage'] = $pages;
    	}

    	$fields = ContactType::getAllFields(User::getLang(), false, false, true);
    	if (Registry::get('SHOWFIELDS')) {
    	    $show_field_ids = Registry::get('SHOWFIELDS');
    	    if (is_array($show_field_ids)) {
    	    	$show_field_ids = array_merge((array)json_decode($this->getSetting('SHOWFIELDS', $mode.$this->response['folder']['ID'])), $show_field_ids);
    	    	$show_field_ids = array_unique($show_field_ids);
    	    }
    	} else {
            $show_field_ids = (array)json_decode($this->getSetting('SHOWFIELDS', $mode.$this->response['folder']['ID']));
            if (!$show_field_ids) {
                $show_field_ids = array(ContactType::getFieldId('C_COMPANY'), ContactType::getFieldId('C_EMAILADDRESS'));
            }
    	}
    	if (!in_array(0, $show_field_ids)) {
            $show_fields = array(
            	array('C_NAME', _s('Full name'), 1)
            );
            $f_id = Registry::get('ADD_SHOW_FIELD');
            if ($f_id && !in_array($f_id, $show_field_ids)) {
                $is_sort = !in_array($fields[$f_id]['type'], array('TEXT', 'IMAGE', 'COUNTRY'));
                $show_fields[] = array($fields[$f_id]['dbname'], $fields[$f_id]['name'], $is_sort, $fields[$f_id]['type']);
            }            
    	} else {
    	    $show_fields = array();
    	}
        foreach ($show_field_ids as $f_id) {
            if (isset($fields[$f_id])) {
                $is_sort = !in_array($fields[$f_id]['type'], array('TEXT', 'IMAGE', 'COUNTRY')); 
        	    $show_fields[] = array($fields[$f_id]['dbname'], $fields[$f_id]['name'], $is_sort, $fields[$f_id]['type']);
            } elseif ($f_id == 0) {
                $show_fields[] = array('C_NAME', _s('Full name'), 1);
	            $f_id = Registry::get('ADD_SHOW_FIELD');
	            if ($f_id && !in_array($f_id, $show_field_ids)) {
					$is_sort = !in_array($fields[$f_id]['type'], array('TEXT', 'IMAGE', 'COUNTRY'));
	                $show_fields[] = array($fields[$f_id]['dbname'], $fields[$f_id]['name'], $is_sort, $fields[$f_id]['type']);
	            }            
            }
        }
		$this->response['fields'] = $show_fields;

		// Save sorting
		if (Env::Post("sortColumn") && Env::Post("sortDirection")) {
			$s = Env::Post("sortColumn").":".Env::Post("sortDirection");
			User::setSetting("SORTING".$mode.$this->response['folder']['ID'], $s);
		}		
		$sort = "";
		if ($this->response['viewMode'] == 'columns') {
			$sort = User::getSetting("SORTING".$mode.$this->response['folder']['ID']);
		}
		if (!$sort) {
			$sort = "C_NAME:asc";
		}
		$sort = explode(":", $sort);
		$this->response['sorting'] = array(
			'column' => $sort[0],
			'direction' => $sort[1]
		);
    }
    
    public function getResponse()
    {
    	try {
	        $this->prepareData();
	        return json_encode($this->response);
	    } catch (MySQLException $e) {
	    	if (defined('DEVELOPER') && DEVELOPER) {
	    		$err = $e->__toString();
	    	} else {
	    		$err = _('Database error');
	    	}
	    	return json_encode(array("isError" => true, "errorStr" => $err));
    	} catch (Exception $e) {
    		return json_encode(array ("isError" => true, "errorStr" => $e->__toString()));
    	} 
    }    
       
}

?>