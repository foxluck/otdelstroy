<?php
	class MainAppScreen {
		public $Url;
		public $AppId;
		public $ScreenId;
		public $Name;
		public $Sorting;
		private $isAvailable;
		private $forMenu;
		private $mainColor;
		private $icon = false;
		
		public function __construct($appId, $screenId, $lang) {
			$this->AppId = $appId;
			$this->ScreenId = $screenId;
			
			$screensFilename = Wbs::getSystemObj()->files()->getAppPath($appId, "_screens.php");
			if (file_exists($screensFilename))
			{
				include($screensFilename);
				$info = $global_screens[$appId][$screenId];
				
				$this->Name = (!empty($info["NAME"][$lang])) ? $info["NAME"][$lang] : $info["NAME"]['eng'];
				
				$this->Url = $info["PAGE"];
				if (isset($info['ICON'])) {
					$this->icon = $info['ICON'];
				}
				$this->Sorting = isset($info["SORTORDER"]) ? $info["SORTORDER"] : 0;
				$this->isAvailable = true;
				$this->forMenu = empty($info["NOT_MENU"]);
				$this->mainColor = (!empty($info["MAINCOLOR"])) ? $info["MAINCOLOR"] : "orange";					
			} else {
				$this->isAvailable = false;
			}
		}
		
		public function getMainColor () {
			return $this->mainColor;
		}
		
		public function forMenu () {
			return $this->forMenu;
		}
		
		public function getUrl() {
			if (substr($this->Url, 0, 1) == '/') {
				return Url::get("/".$this->AppId . $this->Url);
			} else {
				return Url::get("/".$this->AppId . "/html/scripts/" . $this->Url);
			}
			
		}
		
		public function getEncodedUrl() {
			return base64_encode($this->getUrl());
		}
		
		public function getIconUrl() {
			$url = Url::get("/".$this->AppId.($this->icon ? $this->icon : "/html/img/".$this->AppId."40.gif"));
			return $url;
		}
	}
?>