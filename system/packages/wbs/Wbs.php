<?php
/*
	* WebAsyst main application instance class
	*	contains functions working with DBKEY and common installation settings
	*/
class Wbs
{
    /**
     * @var Dbkey
     */
    private static $dbkeyObj;
    private static $systemObj;

    public static function isHosted() 
    {
        return file_exists(WBS_ROOT_PATH . "/kernel/hosting_plans.php");
    }

    public static function getDbKey()
    {
        return self::getDbkeyObj()->getDbkey();
    }

    /**
			Load current DBKey 		
			@return bool successfully load dbkey
     **/
    public static function loadCurrentDbKey ()
    {
        //var_dump(Env::Cookie()); exit;
        $dbkey = null;
        self::$systemObj = new WbsSystem();
        if (!empty($_SESSION["wbs_dbkey"])) {
            $dbkey = $_SESSION["wbs_dbkey"];
        } elseif (Env::Cookie('wbs_login_host')) {
            $dbkey = Env::Cookie('wbs_login_host');
        }

        if (!$dbkey && defined("GET_DBKEY_FROM_URL")) {
            if (Env::Request("DB_KEY")) {
                $dbkey = Env::Request("DB_KEY", Env::TYPE_BASE64, false);
            } elseif (self::isHosted()) {
            	$subdomain = Env::getSubdomain();
            	$dbkey = self::getDbKeyByName($subdomain);
                if (!$dbkey) {
                    $dbkey = $subdomain;
                }
            } else {
            	$dbkey = self::$systemObj->getFrontendDbkey();
            }
        }
        if (!$dbkey) {
            return false;
        }
        self::$dbkeyObj = new Dbkey(self::$systemObj, strtoupper($dbkey));
        return self::$dbkeyObj->loadDBKey();
    }
    
    /**
     * Returns dbkey by dbname (subdomain)
     * 
     * @param $name - subdomain
     * @return string|null
     */
    public static function getDbKeyByName($name)
    {    	
			$name = strtolower($name);
			$dbkey = null;
/*		$file = WBS_DIR.'/dblist/dbnames';
		if ($name && file_exists($file)) {
			$fp = fopen($file, 'r');
			while (!feof($fp)) {
				$info = explode(' ', trim(fgets($fp)), 2);
				if($info[0] == $name && isset($info[1])) {
					$dbkey = $info[1];
					break;				
				}				
			}
			fclose($fp);
		}*/
			if (array_key_exists('HTTP_DBKEY', $_SERVER))
				$dbkey = $_SERVER['HTTP_DBKEY'];

			if (is_null($dbkey)) // Try to get dbkey from accounting database
			{
				$dbconf= Wbs::getSystemObj()->getAccountsDBCredentials();
				if (count($dbconf)) {
					$link = mysql_pconnect ($dbconf["HOST"], $dbconf["USER"], $dbconf["PASS"]) or die (mysql_error());
					$qr = mysql_query ("SELECT DB_KEY FROM ".$dbconf["DBNAME"].".MT_WAHOST_ACCOUNT WHERE ACC_NAME = '".$name."'", $link);
					if ($qr)
						if ($row = mysql_fetch_array($qr)) 
							$dbkey = $row[0];
				}
			}
    	return $dbkey;
    }
    
    /**
     * Returns dbkey by dbname (subdomain)
     * 
     * @param $name - subdomain
     * @return string|null
     */
    public static function getDbNameByDbKey($dbkey)
    {    	
			$dbname = null;
/*		$file = WBS_DIR.'/dblist/dbnames';
		if ($dbkey && file_exists($file)) {
			$fp = fopen($file, 'r');
			while (!feof($fp)) {
				$info = explode(' ', trim(fgets($fp)), 2);
				if(isset($info[1]) && $info[1] == $dbkey) {
					$dbname = $info[0];
					break;				
				}				
			}
			fclose($fp);
		}*/
			$dbconf= Wbs::getSystemObj()->getAccountsDBCredentials();
			if (count($dbconf)) {
				$link = mysql_pconnect ($dbconf["HOST"], $dbconf["USER"], $dbconf["PASS"]) or die (mysql_error());
				$qr = mysql_query ("SELECT ACC_NAME FROM ".$dbconf["DBNAME"].".MT_WAHOST_ACCOUNT WHERE DB_KEY = '".strtoupper($dbkey)."'", $link);
				if ($qr)
					if ($row = mysql_fetch_array($qr))
						$dbname = $row[0];
			}
    	return $dbname;
    }
    
    public static function getDbName()
    {
        if (!isset($_SESSION['wbs_dbname'])) {
            $dbkey = self::getDbkeyObj()->getDbkey();
            $_SESSION['wbs_dbname'] = self::getDbNameByDbKey($dbkey);
        }   
        return $_SESSION['wbs_dbname'];     
    }

    /**
     * @return Dbkey
     */
    public static function getDbkeyObj ()
    {
        return self::$dbkeyObj;
    }

    /**
     * @return WbsSystem
     */
    public static function getSystemObj ()
    {
        return self::$systemObj;
    }

    /**
		 Open Db connect to current DBKey database
     **/
    public static function connectDb ()
    {
        $dbconf = self::$dbkeyObj->getDbConfig();
        Wdb::connect($dbconf["HOST"], $dbconf["PORT"], $dbconf["DB_USER"], $dbconf["DB_PASSWORD"], $dbconf["DB_NAME"]);
        Wdb::setCharset("utf8");
    }

    /**
		 Load current user
     **/
    public static function loadCurrentUser ()
    {   	
        if (!self::checkCurrentUserSession() && !self::cookieAuthorize()) {
            Wbs::logout(true);
        }        
        $user_id = Env::Session("wbs_username");
        User::set($user_id);
       	GetText::load(User::getLang(), SYSTEM_PATH . "/locale", 'system', false);
        User::updateLastTime();
        $timezoneId = User::getSetting("TIME_ZONE_ID", false);       
		if ($timezoneId) {
			$timezoneDst = User::getSetting("TIME_ZONE_DST", false);
			$timeZone = new CTimeZone($timezoneId, $timezoneDst);
			CDateTime::setDefaultDisplayTimeZone($timeZone);
		    WbsDateTime::init($timeZone);
		} else {
			WbsDateTime::init();
		}
        CurrentUser::load($user_id, User::getInfo($user_id));
    }
    
    public static function cookieAuthorize()
    {
        if (Wbs::getDbkeyObj() && Wbs::getDbkeyObj()->getAdvancedParam('show_remember')) {
            $login = strtoupper(Env::Cookie('wbs_username'));
            $hash = Env::Cookie('wbs_hash');
            if ($login && $hash) {
	            $users_model = new UsersModel();
	            $user_info = $users_model->get($login);
	            if (!strcmp($hash, User::getHash($user_info))) {
	                User::authorizeByLogin($login);
	                return true;
	            }
            } 
        }
        return false;
    }

    public static function checkCurrentUserSession ()
    {
        $noExpire = false;
        if (! isset($_SESSION['timestamp']))
            return false;
        $lastStamp = $_SESSION['timestamp'];
        if (isset($_SESSION['NOEXPIRE']))
            $noExpire = $_SESSION['NOEXPIRE'];
        $dbTimeout = 0;
        if (isset($_SESSION["SESSION_EXPIRE_PERIOD"]))
            $dbTimeout = $_SESSION["SESSION_EXPIRE_PERIOD"];
        if ($dbTimeout == "SYSTEM") {
            $sessionTimeout = Wbs::getSystemObj()->getSessionTimeout();
            if ($sessionTimeout > 0)
                $dbTimeout = $sessionTimeout;
            else {
                $dbTimeout = 0;
                $noExpire = true;
            }
        }
        if (! $noExpire && strlen($dbTimeout) && (time() - $lastStamp) > $dbTimeout)
            return false;
        $_SESSION['timestamp'] = time();
        return true;
    }

    public static function logout ($expired = false)
    {
        setcookie("wbs_username", '', time() - 86400, '/');
        setcookie("wbs_hash", '', time() - 86400, '/');
        setcookie(session_name(), '', time() - 86400, '/');
        session_unset();        
        session_destroy();
        $redirectUrl = ($expired) ? "/common/html/scripts/expired.php" : "/login.php";
        if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
            print "{errorStr: 'Session is expired', errorCode: 'SESSION_TIMEOUT', redirectUrl: '" . Url::get($redirectUrl) . "'}";
            exit();
        } else {
            $redirectUrl .= '?from='.$_SERVER['REQUEST_URI']; 
            header("Location: " . Url::get($redirectUrl));
            exit();
        }
    }

    /**
     * Returns object 
     * 
     * @return DbkeyFiles
     */
    public static function getFS ()
    {
        return self::$dbkeyObj->files();
    }
    
    /**
     * Auth current user to application 
     * 
     * @param array|string $appId
     */
    public static function authorizeUser ($appId, $all = false)
    {
    	if ($appId != 'AA' && Wbs::getDbkeyObj()->getDaysToSuspend() < 0) {
    		Url::go('/AA/html/scripts/suspended.php');
    	}
    	if (!$all) {
	        CurrentUser::tryAuthorizeApp($appId);
    	}
	    User::setApp(is_array($appId)? $appId[0] : $appId);
	    if (User::getId()) {
        	User::setSetting(User::PAGE_LAST, $appId, '');
	    }
       	waLocale::init(User::getLang());
    }

    public static function publicAuthorize ($appId = false)
    {
        Kernel::incPackage("users");
        CurrentUser::loadPublic($appId);
        User::setApp($appId); 
       	waLocale::init(CurrentUser::getLanguage());
    }

    public static function getPublishedPath ($path)
    {
        return self::getSystemObj()->files()->getPublishedPath($path);
    }
    
    public static function hasApp($app_id)
    {
    	return in_array($app_id, self::getDbkeyObj()->getApplicationsList());
    }

    public static function getAppPath ($app, $path, $newVersion = false)
    {
        return self::getSystemObj()->files()->getAppPath($app, $path, $newVersion);
    }
    
    public static function getCountries($lang = false)
    {
    	$path = SYSTEM_PATH."/data/countries/";
    	$file = $lang ? $lang : User::getLang();
    	if (!file_exists($path.$file.".dat")) {
    		$file = "eng";
    	}
    	$data = file($path.$file.".dat", FILE_SKIP_EMPTY_LINES);
    	$result = array();
    	foreach ($data as $row) {
    		list($iso3, $iso2, $num, $name) = explode(" ", $row, 4);
    		$result[$iso3] = $name;
    	}
    	asort($result);
    	return $result;
    }
}
?>