<?php

class FolderRights extends RightsItem
{	
	protected $values = array(1, 3, 7);
	protected $parent_id;
	protected $status;
	
	public function __construct($folder)
	{
		parent::__construct($folder['ID'], $folder['NAME']);
		$this->parent_id = $folder['ID_PARENT'];
		$this->status = $folder['STATUS'];
	}
	
	/**
	 * Returns parent's id
	 * 
	 * @return string
	 */
	public function getParent()
	{
		return $this->parent_id;
	}
	
	public function isInherit()
	{
		return false;
	}
	
	/**
	 * @return int
	 */
	public function encodeValue($value)
	{
		$result = 0;
		foreach ($value as $v) {
			$result = $result | $v;
		}
		return parent::encodeValue($result);
	}
	

	public function __toArray($fields = array(), $key = true)
	{
		$info = array(
			'id' => $this->id,
			'name' => $this->name,
			'value' => $this->value,
			'user' => $this->user_value,
			'group' => $this->group_value,
			'folder' => 1,
			'inherit' => false,
			'status' => $this->status,
			'children' => array() 
		);
		if (!$fields) {
			return $info;
		}
		$result = array();
		foreach ($fields as $f) {
			if ($key) {
				$result[$f] = $info[$f];
			} else {
				$result[] = $info[$f];
			}
		}
		return $result;
	}	
}

?>