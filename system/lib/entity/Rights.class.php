<?php

/**
 * Rights of users and groups
 * Description of the rights of applications store in file _rights.php in application's directory
 * 
 * @copyright Webasyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id: Rights.class.php 8711 2010-04-26 10:27:17Z alexmuz $ 
 * @example 
 * 	$rights = new Rights(User::getId());
 *  $rights->get('DD', Rights::FOLDERS, $folder_id, Rights::MODE_APP, Rights::RETURN_OBJECT)->isRead();
 *
 */
class Rights
{
	const FUNCTIONS = 'FUNCTIONS';
	const FOLDERS = 'FOLDERS';
	const MESSAGES = 'MESSAGES';

	// Type
	const USER = 0;
	const GROUP = 1;
	
	// Rights mask
	const RIGHT_NO = 0;
	const RIGHT_READ = 1;
	const RIGHT_WRITE = 3;
	const RIGHT_FOLDER = 7;
	
	// Flags
	const FLAG_RIGHTS_INT = 1;
	const FLAG_NOT_PARENT = 2;
	const FLAG_NOT_EMPTY = 4;
	const FLAG_ARRAY_OFFSET = 8;
	const FLAG_ONLY_FULL = 16;
	const FLAG_ONLY_WRITE = 32;

	// Mode of the reading rights
	const MODE_APP = 1;
	const MODE_ONE = 2;
	const MODE_ALL = 3;
	
	// Types of the returned values
	const RETURN_INT = 1;
	const RETURN_ARRAY = 2;
	const RETURN_OBJECT = 3;
	const RETURN_BOOL = 4;
	
	public static $rights = array();
	public static $inherit_rights = array();
	
	public static $access = array();
	
	public static $apps = array();
	
	protected static $has_admin = array(
		'PD', 'UG', 'CM', 'MW'
	);
	
	protected $id;
	protected $type;
	
	/**
	 * Constructor 
	 * 
	 * @param $id - U_ID of the user or UG_ID of the group
	 */
	public function __construct($id = null, $type = self::USER)
	{
		$this->id = $id;
		$this->type = $type;
		
		if ($this->type == self::USER && $this->id === null) {
		    $this->id = User::getId();
		} 
	}
	
	/**
	 * Returns available rights with names in current language
	 * 
	 * @return array
	 */
	public static function getNames()
	{
	    return array(
	        self::RIGHT_NO => _s('No rights'),
	        self::RIGHT_READ => _s('Read'),
	        self::RIGHT_WRITE => _s('Write'),
	        self::RIGHT_FOLDER => _s('Full')
	    );
	}
	
	/**
	 * Returns available applications
	 * 
	 * @param $add_screen_id
	 * @return array
	 */
	public function getApps($add_screen_id = false, $info = true)
	{
		if (!isset(self::$access[$this->id])) {
			$rights_model = $this->getModel();
			self::$access[$this->id] = $rights_model->getAvailableApps($this->id);
		}
		if (!$info) {
			return self::$access[$this->id];
		}
		$apps = array();
		foreach (self::$access[$this->id] as $app) {
			$app_id = $app['APP_ID'];
			$app_info = self::getApplicationInfo($app['APP_ID']);
			if ($app_info) {
				$apps[$app_id.($add_screen_id ? "/".$app_info['SCREEN_ID'] : "")] = $app_info['TITLE'];
			}
		}
		return $this->sortApps($apps);
	}
	
	/**
	 * Sort applications by ORDER (see file _rights.php in application directory
	 * 
	 * @param $apps
	 * @return array
	 */
	public function sortApps($apps)
	{
		$sort = array();
		$keys = array();
		foreach ($apps as $app_prefix => $v) {
			$temp = explode("/", $app_prefix);
			$app_id = array_shift($temp);
			$keys[$app_id] = $app_prefix;
			$app_info = $this->getApplicationInfo($app_id);
			$sort[$app_id] = $app_info['ORDER'];
		}
		asort($sort);
	    $result = array();
    	foreach ($sort as $app_id => $n) {
    		$result[$keys[$app_id]] = $apps[$keys[$app_id]];
    	}		
    	return $result;
	}
	
	/**
	 * Returns application's info (see _rights.php in application directory)
	 * @param $app_id
	 * @return unknown_type
	 */
	public static function getApplicationInfo($app_id, $cache = true)
	{
		if (isset(self::$apps[$app_id])) {
			return self::$apps[$app_id];
		} else {
    		$filename = AppPath::APP_PATH($app_id)."/_rights.php";
    		if (file_exists($filename)) {
    		    $rights = array();
				include($filename);
				if (isset($rights[$app_id])) {
					 $i = array(
						'TITLE' => $rights[$app_id]['TITLE'],
						'SCREEN_ID' => $rights[$app_id]['SCREEN_ID'],
						'ORDER' => $rights[$app_id]['ORDER'],
					);
					if ($cache) {
						self::$apps[$app_id] = $i;
					} else {
						return $i;
					}
				} else {
					self::$apps[$app_id] = false;
				}
    		} else {
    			self::$apps[$app_id] = false;
    		}
		}
		return self::$apps[$app_id];
	}
	
    /**
     * Returns right (code) of the object in section of the application
     * 
     * @example
     * 		// Check access of the user to reports in Files  
     * 		$rights = new Rights(CurrentUser::getId());
     * 		$right = $rights->get('DD', Rights::FUNCTIONS, 'CANREPORTS');
     * @param $app_id
     * @param $section_id
     * @param $object_id
     * @return array(ALL_RIGHT, USER_RIGHT, USER_GROUPS_RIGHT)
     */
    public function get($app_id, $section_id, $object_id, $mode = self::MODE_APP, $return_type = self::RETURN_INT)
    {
    	// @todo: Use session for save current app rights   

    	$rights_model = $this->getModel();
    	
    	if ($app_id == 'UG' && $this->type == self::USER && $this->id == 'ADMINISTRATOR') {
    	    $v = array(1, 1, 0);
	    	if ($return_type == self::RETURN_ARRAY) {
	    		return $v;
	    	} elseif ($return_type == self::RETURN_OBJECT) {
	    		return new RightValue($v[0]);		
	    	} elseif ($return_type == self::RETURN_BOOL) {
	    		return $v[0] ? 1 : 0;
	    	} else {
	    		return $v[0];
	    	}
    	}
    	
    	// Check admin rights (/ROOT/APP_ID/FUNCTIONS	ADMIN)
    	if (in_array($app_id, self::$has_admin) && !($section_id == self::FUNCTIONS && $object_id == 'ADMIN') && 
    		($v = $this->get($app_id, self::FUNCTIONS, 'ADMIN', $mode, self::RETURN_ARRAY)) && 
    		$v[0]) {
			$v[0] = $v[1] = ($section_id == self::FOLDERS ? 7 : 1);
	    	if ($return_type == self::RETURN_ARRAY) {
	    		return $v;
	    	} elseif ($return_type == self::RETURN_OBJECT) {
	    		return new RightValue($v[0]);		
	    	} elseif ($return_type == self::RETURN_BOOL) {
	    		return $v[0] ? 1 : 0;
	    	} else {
	    		return $v[0];
	    	}
    	}
    	
    	if ($mode == self::MODE_ALL && !isset(self::$rights[$this->id]['load'])) {
			self::$rights[$this->id] = $rights_model->getRights($this->id, false);
			self::$rights[$this->id]['load'] = true;
    	}
    	elseif ($mode == self::MODE_APP && 
    			!isset(self::$rights[$this->id]['load']) && !isset(self::$rights[$this->id][$app_id]['load'])) {
			self::$rights[$this->id][$app_id] = $rights_model->getRights($this->id, $app_id);
			self::$rights[$this->id][$app_id]['load'] = true;
    	}
    	elseif ($mode == self::MODE_ONE && 
    			$this->type == self::USER && 
    			!isset(self::$rights[$this->id]['load']) && 
    			!isset(self::$rights[$this->id][$app_id]['load']) && 
    			!isset(self::$rights[$this->id][$app_id][$section_id][$object_id])) {
    				
    		self::$rights[$this->id][$app_id][$section_id][$object_id] = $rights_model->get($this->id, $app_id, $section_id, $object_id, true);
    	}
    	
    	// Return 
    	if (isset(self::$rights[$this->id][$app_id][$section_id][$object_id])) { 
    		$v = self::$rights[$this->id][$app_id][$section_id][$object_id];
    	} else {
    		$v = array(0, 0, 0);
    	}
    	    	
    	if ($return_type == self::RETURN_ARRAY) {
    		return $v;
    	} elseif ($return_type == self::RETURN_OBJECT) {
    		return new RightValue($v[0]);
	    } elseif ($return_type == self::RETURN_BOOL) {
	    	return $v[0] ? 1 : 0;    				
    	} else {
    		return (int)$v[0];
    	}
    }
        
    
	public function set($app_id, $section_id, $object_id, $value, $save_max = false) 
	{
		$path = $path = "/ROOT/".$app_id."/".$section_id;
		$this->getModel()->save($this->id, $path, $object_id, $value, $save_max);
	}  

	
	public function delete($app_id = false)
	{
		$this->getModel()->delete($this->id, $app_id);
	} 
   
    /**
     * Returns Rights Model
     * 
     * @return UserRightsModel|GroupsRightsModel
     */
    public function getModel()
    {
    	if ($this->type == self::GROUP) {
    		return new GroupsRightsModel();
    	} else {
    		return new UserRightsModel();
    	}
    }
    
    /**
     * Return inherit rights 
     * 
     * @param $app_id
     * @param $section_id
     * @param $object_id
     * @param $app_only
     * @return array - array(RIGHT, USER_RIGHT, GROUP_RIGHT)
     */
    public function getInherit($app_id, $section_id, $object_id, $app_only = false) 
    {
    	if (!isset(self::$inherit_rights[$app_id])) {
			$rights_model = $this->getModel();
			if ($app_only) {
				self::$inherit_rights[$app_id] = $rights_model->getInheritRights($app_only ? $app_id : false);
			} else {
				self::$inherit_rights = $rights_model->getInheritRights(false);
				if (!isset(self::$inherit_rights[$app_id])) {
					self::$inherit_rights[$app_id] = array();
				}
			}    		
       	}
       	if (isset(self::$inherit_rights[$app_id][$section_id][$object_id])) {
       		$link = self::$inherit_rights[$app_id][$section_id][$object_id];
       		return $this->get($link['app_id'], $link['section_id'], $link['object_id'], $app_only ? self::MODE_APP : self::MODE_ALL, self::RETURN_ARRAY);
       	} else {
       		return array(0, 0, 0);
       	}
    } 
    
    
    /**
     * Return array with all rights of the user
     * Use in rights reports etc.
     * Optimized for JSON response
     * 		array(
     * 			APP_ID,
     * 			APP_TITLE,
     * 			SCREEN_ID,
     * 			SCREEN_RIGHT,
     * 			SECTIONS, // array
     * 			FOLDERS // array
     * 
     * @return array()
     */
    public function getAll()
    {    	
    	$apps = array();
    	$sort = array();
    	foreach (Wbs::getDbkeyObj()->getApplicationsList() as $app_id) {
    		$filename = WBS_PUBLISHED_DIR.$app_id."/_rights.php";
    		if (file_exists($filename)) {
    		    $rights = array();
				include($filename);
				if (isset($rights[$app_id])) {
					$app_rights = $rights[$app_id];
					$sort[$app_id] = isset($app_rights['ORDER']) ? $app_rights['ORDER'] : 0;
					$r = $this->get($app_id, 'SCREENS', $app_rights['SCREEN_ID'], self::MODE_ALL, self::RETURN_ARRAY);
					$r_admin = $this->get($app_id, 'FUNCTIONS', 'ADMIN', self::MODE_ALL, self::RETURN_ARRAY);
					if ($r_admin[0]) {
					    $r[0] = 7;
					}
					if ($r_admin[1]) {
					    $r[1] = 7;
					}		
					if ($r_admin[2]) {
					    $r[2] = 7;
					}

                    if ($app_id != 'SC' && isset($app_rights['SECTIONS']) && count($app_rights['SECTIONS']) < 1) {
                        if ($r[0]) $r[0] = 7;
                        if ($r[1]) $r[1] = 7;
                        if ($r[2]) $r[2] = 7;
                    }	
                    				
					$apps[$app_id] = array(
						$app_rights['APP_ID'], // 0
						$app_rights['TITLE'], // 1
						$app_rights['SCREEN_ID'], //2
						$r, // 3
					);
					

					// Sections
					
					// Shopping Cart save rights description in database 
					if ($app_id == 'SC') {
						$db_model = new DbModel();
				      	$admin_id = $db_model->query("SELECT xID FROM `SC_divisions` WHERE xUnicKey = 'admin'")->fetchField('xID');
						// Get id of the language
				      	$lang = mb_substr(CurrentUser::getLanguage(), 0, 2);
					    $lang_id = $db_model->prepare("SELECT id FROM `SC_language` WHERE iso2 = s:lang AND enabled = 1")->query(array('lang' => $lang))->fetchField('id');
					    if (!$lang_id) {
							$lang_id = $db_model->query("SELECT id FROM `SC_language` WHERE enabled = 1")->fetchField('id'); 
					    }
					    
					    $sql = 'SELECT D1.xID ID, IF(SL.value IS NULL, D1.xName, SL.value) NAME, IF(D1.xParentID = i:admin_id, "ROOT", D1.xParentID) PARENT
				      			FROM `SC_divisions` D1 JOIN  
				      				 `SC_divisions` D2 ON D1.xParentID = D2.xID LEFT JOIN
				      				 `SC_local` SL ON D1.xName = SL.id AND SL.lang_id = i:lang_id
								WHERE (D2.xID = i:admin_id || D2.xParentID = i:admin_id) AND D1.xEnabled = 1
								ORDER BY D2.xPriority DESC, D1.xPriority DESC';
					    
					    $data = $db_model->prepare($sql)->query(array('admin_id' => $admin_id, 'lang_id' => $lang_id));

					    foreach ($app_rights['SECTIONS'] as &$section) {
							foreach ($section['OBJECTS'] as &$object) {
								// Read right of the object
								$object[] = $section['ID'] ? $this->get($app_id, $section['ID'], $object[0], self::MODE_ALL, self::RETURN_ARRAY) : array(0, 0, 0);
							}
							$section = array_values($section);
						}
						$sections = $app_rights['SECTIONS'];
					    
					    foreach ($data as $row) {
					    	if ($row['PARENT'] == 'ROOT') {
					    		if (isset($sections[$row['ID']])) {
					    			$sections[$row['ID']] = array(Rights::FUNCTIONS, $row['NAME'], $sections[$row['ID']][2]);
					    		} else {
					    			$sections[$row['ID']] = array(Rights::FUNCTIONS, $row['NAME'], array());
					    		}
					    	} else {
					    		if (!isset($sections[$row['PARENT']][2])) {
					    			$sections[$row['PARENT']][2] = array();
					    		} 
					    		$sections[$row['PARENT']][2][] = array('SC__'.$row['ID'], $row['NAME'], $this->get($app_id, Rights::FUNCTIONS, 'SC__'.$row['ID'], self::MODE_ALL, self::RETURN_ARRAY));
					    	}
					    }
						$apps[$app_id][] = array_values($sections);
					}
					elseif (isset($app_rights['SECTIONS'])) {
						foreach ($app_rights['SECTIONS'] as &$section) {
						    // If SQL
						    if (!is_array($section['OBJECTS'])) {
						        // Get Data
						        $db_model = new DbModel();
						        $data = $db_model->query($section['OBJECTS']);
						        $section['OBJECTS'] = array();
						        foreach ($data as $row) {
						            $section['OBJECTS'][] = array_values($row);
						        }
						    }
							foreach ($section['OBJECTS'] as &$object) {
								// Read right of the object
								$object[] = $this->get($app_id, $section['ID'], $object[0], self::MODE_ALL, self::RETURN_ARRAY);
							}
							$section = array_values($section);
							// Hack for quota
							if ($section[0] == 'QUOTA' && $this->type == self::USER) {
								$disk_quota_model = new DiskQuotaModel();
								$section[] = $disk_quota_model->get($this->id, 'DD');
							} 
						}
						$apps[$app_id][] = $app_rights['SECTIONS'];
					}
					else {
						$apps[$app_id][] = array();
					}
					// Folders
					if (isset($app_rights['FOLDERS'])) {
						 $folders = array(
							'title' => $app_rights['FOLDERS']['TITLE'],
						 	'comment' => isset($app_rights['FOLDERS']['COMMENT']) ? $app_rights['FOLDERS']['COMMENT'] : "", 	 
							'icons' => $app_rights['FOLDERS']['ICONS'], 
							'folders' => $this->getFolders($app_id, $app_rights['FOLDERS'])
						);
						if (isset($app_rights['FOLDERS']['TITLE_INHERIT'])) { 
							$folders['title_inherit'] = $app_rights['FOLDERS']['TITLE_INHERIT'];
						}
						$apps[$app_id][] = $folders;// 5
					} else {
						$apps[$app_id][] = array();
					}
				}    					
    		}
    	}
    	asort($sort);
    	$result = array();
    	foreach ($sort as $app_id => $n) {
    		$result[] = $apps[$app_id];
    	}
    	return $result;
    }
    
   
    /**
     * Returns folders by table info (from rights file) 
     * 
     * @param $app_id
     * @param $info
     * @return array
     */
    public function getFolders($app_id, $info = false, $get_rights = true, $flags = 0, $parent_id = 'ROOT') 
    {   	
    	// Include file (rights descriptor), if info is null
    	if (!$info) {
    		$filename = WBS_PUBLISHED_DIR .$app_id."/_rights.php";
    		if (file_exists($filename)) {
    		    $rights = array();
				include($filename);
				if (isset($rights[$app_id]['FOLDERS'])) {
					$info = $rights[$app_id]['FOLDERS'];
				} else {
					return array();
				}
    		} else {
    			return array();
    		}
    		$app_only = true;
    	} else {
    		$app_only = false;
    	}
    	
    	if ($parent_id != 'ROOT') {
    	    $cond = $info['ID']." LIKE '".$parent_id."%'";
    	    $info['WHERE'] = isset($info['WHERE']) ? $info['WHERE']." AND ".$cond : $cond;
    	}
    	
    	$sql = "SELECT ".$info['ID']." ID, ".$info['PARENT']." PARENT, ".$info['NAME']." NAME, 0 RIGHTS, NULL AS CHILDREN";
    	if (isset($info['INHERIT'])) {
    		$sql .= ", (".$info['INHERIT'].") INHERIT";
    	}
    	$sql .= " FROM ".$info['TABLE'];
    	if (isset($info['STATUS'])) {
    		$sql .= " WHERE ".$info['STATUS']." >= 0";
    	} elseif (isset($info['WHERE'])) {
    		$sql .= " WHERE ".$info['WHERE'];
    	}
    	if (isset($info['ORDER'])) {
    		$sql .= " ORDER BY ".$info['ORDER'];
    	}
    	// Execute SQL - query
    	$model = new DbModel();
    	$folders = $model->prepare($sql)->query(array('U_ID' => $this->type == self::USER ? $this->id : ""))->fetchAll('ID');
    	// Returns folders, if not need get rights of them
    	if (!$get_rights) {
    		return $folders;
    	}
    	
    	$folders['ROOT'] = array('CHILDREN' => array());
    	foreach ($folders as $folder_id => $folder) {
    		if ($folder_id != 'ROOT') {

    			if (isset($info['INHERIT']) && $folders[$folder_id]['INHERIT']) {
    				$folders[$folder_id]['RIGHTS'] = $this->getInherit($app_id, "FOLDERS", $folder_id);
    			} else {
    				$folders[$folder_id]['RIGHTS'] = $this->get($app_id, $app_id == 'PM' ? 'PROJECTS' : "FOLDERS", $folder_id, $app_only ? self::MODE_APP : self::MODE_ALL, self::RETURN_ARRAY);
    			}

    			if ($flags & self::FLAG_RIGHTS_INT) {
    				$folders[$folder_id]['RIGHTS'] = $folders[$folder_id]['RIGHTS'][0];
    			}
    			$parent = $folders[$folder_id]['PARENT'];
    			if ($flags & self::FLAG_NOT_PARENT) {
    				unset($folders[$folder_id]['PARENT']);
    			}
    			if ($flags & self::FLAG_ARRAY_OFFSET) {
    				$folders[$folder_id]['OFFSET'] = count(explode(".", $folder_id)) - 1;
    			}
    			$folders[$parent]['CHILDREN'][] = $folder_id;
	   		}
    	}
   	  
    	if ($flags & self::FLAG_NOT_EMPTY) {
			foreach ($folders as $folder_id => $folder) {
				if ($folder_id != 'ROOT') {
					if (isset($folders[$folder_id]) && !$this->hasRights($folders, $folder_id, $flags)) {
						unset($folders[$folder_id]);
					}
				}
			}
    	}   
    	if ($flags & self::FLAG_ARRAY_OFFSET) {
    		return $this->getChildrenArray($folders, $parent_id);
    	}
    	return $this->getChildren($folders, $parent_id, true);
    }   
    
    
    public function hasRights($folders, $folder_id, $flags)
    {
        if (!isset($folders[$folder_id])) {
            return false;
        }
    	$folder = $folders[$folder_id];
    	$right = ($flags & self::FLAG_RIGHTS_INT) ? $folder['RIGHTS'] : $folder['RIGHTS'][0];
    	if ($right) {
    		return true;
    	} elseif (!$right && !$folder['CHILDREN']) {
    		return false;
    	} else {
    		foreach ($folder['CHILDREN'] as $child) {
    			if ($this->hasRights($folders, $child, $flags)) {
    				return true;
    			}		
    		}
    		return false;		
    	}
    }
    
    public function getChildrenArray($folders, $parent_id)
    {
   		$result = array();
   		if (isset($folders[$parent_id]['CHILDREN']) && is_array($folders[$parent_id]['CHILDREN'])) {
	   		foreach ($folders[$parent_id]['CHILDREN'] as $folder_id) {
	   			if (isset($folders[$folder_id]) && $folder = $folders[$folder_id]) {
		   			$result[] = array(
		   				'ID' => $folder['ID'],
		   				'NAME' => $folder['NAME'],
		   				'RIGHTS' => $folder['RIGHTS'],
		   				'OFFSET' => $folder['OFFSET']
		   			);
		   			$result = array_merge($result, $this->getChildrenArray($folders, $folder_id));
	   			}
	   		}
   		}
   		return $result;
    }
    
    public function getChildren($folders, $folder_id, $onlychildren = false) 
    {
    	if (!isset($folders[$folder_id])) {
    		return array();
    	}
    	$folder = $folders[$folder_id];
		if (!isset($folder['CHILDREN']) || !is_array($folder['CHILDREN'])) {
			$folder['CHILDREN'] = array();
			return $onlychildren ? array() : array_values($folder);
		}    	
		foreach ($folder['CHILDREN'] as $key => $child) {
			if (!($folder['CHILDREN'][$key] = $this->getChildren($folders, $child))) {
				unset($folder['CHILDREN'][$key]);
			}
		}
		$folder['CHILDREN'] = array_values($folder['CHILDREN']);
		return $onlychildren ? array_values($folder['CHILDREN']) : array_values($folder);
    } 

}

?>