<?php

class UGUsersEmailUploadAction extends UGViewAction
{
	private static $uploadSizeLimit = 2000000;

    public function __construct()
    {
        parent::__construct();

		switch(Env::Post('upload_action')) {

			case 'upload':
				$this->upload();
				break;
		
			case 'delete':
				$this->delete();
				break;

		}

    }

	public function upload($key = '')
	{
		$attachments = array();
		$mid = '';
		$errorStr = false;

		if(isset($_FILES['file'])) {
			$file = $_FILES['file'];
		} elseif(isset($_FILES['userfile'])) {
			$file = $_FILES['userfile'];
		} else {
			$file = false;
		}

		if($file)
		{
			if(!$mid = Env::Post('mid', Env::TYPE_INT)) {

				$msg = Mailer::composeMessage();
				$msg->addStatus(0);
				$mid = Mailer::send($msg, false);
			}

			$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath('mm', 'attachments').DIRECTORY_SEPARATOR.$mid;

			$wbs_files = new WbsFiles(User::getAppId());
			$wbs_files->moveUpload($file['tmp_name'], $path.DIRECTORY_SEPARATOR.$file['name']);

			$draft = Mailer::getMessage($mid);
			$attachments = $draft['attachments'];

			$draft['MMM_ATTACHMENT'] = base64_encode(MailMessage::addAttachedFile(base64_decode($draft['MMM_ATTACHMENT']), $file));
			unset($draft['attachments']);
			unset($draft['att_size']);
			unset($draft['images']);
			unset($draft['img_size']);
			Mailer::update($draft);

			$attachments = array_merge($attachments, array($file));
			foreach($attachments as $i=>$f) {
				$attachments[$i]['formatted_size'] = MailDecode::formatFileSize($f['size']);
			}
		}
		$this->smarty->assign('attachments', $attachments);
		$this->smarty->assign('errorStr', $errorStr);
		$this->smarty->assign('mid', $mid);
	}

	public function delete()
	{
		$file = Env::Post('delete_file', Env::TYPE_STRING);
		$mid = Env::Post('mid', Env::TYPE_INT);
		if ($mid) {
			$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath('mm', 'attachments').DIRECTORY_SEPARATOR.$mid;

			$draft = Mailer::getMessage($mid);

			$draft['MMM_ATTACHMENT'] = '';
			$attachments = array();
			foreach((array)$draft['attachments'] as $i=>$f) {
				if($f['name'] == $file) {
					@unlink($path.DIRECTORY_SEPARATOR.$f['name']);
				} else {
					$f['formatted_size'] = MailDecode::formatFileSize($f['size']);
					$attachments[] = $f;
					$draft['MMM_ATTACHMENT'] = base64_encode(MailMessage::addAttachedFile(base64_decode($draft['MMM_ATTACHMENT']), $f));
				}

			}
			unset($draft['attachments']);
			unset($draft['att_size']);
			unset($draft['images']);
			unset($draft['img_size']);
			Mailer::update($draft);

			$this->smarty->assign('attachments', $attachments);
			$this->smarty->assign('mid', $mid);
		}
	}

/*
	public function clearUpload()
	{
		Mailer::deleteDraft();
//		self::unlinkRecursive(self::getTempPath(self::getDataPath()));
	}

	public function getDataPath()
	{
		return Wbs::getSystemObj()->files()->getDataPath().DIRECTORY_SEPARATOR.Wbs::getDbkeyObj()->getDbkey();
	}

	public function getTempPath($data_path)
	{
		if(!$C_ID = User::getContactId()) {
			$C_ID = 'public';
		}
		$tmp_path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath('cm', 'tmp'.DIRECTORY_SEPARATOR.$C_ID);

		if(!MailDecode::forceDirPath(array($data_path, 'attachments', 'cm', 'tmp', $C_ID, 'attachments'))) {
			throw new Exception("Can't create folder $tmp_path");
		}
		return $tmp_path;
	}
*/
/*
	public function readDirectory($dir, $key = '')
	{
		$result = Array('list'=>array(), 'size'=>0);
		$size = 0;
		$list = $res = array();

		if($handle = opendir($dir)) {
			while(false !== ($file = readdir($handle))) {
				if($file != '.' && $file != '..') {
					$sz = filesize($dir.DIRECTORY_SEPARATOR.$file);
					if($key) {
						$file = substr($file, strlen($key));
					}
					$list[] = $file;
					$res[$file] = array(
						'name'=>$file, 'size'=>$sz,
						'formatted_size'=>MailDecode::formatFileSize($sz)
					);
					$size += $sz;
				} 
			}
			closedir($handle);
	
			sort($list);
			foreach($list as $row) {
				$result['list'][] = $res[$row];
			}
			$result['size'] = $size;
		}
		return $result;
	}
/*
	/**
	 * Recursively delete a directory content
	 *
	 * @param string $dir (Directory name)
	 */
/*
	function unlinkRecursive($dir, $deleteRootToo = false)
	{
		if(!$dh = @opendir($dir)) {
			return;
		}
		while(false !== ($obj = readdir($dh))) {

			if($obj == '.' || $obj == '..') {
				continue;
			}

			if(!@unlink($dir.DIRECTORY_SEPARATOR.$obj)) {
				self::unlinkRecursive($dir.DIRECTORY_SEPARATOR.$obj, true);
			}
		}
		closedir($dh);

		if($deleteRootToo) {
			@rmdir($dir);
		}
		return;
	}
*/
}

?>