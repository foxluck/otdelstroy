<?php

class UGErrorsIndexAction extends UGViewAction
{
	
	/**
	 * @var Exception
	 */
	protected $e;
	
	public function __construct(Exception $e)
	{
		parent::__construct();
		$this->e = $e;
		if ($this->e instanceof UserException) {
			error_log($this->e->getLogMessage());
		}
	}
	
	
	public function getResponse()
	{
		if ($this->e instanceof LimitException) {
			return $this->e->__toString();
		} elseif (defined('DEVELOPER') && DEVELOPER) {
	    	return $this->e->__toString();
		} else {
			error_log($this->e->getMessage());
			return _('Error');
		}
	}
	
}
?>