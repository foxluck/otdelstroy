<?php

	class UGUsersImageAction extends UGViewAction
	{
		const AVATAR_SIZE = 96;
		const PREVIEW_SIZE = 512;
		const PREVIEW_W_SIZE = 416;
		const PREVIEW_H_SIZE = 312;
		
		private $contact_id;
		private $contact_info = array();				

		public static $known_extensions = array("jpg", "jpeg", "gif", "png");
		protected $errors = array();
		protected $response = array();
		protected $fields = array();
		
		public function __construct()
		{
			parent::__construct();
		}

		public function prepareData()
		{
			$type = Env::Get('type', Env::TYPE_STRING, 'add');
			$this->smarty->assign("type", $type);
			switch ($type) {
				case 'add': {
					$this->add();
					break;
				}
				case 'resize': {
					$this->resize();
					break;
				}
				case 'change': {
					$this->change();
					break;
				}
				case 'delete': {
					$this->delete();
					break;
				}
				case 'deletePreview': {
					$this->deletePreview();
					break;
				}
			}
            $this->smarty->assign("C_ID", Env::Get('C_ID', Env::TYPE_STRING));
            $this->smarty->assign("CF_ID", Env::Get('CF_ID', Env::TYPE_STRING, '1'));			
		}
		
		private function add()
		{			
		}
		private function resize()
		{
			$this->contact_id = Env::Get('C_ID', Env::TYPE_BASE64_INT, 0);
			$f_id = Env::Get('C_ID', Env::TYPE_INT, 0);
			$this->contact_info = Contact::getInfo($this->contact_id);
			$contact_type = new ContactType($this->contact_info['CT_ID']);
			$this->fields = $contact_type->getFields();
			
			foreach ($_FILES as $field_id => $file_info) {
				if (!$file_info['error'] && isset($this->fields[$field_id])) {
					
					$field_desc = $this->fields[$field_id];
					
					$path_parts = pathinfo($file_info['name']);
					if (isset($path_parts['extension'])) {
						$ext = trim( strtolower($path_parts['extension']));
					} else {
						$ext = "";
					}
		
					if (!$ext || !in_array($ext, self::$known_extensions)) {
						$this->smarty->assign("type", 'add');			
						$this->smarty->assign("error", "Unknown extension of file ".$file_info['name']);
						return false;
					}
					
					$filename = $this->contact_id.($this->contact_info['CT_ID'] == 3 ? '' : uniqid(rand()));
					$subfolder = $this->contact_info['CT_ID'] == 3 ? 'partners/logo/' : 'contacts/'; 
					$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("cm", $subfolder.$filename);
					
					$dir = dirname($path);
					if ( !file_exists($dir) ) {
						mkdir($dir, 0777, true);
					}
					
					if (@!move_uploaded_file($file_info['tmp_name'], $path.".".$ext)) {
						$this->smarty->assign("type", 'add');			
						$this->smarty->assign("error", _s('Permission denied'));
						return false;
					}
					
					$wbsImage = new WbsImage($path.".".$ext);
					$wbsImage->thumbnailImage(self::PREVIEW_W_SIZE, self::PREVIEW_H_SIZE);
					$wbsImage->writeImage("$path.".self::PREVIEW_SIZE.".jpg");
					
					$dom = new DOMDocument('1.0', 'utf-8');
					$root = $dom->createElement("IMAGE");
					$dom->appendChild($root);
					$root->setAttribute( "FILENAME", base64_encode($file_info['name']) );
					$root->setAttribute( "SIZE", filesize($path.".".$ext));
					$root->setAttribute( "DISKFILENAME", base64_encode($this->contact_info['CT_ID'] == 3 ? 'partners/logo/'.$filename : $filename) );
					$root->setAttribute( "TYPE", 'jpg');
					$root->setAttribute( "MIMETYPE", 'image/jpeg');
					$root->setAttribute( "DATETIME", time());
					
					$image = $this->contact_info[$field_desc["dbname"]];			
					if (isset($image["FILENAME"])) {
						$root->setAttribute( "PREVFILENAME", $image["DISKFILENAME"]);
					}
					
					$errors = array();
					$url = Url::get('/common/html/scripts/thumb.php?nocache='.time()."&basefile=".base64_encode($path)."&ext=".base64_encode('jpg'));
					Env::setSession('imageMc', array(
						'path' => $path,
						'ext' => $ext,
						'id' => $field_id,
						'url' => $url,
						'data' => array(
							'contact_id' => $this->contact_id, 
							'field_id' => $field_desc['id'],
							'xml' => $dom->saveXML()
						)
					));
					$this->smarty->assign("IMG", $url);
				}
				else { 
					$this->smarty->assign("type", 'add');			
					$this->smarty->assign("error", _("Upload error!"));
					//Url::go('index.php?mod=users&act=image&C_ID='.base64_encode($this->contact_id).'&CF_ID='.$f_id.'&type=add');
				}
			}
		}

		private function change()
		{
			$this->contact_id = Env::Get('C_ID', Env::TYPE_BASE64_INT, 0);
			$f_info = ContactType::getField(Env::Get('CF_ID', Env::TYPE_INT, 0));
			$this->contact_info = Contact::getInfo($this->contact_id);
			
			$url = $this->contact_info[ $f_info['dbname'] ];
			$this->smarty->assign("IMG", $url);
		}
		
		private function delete($size = self::AVATAR_SIZE)
		{
			$this->contact_id = Env::Get('C_ID', Env::TYPE_BASE64_INT, 0);
			$f_info = ContactType::getField(Env::Get('CF_ID', Env::TYPE_INT, 0));
			
			$contactsModel = new ContactsModel();
			$data = $contactsModel->get($this->contact_id, $f_info['dbname']);
			$image_info = ContactsModel::parseImageXML($data);
			
			$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("cm", "contacts".DIRECTORY_SEPARATOR);
			@unlink($path . base64_decode($image_info['DISKFILENAME']) .'.jpg');
			@unlink($path . base64_decode($image_info['DISKFILENAME']) .'.'. $size .'.jpg');
			
		}
		
		private function deletePreview()
		{
			$image_info = Env::Session('imageMc');

			@unlink($image_info['path'].'.'.$image_info['ext']);
			@unlink($image_info['path'].'.'.self::PREVIEW_SIZE.'.jpg');
		}
	}
	
?>