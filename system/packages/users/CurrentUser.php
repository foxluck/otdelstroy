<?php
	class CurrentUser {
		
		static private $U_ID;
		/**
		 * Instanse of current user
		 *
		 * @var WbsUser or PublicUser
		 */
		static private $instance = null;
		static private $app_id;
		
		static function load($U_ID, $info = array()) {
			$user = new WbsUser ($U_ID, $info);
			
			
			self::$instance = $user;
		}
		
		static function loadPublic($app_id) {
			self::$instance = new PublicUser();
			WbsDateTime::init();
			if ($app_id) {
				self::$app_id = $app_id;
			}
		}
		
		/**
		 * @return WbsUser
		 */
		static function getInstance() {
			if (!self::$instance) {
				return false;
				throw new RuntimeException ("First you need to load CurrentUser with method load");
			}
			return self::$instance;
		}
		
		static function getId () {
			$instance = self::getInstance();
			return $instance->getId();
		}
		
		static function getContactId () {
			$instance = self::getInstance();
			return $instance->getContactId();
		}		
		
		static function getAppId()
		{
			return self::$app_id;
		}
		
		static function getLanguage() {
			$instance = self::getInstance();
			return $instance->getLanguage();
		}
		
		static function getRow() {
		}
		
		static function getName() {
			$instance = self::getInstance();
			return $instance->getDisplayName();			
		}		
		
		static function tryAuthorizeApp($appId) {
			$instanse = self::getInstance();
			if (self::getId()) {
			    if (!is_array($appId)) {
			        $appId = array($appId);
			    }
			    $access = false;
			    foreach ($appId as $app) {
			        $access = $access || $instanse->hasAccessToApp($app);
			    }
			    
				self::$app_id = $appId[0];
			    
				if (!$access) {
					Url::go('/AA/html/scripts/blank.php');
				}			
			}
			return true;
		}
		
		/**
		 * Returns last time of visit to the application
		 *
		 * @param string $appId 
		 * @return int - unix timestamp
		 */
		static function getLastVisit($appId) {
			$instanse = self::getInstance();
			return $instanse->getLastVisit($appId);
		}
	}
?>