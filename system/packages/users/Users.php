<?php
	class Users {
		static $usersData;
		static $usersNames;
		
		public static function getUsersData () {
			if (!self::$usersData) {			
				$sql= new CSelectSqlQuery ("WBS_USER", "U");
				$sql->innerJoin("CONTACT", "C", "U.C_ID=C.C_ID");
				self::$usersData = Wdb::getData($sql);
			}
			
			return self::$usersData;
		}
		
		public static function getUsername($uId) {
			if (empty(self::$usersNames)) {
				$usersData = self::getUsersData();
			
				foreach ($usersData as $cRow) {
					self::$usersNames[$cRow["U_ID"]] = self::getUserDisplayName($cRow);
				}
			}
			if (isset(self::$usersNames[$uId]))
				return self::$usersNames[$uId];
			else
			return $uId;
		}
		
		public static function getUserDisplayName ($data, $short = true, $forceEmail = false, $addLineBreaks = false) {
			$result = array();

			if (strlen($data['C_FIRSTNAME'])) $result[] = $data['C_FIRSTNAME'];
			if (strlen($data['C_MIDDLENAME'])) $result[] = mb_substr( $data['C_MIDDLENAME'], 0, 1 ).".";
			if (strlen($data['C_LASTNAME']) ) $result[] = $data['C_LASTNAME'];

			$namePartsExists = count($result);

			if ( !$namePartsExists || !$short ) {
				if ( isset($data['C_NICKNAME']) && strlen($data['C_NICKNAME']) )
					$result[] =  ( $namePartsExists ) ?	"(".$data['C_NICKNAME'].")" : $data['C_NICKNAME'];
			}

			$namePartsExists = count($result);

			if ( !$short ) {
				if ( isset($data['C_EMAILADDRESS']) && strlen($data['C_EMAILADDRESS']) )
					if ( !$namePartsExists )
						$result[] = $data['C_EMAILADDRESS'];
			} else
				if ( !$namePartsExists )
					if ( isset($data['C_EMAILADDRESS']) && strlen($data['C_EMAILADDRESS']) )
						$result[] = $data['C_EMAILADDRESS'];

			if ( $forceEmail )
				if ( isset($data['C_EMAILADDRESS']) && strlen($data['C_EMAILADDRESS']) )
					$result[] = '<'.$data['C_EMAILADDRESS'].'>';

			if ( !$addLineBreaks )
				$result = implode( " ", $result );
			else
				$result = implode( "\n", $result );

			return trim( $result );
		}
				
		/**
		 * Return user class
		 *
		 * @param int $C_ID - contact id
		 * @return WbsUser
		 */
		public static function getUserByCID($C_ID) {
			$sql = new CSelectSqlQuery ("WBS_USER", "U");
			$sql->setSelectFields("U_ID");
			$sql->innerJoin("CONTACT", "C", "U.C_ID=C.C_ID");
			$sql->addConditions("U.C_ID", $C_ID);
			$uId = Wdb::getFirstField($sql);
		
			$user = new WbsUser($uId);
			return $user;
		}
		
		public static function getLastVisit($U_ID, $APP_ID = false) {
			
			$sql = new CSelectSqlQuery("USER_SETTINGS");
			$sql->addConditions("U_ID", $U_ID);
			$sql->addConditions("NAME", "LAST_TIME");				
			if ($APP_ID) {
				$sql->setSelectFields("VALUE");
				$sql->addConditions("APP_ID", $APP_ID);
			} else {
				$sql->setSelectFields("MAX(VALUE)");
			}
			return Wdb::getFirstField($sql);			
		}
		
		/**
		 * Returns all users
		 * 
		 * @return array
		 */
		public function getAllUsers()
		{
			$sql = new CSelectSqlQuery("WBS_USER");
			return Wdb::getData($sql);			
		}
		
	}
?>