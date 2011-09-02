<?php

class UserException extends Exception
{
	protected $log_message;
	
	public function __construct($message, $log_message = false)
	{
	    if (!$log_message) {
	        $log_message = $message;
	    }
		$this->log_message = $log_message;
		parent::__construct($message);
	}
	
	public function getLogMessage()
	{
		return $this->log_message;
	}
	
	public function __toString()
	{
	    return '<span style="color:red">'.$this->getMessage()."</span>"; 
	}
}

?>