<?php

class RightsModel
{
	/**
	 * The delimiter in right's path
	 */
	const DELIMITER = "/";
	
	/**
	 * Sections
	 */
	const SECTION_SCREENS = "SCREENS";
	const SECTION_FUNCTIONS = "FUNCTIONS";
	const SECTION_DA = "DA";
	const SECTION_FOLDERS = "FOLDERS";
	const SECTION_MESSAGES = "MESSAGES";
	const SECTION_PROJECTS = "PROJECTS";
	
	public static $users_rights = array();
	
	protected $instance = false;
	
	protected $rights = array();

	protected $app_id;
	
	/**
	 * Construct
	 */
	public function __construct($app_id)
	{
		$this->app_id = $app_id;
	}
	
	/**
	 * Describe structure of rights for RightsModel
	 * 
	 * @param RightsDescriptor $desc
	 */
	public function setDescriptor($desc)
	{
		$desc->exec($this);
	}
	

	/**
	 * Returns user's access rights to single object
	 * 
	 * @param $user_id 
	 * @param $section - RightsModel::SECTION_*
	 * @param $object_id
	 * 
	 * @return int
	 */
	public function getUserRights($user_id, $section, $object_id)
	{
		// Get user
		$sql = new CSelectSqlQuery("WBS_USER", "U");
		$sql->innerJoin("U_ACCESSRIGHTS", "UA", "UA.AR_ID=U.U_ID");
		$sql->addConditions("AR_ID", $user_id);
		$sql->addConditions("AR_OBJECT_ID", $object_id);
		$sql->addConditions("AR_PATH LIKE '/ROOT/{$this->app_id}/{$section}'");
		$sql->setSelectFields("AR_VALUE");
		$rights =  Wdb::getFirstField($sql);
		
		// Get group
		$sql = new CSelectSqlQuery("WBS_USER", "U");
		$sql->innerJoin("UGROUP_USER", "UGU", "U.U_ID=UGU.U_ID");
		$sql->innerJoin("UG_ACCESSRIGHTS", "UGA", "UGA.AR_ID=UGU.UG_ID");
		$sql->setSelectFields("UGA.AR_PATH, UGA.AR_OBJECT_ID, UGA.AR_VALUE");
		$sql->addConditions("UGA.AR_PATH LIKE '/ROOT/{$this->app_id}/{$section}'");
		$sql->addConditions("U.U_ID", $user_id);
		$sql->setSelectFields("MAX(AR_VALUE)");
		
		// Returns max
		return max($rights, Wdb::getFirstField($sql));	
	}
		
	/**
	 * Add Rights item
	 * 
	 * @param RightsItem $rights_item
	 */
	public function addSection(RightsSection $rights_section)
	{
		$rights_section->setPath('/ROOT/'.$this->app_id);
		$this->rights[$rights_section->getId()] = $rights_section;	 
	}
	
	/**
	 * 
	 * @param $section_id
	 * @return RightsSection
	 */
	public function getSection($section_id)
	{
		if (isset($this->rights[$section_id])) {
			return $this->rights[$section_id]; 
		} else {
			return false;
		}
	}
	
	/**
	 * Load rights from database
	 * Use, if you need work with many data
	 * 
	 */
	public function loadUserRights($user_id, $full = false)
	{
		if ($full) {
			$this->loadFolders();
		}		
		
		$this->resetRights();
		
		$sql = new CSelectSqlQuery("WBS_USER", "U");
		$sql->innerJoin("U_ACCESSRIGHTS", "UA", "UA.AR_ID=U.U_ID");
		$sql->setSelectFields("UA.AR_PATH, UA.AR_OBJECT_ID, UA.AR_VALUE");
		$sql->addConditions("UA.AR_PATH LIKE '/ROOT/{$this->app_id}/%'");
		$sql->addConditions("UA.AR_VALUE>0");
		$sql->addConditions("U.U_ID", $user_id);
		$user_rights = Wdb::getData($sql);

		foreach ($user_rights as $user_right) {
			$right_info = $this->getRightInfo($user_right);
			$this->setRight($right_info, RightsItem::TYPE_USER);
		}
		
		$sql = new CSelectSqlQuery("WBS_USER", "U");
		$sql->innerJoin("UGROUP_USER", "UGU", "U.U_ID=UGU.U_ID");
		$sql->innerJoin("UG_ACCESSRIGHTS", "UGA", "UGA.AR_ID=UGU.UG_ID");
		$sql->setSelectFields("UGA.AR_PATH, UGA.AR_OBJECT_ID, UGA.AR_VALUE");
		$sql->addConditions("UGA.AR_PATH LIKE '/ROOT/{$this->app_id}/%'");
		$sql->addConditions("UGA.AR_VALUE>0");
		$sql->addConditions("U.U_ID", $user_id);
		$groups_rights = Wdb::getData($sql);	
		
		foreach ($groups_rights as $group_right) {
			$right_info = $this->getRightInfo($group_right);
			$this->setRight($right_info, RightsItem::TYPE_GROUP);			
		}
	}
	
	public function loadGroupRights($group_id, $full = false)
	{
		if ($full) {
			$this->loadFolders();
		}		
		
		$this->resetRights();
		
		$sql = new CSelectSqlQuery("UGROUP", "UG");
		$sql->innerJoin("UG_ACCESSRIGHTS", "UGA", "UGA.AR_ID=UG.UG_ID");
		$sql->setSelectFields("UGA.AR_PATH, UGA.AR_OBJECT_ID, UGA.AR_VALUE");
		$sql->addConditions("UGA.AR_PATH LIKE '/ROOT/{$this->app_id}/%'");
		$sql->addConditions("UGA.AR_VALUE > 0");
		$sql->addConditions("UG.UG_ID", $group_id);
		$rights = Wdb::getData($sql);

		foreach ($rights as $right) {
			$right_info = $this->getRightInfo($right);
			$this->setRight($right_info);
		}			 
	}

	
	public function loadFolders()
	{
		
	}
	
	public function getFolders()
	{
		if (!isset($this->rights[self::SECTION_FOLDERS])) {
			return array();
		}
		$folders = array(
		'ROOT' => 
			array(
				 'ROOT',
				  Locale::getStr("dd", "app_treeavailflds_title"),
				  '',
				  '',
				  '',
				  false,
				  'children' => array()
			)
		);
		foreach ($this->rights[self::SECTION_FOLDERS]->getObjects() as $right)
		{
			if ($right instanceof FolderRights) {
				$folders[$right->getId()] = $right->__toArray();
			}
		}
		foreach ($this->rights[self::SECTION_FOLDERS]->getObjects() as $right)
		{
			if ($right instanceof FolderRights) {
				$folders[$right->getParent()]['children'][] = $right->getId();
			}
		}		
		return $this->getFolder("ROOT", $folders);
	}
	
	
	public function getFolder($id, $folders)
	{
		$folder = $folders[$id];
		if (!$folder['children']) {
			$folder['children'] = array();
			return $folder;
		}
		foreach ($folder['children'] as &$children) {
			$children = $this->getFolder($children, $folders);
		}
		return $folder;
	}

	/**
	 * Returns array with right's information
	 *  
	 * @return array
	 */
	protected function getRightInfo($right_db)
	{
		$right_info = array();
		$path = $right_db['AR_PATH'];
		$path_r = explode(self::DELIMITER, $path);
		if (isset($path_r[3])) {	
			$right_info['SECTION'] = $path_r[3];
		}
		$right_info['OBJECT_ID'] = $right_db['AR_OBJECT_ID'];
		$right_info['VALUE'] = $right_db['AR_VALUE'];
		
		return $right_info;
	}
	
	
	/**
	 * Returns rights of object
	 * 
	 * @param string $section
	 * @param string $object_id
	 * @param int $type - user or group
	 * @return int
	 */
	public function getRight($section, $object_id, $type = 0)
	{
		if (isset($this->rights[$section]) && $this->rights[$section]->getObjects($object_id)) {
			return $this->rights[$section]->getRight($object_id);
		} else {
			$right = 0;
			if ($type == RightsItem::TYPE_USER) {
				$sql = new CSelectSqlQuery("U_ACCESSRIGHT", "UG");
				$sql->addConditions("AR_PATH", "/ROOT/{$this->app_id}/{$section}");
				$sql->addConditions("AR_OBJECT_ID", $object_id);
				$sql->setSelectFields("AR_VALUE");
				$right = max(Wdb::getFirstField($sql), $right);
			}
			if ($type == RightsItem::TYPE_GROUP) {
				$sql = new CSelectSqlQuery("UG_ACCESSRIGHT", "UG");
				$sql->addConditions("AR_PATH", "/ROOT/{$this->app_id}/{$section}");
				$sql->addConditions("AR_OBJECT_ID", $object_id);
				$sql->setSelectFields("AR_VALUE");
				$right = max(Wdb::getFirstField($sql), $right);
			}			
		}
	}
	
	/**
	 * Set right
	 * 
	 * @param array $right_info - array('SECTION' => $section, 'OBJECT_ID' => $object_id, 'VALUE' => $type)
	 * @param int $type - USER_TYPE or GROUP_TYPE 
	 */
	public function setRight($right_info, $type = false)
	{
		if (isset($right_info['SECTION'])) {
			if (isset($this->rights[$right_info['SECTION']])) {
				$this->rights[$right_info['SECTION']]->setRight($right_info, $type);
			}
		}
		else {
			if (isset($this->rights[$right_info['OBJECT_ID']])) {
				$this->rights[$right_info['OBJECT_ID']]->setRight($right_info, $type);
			}
		}
	}
			

	public function resetRights()
	{
		foreach ($this->rights as $right) {	
			$right->resetRights();
		}
	}
	
	/**
	 * @return bool
	 */
	public function checkByMask($value, $mask)
	{
		if (is_array($mask)) {
			foreach ($mask as $m) {
				if (($m & $value) == $m) {
					return true;
				}
			}
			return false;
		}
		return (($value & $mask) == $mask);
	}
	
	
	/**
	 * Returns users and rights for the object
	 * 
	 * @param string $section
	 * @param string $object_id
	 * 
	 * @return array
	 */
	public function getUsersRights($section, $object_id)
	{
		$sql = new CSelectSqlQuery("U_ACCESSRIGHTS", "UA");
		$sql->innerJoin("WBS_USER", "U", "UA.AR_ID = U.U_ID");
		$sql->innerJoin("CONTACT", "C", "U.C_ID=C.C_ID");
		$sql->addConditions("UA.AR_PATH LIKE '/ROOT/{$this->app_id}/{$section}'");
		$sql->addConditions("UA.AR_OBJECT_ID", $object_id);
		$sql->setSelectFields("U.U_ID, UA.*, C.C_FIRSTNAME, C.C_MIDDLENAME, C.C_LASTNAME, C.C_NICKNAME");
		$rows = Wdb::getData($sql);
		$users = array();
		foreach ($rows as $row) {
			$users[$row['U_ID']] = array(
				'ID' => $row['U_ID'],
				'NAME' => Users::getUserDisplayName($row),
				'RIGHT' => $row['AR_VALUE'] 
			);
		}
		return $users;
	}
	
	/**
	 * Returns groups and rights for the object
	 * 
	 * @param string $section
	 * @param string $object_id
	 * 
	 * @return array
	 */	
	public function getGroupsRights($section, $object_id) 
	{
		$sql = new CSelectSqlQuery("UG_ACCESSRIGHTS", "UGA");
		$sql->innerJoin("UGROUP", "UG", "UGA.AR_ID = UG.UG_ID");
		$sql->addConditions("UGA.AR_PATH LIKE '/ROOT/{$this->app_id}/{$section}'");
		$sql->addConditions("UGA.AR_OBJECT_ID", $object_id);
		$sql->setSelectFields("UG.UG_ID, UG.UG_NAME, UGA.*");
		$rows = Wdb::getData($sql);
		$groups = array();
		foreach ($rows as $row) {
			$groups[$row['UG_ID']] = array(
				'ID' => $row['UG_ID'],
				'NAME' => $row['UG_NAME'],
				'RIGHT' => $row['AR_VALUE'] 
			);
		}
		return $groups;		
	}
	
	/**
	 * View of the RightsModel to Array
	 * 
	 * @return array
	 */
	public function __toArray()
	{
		$result = array(
			"app_id" => $this->app_id,
			"rights" => array()
		);

		foreach ($this->rights as $right) {
			$result['rights'][] = $right->__toArray();
		}
		return $result;
	}
	
	
	
	
}


?>