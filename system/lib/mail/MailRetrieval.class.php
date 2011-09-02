<?php

class MailRetrieval 
{
	const TIMEOUT = 10; 
	
	protected $server;
	protected $port;
	protected $login;
	protected $password;
	
	protected $handler;
	
	public $log = null;
	
	public function __construct($server, $port, $login, $password)
	{
		$this->server = $server;
		$this->port = $port;
		$this->login = $login;
		$this->password = $password;
		
		$this->open();
	}
	
	public function logging($flag) 
	{
		if ($flag) {
			$this->log = "";
		} else {
			$this->log = null;
		}
	} 
	
	public function open() 
	{
		$this->handler = @fsockopen($this->server, $this->port, $errno, $errstr, self::TIMEOUT);
		if ($this->handler) {
			$this->auth();
		} else {
			throw new Exception("Could not connect to the server.", 602);
		}
	}
	
	protected function auth() {}
	
	public function count() {}
	
	public function get($id) {}
	
	public function delete($id) {}
	
	public function close() 
	{
		fclose($this->handler);		
	}
	
	public function write($command, $return = true)
	{
		if ($this->log !== null) {
			$this->log .= $command."\r\n";
		}
		fputs($this->handler, $command."\r\n");
		if ($return) {
			return $this->read();
		}
	} 
	
	public function read($length = false)
	{
		if ($length) {
			$data = fgets($this->handler, $length);
		} else {
			$data = fgets($this->handler);
		}
		if ($this->log !== null) {
			$this->log .= "\r\n".$data;
		}
		return $data;
	}
}

?>