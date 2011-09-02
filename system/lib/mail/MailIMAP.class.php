<?php

class MailIMAP extends MailRetrieval 
{
	protected function auth()
	{
		$this->read(1024);
		$data = $this->write("LOGIN ".$this->login." ".$this->password);
		if (stripos($data, 'OK') === 0) {
			return true;
		} else {
			$this->close();
			throw new Exception(_s('Incorrect login or password.'));
		}
	}	
}