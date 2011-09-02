<?php	
class UGAjaxUsersEmailAction extends UGAjaxAction
{

	public function __construct()
	{
		parent::__construct();
	}

	public function prepareData() // autocomplete
	{
		$text = Env::Get('text', Env::TYPE_STRING, '');
		
		$result = array();
		if($text)
		{
			$contacts_model = new ContactsModel();
			$data = $contacts_model->getByName($text, false, 10, true);

			$result = array();
			Contact::useStore(false);
			foreach($data as $contact) {
					$result[] = Contact::getName($contact['C_ID'], Contact::FORMAT_NAME_EMAIL, $contact);
			}
			Contact::useStore(true);
		}

		$this->response = $result;
	}

	public function getResponse() {

		if(Env::Get('autosave', Env::TYPE_INT)) {

			if(!($data['MMM_ID'] = Env::Post('mid', Env::TYPE_INT))) {
				$msg = Mailer::composeMessage();
				$msg->addStatus(0);
				$msg->addSubject(Env::Post('subject', Env::TYPE_STRING));
				$msg->addContent(Env::Post('text', Env::TYPE_STRING));
				$data['MMM_ID'] = Mailer::send($msg);
			}
			$data['MMM_SUBJECT'] = Env::Post('subject', Env::TYPE_STRING);
			$data['MMM_CONTENT'] = Env::Post('text', Env::TYPE_STRING);
			Mailer::update($data);

			return $data['MMM_ID'];

		} elseif(Env::Get('uploadimage', Env::TYPE_INT)) {

			$mid = '';

			$file = current($_FILES);
			if($file['error'] == UPLOAD_ERR_OK) {

				if(!($mid = Env::Get('mid', Env::TYPE_INT))) {
					$msg = Mailer::composeMessage();
					$msg->addStatus(0);
					$mid = Mailer::send($msg);
				}
				$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath('mm', 'images').DIRECTORY_SEPARATOR.$mid;

				$wbs_files = new WbsFiles(User::getAppId());
				$wbs_files->moveUpload($file['tmp_name'], $path.DIRECTORY_SEPARATOR.$file['name']);

				$img_url = Url::Get('/common/html/scripts/getimage.php', true).'?user='.base64_encode(Wbs::getDbkeyObj()->getDbkey()).'&msg='.$mid.'&file='.base64_encode($file['name']);
				$file['path'] = $img_url;

				$draft = Mailer::getMessage($mid, false);
				$draft['MMM_IMAGES'] = base64_encode(MailMessage::addAttachedFile(base64_decode($draft['MMM_IMAGES']), $file));
				Mailer::update($draft);

			} else {
				$img_url = '';
			}
			return json_encode(array($img_url, $mid));

		} else {
			$this->prepareData();
		}
		return json_encode($this->response);
	}
}
?>