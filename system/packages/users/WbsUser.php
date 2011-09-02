<?php
	
	class WbsUser implements IUser {
		private $settings = array();
		private $U_ID;
		private $language;
		private $settingsXml;
		private $data;
		private $timeZone;
		/**
		private $screens;
		 * User's Contacts
		 *
		 * @var WbsContact
		 */
		private $contact;
		public $screens = array();
		
		public function __construct($U_ID, $info = array()) {
			$this->U_ID = $U_ID;
			$this->data = $info;
		}
		
		/**
		 * Returns class WbsContcat for user
		 *
		 * @return WbsContact
		 */
		public function getContact()
		{
			if (!$this->contact) { 
				$data = $this->getData();
				$this->contact = new WbsContact($data['C_ID'], $data);
			}
			return $this->contact;
		}
		
		public function getContactId()
		{
			$data = $this->getData();
			return $data['C_ID'];
		}
		
		public function getData() {
			if (!$this->data) {
				if ($this->isAdmin()) {
					$this->data = array ("C_LASTNAME" => "ADMINISTRATOR");
				} else {
					$users_model = new UsersModel();
					$this->data = $users_model->get($this->U_ID);
					if (!$this->data)
						throw new RuntimeException ("Not found user: " . $this->U_ID);
				}
			}
			return $this->data;
		}
		
		public function getAppSettings($appId)
		{
			$sql = new CSelectSqlQuery("USER_SETTINGS", "US");
			$sql->addConditions("U_ID", $this->getId());
			$sql->addConditions("APP_ID", $appId);
			$sql->setSelectFields(array("NAME", "VALUE"));
			return Wdb::getData($sql);
		}
		
		public function getSetting($name, $defaultValue = null)
		{
			return $this->getAppSetting("", $name, $defaultValue);
		}
		
		
		public function getAppSetting($appId, $name, $defaultValue = null)
		{
			
			if (isset($this->settings[$appId ? $appId : "ALL"][$name])) {
				$value = $this->settings[$appId ? $appId : "ALL"][$name];
			} else {
				$sql = new CSelectSqlQuery("USER_SETTINGS", "US");
				$sql->addConditions("U_ID", $this->getId());
				$sql->addConditions("APP_ID", $appId);
				$sql->addConditions("NAME", $name);
				$sql->setSelectFields("VALUE");
				$value =  Wdb::getFirstField($sql);
				$this->settings[$appId ? $appId : "ALL"][$name] = $value;
			}
			if (!strlen($value)) {
				return $defaultValue;
			}
			return $value;
		}
		
		
		public function setSetting($appId, $name, $value)
		{
			User::setSetting($name, $value, $appId, $this->getId());
		}
		
		public function setPassword($password) {
			$new_password = md5($password);
			$sql = new CUpdateSqlQuery("WBS_USER");
			$sql->addConditions("U_ID", $this->U_ID);
			$sql->addFields(array('U_PASSWORD' => $new_password), array("U_PASSWORD"));
			Wdb::runQuery($sql);
		}
		
		public function getId () {
			return $this->U_ID;
		}
		
		/**
		 * Returns user's language
		 *
		 * @return string
		 */
		public function getLanguage() 
		{
			if (!$this->language) {
				$this->language = User::getLang($this->getId());
			}
			return $this->language;
		}
		
		public function setTimeZone($timeZone) {
			$this->timeZone = $timeZone;
		}
		
		/**
		 * Returns user's timezone
		 *
		 * @return array
		 */
		public function getTimeZone() {
			return $this->timeZone;
		}
		
	
		function getUserRightsSql() {
			$userSql = new CSelectSqlQuery("WBS_USER", "U");
			$userSql->innerJoin("U_ACCESSRIGHTS", "UA", "UA.AR_ID=U.U_ID");
			$userSql->setSelectFields("UA.AR_PATH, UA.AR_OBJECT_ID");
			$userSql->addConditions("U.U_ID", $this->getId());
			$userSql->addConditions("UA.AR_VALUE>0");
			return $userSql;
		}
		
		function getGroupRightsSql() {
			$groupSql = new CSelectSqlQuery("WBS_USER", "U");
			$groupSql->innerJoin("UGROUP_USER", "UGU", "U.U_ID=UGU.U_ID");
			$groupSql->innerJoin("UG_ACCESSRIGHTS", "UGA", "UGA.AR_ID=UGU.UG_ID");
			$groupSql->setSelectFields("UGA.AR_PATH, UGA.AR_OBJECT_ID");
			$groupSql->addConditions("UGA.AR_VALUE>0");
			$groupSql->addConditions("U.U_ID", $this->getId());
			return $groupSql;
		}
		
		function isAdmin() {
			return strtoupper($this->U_ID) == "ADMINISTRATOR";
		}
		
		public function isPublic() {
			return false;
		}
		
		public function getRightValue($path, $objectId) {
			// todo: rewrite with rights manager
			$value = 0;
			
			$userSql = $this->getUserRightsSql();
			$userSql->addConditions ("UA.AR_PATH='$path'"); 
			$userSql->addConditions ("UA.AR_OBJECT_ID='$objectId'");
			$userSql->setSelectFields("UA.AR_VALUE");
			
			$userRow = Wdb::getRow($userSql);
			$userVal = $userRow["AR_VALUE"];
			
			$groupSql = $this->getGroupRightsSql();
			$groupSql->setSelectFields("UGA.AR_VALUE");
			$groupSql->addConditions ("UGA.AR_PATH='$path'"); 
			$groupSql->addConditions ("UGA.AR_OBJECT_ID='$objectId'");
			$groupData = Wdb::getData($groupSql);
			
			$maxVal = $userVal;
			foreach ($groupData as $cRow) 
				$maxVal = max($maxVal, $cRow["AR_VALUE"]);
			
			return $maxVal;
		}
		
		
		function getAvailableScreens($language = false) {
			if ($this->screens)
				return $this->screens;
			
			$language = $language ? $language : $this->getLanguage();						
			if ($this->isAdmin()) {
				$screens = array();
				$screens["AA"] = new MainAppScreen("AA", "CP", $language);
				$screens["UG"] = new MainAppScreen("UG", "UNG", $language);
				return $screens;				
			}
			
			$rights = new Rights($this->getId());
			$data = $rights->getApps(false, false);
			
			// Get the all dbkey applications
			$dbkeyApps = Wbs::getDbkeyObj()->getApplicationsList();
			
			// Intersect dbkey applications and getted from user rights apps
			$screens = array ();
			foreach ($data as $cRow) {
				$appId = $cRow["APP_ID"];
				$screenId = $cRow["SCREEN_ID"];
				
				if (in_array($appId, $dbkeyApps) && empty($screens[$appId])) {
					$screens[$appId] = new MainAppScreen($appId, $screenId, $this->getLanguage());
					if ($appId == "DD") {
						$screens[$appId]->Url = "../../2.0/backend.php";
					}
				}
			}
			$this->screens = $screens;
			
			uasort($screens, "wbsUserSortScreens");
			
			return $screens;
		}
		
		function hasAccessToApp($app) {
			$screens = $this->getAvailableScreens();
			foreach ($screens as $cApp => $cScreen) {
				if ($cApp == $app)
					return true;
			}
			return false;
		}
		
		function getDisplayName($short = true, $forceEmail = false, $addLineBreaks = false) {
			$data = $this->getData();
			
			$name = Users::getUserDisplayName ($data, $short, $forceEmail, $addLineBreaks);
			
			return $name;
		}
		
		public function getFirstPage() {
			$page = $this->getSetting(START_PAGE);
			if ($page == USE_LAST)
				$page = trim($this->getSetting(PAGE_LAST));
			
			if ( !strlen($page) || $page == USE_BLANK || $page == USE_TIPSANDTRICKS )
				return array("app" => "AA", "url" => PAGE_BLANK);

			$pageData = explode( "/", $page );
			$APP_ID = strtoupper( $pageData[0] );
			
			return array("app" => $APP_ID);
		}
		
		
		/**
		 * Returns user's groups
		 *
		 * @return array
		 */
		public function getGroups()
		{
			$sql = new CSelectSqlQuery ("UGROUP_USER", "U");
			$sql->innerJoin("UGROUP", "G", "U.UG_ID=G.UG_ID");
			$sql->setSelectFields("G.UG_ID, G.UG_NAME");
			$sql->addConditions("U.U_ID", $this->U_ID);
			return Wdb::getData($sql);
		}
		
	}
	
	function wbsUserSortScreens($a, $b) {
		return $a->Sorting > $b->Sorting;
	}
	
?>
