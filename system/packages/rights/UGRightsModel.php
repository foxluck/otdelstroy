<?php

class UGRightsModel extends RightsModel 
{
	public function __construct()
	{
		parent::__construct("UG");
		$this->setDescriptor(new UGRightsDescriptor());
	}
	
}
?>