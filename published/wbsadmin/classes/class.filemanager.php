<?php
class FileManager
{
	var $path = '';
	var $basePath = null;
	var $linesPerPage = 200;
	var $lines = 0;
	/**
	 * Maximum filesize in bytes for edit via browser
	 *
	 */
	const MAX_EDITABLE_FILESIZE = 524288;

	function FileManager($basePath = null)
	{
		if($basePath&&($basePath = realpath($basePath))){
			$this->basePath = $basePath;
		}else{
			$this->basePath = getcwd();
		}
		$this->basePath = preg_replace('@[/\\\\]+@',DIRECTORY_SEPARATOR,$this->basePath);
		//$this->basePath = str_replace('\\','/',$this->basePath);
	}
	function setFile($path = '')
	{
		$this->path = $path;
		$this->id = md5($this->path);
		if($this->basePath){
			$count = 0;
			$this->path = str_replace($this->basePath,'',$this->path);
		}
		if(file_exists($path)){
			$this->fileName = basename($path);
			$this->exists = true;
			$this->size = filesize($path);
			$size = $this->size;
			while($size){
				$this->printSize = sprintf(($size>1000?'&nbsp;%03d':'%d'),$size%1000).$this->printSize;
				$size = ($size-$size%1000)/1000;
			}
			$this->perms = fileperms($path);
			$this->writable = is_writable($path);
			
		}else{
			$this->exists = false;
			$this->writable = is_writable(dirname($path));	
		}
		
	}
	
	function delete($file)
	{
		$res = false;
		if(isset($file['fullpath'])){
			$path = $file['fullpath'];
			if(file_exists($path)){
				$res = @unlink($path);
			}
		}
		return $res;
	}
	
	function rename($file,$newName = null)
	{
		$res = false;
		if(isset($file['fullpath'])){
			$path = $file['fullpath'];
			$newpath=preg_replace('/(\.[^\.]+)$/i','.'.date('(Y.m.d H.i.s)').'\\1',$path);
			if(file_exists($path)&&!file_exists($newpath)){
				$res = @rename($path,$newpath);
			}
			
		}
		return $res;
	}
	
	function backup($file)
	{
		$res = false;
		if(isset($file['fullpath'])){
			$path = $file['fullpath'];
			$newpath=preg_replace('/(\.[^\.]+)$/i','.'.date('(Y.m.d H.i.s)').'\\1',$path);
			if(file_exists($path)&&!file_exists($newpath)){
				$res = copy($path,$newpath);
			}
			
		}
		return $res?$newpath:null;
	}
	
	function view($file, &$navigator, $page = 0, $navigatorUrl = '', $editMode = false,$linesPerPage = null)
	{
		//$path = ($this->basePath?$this->basePath.'/':'').$path;
		$path = isset($file['fullpath'])?$file['fullpath']:'';
		if($file['size']>FileManager::MAX_EDITABLE_FILESIZE){
			$editMode = false;
		}
		$navigatorUrl .= ((!strlen($navigatorUrl)||(strpos($navigatorUrl,'?')===false ))?'?':'&amp;').'id='.$file['id'];
		$linesPerPage = intval($linesPerPage);
		$page = intval($page);
		$page = $page<0?0:$page;
		if($linesPerPage){
			$this->linesPerPage = $linesPerPage;
		}else{
			$linesPerPage = $this->linesPerPage;
		}
		
		
		$fileContentStrings = array();
		$lines = 0;
		$currentLine = 0;
		if(file_exists($path)&&!is_dir($path)&&($fp=fopen($path,'rb'))){
			while($line = fgets($fp)){
				if($editMode||(($lines++/$linesPerPage-1)<$page)){
					if((++$currentLine>$this->linesPerPage)&&(!$editMode)){
						$currentLine = 1;						
					}
					$fileContentStrings[$currentLine] = /*"[{$lines}]:[{$currentLine}] ".*/$line;
				}
			}
			fclose($fp);
			$fileContent = implode('',array_slice($fileContentStrings,0,$currentLine));
			unset($fileContentStrings);
						
			if(magic_quotes_runtime()){
				$fileContent = stripslashes($fileContent);
			}
			$page=min($page,floor($lines/$linesPerPage));
			$navigator = $this->getNavigator($page,$lines,$navigatorUrl);
		}else{
			$fileContent = get_class($this).': error read '.$path."\n";
		}
		return $fileContent;
	}
	function save($file,&$errorStr)
	{
		//write to copy of file
		$perm = sscanf($file['perm'],'%3o');
		if(isset($perm[0])){
			$perm = $perm[0];
		}else{
			$perm = 0644;
		}
		return $this->_safeWrite($file['fullpath'],$file['content'],$errorStr,$perm);
	}
	function download($file)
	{
		header('Content-type: application/force-download');
		header('Content-Transfer-Encoding: Binary');
		header('Content-length: '.$file['size']);
		header('Content-disposition: attachment; filename='.$file['name']);
		readfile($file['fullpath']);
		exit();
	}
	
	function getNavigator($curentPage,$lineCount,$url = '')
	{
		if(strlen($url)){
			if(strpos($url,'?')===false){
				$url .='?';
			}
		}else{
			$url = '?';
		}
		$links = array();
		$totalPageCount = ceil($lineCount/$this->linesPerPage);
		if($totalPageCount<=1){
			return '';
		}
		for($page = 0;($page <5)&&(($page)<$totalPageCount);$page++){
			$links[$page] = true;
		}
		for($page = ($totalPageCount-1);(($page+5)>=$totalPageCount)&&($page>=0);$page--){
			$links[$page] = true;
		}
		for($page = ($curentPage-2);($page<=($curentPage+2));$page++){
			if($page>=0&&$page<$totalPageCount){
				$links[$page] = true;
			}
		}
		ksort($links);
		$prevPage = 0;
		$navigator = '';
		foreach($links as $page => $link){
			if($prevPage!=$page){
				$navigator .= '&nbsp;...&nbsp;';
			}
			if($page == $curentPage){
				$navigator .= '<b>'.(++$page).'</b>&nbsp;';
			}else{
				$navigator .= '<a href="'.$url.'&amp;page='.($page++).'">'.$page.'</a>&nbsp;';
			}
			$prevPage = $page;
		}
		if($curentPage>0){
			$navigator = '<a href="'.$url.'&amp;page='.($curentPage-1).'">&larr;</a>&nbsp;'.$navigator;
		}else{
			$navigator = '&nbsp;&nbsp;&nbsp;'.$navigator;
		}
		if(($curentPage+1)<$totalPageCount){
			$navigator .= '<a href="'.$url.'&amp;page='.($curentPage+1).'">&rarr;</a>';
		}
		$rowInfo = sprintf(translate('diagnostic_file_row_info'),$curentPage*$this->linesPerPage+1,min($lineCount,($curentPage+1)*$this->linesPerPage),$lineCount);
		
		return $rowInfo.'&nbsp;&nbsp;&nbsp;'.$navigator;
	}
	
	function chmod($paths,&$error)
	{
		$count = 0;
		ob_start();
		foreach($paths as $pathInfo){
			$count += $this->_chmod(str_replace('//','/',$this->basePath.'/'.$pathInfo['path']),$pathInfo['perm'],$pathInfo['recursive']);
		}
		$error .= ob_get_contents();
		ob_end_clean();
		return $count;
	}
	
	function _chmod($paths,$permision,$recursive)
	{
		$count = 0;
		
		if(!is_array($paths)){
			$paths = array($paths);
		}
		foreach($paths as $path){
			if(!$path){
				continue;
			}
			$r_path = str_replace('\\','/',strrev($path));
			if((strpos($r_path,'../')===0)||(strpos($r_path,'./')===0)){
				continue;
			}
			if(!file_exists($path)){
				print sprintf(translate('filemanager_path_not_found'),$path)."<br>\n";
			}else{
				$is_dir = is_dir($path);
				$count++;
				if(!chmod($path,$permision&($is_dir?0777:0666))){
					$count--;
					print sprintf(translate('filemanager_cannot_change_perms'),$path)."<br>\n";
					if($is_dir&&$recursive){
						print sprintf(translate('filemanager_subfolders_skipped'),$path)."<br>\n";
					}
				}elseif($is_dir&&$recursive){
					$subPath = scandir($path);
					$subPath = array_map(create_function('$row','return (($row==\'.\')||($row==\'..\'))?null:\''.$path.'/\'.$row;'),$subPath);
					if($subPath){
						$count += $this->_chmod($subPath,$permision,$recursive);
					}
				}
			}
		}
		return $count;
	}
	
	function getIcon($file,$isDir = false)
	{
		static $existIcons = array();

		if($isDir){
			$type = 'folder';
			if(!isset($existIcons[$type])){
				$iconPath = '../../../common/html/res/images/folder_open.gif';
				if(file_exists($iconPath)){
					$existIcons[$type] = array('src'=>$iconPath,'alt'=>$type);
				}else{
					$existIcons[$type] = null;
				}
			}
		}else{
			$defaultIconPath = '../../../common/html/thumbnails/common.win.16.gif';
			if(preg_match('/([^\?]*)\.([^\.]+)/',$file,$matches)){
				$type = $matches[2];
					
			}else{
				$type = 'common';
			}
		
			if(!isset($existIcons[$type])){
				$iconPath = sprintf('../../../common/html/thumbnails/%s.win.16.gif',$type);
				if(file_exists($iconPath)){
					$existIcons[$type] = array('src'=>$iconPath,'alt'=>$type);
				}elseif(file_exists($defaultIconPath)){
					$existIcons[$type] = array('src'=>$defaultIconPath,'alt'=>$type);
				}else{
					$existIcons[$type] = null;
				}
			}
		}
		return $existIcons[$type];
	}
	function getAllowedActions($fileInfo,$isWritable = true)
	{
		global $messageStr;
		static $actionCondition = array(
			'edit'		=>array('pattern'=>'/(^.htaccess)|(\.php$)|(\.[p]{0,1}htm[l]{0,1}$)|(\.txt$)|(\.ini$)|(\.css$)|(\.js$)/i',
								'link'=>'%url%&amp;action=edit&amp;id=%id%&amp;path=%encodedpath%',
								'icon'=>'../../../common/html/res/images/edit.gif',
								'target'=>'_self'),								
			'view'		=>array('pattern'=>'/(readme)|(^\.[\w]+)|(\.(([p]{0,1}htm[l]{0,1})|(log)|(sql)|(xml))$)/i',
								'link'=>'%url%&amp;action=view&amp;id=%id%&amp;path=%encodedpath%',
								'icon'=>'../../../common/html/res/images/album.gif',
								'target'=>'_self'),
			'open'		=>array('pattern'=>'/\.(gif$)|(jp[e]{0,1}g$)|([tg]i[f]{1,2}$)|(png$)|(bmp$)|(png$)|(png$)/i',
								'link'=>'../../../../%path%/%name%',
								'icon'=>'../../../common/html/res/images/add.gif',
								'target'=>'_blank'),
			'download'	=>array('pattern'=>'/[^\?]+/i',
								'link'=>'%url%&amp;action=download&amp;id=%id%&amp;path=%encodedpath%',
								'icon'=>'../../../common/html/res/images/download.gif',
								'target'=>'_blank'),
			'delete'	=>array('pattern'=>'/(\.(temp$))|(^\.update_state$)|((^\.cache\.)|(^install\.php$)|(^wbs\.tgz$))|(^serlang[\d]+_[\w]{32}\.cch)|(\.log$)|(^\.settings\.[A-Z0-9]+$)/i',
								'link'=>'%url%&amp;action=delete&amp;id=%id%&amp;path=%encodedpath%',
								'icon'=>'../../../common/html/res/images/delete.gif',
								'target'=>'_self'));
		
								
		$allowedActions = array('default'=>array('link'=>null,'name'=>'none','icon'=>''));								
		foreach($actionCondition as $action=>$Condition){
			/*
			if((($action=='_delete')||($action=='_edit'))&&!$isWritable){
				$allowedActions[$action] = array('link'=>'','icon'=>'');
				continue;
			}
			*/
			if(strlen($Condition['pattern'])&&preg_match($Condition['pattern'],$fileInfo['name'])){
				$allowedActions[$action] = array('link'=>str_replace('\\','/',preg_replace('/%([^%]+)%/e','isset($fileInfo[\'\\1\'])?$fileInfo[\'\\1\']:\'%\\1%\'',$Condition['link'])),
												'icon'=>$Condition['icon'],
												'target'=>$Condition['target'],
												'name'=>$action);
				if(!$allowedActions['default']['link']&&($action!='delete')){
					$allowedActions['default']=$allowedActions[$action];
					$allowedActions['default']['name'] = $action;
				}
												 
				//$messageStr .= var_export($allowedActions[$action],true)."<br>\n";
				//str_replace(array('%id%','%name%','%encodedpath%'));								
			}else{
				$allowedActions[$action] = array('link'=>'','icon'=>'');
			}
			
		}
		
		
		
			
		return $allowedActions;
	}
	
	function scanDir($dir = '.',$editableFilePattern = null)
	{
		clearstatcache();
		$dir = preg_replace('@[/\\\\]+@',DIRECTORY_SEPARATOR,$dir);
		$dir = preg_replace('@[/\\\\]+@',DIRECTORY_SEPARATOR,$this->basePath.'/'.preg_replace('@/$@','',str_replace($this->basePath,'',$dir)).DIRECTORY_SEPARATOR);
		
		$count = 0;
		$path = preg_replace('@/$@','',str_replace($this->basePath,'',$dir));
		$pathFixed = preg_replace('@[/\\\\]+@',DIRECTORY_SEPARATOR,$path);
		$directoryContent = $this->getDirectoryContent($path);

		$directoryInfo = array('dirs'=>array(),
		'files'=>array(),
		'path'=>array(),
		'fullpath'=>FileManager::encodePath($path),
		'freespace'=>function_exists('disk_free_space')?disk_free_space($dir):0,
		);
		$directoryInfo['printfreespace'] = $directoryInfo['freespace']?$this->_sizeToPrint($directoryInfo['freespace'],null,true):'';
		$directoryInfo['description'] = $this->getPathDescription($pathFixed);
		$pathParts = explode(DIRECTORY_SEPARATOR,$pathFixed);
		$pathPart = '';
		$directoryInfo['path'][] = array('encoded'=>FileManager::encodePath($pathPart),'name'=>preg_replace('@[/\\\\]+@',DIRECTORY_SEPARATOR,$this->basePath.'/'));
		foreach($pathParts as $part){
			if(!$part||$part == DIRECTORY_SEPARATOR){
				continue;
			}
			$part = preg_replace('@[/\\\\]+@',DIRECTORY_SEPARATOR,$part.DIRECTORY_SEPARATOR);
			$pathPart .= $part.DIRECTORY_SEPARATOR;
			$directoryInfo['path'][] = array('encoded'=>FileManager::encodePath($pathPart),'name'=>$part);
		}
		$directoryFiles = array();

		foreach($directoryContent as $name){
			if(($name == '.')||($name == '..')){
				continue;
			}
			$isDir = null;
			if($row = $this->getPathInfo($dir.'/'.$name,$isDir, $editableFilePattern)){
				if($isDir){
					$directoryInfo['dirs'][] = $row;
				}else{
					$directoryInfo['files'][]= $row;
				}
			}
		}
		return $directoryInfo;
	}
	
	function getDirectoryContent($dir)
	{
		$dir = realpath($this->basePath.'/'.$dir);
		$direcoryContent = scandir($dir);
		if($direcoryContent){
			$key = array_search('..',$direcoryContent);
			unset($direcoryContent[$key]);
			$key = array_search('.',$direcoryContent);
			unset($direcoryContent[$key]);
		}
		return $direcoryContent;
		
	}
	
	function getPathDescription($pathName = '')
	{
		global $language;
		static $descriptions;
		static $PatternDescriptions;
		if(is_null($descriptions)){
			if(file_exists(WBS_DIR.'/published/wbsadmin/localization/path_description.php')){
				include_once WBS_DIR.'/published/wbsadmin/localization/path_description.php';
				$descriptions = (isset($descriptions[$language])&&is_array($descriptions[$language]))?$descriptions[$language]:array();
				$PatternDescriptions = (isset($PatternDescriptions[$language])&&is_array($PatternDescriptions[$language]))?$PatternDescriptions[$language]:array();
			}else{
			$descriptions = array();
			$PatternDescriptions = array();
			}
		}
		
		$pathName = str_replace('\\','/',$pathName);
		if(isset($descriptions[$pathName])){
			return $descriptions[$pathName];
		}
		foreach($PatternDescriptions as $pattern=>$description){
			if(preg_match($pattern,$pathName)){
				return $description;
			}
		}
		return '';
	}
	
	function getPathInfo($file,&$isDir,$editableFilePattern = null)
	{
		if(!file_exists($file)){
			return null;
		}
			
		$name = basename($file);
		$dir = dirname($file);
		if($this->basePath){
			$count = 0;
			$path = str_replace(preg_replace('/([\\\\\/]+)$/','',$this->basePath),'',$dir,$count);
		}else{
			$path = $dir;
		}
		$path = preg_replace('/(^([\\\\\/]+))/','',$path);
		//$path = str_replace('\\','/',$path);
		$row = array();
		$row['name'] = $name;
		$row['path'] = $path;
		$row['fullpath'] = realpath($dir.'/'.$name);
		
		$row['id'] = md5($row['fullpath']);
		$row['perm'] = sprintf('%03o',fileperms($row['fullpath'])&0777);
		$row['owner'] = function_exists('posix_getpwuid')?posix_getpwuid(intval(fileowner($row['fullpath']))):array('name'=>'-','uid'=>'-','gname'=>'-','gid'=>'-');
		if(function_exists('posix_getgrgid')&&isset($row['owner']['gid'])){
			$row['owner']['gname'] = posix_getgrgid(intval($row['owner']['gid']));
			$row['owner']['gname'] = $row['owner']['gname']['name']?$row['owner']['gname']['name']:'-';	
		}			 
		$row['writable'] = is_writable($row['fullpath']);
		$isDir = is_dir($row['fullpath']);
		//$row['encodedpath'] = urlencode(base64_encode($path.'/'.$name));
		$row['encodedpath'] = FileManager::encodePath($path.($isDir?('/'.$name):''));
		$row['encodedpathname'] = $isDir?$row['encodedpath']:FileManager::encodePath($path.'/'.$name);
		$row['isdir'] = $isDir;
		//$row['editable'] = $isDir?false:($editableFilePattern&&preg_match($editableFilePattern,$name)?true:false);
		$row['icon'] = $this->getIcon($name,$isDir);
		$size = $isDir?'':filesize($row['fullpath']);
		$row['size'] = $size;
		$row['printSize'] = $isDir?'':$this->_sizeToPrint($size,$name);
		$row['allowedactions'] = $isDir?array():$this->getAllowedActions($row,$row['writable']);
		$row['editable'] = $isDir?false:(strlen($row['allowedactions']['edit']['link'])?true:false);
		$row['description'] = $this->getPathDescription($path.($path?'/':'').$name);
		$row['version'] = $isDir?'':$this->_get_version($path.($path?'/':'').$name);
		return $row;		
	}
	
	function searchFiles($folders,$patern = '*')
	{
		$folders = is_array($folders)?$folders:array($folders);
		$curDir = getcwd();
		$files = array();

		foreach ($folders as $folder){
			$folder = fixPathSlashes($this->basePath.'/'.$folder);
			if(file_exists($folder)&&is_dir($folder)){
				chdir($folder);
				$filesAdd = glob($patern);
				if($filesAdd){
					$files = array_merge($files,array_map(create_function('$arrayItem','return realpath("'.$folder.'/".$arrayItem);'),$filesAdd));
				}
				//$files = array_merge($files,array_map(create_function('$arrayItem','return "'.$folder.'/".$arrayItem;'),glob($patern)));
			}
		}
		chdir($curDir);
		
		return $files;		
	}
	function _sizeToPrint($size,$fileName,$useMb = false)
	{
		$printSize = '';
		static $warningSizeMatches = array(
			array('size'=>10485760,'pattern'=>'/\.log$/i'),
		);

		if($fileName){
			foreach($warningSizeMatches as $warningSizeMatche){
				if($size<$warningSizeMatche['size']){continue;}
				if(preg_match($warningSizeMatche['pattern'],$fileName)){
					$warning = true;
					break;
				}
			}
		}
		
		$size = ($useMb?round($size/1048576):$size);
		while($size){
			
			$size_ = round($size/1000-0.5);
			$printSize = sprintf(($size>1000?'&nbsp;%03d':'%d'),($size-$size_*1000)).$printSize;
			$size = $size_;
			
		}
		$printSize = ($printSize?$printSize:'0').'&nbsp;'.($useMb?'MB':'B');
		if($warning){
			$printSize = sprintf('<span style="color:red;">%s</span>',$printSize);
		}
		return $printSize;
		
	}
	function _safeWrite($fileName,$content,&$errorStr,$targetPermission = 0664)
	{
		if(get_magic_quotes_gpc()&& false){//data already escaped at httpinit.php
			$content = stripslashes($content);
		}
		
		$res = false;	
		$tmpFileName = tempnam(dirname($fileName),'_fm');
		if(!$tmpFileName){
			$errorStr .= translate('filemanager_error_create_temp_file')."<br>\n";
			return $res;
		}
		if(!unlink($tmpFileName)){
			$errorStr .= sprintf(translate('filemanager_error_remove_temp_file'),$tmpFileName)."<br>\n";
			return $res;
		}
		if(!rename($fileName,$tmpFileName)){
			$errorStr .= sprintf(translate('filemanager_error_create_backup'),$fileName)."<br>\n";
			return $res;
		}
		
		if($fp = fopen($fileName,'wb')){
			$size = fwrite($fp,$content);
			fclose($fp);
			$res = (mb_strlen($content,'latin1')==$size)?true:false;
		}else{
			$errorStr .= sprintf(translate('filemanager_error_create_backup'),$fileName)."<br>\n";
			return $res;
		}
		if($res){
			if(!@unlink($tmpFileName)){
				$errorStr .= sprintf(translate('filemanager_error_remove_backup_file'),$tmpFileName)."<br>\n";
			}
			if(!chmod($fileName,$targetPermission)){
				$errorStr .= sprintf(translate('filemanager_error_chmod'),$fileName)."<br>\n";
			}
		}else{
			$errorStr .= sprintf(translate('filemanager_error_write'),$fileName)."<br>\n";
			if(rename($tmpFileName,$fileName)){//rollback
			}else{
				$errorStr .= sprintf(translate('filemanager_error_rollback'),$fileName,$tmpFileName)."<br>\n";
			}
			
		}
		return $res;
	}
	
	function _get_version($file)
	{
		$file = str_replace('\\','/',$file);
		static $md5;
		if(is_null($md5)){
			$md5 = array();
			$path = WBS_DIR.'/.webasyst.md5';
			if(file_exists($path)&&($fp = fopen($path,'r'))){
				while($line = fgets($fp)){
					if(preg_match('/^([0-9a-f]{32})\s+\*(.+)$/',$line,$matches)){
						$md5[$matches[2]] = $matches[1]; 
					}
				}
			}
		}
		$result = array(
		);
		if($md5){
			if(isset($md5[$file])){
				if($md5[$file] == md5_file(WBS_DIR.'/'.$file)){
					$result['color'] = '#dfd'; 
				}else{
					$result['color'] = '#ffd';
					$result['mtime'] = filemtime(WBS_DIR.'/'.$file);
				}
			}else{
				$result = false;
			}
		}
		return $result;
	}
	
	static function encodePath($path)
	{
		$path = preg_replace('@[/\\\\]+@',DIRECTORY_SEPARATOR,$path);
		return urlencode(base64_encode($path));
		return urlencode(convert_uuencode($path));
	}
	static function decodePath($encodedPath)
	{
		return base64_decode(urldecode($encodedPath));
		return convert_uudecode(urldecode($encodedPath));
	}
}

?>