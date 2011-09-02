<?php

abstract class MailAction extends UGViewAction
{
	const TITLE = 0;
	const VALUE = 1;
	const SHOW = 2;
	const EDIT = 3;
	const TYPE = 4;
	
	protected $app_id;
	protected $fields = array();
	
	public $title = "";
	/**
	 * @var Smarty
	 */
	protected $smarty;
	/**
	 * Path to template
	 * 
	 * @var string
	 */
	protected $template;
	
	public function __construct($app_id, $smarty, $template)
	{
		$this->app_id = $app_id;
		$this->smarty = Registry::get($smarty);
		$this->template = $template;
		
		$this->fields = array(
			'from' => array(_("From"), "", false, false, array("input", "text")),
			'to' => array(_("To"), "", true, true, array("input", "text")),
			'subject' => array(_("Subject"), "", true, true, array("input", "text")),
			'content' => array(_("Content"), "", true, true, array("textarea")),
			//'attachments' => array("", false, false)
		);		
	}
	
	public function getValue($field)
	{
	    return Env::Post($field);
	}
	
	public function send()
	{
		$message = Mailer::composeMessage($this->app_id);
		foreach ($this->fields as $field => $options) {
            $method = "add".ucfirst($field);
			if ($options[self::SHOW]) {
				if ($field == 'content') {
					$message->$method($this->getContent());
				} else {
					$message->$method($this->getValue($field));
				}
			} elseif ($options[self::VALUE]) {
    		    $message->$method($options[self::VALUE]);
			}		
		}
		
		if(!($id = Env::Post('mid', Env::TYPE_INT))) {

			Mailer::send($message);

		} else {

			$draft = Mailer::getMessage($id);
			$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath('mm', 'attachments').DIRECTORY_SEPARATOR.$id;
			foreach($draft['attachments'] as $i=>$f) {
				$draft['attachments'][$i]['path'] = $path.DIRECTORY_SEPARATOR.$f['name'];
			}
			$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath('mm', 'images').DIRECTORY_SEPARATOR.$id;
			foreach($draft['images'] as $i=>$f) {
				$draft['images'][$i]['path'] = $path.DIRECTORY_SEPARATOR.$f['name'];
			}
			$message->addAttachments($draft['attachments']);
			$message->addImages($draft['images']);
			$message->addId($id);
			Mailer::send($message, 'now', true);
		}
	}
	
	public function getContent()
	{
		return Env::Post('dsrte_text');
	}
	
	public function onSend()
	{
	}

	
	public function prepareData()
	{
		if (Env::Post('status') == 'send') {
			$this->send();
			$this->onSend();
			exit;
		}
	    
		$this->smarty->assign('status', Env::Post('status', Env::TYPE_STRING, ''));
		$this->smarty->assign('fields', $this->fields);		
	}	
	
	public function getResponse()
	{
		$this->prepareData();
		return $this->smarty->fetch($this->template);
	}
}

?>