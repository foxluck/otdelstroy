<?php
	class DbkeyFiles { 
		private $dbkeyObj;
		private $systemFilesObj;
		private $attachmentsDir;
		private $publicAttachmentsDir;
		
		public function __construct($dbkeyObj, $systemFilesObj) {
			$this->dbkeyObj = $dbkeyObj;
			$this->systemFilesObj = $systemFilesObj;
			
			
			$this->attachmentsDir = sprintf( "%s".DIRECTORY_SEPARATOR."%s".DIRECTORY_SEPARATOR."attachments", $this->systemFilesObj->getDataPath(), $this->dbkeyObj->getDbkey() );
			$this->publicAttachmentsDir = sprintf( "%s/%s/attachments", WBS_DIR."/published/publicdata", $this->dbkeyObj->getDbkey() );
		}
		
		public function getAppAttachmentsDir($appId) {
			return sprintf("%s".DIRECTORY_SEPARATOR."%s", $this->attachmentsDir, strtolower($appId));
		}
		
		public function getKernelAttachmentsDir() {
			return $this->getAppAttachmentsDir("AA");
		}
		
		public function getAppAttachmentPath($appId, $filepath) {
			return $this->getAppAttachmentsDir($appId) . DIRECTORY_SEPARATOR . $filepath;
		}
		
	}
?>