<?php

class NotificationMessage
{
	/**
	 * @var string
	 */
	protected $subject;
	/**
	 * @var string
	 */
	protected $content;

	function NotificationMessage($subject, $content) {
		$this->subject = $subject;
		$this->content = $content;
	}

	/**
	 * Returns id of sent message
	 * 
	 * @param string $to
	 * @return int
	 */
	public function send($to)
	{
		$msg = Mailer::composeMessage();
		$msg->addTo($to);
		$msg->addSubject($this->subject);
		$msg->addContent($this->content);
		return Mailer::send($msg);
	}

	/**
	 * Returns id of sent message
	 * 
	 * @param array $contact_ids
	 * @return int
	 */
	public function sendToContacts($contact_ids)
	{
		$to = array();
		foreach($contact_ids as $id) {
			$to[] = Contact::getName($id, Contact::FORMAT_NAME_EMAIL, false, false);
		}
		$to = join(', ', $to);
	
		$msg = Mailer::composeMessage();
		$msg->addTo($to);
		$msg->addSubject($this->subject);
		$msg->addContent($this->content);
		return Mailer::send($msg);
	}
	
}
?>