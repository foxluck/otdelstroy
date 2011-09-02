<?php
	class WbsSystemFiles {
		private $dataPath;
		
		public function getKernel(){ 
		}
		
		public function initialize($dataPathTemplate) {
			$this->dataPath = str_replace( "[WBS_PATH]", realpath(WBS_DIR), $dataPathTemplate);
		}
		
		public function getDataPath (){
			return $this->dataPath;
		}
		
		public function getWbsPath ($filePath = null) {
			return FilesFunctions::fixPathSlashes(WBS_ROOT_PATH . DIRECTORY_SEPARATOR . $filePath);
		}
		
		public function getPublishedPath($path) {
			return $this->getWbsPath("published/" . $path);
		}
		
		public function getAppPath ($appId, $filepath) {
			$path = sprintf("%s%s", WBS_PUBLISHED_DIR, $appId);
			if ($filepath)
				$path .= DIRECTORY_SEPARATOR . $filepath;
			return $path;
		}
		
	}
?>