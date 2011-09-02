<?php
	class RightsMask {
		public static function getRightsStr($rightsValue, $short = false) {
			waLocale::loadFile(Wbs::getPublishedPath("common/templates/localization"), "common");
			switch ($rightsValue) {
				case 0:
					return ($short) ? "N" : waLocale::getStr("common", "rights_no");
				case 1:
					return ($short) ? "R" : waLocale::getStr("common", "rights_read");
				case 3:
					return ($short) ? "RW" : waLocale::getStr("common", "rights_write");
				case 7:
					return ($short) ? "RWF" : waLocale::getStr("common", "rights_full");
				default: 
					return "Unknown rights: " + rightsValue;
			}
		}
		
		public static function hasReadRights($rightsValue) {
			return $rightsValue > 0;
		}
		
		public static function hasWriteRights($rightsValue) {
			return $rightsValue >= 3;
		}
		
		public static function hasFolderRights($rightsValue) {
			return $rightsValue == 7;
		}
	}
?>