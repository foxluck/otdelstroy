<?php

	class UGUsersImageUploadAction extends UGAjaxAction
	{
		public $contact_id;
		public $contact_info = array();				

		public static $known_extensions = array("jpg", "jpeg", "gif", "png");
		
		protected $errors = array();
		protected $response = array();
		
		public function __construct()
		{
			$this->contact_id = Env::Get('C_ID', Env::TYPE_BASE64_INT, 0);
			$this->contact_info = Contact::getInfo($this->contact_id);
			$contact_type = new ContactType($this->contact_info['CT_ID']);
			$this->fields = $contact_type->getFields();
			
			foreach ($_FILES as $field_id => $file_info) {
				if (!$file_info['error'] && isset($this->fields[$field_id])) {
					$this->response[] = array('id' => $field_id, 'url' => $this->processFile($file_info, $this->fields[$field_id]));
				}
				else $this->setError("Upload error!", $field_id);
			}
			
		}
		
		public function processFile($file_info, $field_desc) 
		{
			$path_parts = pathinfo($file_info['name']);
			if (isset($path_parts['extension'])) {
				$ext = trim( strtolower($path_parts['extension']));
			} else {
				$ext = "";
			}

			if (!$ext || !in_array($ext, self::$known_extensions)) {
				$this->setError("Unknown extension of file ".$file_info['name'], $field_desc["id"]);
				return false;
			}
			$filename = $this->contact_id.($this->contact_info['CT_ID'] == 3 ? '' : uniqid(rand()));
			$subfolder = $this->contact_info['CT_ID'] == 3 ? 'partners/logo/' : 'contacts/'; 
			$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("cm", $subfolder.$filename);
			$dir = dirname($path);
			if ( !file_exists($dir) ) {
				mkdir($dir, 0777, true);
			}
			@move_uploaded_file($file_info['tmp_name'], $path.".".$ext);			
			
			$wbsImage = new WbsImage($path.".".$ext);
			$wbsImage->thumbnailImage(96, 96, true);
			$wbsImage->writeImage($path.".96.".$ext);
			if (Env::Get('avatar')) {
			    
			}
			
			$dom = new DOMDocument('1.0', 'utf-8');
			$root = $dom->createElement("IMAGE");
			$dom->appendChild($root);
			$root->setAttribute( "FILENAME", base64_encode($file_info['name']) );
			$root->setAttribute( "SIZE", filesize($path.".".$ext));
			$root->setAttribute( "DISKFILENAME", base64_encode($this->contact_info['CT_ID'] == 3 ? 'partners/logo/'.$filename : $filename) );
			$root->setAttribute( "TYPE", $ext);
			$root->setAttribute( "MIMETYPE", $file_info['type']);
			$root->setAttribute( "DATETIME", time());
			
			$image = $this->contact_info[$field_desc["dbname"]];			
			if ($image && isset($image["FILENAME"]) && $image["FILENAME"]) {
				$root->setAttribute( "PREVFILENAME", $image["DISKFILENAME"]);
			}
			
			$errors = array();
			if (Contact::save($this->contact_id, array($field_desc['id'] => $dom->saveXML()), $errors) && !$errors) {
				return Url::get('/common/html/scripts/thumb.php?nocache='.time()."&basefile=".base64_encode($path)."&ext=".base64_encode($ext));
			} else {
				$this->setError(implode(", ", $errors), $field_desc['id']); 
			}			
					
		}
		
		/**
		 * Save error
		 *
		 * @param string $error - description
		 * @param string $id - ID of the bad element 
		 */
		public function setError($error, $id = false) 
		{
			$this->errors[] = array("text" => $error, "id" => $id);	
		}
		
		/**
		 * Returns PHP response
		 *
		 * @return array
		 */
		public function getResponse()
		{	
			$response = array(
				'status' => $this->errors ? 'ERR' : 'OK',
				'error' => $this->errors,
				'files' => $this->response
			);
			return json_encode($response);
		}		
					
	}
	
?>