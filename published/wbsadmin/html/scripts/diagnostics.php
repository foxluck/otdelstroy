<?php

$init_required = false;
require_once( "../../../common/html/includes/httpinit.php" );
require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

$mainMenu[PAGE_SECTION_DIAGNOSTIC]['link'] = '';

$fatalError = false;
$errorStr = null;
$messageStr = '';
$invalidField = null;
$profileCreated = false;

$section = isset($_POST['section'])?$_POST['section']:(isset($_GET['section'])?$_GET['section']:'systeminfo');
$id = isset($_POST['id'])?$_POST['id']:(isset($_GET['id'])?$_GET['id']:null);
$page = isset($_GET['page'])?$_GET['page']:0;
$action = isset($_POST['action'])?$_POST['action']:(isset($_GET['action'])?$_GET['action']:null);

//$messageStr .= nl2br(var_export(array('action'=>$action,'section'=>$section),true));

$allowEdit = false;
$allowDelete = false;
$file2view = null;

switch ($section) {
	///
	case ('viewlogs') :
		{
			require_once( "../../classes/class.filemanager.php" );
			$logManager = new FileManager(WBS_DIR);
			
			$logContent = null;
			$logs = array();
			$navigator = null;
			$logpath = null;
			$files = array();
			
			$dirs = array('dblist','temp','kernel');			
			$dbnames =$logManager->searchFiles('dblist','*.xml');
			foreach($dbnames as $file){
				$file = fixPathSlashes($file);
				if(preg_match('/([^\?]+)\/([^\/]+)\.xml/',$file,$matches)){
					$dirs[] = 'data/'.$matches[2].'/attachments/SC/temp/';
				}
			}
			
			$files = $logManager->searchFiles($dirs,'*.log');
			//$messageStr .= nl2br(var_export($logManager,true));
			//$messageStr .= nl2br(var_export($files,true));
			
			foreach($files as $file){
				$isDir = false;
				$logItem = $logManager->getPathInfo($file,$isDir);
				if($logItem&&!$isDir){
					if($id && ($id == $logItem['id'])){
						switch($action){
							case 'view':
								$file2view = $logItem;
								$file2view['navigator'] = '';
								$file2view['content'] = $logManager->view($logItem,$file2view['navigator'],$page,'?section=viewlogs&amp;action=view');
								$logs[] = $logItem;
								break;
							case 'delete':
								if(!$logManager->delete($logItem)){
									$logs[] = $logItem;
									$errorStr .= "Couldn't delete {$logItem['fullpath']}<br>\b";
								}
								redirectBrowser( PAGE_SECTION_DIAGNOSTIC.'?section='.$section, array( "msg"=>base64_encode($errorStr?$errorStr:'diagnostic_file_deleted_success') ),'',false,false, true );//, "", false, false, true );							
								break;
							case 'rename':
								if(!$logManager->backup($logItem)){
									$logs[] = $logItem;
									$errorStr .= "Couldn't backup {$logItem['fullpath']}<br>\b";
								}
								redirectBrowser( PAGE_SECTION_DIAGNOSTIC.'?section='.$section, array( "msg"=>base64_encode($errorStr?$errorStr:'diagnostic_file_backuped_success') ),'',false,false, true );//, "", false, false, true );							
								break;
							case 'download':
								$logManager->download($logItem);
								break;
							default:
								$logs[] = $logItem;
								break;
						}
						
					}else{
						$logs[] = $logItem;
					}
				}
			}
			//$messageStr .= nl2br(var_export($logpath,true));
			//$messageStr .= nl2br(var_export($logs,true));
			break;
		}
	case ('systeminfo'):
		{
			if(isset($_GET['action'])&&($_GET['action']=='phpinfo')){
				phpinfo();
				exit;
			}
			require_once('../../classes/class.diagnostictests.php');
			$tests = new DiagnosticTests();
			$tests->runTest();
			$diagnosticResult = $tests->getResult();
			break;
		}
	case ('filemanager'):
		{
			require_once('../../classes/class.filemanager.php');
			$editableFilePattern = '/^\.htaccess/';
			$fileManager = new FileManager(WBS_DIR);
			//$messageStr .= nl2br(var_export($fileManager,true));
			if(isset($_POST['path'])){
				$_GET['path'] = $_POST['path'];
			}
			$path = isset($_GET['path'])?FileManager::decodePath($_GET['path']):'';
			
			$path = preg_replace('@[/\\\\]+@','/',$path);
			
			switch($action){
				case ('chmod'):
					$chmodPaths = array();
					$groupMode = (isset($_POST['mode'])&&$_POST['mode']=='group')?true:false;
					$recursive = false;
					if($groupMode){
						$perm = sscanf(isset($_POST['perm'])?$_POST['perm']:'','%3o');
						$perm = isset($perm[0])?$perm[0]:0775;
						$recursive = isset($_POST['recursive'])&&(intval($_POST['recursive'])&1==1)?true:false;
						$chmodPaths[] = array('path'=>$path,'perm'=>$perm,'recursive'=>$recursive);
						$chmodPaths_ = $fileManager->getDirectoryContent($path);
						foreach($chmodPaths_ as $path_){
							$chmodPaths[] = array('path'=>$path_,'perm'=>$perm,'recursive'=>$recursive);
						}
						
					}else{
					
						foreach($_POST as $key=>$value){
							if(strpos($key,'path_')===0){
								$key = str_replace('path_','',$key);
								$path_ = FileManager::decodePath($key);
								$perm = sscanf($value,'%3o');
								$perm = isset($perm[0])?$perm[0]:0775;
								$recursive = isset($_POST['recursive_'.$key])?intval($_POST['recursive_'.$key])&1:0;
								$chmodPaths[] = array('path'=>$path_,'perm'=>$perm,'recursive'=>$recursive);
							}
						}
					}
					//$messageStr .= var_export(array($groupMode,$recursive,$_POST),true);
					//$messageStr .= var_export($chmodPaths,true);
					$chmodCount = $fileManager->chmod($chmodPaths,$errorStr);
					$messageStr .= sprintf(translate('filemanager_chmod_stat'),$chmodCount);

					$directoryContent = $fileManager->scanDir($path,$editableFilePattern);
					break;
				case ('view'):
				case ('edit'):
				case ('delete'):
				case ('save'):
				case ('download'):
						$directoryContent = $fileManager->scanDir($path,$editableFilePattern);
						//$messageStr .= var_export(array($id,$directoryContent['files']),true);
							
						foreach($directoryContent['files'] as &$file){
							if($id && ($id == $file['id'])){
								$editMode = true;
								
								switch($action){
									case 'view':
										$editMode = false;
										$file['editable'] = false;
									case 'edit':
										$file2view = $file;
										$file2view['navigator'] = '';
										$file2view['content'] = $fileManager->view($file,$file2view['navigator'],$page,'?section='.$section.'&amp;action=view&amp;path='.$directoryContent['fullpath'],$editMode);
										$allowEdit = $file['editable'];
										$filePath = $file['fullpath'];										
										break;
									case 'delete':
										if(!$fileManager->delete($file)){
											$errorStr .= "Couldn't delete {$file['fullpath']}<br>\n";
										}else{
											unset($file);							
										}
										redirectBrowser( PAGE_SECTION_DIAGNOSTIC.'?section='.$section.'&path='.$directoryContent['fullpath'], array("msg"=>base64_encode($errorStr?$errorStr:'diagnostic_file_deleted_success')), "", false, false, true );
										//redirectBrowser( PAGE_SECTION_DIAGNOSTIC.'?section='.$section, array( "msg"=>base64_encode($errorStr?$errorStr:'diagnostic_file_deleted_success') ),'',false,false, true );//, "", false, false, true );
										break;
									case 'download':
										$fileManager->download($file);
										break;
									case 'save':
										if($file['editable']){
											$file['content'] = isset($_POST['content'])?$_POST['content']:'';
											//$messageStr .=var_export(array($_GET,$_POST,$action),true); 
											if($fileManager->save($file,$errorStr)){
												$messageStr .= translate('diagnostic_file_save_success');
											}
											$isDir = null;
											$file =$fileManager->getPathInfo($file['fullpath'],$isDir,$editableFilePattern);
										}else{
											$errorStr .= sprintf(tranalsta('filemanager_file_not_editable'),$file['fullpath'])."<br>\n";
										}
										break;
								}
								break;
							}
						}

					break;
				default:
					$directoryContent = $fileManager->scanDir($path,$editableFilePattern);
					break;
			}
			
			
			break;
		}
	case ('cache'):
		{
			
			require_once('../../classes/class.diagnostictools.php');
			switch($action){
				case 'resetcache':	
					
					$tools = new DiagnosticTools(WBS_DIR);
					$res = true;
					if(isset($_POST['system'])&&$_POST['system']){
						$res = $res&$tools->cleanCache('temp',$errorStr,'/^\.cache\.|^\.settings\./');
						$SCFolders = scandir(WBS_DIR.'/data');
						$applications = array();
						foreach($SCFolders as $SCFolder){
							if(($SCFolder == '.')
								||($SCFolder == '..')
								||!is_dir(WBS_DIR.'/data/'.$SCFolder)
							){
								continue;		
							}
							if(realpath(WBS_DIR.'/data/'.$SCFolder.'/attachments/SC/temp/')){
								$applications[] = 'data/'.$SCFolder.'/attachments/SC/temp/';
							}
						}
						if(count($applications)){
							$res = $res&$tools->cleanCache($applications,$errorStr,'/^\.cache\.|^\.settings\./');
						}
					}
					if(isset($_POST['smarty'])&&$_POST['smarty']){
						$res = $res&$tools->cleanCache('kernel/includes/smarty/compiled',$errorStr,'/\.php$/');
					}
					if(isset($_POST['localization'])&&$_POST['localization']){
						$applications = array('wbsadmin/localization');
						$applicationFolders = scandir(WBS_DIR.'/published');
						foreach($applicationFolders as $applicationFolder){
							if(preg_match('/^\w{2}$/',$applicationFolder)){
								$applications[] = 'published/'.$applicationFolder.'/localization/';
								$applications[] = 'published/'.$applicationFolder.'/2.0/localization/';
							}
						}
						$applications[] = 'published/wbsadmin/localization/';
						$SCFolders = scandir(WBS_DIR.'/data');
						foreach($SCFolders as $SCFolder){
							if(($SCFolder == '.')
								||($SCFolder == '..')
								||!is_dir(WBS_DIR.'/data/'.$SCFolder)
							){
								continue;		
							}
							if(realpath(WBS_DIR.'/data/'.$SCFolder.'/attachments/SC/temp/loc_cache/')){
								$applications[] = 'data/'.$SCFolder.'/attachments/SC/temp/loc_cache/';
							}
						}
						//var_dump($applications);exit;
						$res = $res&$tools->cleanCache($applications,$errorStr,'/(^\.cache\.)|(^serlang.+\.cch$)/',null,false);
					}
					
					if(isset($_POST['updatestate'])&&$_POST['updatestate']){
						$res = $res&$tools->cleanCache('temp',$errorStr,'/^\.update_state$/',null,false);
					}
				if($res){$messageStr .=translate('dignostic_cache_cleaned')."<br>\n";}
				break;
			}
			
			break;
		}
}

$pageTitle = $LocalizationStrings[5];

$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );

$preproc->assign( PAGE_TITLE, sprintf( "%s - %s", $LocalizationStrings[6], $pageTitle ) );
$preproc->assign( FORM_LINK, PAGE_SECTION_DIAGNOSTIC );
$preproc->assign( ERROR_STR, $errorStr );
$preproc->assign( 'messageStr', $messageStr );

$preproc->assign( FATAL_ERROR, $fatalError );
$preproc->assign( INVALID_FIELD, $invalidField );

$subMenu = array();
$subMenu[] = array('title'=>'diagnostic_systeminfo','link'=>($section == 'systeminfo')?'':'?section=systeminfo','description'=>'','info'=>'');
$subMenu[] = array('title'=>'diagnostic_viewlogs','link'=>($section == 'viewlogs')?'':'?section=viewlogs','description'=>'','info'=>'');
$subMenu[] = array('title'=>'diagnostic_filemanager','link'=>($section == 'filemanager')?'':'?section=filemanager','description'=>'','info'=>'');
$subMenu[] = array('title'=>'diagnostic_cache','link'=>($section == 'cache')?'':'?section=cache','description'=>'','info'=>'');

$preproc->assign('mainMenu',$mainMenu);
$preproc->assign('subMenu',$subMenu);

$preproc->assign( 'section',$section);



if ( !$fatalError )
{
	switch($section){
		case 'viewlogs':
		
			$preproc->assign( 'logsInfo', $logs );
			$preproc->assign( 'id', $id );
			$preproc->assign( 'file2view', $file2view );
			
			break;
		case 'systeminfo':
			$preproc->assign( 'diagnosticResult', $diagnosticResult );
			
			break;
		case 'filemanager':
			$preproc->assign( 'directoryContent',$directoryContent);
			$preproc->assign( 'file2view', $file2view );
			break;
		case 'cache':
			break;
	}
	
	
}

$message = isset($_POST['msg'])?$_POST['msg']:(isset($_GET['msg'])?$_GET['msg']:'');
$messageTypeIsErr = isset($_POST['msgtype'])?$_POST['msgtype']:(isset($_GET['msgtype'])?$_GET['msgtype']:false);
$message_ = base64_decode($message);
if(!$message_){
	$message = base64_decode(urldecode($message));
}else{
	$message = $message_;
}
if($message&&strlen($message)){
	$preproc->assign('message',$message);
	$preproc->assign('messageType',$messageTypeIsErr);
}

$preproc->assign( PAGE_TITLE, translate('main_menu_diagnostic').' &mdash; '.translate('diagnostic_'.$section));
$preproc->assign( 'installInfo', $installInfo );
$preproc->assign( "mainTemplate","diagnostics.htm" );
$preproc->display( "main.htm" );
?>