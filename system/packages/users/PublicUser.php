<?php
	class PublicUser implements IUser {
		private $language;
		
		public function __construct() {
		}

		public function getId () {
			return null;
		}
		
		public function isAdmin() {
			return false;
		}
		
		public function isPublic() {
			return true;
		}
		
		public function getTimeZone() {
			return false;
		}		
		
		public function getLanguage() {
			return Wbs::getDbkeyObj()->getLanguage();
		}

		public function getLastVisit($appId = false) {
			return false; 		
		}
	}
?>