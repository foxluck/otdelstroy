<?php

class MailModel extends DbModel
{

	protected $table = "MMMESSAGE";
	
	public function getNextId()
	{
		$sql = "SELECT MAX(MMM_ID) M FROM ".$this->table;
		return $this->query($sql)->fetchField('M') + 1;
	}
}

?>