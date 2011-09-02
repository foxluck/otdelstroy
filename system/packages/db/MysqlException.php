<?
	class MysqlException extends Exception {
		var $Errno;
		var $QueryStr;
		
		public function __construct ($message, $errno, $queryStr = "")  {
			parent::__construct($message);
			$this->Errno = $errno;
			$this->QueryStr = $queryStr;
		}
	}
	
?>