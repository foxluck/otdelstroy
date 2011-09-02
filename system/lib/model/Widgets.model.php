<?php

class WidgetsModel extends DbModel
{
	protected $table = "WG_WIDGET";
	protected $id = "WG_ID";
	
	public function getByCode($fprint)
	{
	    return $this->getByKey('WG_FPRINT', $fprint, false);
	}
	
	public function add($type, $subtype, $desc, $lang)
	{
		$sql = "INSERT INTO ".$this->table." 
				SET WT_ID = s:type,
					WST_ID = s:subtype,
					WG_FPRINT = s:fprint, 	
					WG_DESC = s:desc,
					WG_USER = s:user_id, 	
					WG_LANG = s:lang, 		
					WG_CREATED_BY = s:user_name, 	
					WG_CREATED_DATETIME = s:time";
		$data = array(
			'type' => $type,
			'subtype' => $subtype,
			'fprint' => substr(md5(rand()), 0, 8),
			'desc' => $desc,
			'lang' => $lang,
			'user_id' => User::getId(),
			'user_name' => User::getName(false, true),
			'time' => date("YmdHis")
		);
		return $this->prepare($sql)->query($data)->lastInsertId();
	}
	
	public function set($widget_id, $field, $value)
	{
	    $sql = "UPDATE ".$this->table." 
	    		SET ".$field." = s:value 
	    		WHERE WG_ID = i:id";
	    return $this->prepare($sql)->exec(array('id' => $widget_id, 'value' => $value));
	}
	
	public function deleteParams($widget_id, $params)
	{
	    $sql = "DELETE FROM WG_PARAM 
	    		WHERE WG_ID = i:widget_id AND 
	    			  WGP_NAME IN ('".implode("', '", $this->escape($params))."')";
	    return $this->prepare($sql)->exec(array('widget_id' => $widget_id));
	}

	public function setParam($widget_id, $name, $value)
	{
		$sql = "REPLACE INTO WG_PARAM 
				SET WG_ID = i:widget_id, WGP_NAME = s:name, WGP_VALUE = s:value";
		return $this->prepare($sql)->exec(array('widget_id' => $widget_id, 'name' => $name, 'value' => $value));
	}
	
	public function getByType($type)
	{
		return $this->getByKey('WT_ID', $type, false, true);
	}
	
	public function delete($widget_id)
	{
	    $sql = "DELETE FROM WG_PARAM WHERE WG_ID = i:id";
	    if ($this->prepare($sql)->exec(array('id' => $widget_id))) {
	        $sql = "DELETE FROM ".$this->table." WHERE WG_ID = i:id";
	        return $this->prepare($sql)->exec(array('id' => $widget_id));
	    }
	    return false;
	}
}
?>