<?php

include_once WBS_DIR.'published/common/scripts/mailconsts.php';

class MailMessage
{
	public $DB_KEY;
	/**
	 * @var int(11)
	 */
	public $MMM_ID;
	/**
	 * @var varchar(255)
	 */
	protected $MMF_ID = 0;

	/**
	 * @var int(11)
	 */
	protected $MMM_STATUS = MM_STATUS_SENDING;

	/**
	 * @var varchar(100)
	 */
	protected $MMM_FROM = '';
	/**
	 * @var text
	 */
	protected $MMM_TO = '';
	/**
	 * @var text
	 */
	protected $MMM_CC = '';
	/**
	 * @var text
	 */
	protected $MMM_BCC = '';
	/**
	 * @var text
	 */
	protected $MMM_LISTS = '';

	/**
	 * @var varchar(255)
	 */
	protected $MMM_SUBJECT;
	/**
	 * @var text
	 */
	protected $MMM_CONTENT;
	/**
	 * @var text (serialized array)
	 */
	protected $MMM_ATTACHMENT = array();
	/**
	 * @var text (serialized array)
	 */
	protected $MMM_IMAGES = array();

	/**
	 * @var datetime
	 */
	protected $MMM_DATETIME;
	/**
	 * @var varchar(50)
	 */
	protected $MMM_USERID;
	/**
	 * @var datetime
	 */
	protected $MMM_PRIORITY = MM_PRIORITY_NORMAL;
	/**
	 * @var text
	 */
	protected $MMM_HEADER;
	/**
	 * @var char(2)
	 */
	protected $MMM_APP_ID;
	/**
	 * @var char(2)
	 */
	protected $MMM_REPLY_TO = false;

	public $attachments;

	
	public function __construct()
	{
		$this->MMM_APP_ID = User::getAppId();
	}
	/**
	 * Adds recipient's email ("To" field)
	 * @param string $to
	 */
	public function addTo($to)
	{
		$this->MMM_TO = str_replace("\n", ' ', str_replace("\r", '', $to));
	}

	/**
	 * Adds recipient's email ("Cc" field)
	 * @param string $cc
	 */
	public function addCc($cc)
	{
		$this->MMM_CC = str_replace("\n", ' ', str_replace("\r", '', $cc));
	}

	/**
	 * Adds recipient's email ("Bcc" field)
	 * @param string $bcc
	 */
	public function addBcc($bcc)
	{
		$this->MMM_BCC = $bcc;
	}

	/**
	 * Adds recipient's lists
	 * array(CL_ID, ...)
	 * @param array $list_ids
	 */
	public function addLists($list_ids)
	{
	  if(!is_array($list_ids))
	    $list_ids = array($list_ids);

		$this->MMM_LISTS = join(',', $list_ids);
	}

	/**
	 * Adds sender
	 * @param string $sender
	 * Format: User Name <e-mail>
	 */
	public function addFrom($from)
	{
		$this->MMM_FROM = $from;
	}

	/**
	 * Adds sender
	 * @param string $sender
	 * Format: User Name <e-mail>
	 */
	public function addReplyTo($reply_to)
	{
		$this->MMM_REPLY_TO = $reply_to;
	}

	/**
	 * Adds contact ids
	 * @param array $contact_ids
	 */
	public function addContacts($contact_ids) // TODO: ?????????????????????????
	{
	  if(!is_array($contact_ids))
	    $contact_ids = array($contact_ids);

		$this->MMM_CONTACTS = $contact_ids;
	}

	/**
	 * Adds send datetime
	 * @param timestamp $whentimestamp
	 */
	public function addDateTime($whentimestamp)
	{
		if (!$whentimestamp) {
			$whentimestamp = WbsDateTime::getTimeStamp(time()); 
		}
		$this->MMM_DATETIME = WbsDateTime::getServerTime($whentimestamp);
	}

	/**
	 * Adds folder ID
	 * @param string $id
	 */
	public function addFolderId($id)
	{
		$this->MMF_ID = $id;
	}

	/**
	 * Adds subject
	 * @param string $subject
	 */
	public function addSubject($subject)
	{
		$this->MMM_SUBJECT = trim(str_replace("\n", ' ', str_replace("\r", '', $subject)));
	}

	/**
	 * Adds content
	 * @param string $content
	 */
	public function addContent($content)
	{
		$this->MMM_CONTENT = trim($content);
	}

	/**
	 * Adds status
	 * @param string $status
	 */
	public function addStatus($status)
	{
		$this->MMM_STATUS = $status;
	}

	/**
	 * Adds priority
	 * @param string $priority
	 */
	public function addPriority($priority)
	{
		$this->MMM_PRIORITY = $priority;
	}

	/**
	 * Adds attachments
	 * @param array $attachments
	 * Each row must contain [name], [type], [size] and [path] fields, i.e.
	 * array(
	 *     array('name'=>..., 'type'=>..., 'size'=>..., 'path'=>...),
     *     ...
     * )
	 * where 'path' is absolute path to the file (include file name).
	 * If 'path' is empty, temporary uploads will be attached.
	 * Will be converted to XML format and serialized.
	 */
	public function addAttachments($attachments)
	{
		$this->MMM_ATTACHMENT = $attachments;
	}

	/**
	 * Adds images
	 * @param array $images
	 * Each row must contain [name], [type], [size] and [path] fields (see addAttachments())
	 * will be converted to XML format and serialized
	 */
	public function addImages($images)
	{
		$this->MMM_IMAGES = $images;
	}

	/**
	 * Adds message id
	 * @param string $mid
	 */
	public function addId($id)
	{
		$this->MMM_ID = $id;
	}

	/**
	 * Adds application id
	 * @param string $id
	 */
	public function addAppID($id)
	{
		$this->MMM_APP_ID = $id;
	}

	/**
	 * Returns data in database format
	 * 
	 * @return array
	 */
	public function getData($doSend = 'now')
	{
		$this->MMM_USERID = User::getId() ? User::getId() : '';

		$this->DB_KEY = Wbs::getDbKey();

		if(empty($this->MMM_DATETIME))
			$this->MMM_DATETIME = date('Y-m-d H:i:s');

		if(empty($this->MMM_FROM) && ($this->MMM_STATUS != MM_STATUS_TEMPLATE)) {
			$this->MMM_FROM = Contact::getName(User::getContactId(), Contact::FORMAT_NAME_EMAIL, false, false);
		}

		$this->MMM_REPLY_TO = $this->MMM_REPLY_TO ? $this->MMM_REPLY_TO : $this->MMM_FROM;

		if(empty($this->MMM_ID))
		{
			$this->MMM_ID = self::getNextId();
			$doCreate = true;
		}
		else
			$doCreate = false;

		$size = 1;

		$attachments = array();
		$MMM_ATTACHMENT = '';
		foreach($this->MMM_ATTACHMENT as $file)
		{
			$attachments[] = array('name'=>$file['name'], 'path'=>$file['path'], 'type'=>'attachments');
			unset($file['path']);
			$MMM_ATTACHMENT = self::addAttachedFile($MMM_ATTACHMENT, $file);
			$size += $file['size'];
		}
		$MMM_IMAGES = '';
		foreach($this->MMM_IMAGES as $file)
			if(strpos($this->MMM_CONTENT, base64_encode($file['name'])))
			{
				$attachments[] = array('name'=>$file['name'], 'path'=>$file['path'], 'type'=>'images');
				unset($file['path']);
				$MMM_IMAGES = self::addAttachedFile($MMM_IMAGES, $file);
				$size += $file['size'];
			}
		$MMM_ATTACHMENT = base64_encode($MMM_ATTACHMENT);
		$MMM_IMAGES = base64_encode($MMM_IMAGES);
		$this->attachments = $attachments;

		$uri = '/common/html/scripts/getimage.php?user='.base64_encode($this->DB_KEY).'&msg='.$this->MMM_ID.'&file=';
		$MMM_CONTENT = str_replace(PAGE_PREVIEW.'?file=', $uri, $this->MMM_CONTENT);

		$size += strlen($MMM_CONTENT) + strlen($this->MMM_SUBJECT) + strlen($this->MMM_TO);

		$hash = array(
			'MMM_ID' => $this->MMM_ID,
			'MMF_ID' => $this->MMF_ID,

			'MMM_STATUS' => $this->MMM_STATUS,

			'MMM_FROM' => $this->MMM_FROM,
			'MMM_REPLY_TO' => $this->MMM_REPLY_TO,
			'MMM_TO' => $this->MMM_TO,
			'MMM_CC' => $this->MMM_CC,
			'MMM_BCC' => $this->MMM_BCC,
			'MMM_LISTS' => $this->MMM_LISTS,

			'MMM_SUBJECT' => $this->MMM_SUBJECT,
			'MMM_CONTENT' => $MMM_CONTENT,
			'MMM_ATTACHMENT' => $MMM_ATTACHMENT,
			'MMM_IMAGES' => $MMM_IMAGES,

			'MMM_PRIORITY' => $this->MMM_PRIORITY,
			'MMM_SIZE' => $size,

			'MMM_DATETIME' => $this->MMM_DATETIME,
			'MMM_USERID' => $this->MMM_USERID,
			'MMM_APP_ID' => $this->MMM_APP_ID
		);
		return $hash;
	}

	private static function getNextId()
	{
		$mail_model = new MailModel();
		$id = $mail_model->getNextId();
		if($id < MMMESSAGE_MIN_ID) {
			$id = MMMESSAGE_MIN_ID;
		}
		return $id;
	}

	public static function addAttachedFile($fileList, $fileinfo)
	//
	// Adds file to the list of attached files
	//
	//		Parameters:
	//			$fileList - file list in XML format
	//			$fileInfo - information about file in the form of associative array with the following fields
	//				name - disk file name
	//				type - mime-type
	//				size - size, in bytes
	//
	//	Returns string containing file list in XML format
	//
	{
		$filename = base64_encode($fileinfo['name']);

		if(!strlen($fileList))
			$fileList = '<'.'?xml version="1.0"?'.'><FILELIST></FILELIST>';

		$xml = new SimpleXMLElement($fileList);

		$file = $xml->addChild('FILE');
		$file->addAttribute('FILENAME', $filename);
		$file->addAttribute('MIME_TYPE', $fileinfo['type']);
		$file->addAttribute('FILESIZE', $fileinfo['size']);
		$file->addAttribute('DISKFILENAME', $filename);
		$file->addAttribute('SCREENFILENAME', $filename);

		return $xml->asXml();
	}

	/**
	 * Returns message data in database format
	 * 
	 * @return array
	 */
	public static function extractSenderVars(&$message, $companyVariables=array())
	{
		$content = $message['MMM_CONTENT'];
		$subject = $message['MMM_SUBJECT'];

		// Try to find variables for extraction
		$sender = User::getInfo($message['MMM_USERID']); 
		$sender['C_EMAILADDRESS'] = $sender['C_EMAILADDRESS'][0];

		$doExtract = false;

		$vars = ContactType::getFieldsNames(false, false, true);
		$contactVariables = array('NAME' => '');
		foreach($vars as $key=>$val) {

			$user_key = preg_replace('/^C_/i', '', $key);
			$contactVariables[$user_key] = $val;

			// replace sender variable here
			if(isset($sender[$key])) {
				$content = str_ireplace('{MY_'.$user_key.'}', $sender[$key], $content);
				$subject = str_ireplace('{MY_'.$user_key.'}', $sender[$key], $subject);
			}
			
			if(strpos($subject.$content, '{'.$user_key.'}') !== false) {
				$doExtract = true;
			}
		}

		$user_name = User::getName($message['MMM_USERID'], true);
		$content = str_ireplace('{MY_NAME}', $user_name, $content);
		$subject = str_ireplace('{MY_NAME}', $user_name, $subject);

		$comVars = Company::get();

		foreach($companyVariables as $key=>$val) {
			if(strpos($content, '{'.$key.'}') !== false) {
				$doExtract = true;
			}
			// replace company variable here
			$content = str_ireplace('{'.$key.'}', $comVars[$val[1]], $content);
			$subject = str_ireplace('{'.$key.'}', $comVars[$val[1]], $subject);
		}

		if(strpos($content, '{UNSUBSCRIBE}') !== false || strpos($subject.$content, '{NAME}') !== false	|| strpos($content, '{MANAGE_') !== false) {
			$doExtract = true;
		}

		$message['MMM_CONTENT'] = $content;
		$message['MMM_SUBJECT'] = $subject;
		$message['doExtract'] = $doExtract;
		$message['contactVariables'] = $contactVariables;
	}

	/**
	 * Returns message data in database format
	 * 
	 * @return array
	 */
	public static function extractRecipientVars(&$message, $contact)
	{
		$content = $message['MMM_CONTENT'];
		$subject = $message['MMM_SUBJECT'];

		if(!empty($contact['C_ID'])) {
			$href = Contact::getSubscribeLink($contact['C_ID'], true);
			$content = preg_replace('/href=".*{UNSUBSCRIBE}"/', 'href="'.$href.'&do=unsubscribe"', $content); // preg_replace fixes IE bug
			$content = preg_replace('/href="{MANAGE_(YOUR_)?SUBSCRIPTION_URL}"/', 'href="'.$href.'"', $content);
			$content = str_replace(array('{MANAGE_SUBSCRIPTION_URL}', '{MANAGE_YOUR_SUBSCRIPTION_URL}'), '<a href="'.$href.'">'.$href.'</a>', $content);
			$content = preg_replace('/(href[\s]*=[\s*][\'"]){REQUEST_LIST_URL}/', '$1'.$href.'&t=requests', $content); 
			$content = str_replace('{REQUEST_LIST_URL}', '<a href="'.$href.'&t=requests">'.$href.'&t=requests</a>', $content);
			$contact['C_NAME'] = Contact::getName($contact['C_ID'], false, $contact, false);

			// replace contact variables
			foreach($message['contactVariables'] as $key=>$val) {

				if(isset($contact['C_'.$key])) {
					$content = str_ireplace('{'.$key.'}', htmlspecialchars($contact['C_'.$key]), $content);
					$subject = str_ireplace('{'.$key.'}', $contact['C_'.$key], $subject);
					$content = str_ireplace('{C_'.$key.'}', htmlspecialchars($contact['C_'.$key]), $content);
					$subject = str_ireplace('{C_'.$key.'}', $contact['C_'.$key], $subject);
				} else {
					$content = str_ireplace('{'.$key.'}', '', $content);
					$subject = str_ireplace('{'.$key.'}', '', $subject);
					$content = str_ireplace('{C_'.$key.'}', '', $content);
					$subject = str_ireplace('{C_'.$key.'}', '', $subject);
				}
			}
		} else {
			foreach($message['contactVariables'] as $key=>$val) {
				$content = str_ireplace('{'.$key.'}', '', $content);
				$subject = str_ireplace('{'.$key.'}', '', $subject);
				$content = str_ireplace('{C_'.$key.'}', '', $content);
				$subject = str_ireplace('{C_'.$key.'}', '', $subject);
			}
			$content = str_replace('{UNSUBSCRIBE}', '', $content);
			$content = str_replace('{REQUEST_LIST_URL}', '', $content);
		}
		$message['MMM_CONTENT'] = $content;
		$message['MMM_SUBJECT'] = $subject;
	}

	/**
	 * Returns message data in database format
	 * 
	 * @return array
	 */
	public function preview($cid=false)
	{
		$message = $this->getData();

		self::extractSenderVars($message);

		if($message['doExtract']) {
			try { 
				$contact_info = Contact::getInfo($cid);
			} catch (Exception $e) { 
				return; 
			}
			self::extractRecipientVars($message, $contact_info);
		}
		return $message;
	}

}
?>