<?php

class UGAjaxErrorsIndexAction extends UGAjaxAction
{
	/**
	 * Exception instance
	 * 
	 * @var Exception
	 */
	protected $e;
	public function __construct($e)
	{
		$this->e = $e;
	}

	public function getResponse()
	{
		return json_encode(array('status' => 'ERR', 'error' => $this->e->getMessage(), 'data' => ''));
	}
}
?>