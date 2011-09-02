<?php
class DiagnosticTools
{
	var $toolsCollection = array();
	var $baseDir = '';
	function DiagnosticTools($baseDir = null)
	{
		if($baseDir&&($baseDir = realpath($baseDir))){
			$this->baseDir = $baseDir;
		}else{
			$this->baseDir = getcwd();
		}
	}
	
	/**
	 * Remove files from folders
	 *
	 * @param mixed $paths
	 * @param string $errorStr
	 * @return boolean
	 */
	function cleanCache($paths,&$errorStr,$removePattern = null,$skippedPattern = null,$cleanSubfolders = true)
	{
		$res = true;
		if(!is_array($paths)){
			$paths = array($paths);
		}
		//$errorStr .= "start<br>\n";
		foreach($paths as $dir){
			$count = 0;
			//$errorStr .= "scan {$this->baseDir}/{$dir}<br>\n";
			$dir = $this->baseDir.'/'.$dir;
			$path = substr(str_replace($this->baseDir,'',$dir,$count),$count?1:0);
			$files = file_exists($dir)?scandir($dir):array();
			$subFolders = array();
			foreach($files as $file){
				if(($file=='.')||($file=='..')){
					continue;
				}
				$fullPath = $dir.'/'.$file;
				//$errorStr .= "work around file {$path}/{$file}<br>\n";
				if(is_file($fullPath)&&
				(is_null($removePattern)||preg_match($removePattern,$file))
				&&(is_null($skippedPattern)||!preg_match($skippedPattern,$file))){
					if(!unlink($fullPath)){
					//if(false){
						$errorStr .= "Error unlink file {$path}/{$file}<br>\n";
						$res = false;
					}else{
						//$errorStr .= "attempt to unlink {$path}/{$file}<br>\n";
					}
				}elseif(is_dir($fullPath)&&$cleanSubfolders){
					$subFolders[] = $path.'/'.$file;
				}else{
					//$errorStr .= "skipped {$path}/{$file}<br>\n";
				}
			}
			if($subFolders){
				if(!$this->cleanCache($subFolders,$errorStr)){
					$res = false;
				}
			}
			
		}
		return $res;
	}

}

?>