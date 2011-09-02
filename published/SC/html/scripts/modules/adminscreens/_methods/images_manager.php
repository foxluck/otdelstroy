<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
class ImagesManagerController extends ActionsController {
	
	
	const IMAGES_PER_PAGE = 40;
	/**
	 * @return array  {id, dir, url, description}
	 */
	function __getFolderParams(){
			
		static $folder_data;
		if(!is_array($folder_data)){

			$Register = &Register::getInstance();
			/*@var $Register Register*/
			$GetVars = $Register->get(VAR_GET);
			$folders = array(
			'images' => array( 'id' => 'images', 'dir' => DIR_IMG, 'url' => /*CONF_FULL_SHOP_URL.'images'*/ (SystemSettings::is_hosted()?'/shop/':'').URL_IMAGES , 'description' => 'imm_folder_images_dscr'),
			'prdpicts' => array( 'id' => 'prdpicts', 'dir' => DIR_PRODUCTS_PICTURES, 'url' => /*CONF_FULL_SHOP_URL.'products_pictures'*/ (SystemSettings::is_hosted()?'/shop/':'').URL_PRODUCTS_PICTURES , 'description' => 'imm_folder_prdpicts_dscr'),
			);
			$folder_id = isset($GetVars['folder_id'])?$GetVars['folder_id']:'';
			if(!array_key_exists($folder_id, $folders))$folder_id = 'images';
			renderURL('folder_id='.$folder_id, '', true);
			$folder_data = $folders[$folder_id];
		}
		return $folder_data;
	}

	function __getImagesList($offset = 0, $limit = -1, &$cnt,$fullinfo = true, $order = 0){

		$folder = $this->__getFolderParams();
		$images = array();
		//$dr = opendir($folder['dir']);
		$cnt = 0;
		$files = file_exists($folder['dir'])&&is_dir($folder['dir'])?scandir($folder['dir'],$order):array();
		//while ($file = readdir($dr)){
		foreach($files as $file){

			if($file == '.' || $file == '..' || !is_image($file) || is_dir($folder['dir'].'/'.$file))continue;
			$cnt++;
			if($cnt<=$offset)continue;
			if($limit==0)continue;
			$row = array(
			'file'	=> $file,
			'url'	=> $folder['url'].'/'.$file,
			'full_url' =>BASE_URL.preg_replace('/(^\/)/','',$folder['url'].'/'.$file),
			'safe_url'	=> $folder['url'].'/'.urlencode($file),
			'id'	=> md5($file),
			);
			if($fullinfo){
				$filesize = filesize($folder['dir'].'/'.$file);
				$row['fullsize']= $filesize;
				$row['size']	= $filesize>1024?sprintf('%0.1f KBytes',round($filesize/1024,1)):sprintf('%d Bytes',$filesize);
				$row['mtime'] = Time::standartTime(filemtime($folder['dir'].'/'.$file));
				list($row['width'], $row['height']) = @getimagesize($folder['dir'].'/'.$file);
				list($row['thumb_width'], $row['thumb_height']) = shrink_size($row['width'], $row['height'], 100, 100);
			}
			$images[] = $row;
			$limit--;
		}
		//closedir($dr);
		return count($images)?$images:null;
	}

	function delete_all(){
			
		@ini_set( 'max_execution_time', 30000000000 );
		$folder = $this->__getFolderParams();
		if($folder['id']!=='prdpicts')RedirectSQ('');
		$images_list = $this->__getImagesList(0, -1, $cnt,false);
		foreach ($images_list as $_image){
			Functions::exec('file_remove', array($folder['dir'].'/'.$_image['file']));
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'imm_images_deleted');
	}

	function delete_image(){
		$ids = scanArrayKeysForID($this->getData(),'image');
		$ids = array_keys($ids);
		if($this->getData('img_id')){
			$ids[] = $this->getData('img_id');
		}
			
		$images_list = $this->__getImagesList(0, -1, $cnt,false);
		foreach ($images_list as $_image){

			//if($_image['id'] != $this->getData('img_id'))continue;
			if(!in_array($_image['id'],$ids))continue;
			$folder = $this->__getFolderParams();

			Functions::exec('file_remove', array($folder['dir'].'/'.$_image['file']));
			unset($ids[array_search($_image['id'],$ids)]);
			if(!count($ids))break;
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'img_id=', 'imm_image_deleted');
	}

	function upload_image(){
			
		$Register = &Register::getInstance();
		$FilesVar = &$Register->get(VAR_FILES);
		$error = null;
		do{
			if(isset($FilesVar['Filedata'])){
				$FilesVar['upload_picture'] = $FilesVar['Filedata'];
			}

			if(!isset($FilesVar['upload_picture'])){
				$this->main();return;
			}

			if(!is_image($FilesVar['upload_picture']['name'])){
				$error = PEAR::raiseError('imm_notimage');break;
			}

			$folder = $this->__getFolderParams();
			$file_name = $FilesVar['upload_picture']['name'];
			if(get_magic_quotes_gpc()){
				$file_name = stripslashes($file_name);
			}
			if(file_exists($folder['dir'].'/'.$file_name)){
				$file_name = getUnicFile(2, preg_replace('@\.([^\.]+)$@', '%s.$1', $file_name), $folder['dir']);
			}
			$res = Functions::exec('file_move_uploaded', array($FilesVar['upload_picture']['tmp_name'], $folder['dir'].'/'.$file_name));
			if(PEAR::isError($res)){
				$error = $res;break;
			}
		}while(0);
		if(isset($_POST['source'])&&$_POST['source']=='swfupload'){
			$code = 500;
			if(PEAR::isError($error)){
				//header("HTTP/1.1 $code ".translate($error->getMessage()));
				print translate($error->getMessage());
			}else{
				list($width, $height) = @getimagesize($folder['dir'].'/'.$file_name);
				$k = max($width/150,$height/75);
				$width = round($width/$k);
				$height = round($height/$k);
				print "OK:{$file_name}:{$folder['url']}/{$file_name}:{$width}:{$height}";
			}
			exit;
		}else{
			if(PEAR::isError($error)){
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', $error->getMessage());die;
			}else{
				Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'imm_image_uploaded');
				//.var_export(array($FilesVar['upload_picture'],$FilesVar['upload_picture']['tmp_name'], $folder['dir'].'/'.$file_name),true));;
			}
		}
	}

	function main(){
			
		$Register = &Register::getInstance();
		$smarty = &$Register->get(VAR_SMARTY);
		/* @var $smarty Smarty */
		$GetVars = &$Register->get(VAR_GET);
		$GetVars['view_mode'] = isset($GetVars['view_mode'])&&($GetVars['view_mode'] == 'thumbnails')?'thumbnails':'list';

		$folder_data = $this->__getFolderParams();
			
		$total_images = 0;
		$max_images = self::IMAGES_PER_PAGE;
		$page = isset($GetVars['page'])?intval($GetVars['page']):1;
		$page = $page>0?$page:1;
		$images_list = $this->__getImagesList(($page-1)*$max_images, $max_images, $total_images);
		if(!$images_list&&$total_images){
			$page = 1;
			$images_list = $this->__getImagesList(($page-1)*$max_images, $max_images, $total_images);
		}
		$total_pages = ceil($total_images/$max_images);
			
		if(function_exists("getallheaders")){
			$request_headers = getallheaders();
			if(isset($request_headers['X-Real-IP']))
			$remote_ip = $request_headers['X-Real-IP'];
		}
		if(!$remote_ip){
			$remote_ip = $_SERVER["REMOTE_ADDR"];
		}
		$images_list_info =array();
		$images_list_info['total']	= $total_images;
		$images_list_info['from']	= ($page-1)*self::IMAGES_PER_PAGE+1;
		$images_list_info['to']		= $images_list_info['from']-1+count($images_list); 
		$images_list_info['string'] = sprintf(translate('imm_images_count_info'),$images_list_info['from'],$images_list_info['to'],$images_list_info['total']);
		//'imm_images_count_info'=>'%d &mdash; %d of %d' 
			
		$smarty->assign('folder_id', $folder_data['id']);
		$smarty->assign('view_mode',$GetVars['view_mode']);
		$smarty->assign('folder', $folder_data);
		$smarty->assign('images_list', $images_list);
		$smarty->assign('images_list_info',$images_list_info);
		$smarty->assign('Lister', getLister($page, $total_pages));
		$smarty->assign('admin_sub_dpt', 'images_manager.html');
		$smarty->assign('session_id',session_id());
		sc_setSessionData('swfupload_ip',$remote_ip);
	}
}

ActionsController::exec('ImagesManagerController');
?>