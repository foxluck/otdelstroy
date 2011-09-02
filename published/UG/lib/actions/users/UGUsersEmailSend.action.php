<?php

class UGUsersEmailSendAction extends UGViewAction 
{
	protected $users = array();
	protected $send_to = ''; 
	
	public function __construct()
	{
		parent::__construct();
		$this->title = _('Compose message');
		$this->users = Env::Get('user_ids');
		if ($this->users) {
			$this->users = explode(',', $this->users);
			$this->send_to = array();
			foreach($this->users as $contact_id) {
				$info = Contact::getInfo($contact_id);
				if ($info['C_EMAILADDRESS']) {
					$this->send_to[] = Contact::getName($contact_id, Contact::FORMAT_NAME_EMAIL);
				}
			}
			$this->send_to = join(', ', $this->send_to);
			if($this->send_to) {
				$this->send_to .= ', ';
			}
		} elseif (($cid = Env::Get('id', Env::TYPE_INT, 0)) && Env::Get('email')) {
			$this->users = array($cid);
			$this->send_to = Contact::getName($cid)." <".Env::Get('email').">";
		} elseif (($cid = Env::Get('contact_id', Env::TYPE_BASE64_INT, 0)) && Env::Get('email')) {
			$this->users = array($cid);
			$this->send_to = Contact::getName($cid)." <".Env::Get('email').">";
			$this->smarty->assign('back_url', 'index.php?mod=users&C_ID='.base64_encode($cid));
		} else {		
			$this->users = array();
			$this->send_to = '';
		}

		$this->data = Env::Post('data');
		if ($this->data) {
			$this->send();
		} else {
			$this->form();
		}
	}

    public function form()
    {
		// Generate editor instance
		$dsrte = new dsRTE('dsrte', false, false);
    	$this->smarty->assign('editor_scripts', $dsrte->getScripts());
    	$this->smarty->assign('editor_HTML', $dsrte->getHTML(Env::Post('dsrte_text', Env::TYPE_STRING, '')));

    	$this->smarty->assign('send_from', Contact::getName(User::getContactId(), Contact::FORMAT_NAME_EMAIL));
    	$this->smarty->assign('send_to', $this->send_to);
    	$this->smarty->assign('redactor_id', 'dsrte');
    	$this->smarty->assign('title', $this->title);
    	
    }

    public function send()
    {
        $limits = Limits::get('MM');
        if ($limits) {
	        $emails = preg_split("/[,;]/", $this->data['to']);
	        $emails = array_merge($emails, preg_split("/[,;]/", $this->data['cc']));
	        $emails = array_merge($emails, preg_split("/[,;]/", $this->data['bcc']));
	        
	        foreach ($emails as $i => $e) {
	            $e = trim($e);
	            if (strpos($e, '@') === false) {
	                unset($emails[$i]);
	            } else {
	                $emails[$i] = $e;
	            }
	        }
	        $emails = array_unique($emails);
	        $model = new DbModel();
	        $sql = "SELECT MMS_COUNT FROM MMSENT WHERE MMS_DATE	= s:date";
	        $c = $model->prepare($sql)->query(array('date' => date("Y-m-d")))->fetchField('MMS_COUNT');
	        $sql = "SELECT COUNT(*) C FROM MMMESSAGE WHERE MMM_STATUS = 1 AND DATE(MMM_DATETIME)= s:date";
	        $c += $model->prepare($sql)->query(array('date' => date("Y-m-d")))->fetchField('C');
	        if ($c + count($emails) > $limits) {
	            try {
	                throw new LimitException(_('Daily quota for outgoing messages has been exceeded ') . $limits);
	            } catch (LimitException $e) {
	                $this->smarty->assign('error', $e->__toString());
	                $this->smarty->assign('subject', $this->data['subject']);
	                $this->form();
	                return;
	            }
	        }
        }
       

		$msg = Mailer::composeMessage();
		$msg->addTo($this->data['to']);
		$msg->addCc($this->data['cc']);
		$msg->addBcc($this->data['bcc']);
		$msg->addSubject($this->data['subject']);
		$content = Env::Post('dsrte_text');
		if(!preg_match('/<a[^>]+href\s*=[^>]+>/i', $content) && !preg_match('/<img[^>]+src\s*=[^>]+>/i', $content)) {
			$content = preg_replace("/(?:http(s?):\/\/(www\.)|http(s?):\/\/|(www\.))([a-z0-9_\.\-]{2,}\.[a-z]{2,4}[a-z0-9_\.\-\/\?&=@:%]*)/i",
				"<a href=\"http$1$3://$2$4$5\" target=\"_blank\">$0</a>", $content);
			$content = preg_replace("/ftp:\/\/[a-z0-9_\.\-]{2,}\.[a-z]{2,4}[a-z0-9_\.\-\/\?&=@:%]*/i",
				"<a href=\"$0\" target=\"_blank\">$0</a>", $content);
			$content = preg_replace("/[a-zA-Z0-9]+[\.\-_]?[a-zA-Z0-9]+@([a-z0-9]+[\.|\-]?[a-z0-9]+){1,4}\.[a-z]{2,4}/",
				"<a href=\"mailto:$0\">$0</a>", $content);
		}

		$msg->addContent($content);
		
		if(!($mid = Env::Post('mid', Env::TYPE_INT))) {

			Mailer::send($msg);

		} else {

			$draft = Mailer::getMessage($mid);

			$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath('mm', 'attachments').DIRECTORY_SEPARATOR.$mid;
			foreach($draft['attachments'] as $i=>$f) {
				$draft['attachments'][$i]['path'] = $path.DIRECTORY_SEPARATOR.$f['name'];
			}
			$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath('mm', 'images').DIRECTORY_SEPARATOR.$mid;
			foreach($draft['images'] as $i=>$f) {
				$draft['images'][$i]['path'] = $path.DIRECTORY_SEPARATOR.$f['name'];
			}
			$msg->addAttachments($draft['attachments']);
			$msg->addImages($draft['images']);
			$msg->addId($mid);
			Mailer::send($msg, 'now', true);
		}

		$addr_to = MailParsers::parseAddressString($this->data['to']);
		$addr_cc = MailParsers::parseAddressString($this->data['cc']);
		$addr_bcc = MailParsers::parseAddressString($this->data['bcc']);
		$sent = count($addr_to['accepted']) + count($addr_cc['accepted']) + count($addr_bcc['accepted']);
		$not_sent = count($addr_to['bounced']) + count($addr_cc['bounced']) + count($addr_bcc['bounced']);
	
		$res = array();
		if($sent) {
			$res[] = sprintf(_('Message was successfully sent to %d recipient(s)'), $sent);
		}
		if($not_sent) {
			$res[] = sprintf(_('%d address(es) bounced'), $not_sent);
		}
		$sent_result = join('. ', $res);
		if ($contact_id = Env::Get('contact_id')) {
		    $_SESSION['MESSAGE'] = $sent_result;
		    Url::go('/'.User::getAppId().'/index.php?mod=users&C_ID='.$contact_id);
		}
		echo <<<HTML
<script type="text/javascript">
if (parent && parent.document.app) {
	parent.document.app.showInfoMessage('{$sent_result}', 1);
	parent.document.app.closeSubframe();
} else {
	location.href = 'index.php';
}
</script>
HTML;
        exit;
	}
}

?>