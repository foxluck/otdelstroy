<?php

class RightsSection 
{
	
	protected $id;
	protected $name;
	protected $path;
	
	protected $rights = array();
	
	
	public function getObjects($object = false)
	{
		if ($object) {
			if (isset($this->rights[$object])) {
				return $this->rights[$object];
			} else {
				return false;
			}
		}
		return $this->rights;
	}
	
	public function __construct($id, $name)
	{
		$this->id = $id;
		$this->name = $name;
	}
	
	/**
	 * Set path
	 * @param $path
	 */
	public function setPath($path)
	{
		$this->path = $path.'/'.$this->name;
	}
	
	/**
	 * @param RightsItem $right
	 */
	public function addRight(RightsItem $right)
	{
		$rights = func_get_args();
		foreach ($rights as $right) {
			if ($right instanceof RightsItem) {
				$right->setPath($this->path);
				$this->rights[$right->getId()] = $right;
			}
		}
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}	
	
	public function getRight($object_id, $type = false)
	{
		if (isset($this->rights[$object_id])) {
			return $this->rights[$object_id]->getRight($type);
		} else {
			return false;
		}
	}
	
	public function setRight($right_info, $type = false)
	{
		if (isset($this->rights[$right_info['OBJECT_ID']])) {
			$this->rights[$right_info['OBJECT_ID']]->setRight($right_info['VALUE'], $type);
		}
	}
	
	
	/**
	 * Reset rights
	 */
	public function resetRights()
	{
		foreach ($this->rights as $right) {
			$right->resetRights();
		}
	}
	
	
	/**
	 * Get to array
	 * @return array
	 */
	public function __toArray()
	{
		$result = array(
			'id' => $this->id,
			'name' => $this->name,
			'rights' => array() 
		);
		
		foreach ($this->rights as $rights_item) {
			$result['rights'][] = $rights_item->__toArray();	
		}
		return $result;
	}

}
?>