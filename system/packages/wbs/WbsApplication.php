<?php
	class WbsApplication {
		private $appId;
		private $settingsXml;
		
		
		protected function __construct($appId) {
			$this->appId = $appId;
		}
		
		public function getId() {
			return $this->appId;
		}
		
		public function getScriptUrl($url, $params) {
			$url = $this->appId . "/html/scripts/$url";
			
			
			return WebQuery::getPublishedUrl ($url, $params);
		}
		
		public function getPath($path, $newVersion = false) {
			if ($newVersion)
				$path = "2.0/" . $path;
			return Wbs::getSystemObj()->files()->getAppPath($this->appId, $path);
		}
		
		public function getAttachmentsPath ($subdir = null) {
			$dir = Wbs::getDbkeyObj()->files()->getAppAttachmentPath($this->appId, $subdir);
			return $dir;
		}
		
		public function getSetting($settingName) {
			$xml = $this->getSettingsXml();
			if (isset($xml[$settingName]))
				return (string)$xml[$settingName];
			else
				return null;
		}
		
		public function getSettingsXml() {
			if ($this->settingsXml)
				return $this->settingsXml;
			
			$sql = new CSelectSqlQuery("APPSETTINGS");
			$sql->setSelectFields("SETTINGS");
			$sql->addConditions("APP_ID", $this->appId);
			$str = Wdb::getFirstField($sql);
			if (!$str)
				$str = "<APP_SETTINGS></APP_SETTINGS>";
			
			$this->settingsXml = simplexml_load_string($str);
			
			return $this->settingsXml;
		}
		
		public function setSetting($settingName, $value) {
			$xml = $this->getSettingsXml();
			$xml[$settingName] = $value;
			$this->saveSettingsXml();
		}
		
		public function saveSettingsXml() {
			$xml = $this->getSettingsXml();
			
			$sql = new CUpdateSqlQuery("APPSETTINGS");
			$sql->addFields(array_keys("SETTINGS", $xml->asXml()), array("SETTINGS"));
			Wdb::runQuery($sql);
		}
	}
?>