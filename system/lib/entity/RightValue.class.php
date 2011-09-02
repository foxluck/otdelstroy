<?php 
class RightValue
{
	protected $value;
	
	public function __construct($value)
	{
		$this->value = $value;	
	}

	public function isFull() 
	{
		return $this->value >= Rights::RIGHT_FOLDER;	
	}
	
	public function isWrite()
	{
		return $this->value >= Rights::RIGHT_WRITE;
	}
	
	public function isRead()
	{
		return $this->value >= Rights::RIGHT_READ;
	}
	
	public function value()
	{
		return $this->value;
	}
	
	public function __toString()
	{
		return $this->value;	
	}
	
}
?>