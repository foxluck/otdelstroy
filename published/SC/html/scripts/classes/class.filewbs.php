<?php
	class FileWBS extends File {
		function __onWebAsyst(){
			static $wa;
			if(!isset($wa)){
				//$wa=file_exists(WBS_DIR."kernel/classes/class.accountname.php");
				$wa = SystemSettings::is_hosted();
			}
			return $wa;
		}
		
		function __allowAddDiskUsage($size){
			if(!$this->__onWebAsyst())return true;
			
			$httpMsgClient = new WbsHttpMessageClient(SystemSettings::get('DB_KEY'),'wbs_msgserver.php');
			
			$httpMsgClient->putData('action', 'ALLOW_ADD_FILE');
			$httpMsgClient->putData('file_size', $size);
			
			$res = $httpMsgClient->send();
			
			return !(!$res || !$httpMsgClient->getResult('result'));
			return ($res&&$httpMsgClient->getResult('result'))||!$res;
		}
		
		function __addDiskUsage($size){
			if(!$this->__onWebAsyst())return true;
			
			$httpMsgClient = new WbsHttpMessageClient(SystemSettings::get('DB_KEY'),'wbs_msgserver.php');
			
			$httpMsgClient->putData('action', 'ADD_DISKUSAGE_RECORD');
			$httpMsgClient->putData('file_size', $size);
			
			$res = $httpMsgClient->send();

			return $res;
		}
		
		function __removeDiskUsage($size){
			if(!$this->__onWebAsyst())return true;
		
			$httpMsgClient = new WbsHttpMessageClient(SystemSettings::get('DB_KEY'),'wbs_msgserver.php');
			
			$httpMsgClient->putData('action', 'REMOVE_DISKUSAGE_RECORD');
			$httpMsgClient->putData('file_size', $size);
			
			$res = $httpMsgClient->send();
			
			return $res;
		}

		
		/**
		 * Public methods
		 */
		
		function copy($src_file, $dest_file){
			
			$res = $this->__allowAddDiskUsage(filesize($src_file));
			if(!$res)return PEAR::raiseError('msg_error_diskusage', null, null, null, array('source_file' => $src_file, 'destination_file' => $dest_file));
			
			if(file_exists($dest_file)){
				
				$res = $this->remove($dest_file);
				if(PEAR::isError($res))return $res;
			}
			
			$res = parent::copy($src_file, $dest_file);
			if(PEAR::isError($res))return $res;

			if(!$this->__addDiskUsage(filesize($dest_file))){
				parent::remove($dest_file);
				return PEAR::raiseError('msg_error_filecopy', null, null, null, array('source_file' => $src_file, 'destination_file' => $dest_file));
			}
		}
		
		function move_uploaded($src_file, $dest_file){
			
			if(!$this->__allowAddDiskUsage(filesize($src_file)))
				return PEAR::raiseError('msg_error_diskusage', null, null, null, array('source_file' => $src_file, 'destination_file' => $dest_file));
			
			if(file_exists($dest_file)){
				
				$res = $this->remove($dest_file);
				if(PEAR::isError($res))return $res;
			}
				
			$res = parent::move_uploaded($src_file, $dest_file);
			if(PEAR::isError($res))return $res;

			if(!$this->__addDiskUsage(filesize($dest_file))){
				parent::remove($dest_file);
				return PEAR::raiseError('msg_error_filemoveupload', null, null, null, array('source_file' => $src_file, 'destination_file' => $dest_file));
			}
		}
		
		function remove($file){
			
			$file_size = filesize($file);
			
			$res = parent::remove($file);
			if(PEAR::isError($res))return $res;
			
			$res = $this->__removeDiskUsage($file_size);
			
			if(!$res)return PEAR::raiseError('msg_error_fileremove', null, null, null, array('file' => $file));
		}

	}
?>