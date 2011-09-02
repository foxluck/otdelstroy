<?php

class AARightsModel extends RightsModel 
{
	public function __construct()
	{
		parent::__construct("AA");
		$this->setDescriptor(new AARightsDescriptor());
	}
	
}
?>