<?php

	class UGUsersImageSaveAction extends UGAjaxAction
	{
		protected $errors = '';
		
		public function __construct()
		{
			$image = Env::Session('imageMc');
			
			$errors = array();
			if (!Contact::save(
					$image['data']['contact_id'], 
					array($image['data']['field_id'] => $image['data']['xml']), $errors) && !$errors) 
			{
				$this->errors[] = array(implode(", ", $errors), 'error');
				return false; 
			}	
			
			$x = Env::POST('x', Env::TYPE_INT);
			$y = Env::POST('y', Env::TYPE_INT);
			$w = Env::POST('w', Env::TYPE_INT);
			$h = Env::POST('h', Env::TYPE_INT);

			
			try {
				$sourceImage = new WbsImage($image['path'].".".$image['ext']);
				$info = getimagesize($image['path'].".".$image['ext']);
				$sourceWidth = $info[0];
				$sourceHeight = $info[1];
				
				$previewImage = new WbsImage($image['path'].".".UGUsersImageAction::PREVIEW_SIZE.".jpg");
				$info = getimagesize($image['path'].".".UGUsersImageAction::PREVIEW_SIZE.".jpg");
				$previewWidth = $info[0];
				$previewHeight = $info[1];
				$previewImage->destroy();
				unlink($image['path'].".".UGUsersImageAction::PREVIEW_SIZE.".jpg");
				unlink($image['path'].".".$image['ext']);
				
				$k = $sourceWidth / $previewWidth;
				
				$dir = dirname($image['path']);
				if ( !file_exists($dir) ) {
					mkdir($dir, 0777, true);
				}
				
				$sourceImage->cropImage($w * $k, $h * $k, $x * $k, $y * $k);
				$sourceImage->writeImage($image['path'].".jpg");
				$sourceImage->resizeToFill(UGUsersImageAction::AVATAR_SIZE, UGUsersImageAction::AVATAR_SIZE);
				$sourceImage->writeImage($image['path'].".".UGUsersImageAction::AVATAR_SIZE.".jpg");
				$sourceImage->destroy();
			}
			catch( Exception $e ) {
				$this->errors[] = $e->getMessage();
			}
		}
		
		/**
		 * Returns PHP response
		 *
		 * @return array
		 */
		public function getResponse()
		{	
			if ($this->errors)
				$response = array(
					'status' => 'ERR',
					'error' => $this->errors 
				);
			else {
				$image = Env::Session('imageMc');
				$response = array(
					'status' => 'OK',
					'close_popup' => 1,
				    'id' => $image['id'], 
				    'url' => $image['url']
				);
			}

			return json_encode($response);	
		}
	}
	
?>