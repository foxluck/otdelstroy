<?php

class RightsItem 
{
	const TYPE_USER = 1;
	const TYPE_GROUP = 2;
	
	const TITLE = "__TITLE__";

	protected $type = 0;
	protected $id;
	protected $path;
	
	protected $name;
	protected $user_value = 0;
	protected $group_value = 0;
	protected $value = 0; 
	
	protected $values = array(1);
	
	public function __construct($id, $name, $type = false)
	{
		$this->id = $id;
		$this->name = $name;	
		$this->type = $type;
	}
	
	public function setPath($path)
	{
		$this->path = $path;
	}
	
	public function saveRight($value)
	{
		if (!$this->type) {
			return false;
		}
		$value = $this->encodeValue($value);
		$table = $this->type == self::TYPE_GROUP ? "UG_ACCESSRIGHTS" : "U_ACCESSRIGHTS"  ; 
		$sql = new CReplaceSqlQuery($table);
		$sql->addConditions("AR_PATH", $this->path);
		$sql->addConditions("AR_OBJECT_ID", $this->id);
		$sql->addFields(array("AR_VALUE" => $value), array("AR_VALUE"));
		Wdb::runQuery($sql);
	}
	
	/**
	 * @param int $value
	 */
	protected function encodeValue($value)
	{
		if (in_array($value, $this->values)) {
			return $value;
		} else {
			return 0;
		}
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setRight($value, $type = false)
	{
	
		if ($type == self::TYPE_USER) {
			$this->user_value = $value;
			$this->value = max($this->value, $this->user_value, $this->group_value);
		}
		elseif ($type == self::TYPE_GROUP) {
			$this->group_value = max($value, $this->group_value);
			$this->value = max($this->value, $this->user_value, $this->group_value);
		} 
		else {
			$this->value = $value;
		}
	}
	
	public function getRight($type = false)
	{
		switch ($type) {
			case self::TYPE_USER:
				return $this->user_value;
			case self::TYPE_GROUP:
				return $this->group_value;
			default:
				return $this->value;
		}
	}
	
	public function resetRights()
	{
		$this->value = $this->user_value = $this->group_value = 0;
	}
	
	public function __toArray()
	{
		return array(
			'id' => $this->id,
			'name' => $this->name,
			'value' => $this->value,
			'user' => $this->user_value,
			'group' => $this->group_value 
		);
	}
	
	
	
	
}

?>