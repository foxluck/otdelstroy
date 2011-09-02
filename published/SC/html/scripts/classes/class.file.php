<?php
	class File{

		/**
		 * Copy file
		 *
		 * @param string $src_file
		 * @param string $dest_file
		 * @return PEAR_Error | null
		 */
		function copy($src_file, $dest_file){

			$dirPath=pathinfo($dest_file);
			$dirPath=$dirPath['dirname'];
			$errStr='';
			if ( !self::_forceDirPath( $dirPath, $errStr ) )
				return PEAR::raiseError( $errStr, null, null, null, array('source_file' => $src_file, 'destination_file' => $dest_file) );
			$bool = copy($src_file, $dest_file);
			return $bool?null:PEAR::raiseError('msg_error_filecopy', null, null, null, array('source_file' => $src_file, 'destination_file' => $dest_file));
		}

		/**
		 * Move file
		 *
		 * @param string $src_file
		 * @param string $dest_file
		 * @return PEAR_Error | null
		 */
		function move($src_file, $dest_file){


			$res = $this->copy($src_file, $dest_file);
			if(PEAR::isError($res))return $res;

			$res = $this->remove($src_file);
			if(PEAR::raiseError($res)){

				$this->remove($dest_file);
				return $res;
			}
		}

		/**
		 * Delete file
		 *
		 * @param string $file
		 * @return PEAR_Error | null
		 */
		function remove($file){

			if(!file_exists($file))return null;
			$bool = unlink($file);
			return $bool?null:PEAR::raiseError('msg_error_fileremove', null, null, null, array('file' => $file));
		}

		function move_uploaded($src_file, $dest_file){

			$dirPath=pathinfo($dest_file);
			$dirPath=$dirPath['dirname'];
			$errStr='';

			if ( !self::_forceDirPath( $dirPath, $errStr ) )
				return PEAR::raiseError( $errStr, null, null, null, array('source_file' => $src_file, 'destination_file' => $dest_file) );

			$bool = move_uploaded_file($src_file, $dest_file);
			return $bool?null:PEAR::raiseError('msg_error_filemoveupload', null, null, null, array('source_file' => $src_file, 'destination_file' => $dest_file));
		}
		
		static function convert($file,$from,$to,$use_mb = false)
		{
			$converted = false;
			if($fp_source = fopen($file ,'rb')){
				if($fp_target = fopen($file.'_iconv' , 'wb')){
					while ($block = fgets($fp_source)){
						fwrite($fp_target, ($use_mb&&function_exists(mb_convert_encoding))?mb_convert_encoding($block,$to,$from):iconv($from, $to.'//IGNORE', $block));
					}
					$converted = true;
					fclose($fp_target);
				}
				fclose($fp_source);
			}
			if($converted){
				unlink($file);
				copy($file.'_iconv', $file);
				unlink($file.'_iconv');
			}
			return $converted;
		}
		
		/**
		 * Verify file uploading
		 *
		 * @param array $uploadFile $_FILES['upload_name'] or etc.
		 * @param string $extensions regexp pattern
		 * @return bool:PEAR::Error
		 */
		static function checkUpload($uploadFile,$extensions = null)
		{
			$Register = &Register::getInstance();
			/*@var $Register Register*/
			$FilesVar = &$Register->get(VAR_FILES);
			$error = null;
			do{

				if(!isset($uploadFile))return ;
				if(!isset($uploadFile['name']))return ;
				if(!$uploadFile['name'])return;

				if($uploadFile['error']){
					switch ($uploadFile['error']){
						case 1:$error='Target file exceeds maximum allowed size. (upload_max_filesize)';break;
						case 2:$error='Target file exceeds the MAX_FILE_SIZE value specified on the upload form.';break;
						case 3:$error='Target file was not uploaded completely.';break;
						case 4:$error='No target file was uploaded.';break;
						case 6:$error='Missing a temporary folder.';break;
						case 7:$error='Failed to write target file to disk.';break;
						case 8:$error='File upload stopped by extension.';break;
					}
					if(isset($error)){
						return PEAR::raiseError($error);
					}
				}
			}while(false);
			return true;
		}
		
		static function chmod($file, $permission = 0666){
			if(!file_exists($file))return false;
			return @chmod($file,$permission&0666);
		}


		static function _forceDirPath( $dirPath, &$errStr, $baseDir = null )
		
	//
	// Creates all folders leading to $dirPath
	//
	//		Parameters:
	//			$dirPath - path to folder
	//			$errStr - variable for storing error message
	//			$baseDir - base directory
	//
	//	Returns true, in case of success
	//
	{
		$currentDir=getcwd();
		if ( is_null($baseDir) )
			$baseDir = WBS_DIR;

		$baseDir = trim(str_replace(array('\\','//'),'/',$baseDir));
		$dirPath = trim(str_replace(array('\\','//'),'/',$dirPath));
		$strlen = strlen( $baseDir );
		if ( $baseDir[$strlen-1] == "/")
			$baseDir=substr( $baseDir, 0, --$strlen );
			
		if ( strcmp(strtolower(substr($dirPath, 0, $strlen)),strtolower($baseDir))==0 )
			$dirPath = substr( $dirPath, ++$strlen );
		
		$path=$dirPath;
		while (strpos($path,'\\')!==false) {
			$path=str_replace('\\','/',$path);
		}
		while (strpos($path,'//')!==false) {
			$path=str_replace('//','/',$path);
		}
		$dirs = explode('/', $path);
    	$dir=$baseDir.(strlen($baseDir)?'/':'');
    	$oldMask = @umask(0);
    	foreach ($dirs as $part) {
    		if(strlen($part)==0)
    			continue;
       		$dir.='/'.$part;
        	if (!is_dir($dir) && strlen($dir)>0)
        	{
            	if(!@mkdir($dir, 0777))
					$errStr = sprintf( "Unable to create directory %s", $dir );
            	@umask($oldMask);
            }
        }
		chdir( $currentDir );
		return (strlen($errStr)>0)?false:true;
	}


	}
?>