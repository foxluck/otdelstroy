<?php

/**
 * Class User
 * 
 * @copyright WebAsyst © 2008-2009
 * @author WebAsyst Team
 * @version SVN: $Id: User.class.php 9970 2010-09-21 06:01:03Z alexmuz $
 */
class User
{
	
	const CURRENT = 'CURRENT';
	const ONLINE_TIMEOUT = 300; // in sec

	const PAGE_BLANK = "AA/html/scripts/blank.php";
	const PAGE_TIPSANDTRICKS = "AA/html/scripts/tipsandtricks.php";
	const PAGE_LAST = "LAST_PAGE";
	const START_PAGE = "START_PAGE";
	const USE_BLANK = "BLANK";
	const USE_LAST = "LAST";
	const USE_TIPSANDTRICKS = "TIPSANDTRICKS";	
	
    const STATUS_LOCKED = 2;
    const STATUS_INVITED = 3;	
	
	protected static $users = array();
	protected static $settings = array();
	protected static $user_id = null;
	protected static $app_id = null;
	
	
	public static function authorize($app_id)
	{
		self::$app_id = $app_id;
	}
	
	/**
	 * System authorize user by info
	 * 
	 * @param $info
	 * @param $app_id
	 */
	public static function authorizeByInfo($info, $app_id = false)
	{
		self::$user_id = $info['U_ID'];
		if ($app_id) {
			self::$app_id = $app_id;
		}
		self::$users[self::$user_id] = Contact::getInfo($info['C_ID'], $info);
	}
	
	public static function set($user_id)
	{
		self::$user_id = $user_id;
	}
	
	public static function updateLastTime()
	{
	    if (time() - Env::Session('LAST_TIME') > (self::ONLINE_TIMEOUT / 2)) {
	        Env::setSession('LAST_TIME', time());
	        User::setSetting('LAST_TIME', time(), '');
	    }
	}

	public static function authorizeByLogin($login) 
	{
        @session_start();
		$_SESSION["wbs_username"] = $login;
		$_SESSION["wbs_dbkey"] = Wbs::getDbkeyObj()->getDbkey();
		$_SESSION["timestamp"] = time();
		$_SESSION["LOGIN_PAGE_URL"] =  "";
	}
	
	public static function getHash($info)
	{
	    return md5($info['U_PASSWORD']);
	}
	
	/**
	 * Returns id of the current user
	 * 
	 * @return array
	 */
	public static function getId()
	{
		return self::$user_id;	
	}
	
	/**
	 * Returns contact id of the current user
	 * 
	 * @return array
	 */
	public static function getContactId($user_id = false)
	{
		return self::getInfo($user_id, 'C_ID');
	}
	
	/**
	 * Return current application id
	 * 
	 * @return string
	 */
	public static function getAppId()
	{
		return self::$app_id;
	}
	
	public static function setApp($app_id)
	{
		self::$app_id = $app_id;
	}
	
	/**
	 * Returns display info of the user
	 * 
	 * @param string $user_id - id of the user
	 * @param string $field - name of the field in database
	 * @return array
	 */
	public static function getInfo($user_id = false, $field = false)
	{
		if (!$user_id && !($user_id = self::$user_id)) {
			return $field ? null : array();
		}
		if (!isset(self::$users[$user_id])) {
			$users_model = new UsersModel();
			$info = $users_model->get($user_id);
			if ($info['C_EMAILADDRESS']) {
				$contacts_model = new ContactsModel();
				$info['C_EMAILADDRESS'] = array($info['C_EMAILADDRESS']) + $contacts_model->getEmail($info['C_ID'], $info['C_EMAILADDRESS']);
				$info['C_EMAILADDRESS'] = array_unique($info['C_EMAILADDRESS']);
			} else {
				$info['C_EMAILADDRESS'] = array();
			}
			if ($user_id == 'ADMINISTRATOR') {
				$info['C_ID'] = 'ADMINISTRATOR'; 
				$info['CT_ID'] = 1;
				$info['C_FULLNAME'] = _s('Administrator');
				$info['C_FIRSTNAME'] = _s('Administrator');
				$info['C_CREATEDATETIME'] = false;
				$info['C_CREATEAPP_ID'] = false;
				$info['C_CREATEMETHOD'] = false;
				$info['C_CREATECID'] = false; 
			}
			self::$users[$user_id] = Contact::getInfo($info['C_ID'], $info);			
		}
		if ($field) {
			return isset(self::$users[$user_id][$field]) ? self::$users[$user_id][$field] : "";
		}
		return self::$users[$user_id];
	}

	/**
	 * Returns main name of the user
	 *  
	 * @see Contact::getName($contact_id, $n)
	 * @param string $user_id - id of the user
	 * @return string
	 */
	public static function getName($user_id = false, $tags_disable = false)
	{
		if ($user_id || ($user_id = self::$user_id)) {		
			$info = User::getInfo($user_id);
			$name = Contact::getName($info['C_ID']);
			return $tags_disable ? strip_tags($name) : $name;
		} else {
			null;
		}
	}
	
	public static function getEmail($with_name = false)
	{
		if ($with_name) {
			return Contact::getName(self::getContactId(), Contact::FORMAT_NAME_EMAIL, false, false);
		} else {
			$email = self::getInfo(false, 'C_EMAILADDRESS');
			return isset($email[0]) ? $email[0] : ""; 
		}
	}

	/**
	 * Returns setting
	 * 
	 * @param string $app_id
	 * @param string $name
	 * @param string $user_id - if false, then current user
	 * @return string
	 */
	public static function getSetting($name, $app_id = self::CURRENT, $user_id = false)
	{
		// For right access to index of the array
		if ($app_id == 'CURRENT') {
			$app_id = self::$app_id;
		}
		if (!$app_id) {
			$app_id = "  ";
		}
		if ($user_id === false) {
			$user_id = self::$user_id;
		}
		
		if (!isset(self::$settings[$user_id][$app_id]) || !isset(self::$settings[$user_id][$app_id]['load'])) {
			$user_settings_model = new UserSettingsModel();
			self::$settings[$user_id][$app_id] = $user_settings_model->getAll($user_id, trim($app_id));
			self::$settings[$user_id][$app_id]['load'] = true;
		}
		return isset(self::$settings[$user_id][$app_id][$name]) ? self::$settings[$user_id][$app_id][$name] : "";
	}	
	
	
	public static function addMetric($action_type, $client_type = 'ACCOUNT', $data = '') 
	{
	    if (Wbs::isHosted()) {
		    $metric = metric::getInstance();
		    $metric->addAction(Wbs::getDbKey(), self::$user_id, self::$app_id, $action_type, $client_type, $data);
	    }
	}
	
	/**
	 * Returns language of the user
	 * 
	 * @param $user_id
	 * @return string (ISO3)
	 */
	public static function getLang($user_id = false)
	{
		if ($user_id === false) {
			$user_id = self::$user_id;
		}
		if (!self::$user_id) {
		    $lang = Env::Get('lang');
		} else {
		    $lang = self::getSetting('language', false, $user_id);
		}
		// for to support old versions
		if ($lang == 'gem') {
		    $lang = 'deu';
		}
		return $lang ? $lang : Wbs::getDbkeyObj()->getLanguage();
	}
	
	
	public static function getStartPage($app_ids)
	{	
		if (self::$user_id) {
			$page = self::getSetting(self::START_PAGE);
			if ($page == self::USE_LAST) {
				$page = trim(self::getSetting(self::PAGE_LAST));
			}
			if ( !strlen($page) || $page == self::USE_BLANK || $page == self::USE_TIPSANDTRICKS ) {
				return array("app" => "AA", "url" => self::PAGE_BLANK);
			}
			$page_array = explode("/", $page);
			$app_id = strtoupper($page_array[0]);
		} else {
			return array("app" => "AA", "url" => self::PAGE_BLANK);
		}		
		if (!in_array($app_id, $app_ids)) {
			return array("app" => "AA", "url" => self::PAGE_BLANK);
		}
		return array("app" => $app_id);	
	}
	
	public static function setSetting($name, $value, $app_id = self::CURRENT, $user_id = false)
	{
		if ($app_id == self::CURRENT) {
			$app_id = self::$app_id;
		}
		if (!$app_id) {
			$app_id = "  ";
		}
		if ($user_id === false) {
			$user_id = self::$user_id;
		}
		self::$settings[$user_id][$app_id][$name] = $value;
		$user_settings_model = new UserSettingsModel();
		$user_settings_model->set($user_id, $app_id, $name, $value);
	}
	
	public static function unsetSetting($name, $app_id = 'CURRENT', $user_id = false)
	{
		if ($app_id == 'CURRENT') {
			$app_id = self::$app_id;
		}
		if (!$app_id) {
			$app_id = "  ";
		}
		if ($user_id === false) {
			$user_id = self::$user_id;
		}
		if (isset(self::$settings[$user_id][$app_id][$name])) {
			unset(self::$settings[$user_id][$app_id][$name]);
		}
		$user_settings_model = new UserSettingsModel();
		$user_settings_model->delete($user_id, $app_id, $name);
	}	
	
	public static function getUsers()
	{
		
	}
	
	public static function getGroups($user_id = false)
	{
		if ($user_id === false) {
			$user_id = self::$user_id;
		}		
		$users_model = new UsersModel();
		return $users_model->getGroups($user_id);
	}
	
	/**
	 * Returns true if the user has admin rights for the application
	 * 
	 * @param $user_id - user
	 * @param $app_id - application
	 * @param $mode - mode of getting right (for only optimization)
	 * @return bool
	 */
	public static function isAdmin($app_id = false, $mode = false, $user_id = false) 
	{
		if ($user_id === false) {
			$user_id = self::$user_id;
		}	 		
		if ($app_id === false) {
			$app_id = self::$app_id;
		}			
		$rights = new Rights($user_id, Rights::USER);
		return $rights->get($app_id, Rights::FUNCTIONS, 'ADMIN', $mode ? $mode : Rights::MODE_ONE, Rights::RETURN_INT);
	}
	
	/**
	 * Check rights of the user
	 * 
	 * @param $app_id
	 * @param $section_id
	 * @param $object_id
	 * @return int
	 */
	public static function hasAccess($app_id, $section_id = false, $object_id = false)
	{
		$rights = new Rights(self::$user_id, Rights::USER);
		if (!$section_id) {
			$section_id = 'SCREENS';
			$app_info = $rights->getApplicationInfo($app_id);
			if (!$app_info) {
				return 0;  
			}
			$object_id = $app_info['SCREEN_ID'];
			if (!$object_id) {
				return 0;
			}
		}
		return $rights->get($app_id, $section_id, $object_id, Rights::MODE_ONE, Rights::RETURN_INT);
	}
	
	/**
	 * Check limit to add $n users
	 * Throw new Exception if no limits 
	 * 
	 * @param $n
	 */
	public static function checkLimits($n = 1)
	{
		$limit =  Limits::get("AA", "USERS");
		if ($limit) {
			$users_model = new UsersModel();
			$count = $users_model->getQueryConstructor()->count();
			if ($count + $n > $limit) {
				throw new LimitException(_("Number of users can not exceed ").$limit.".");
			}			
		}
	}
	
	public static function delete($user_ids)
	{
		// User Settings
		$user_settings_model = new UserSettingsModel();
		$user_settings_model->deleteByUserId($user_ids);
		// User Rights
		$user_rights_model = new UserRightsModel();
		$user_rights_model->deleteByUserId($user_ids);
		// User Group
		$user_groups_model = new UserGroupsModel();
		$user_groups_model->deleteByUserId($user_ids);
		// User Quota
		$disk_quota_model = new DiskQuotaModel();
		$disk_quota_model->deleteByUserId($user_ids);
		// User
		$users_model = new UsersModel();
		$users_model->delete($user_ids);
	}
	
	
	/**
	 * Returns last time of the activity of the user 
	 * 
	 * @param $app_id
	 * @param $user_id
	 * @return int - unix timestamp
	 */
	public static function getLastTime($user_id, $app_id = false) 
	{
		if ($app_id) {
			return self::getSetting('LAST_TIME', $app_id, $user_id);
		} else {
			$user_settings_model = new UserSettingsModel();
			return $user_settings_model->getMax($user_id, 'LAST_TIME');
		}				
	}
	
	/**
	 * Change login and returns new login or throw exception
	 * 
	 * @param $user_id - old login
	 * @param $login - new login
	 * @return string
	 */
	public static function changeLogin($user_id, $login)
	{
		$login = mb_strtoupper($login);
        if (!preg_match("/^[A-Z0-9_]+$/i", $login) && !preg_match("/\\\$INVITED[0-9]+/", $login)) {
        	throw new Exception(_("Latin letters and numbers only, no spaces."));
        }
        // Change login in main table of the users
		$users_model = new UsersModel();
		$users_model->changeLogin($user_id, $login);
		// User Settings
		$user_settings_model = new UserSettingsModel();
		$user_settings_model->changeLogin($user_id, $login);
		// User Rights
		$user_rights_model = new UserRightsModel();
		$user_rights_model->changeLogin($user_id, $login);
		// User Group
		$user_groups_model = new UserGroupsModel();
		$user_groups_model->changeLogin($user_id, $login);
		// User Quota
		$disk_quota_model = new DiskQuotaModel();
		$disk_quota_model->changeLogin($user_id, $login);
		
		return $login;
	}
}


?>