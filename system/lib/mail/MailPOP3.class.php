<?php

class MailPOP3 extends MailRetrieval
{
	protected function auth()
	{
		$this->read(1024);
		$this->write("USER ".$this->login);
		$data = $this->write("PASS ".$this->password);
		if (stripos($data, '+OK') === 0) {
			return true;
		} else {
			$this->close();
			throw new Exception(_s('Incorrect login or password.'), 601);
		}
	}
	
	public function count()
	{
		$data = $this->write("STAT");
		if (preg_match('/^\+OK (\d+)/i', $data, $match)) {
			return $match[1];
		}
		return 0;
	}
	
	public function get($id)
	{
		$data = $this->write("RETR ".$id, true);
		$result = array('headers' => '', 'body' => '');
		$pattern = "/^(.+?)\r?\n\r?\n(.*)/s";
		if (stripos($data, '+OK') === 0) {
			$state = false;
			while(rtrim($data = $this->read()) != '.') {
				if (!$state) {
					$result['headers'] .= $data;
					if (preg_match($pattern, ltrim($result['headers']), $match)) {
						$result['headers'] = $match[1];
						$result['body'] = $match[2];
						$state = true;
					}
				} else {
					$result['body'] .= $data;
					/*
					if (strpos(substr($result['body'], 0, 256), ':') !== false) {
						if (preg_match($pattern, $result['body'], $match)) {
							$result['headers'] .= $match[1];
							$result['body'] = $match[2];	
						}
					}
					*/
				}
			}

		} 
		return $result;
	}
	
	public function delete($id)
	{
		return $this->write("DELE ".$id, true);
	}
	
	
	public function close()
	{
		$this->write("QUIT", false);
		$this->read(1024);

		parent::close();
	}
	
}