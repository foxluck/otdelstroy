<?php

class HideException extends Exception
{
	
    public function __toString()
    {
        return $this->message;
    }
}

?>