<?php

class MailReader
{
	protected $protocol;
	
	/**
	 * 
	 * @var MailRetrieval
	 */
	public $reader;
	public function __construct($protocol, $server, $port, $login, $password)
	{
		$this->protocol = strtolower($protocol);
		switch ($protocol) {
			case 'pop3': 
				$this->reader = new MailPOP3($server, $port, $login, $password);
				break;
			case 'imap':
				$this->reader = new MailIMAP($server, $port, $login, $password);
				break;
			default:
				throw new Exception(_s('Incorrect or unkown protocol.'));
		}
	}
	
	public function logging($flag)
	{
		$this->reader->logging($flag);
	}
	
	public function getLog()
	{
		return $this->reader->log;
	}
	
	public function count()
	{
		return $this->reader->count();
	}
	
	public function get($id)
	{
		$message = $this->reader->get($id);
		$decoder = new MailDecoder();
		return $decoder->decode($message);
	}
	
	public function delete($id)
	{
		return $this->reader->delete($id);
	}
	
	public function close() {
		$this->reader->close();
	}
}

?>