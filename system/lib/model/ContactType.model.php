<?php

class ContactTypeModel extends DbModel
{
	protected $table = "CONTACTTYPE";

	/**
	 * Adds new field and returns id of the new field
	 * 
	 * @param string $dbname - DBNAME of the field
	 * @param string $type - TEXT|MENU|NUMERIC...
	 * @param array $name - array('eng' => 'Eng Name', 'rus' => 'Rus Name')
	 * @param $options
	 * @param bool $standart
	 * @return int
	 */
	public function addField($dbname, $type, $name, $options, $section, $sorting, $standart = false)
	{
	    $this->addCacheCleaner(new DbCacher('CONTACTFIELDS', 60, 'SYSTEM'));
		$sql = "INSERT IGNORE INTO CONTACTFIELD
				SET CF_DBNAME = s:dbname, CF_TYPE = s:type, CF_NAME = s:name,
					CF_OPTIONS = s:options, CF_SECTION = s:section, CF_SORTING = i:sorting, CF_STD = i:std";
		if ($type == 'MENU') {
		    $options = explode("\n", trim($options));
		}
		$params = array(
			'dbname' => $dbname,
			'type' => $type,
			'name' => json_encode($name),
			'options' => $options ? json_encode($options) : "",
			'section' => $section ? (int)$section : null,
			'sorting' => $sorting,
			'std' => $standart
		);
		return $this->prepare($sql)->query($params)->lastInsertId();
	}
	
	/**
	 * Returns id of the previous field orber by CF_SORTING in each section
	 * 
	 * @param int $sorting - sorting of the current field
	 * @param int|null $section - section of the current field
	 * @return int
	 */
	public function getPreviousField($sorting, $section = null)
	{
	    $sql = "SELECT CF_ID FROM `CONTACTFIELD` 
	    		WHERE CF_SORTING < i:sorting".($section ? " AND CF_SECTION = i:section" : " AND CF_SECTION IS NULL")."  
	    		ORDER BY CF_SORTING DESC LIMIT 1";
        $after_id = $this->prepare($sql)
                    ->query(array('sorting' => $sorting, 'section' => $section))
                    ->fetchField('CF_ID');
                    
        // if field is first in section
        if (!$after_id && $section) {
            $after_id = $section; 
        }
        return $after_id;
	}
	
	/**
	 * Deletes the field
	 * 
	 * @param $field_id
	 * @return bool
	 */
	public function deleteField($field_id)
	{
        $this->addCacheCleaner(new DbCacher('CONTACTFIELDS', 60, 'SYSTEM'));	    
		$sql = "DELETE FROM `CONTACTFIELD` WHERE CF_ID = i:id";
		return $this->prepare($sql)->exec(array('id' => $field_id));
	}
	
	/**
	 * Set name of the field by it id
	 * 
	 * @param int $field_id
	 * @param array $name
	 * @return bool
	 */
	public function setFieldName($field_id, $name) 
	{
	    $this->addCacheCleaner(new DbCacher('CONTACTFIELDS', 60, 'SYSTEM'));
	    $sql = "UPDATE CONTACTFIELD SET CF_NAME = s:name WHERE CF_ID = i:id";
	    return $this->prepare($sql)->exec(array('id' => $field_id, 'name' => json_encode($name)));
	}
	
	public function setFieldSorting($field_id, $sorting) 
	{
	    $this->addCacheCleaner(new DbCacher('CONTACTFIELDS', 60, 'SYSTEM'));
	    $sql = "UPDATE CONTACTFIELD SET CF_SORTING = i:sorting WHERE CF_ID = i:id";
	    return $this->prepare($sql)->exec(array('id' => $field_id, 'sorting' => $sorting));
	}
	
	public function saveSorting($field_id, $section_id, $sorting)
	{
	    $this->addCacheCleaner(new DbCacher('CONTACTFIELDS', 60, 'SYSTEM'));
	    $sql = "UPDATE CONTACTFIELD 
	    		SET CF_SECTION = i:section_id, CF_SORTING = i:sorting 
	    		WHERE CF_ID = i:id";
	    return $this->prepare($sql)->exec(array(
	    	'id' => $field_id, 'sorting' => $sorting, 'section_id' => $section_id
	    ));
	}
	
	public function setDbName($field_id, $dbname) 
	{
	    $this->addCacheCleaner(new DbCacher('CONTACTFIELDS', 60, 'SYSTEM'));
	    $sql = "UPDATE CONTACTFIELD SET CF_DBNAME = s:dbname WHERE CF_ID = i:id";
	    return $this->prepare($sql)->exec(array('id' => $field_id, 'dbname' => $dbname));
	}
	
	public function saveField($field_id, $type, $name, $options, $section, $sorting, $standart = false)
	{
	    $this->addCacheCleaner(new DbCacher('CONTACTFIELDS', 60, 'SYSTEM'));
		$sql = "UPDATE CONTACTFIELD
				SET CF_TYPE = s:type, CF_NAME = s:name,
					CF_OPTIONS = s:options, CF_SECTION = s:section, CF_SORTING = i:sorting" . ($standart ? ", CF_STD = b:std" : "")."
				WHERE CF_ID = i:field_id";
		if ($type == 'MENU') {
		    $options = explode("\n", trim($options));
		}
		$params = array(
			'field_id' => $field_id,
			'type' => $type,
			'name' => json_encode($name),
			'options' => $options ? json_encode($options) : "",
			'section' => $section ? (int)$section : null,
			'sorting' => $sorting,
			'std' => $standart
		);
		return $this->prepare($sql)->query($params);
	}	
	
	/**
	 * Return all fields
	 * 
	 * @param $index
	 * @return unknown_type
	 */
	public function getFields($section_id = false)
	{
		$fields = array();
		if ($section_id) {
			$sql = "SELECT * FROM CONTACTFIELD 
					WHERE CF_SECTION = ".(int)$section_id." 
					ORDER BY `CF_SECTION` ASC, CF_SORTING ASC";
		} else {
		    $this->setCacher(new DbCacher('CONTACTFIELDS', 60, 'SYSTEM'));
		    $sql = "SELECT C1.* FROM CONTACTFIELD C1 LEFT JOIN 
					CONTACTFIELD C2 ON C1.CF_SECTION = C2.CF_ID
					ORDER BY C2.CF_SORTING, C1.CF_SORTING";
		}
		$data = $this->query($sql);
		foreach ($data as $row) {
			// Fix illegal format of json name
			if (preg_match('/^[a-z\s-]+$/ui', $row['CF_NAME'])) {
				$row['CF_NAME'] = $row['CF_NAME'];
			} else {
				$row['CF_NAME'] = json_decode($row['CF_NAME'], true); 
			}
			if ($row['CF_TYPE'] == 'EMAIL' && $row['CF_DBNAME'] != 'C_EMAILADDRESS') {
			    $row['CF_TYPE'] = 'VARCHAR';
			}
			if (!$row['CF_OPTIONS'] && in_array($row['CF_TYPE'], array('VARCHAR', 'EMAIL', 'URL'))) {
				$row['CF_OPTIONS'] = '255';
			}
			
			$field_info = array(
					'id' => $row['CF_ID'],
					'dbname' => $row['CF_DBNAME'],
					'type' => $row['CF_TYPE'],	
					'name' => $row['CF_NAME'],
					'options' => json_decode($row['CF_OPTIONS'], true),
					'standart' => $row['CF_STD'],
					'section' => $row['CF_SECTION'],
					'sorting' => $row['CF_SORTING']
			);
			$fields[$field_info['id']] = $field_info;  
		}
		return $fields;
	}
	
	public function getFieldInfo($dbname)
	{
	    $sql = "SELECT * FROM CONTACTFIELD WHERE CF_DBNAME = s:dbname";
	    $row = $this->prepare($sql)->query(array('dbname' => $dbname))->fetch();
		if (preg_match('/^[a-z\s-]+$/ui', $row['CF_NAME'])) {
			$row['CF_NAME'] = $row['CF_NAME'];
		} else {
			$row['CF_NAME'] = json_decode($row['CF_NAME'], true); 
		}
	    
		return array(
				'id' => $row['CF_ID'],
				'dbname' => $row['CF_DBNAME'],
				'type' => $row['CF_TYPE'],	
				'name' => $row['CF_NAME'],
				'options' => json_decode($row['CF_OPTIONS'], true),
				'standart' => $row['CF_STD'],
				'section' => $row['CF_SECTION'],
				'sorting' => $row['CF_SORTING']
		);
	    
	}
	
	/**
	 * 
	 * @param string $name - name of the type
	 * @param array $formats - array("1 2, 4", "1 2 3") 
	 * @param array $sections
	 * array( 
	 * 		array(
	 *      	// Name of the section
	 *      	array('eng' => 'Eng Name', 'rus' => 'Rus Name'),
	 * 			array(
	 * 				array(field_id, required, unique),
	 *	 			...
	 * 			)						
	 *  	),
	 *  ...
	 *  )
	 * @param bool $standart
	 * @return int
	 */
	public function addType($name, $required, $formats, $fields, $standart = false)
	{
		$params = array(
			'name' => json_encode($name),
			'options' => json_encode(array(
				'fname_required' => $required,
				'fname_format' => $formats, 
				'fields' => $fields)
			),
			'standart' => $standart
		); 
		
		$sql =  "INSERT INTO ".$this->table." 
				 SET CT_NAME = s:name, 
				 	 CT_OPTIONS = s:options, 
				 	 CT_STD = b:standart";
		return $this->prepare($sql)->query($params)->lastInsertId();
	}
	
	/**
	 * Returns contact type description
	 * 	array(
	 * 		'id' => 
	 * 		'name' => 
	 * 		'standart' =>
	 * 		'formats' =>
	 * 		'fields' =>
	 *  )
	 * @param $id
	 * @return array
	 */
	public function getType($id)
	{
	    $this->setCacher(new DbCacher('CONTACTTYPE'.$id, 60, 'SYSTEM'));
	    
		$sql = "SELECT * FROM ".$this->table." WHERE CT_ID = i:id";
		$data = $this->prepare($sql)->query(array('id' => $id))->fetch();
		if (!$data) {
			return array();
		}
		if (preg_match('/^[a-z\s-]+$/ui', $data['CT_NAME'])) {
			$data['CT_NAME'] = $data['CT_NAME'];
		} else {
			$data['CT_NAME'] = json_decode($data['CT_NAME'], true); 
		}
		$type_info = array(
			'id' => $id,
			'name' => $data["CT_NAME"],
			'standart' => $data['CT_STD']
		);
		$options = json_decode($data['CT_OPTIONS'], true);
		$type_info['fname_required'] = $options['fname_required'];
		$type_info['fname_format'] = $options['fname_format'];
		$type_info['fields'] = $options['fields'];
		return $type_info;
	}
	
	
	/**
	 * Returns all available types of contacts
	 * 
	 * @return array - array('ID' => 'NAME', ...)
	 */
	public function getTypeNames()
	{
		$types = array();
		$data = $this->query('SELECT CT_ID, CT_NAME FROM '.$this->table);
		foreach ($data as $row) {
			if (preg_match('/^[a-z\s-]+$/ui', $row['CT_NAME'])) {
				$types[$row['CT_ID']] = $row['CT_NAME'];
			} else {
				$types[$row['CT_ID']] = json_decode($row['CT_NAME'], true);
			}
		}
		return $types;
	}
	
	public function getTypeIds()
	{
		$ids = array();
		$data = $this->query('SELECT CT_ID FROM '.$this->table);
		foreach ($data as $row) {
			$ids[] = $row['CT_ID'];
		}		
		return $ids;
	} 
	
	public function getTypes()
	{
	    $this->setCacher(new DbCacher('CONTACTTYPEALL', 60, 'SYSTEM'));
		$types = array();
		$data = $this->query('SELECT * FROM '.$this->table);
		foreach ($data as $type) {
			if (preg_match('/^[a-z\s-]+$/ui', $type['CT_NAME'])) {
				$type['CT_NAME'] = $type['CT_NAME'];
			} else {
				$type['CT_NAME'] = json_decode($type['CT_NAME'], true);
			}
			$types[$type['CT_ID']] = array(
				'id' => $type['CT_ID'],
				'name' => $type['CT_NAME'],
				'standart' => $type['CT_STD']
			); 
			$options = (array)json_decode($type['CT_OPTIONS']);
			$types[$type['CT_ID']]['fname_required'] = (array)$options['fname_required'];
			$types[$type['CT_ID']]['fname_format'] = (array)$options['fname_format'];
			$types[$type['CT_ID']]['fields'] = (array)$options['fields'];
		}
		return $types;
	}
	
	public function setTypeField($type_id, $field_id, $use)
	{
		$sql = 'SELECT CT_OPTIONS FROM '.$this->table. ' WHERE CT_ID = i:type_id';
		$options = $this->prepare($sql)->query(array('type_id' => $type_id))->fetchField('CT_OPTIONS');
		$options = json_decode($options, true);
		$fields = $options['fields'];
		$add_flag = true;
		foreach ($fields as $id => $field) {
			if ($field[0] == $field_id) {
				$add_flag = false;
				if (!$use) {
					unset($fields[$id]);
				}
			}
		}
		if ($use > 0 && $add_flag) {
			$fields[] = array("$field_id");
		}
		$options['fields'] = array_values($fields);		
		$this->addCacheCleaner(new DbCacher("CONTACTTYPE".$type_id));
		$this->addCacheCleaner(new DbCacher("CONTACTTYPEALL"));
		$sql = "UPDATE ".$this->table." 
				SET CT_OPTIONS = s:options 
				WHERE CT_ID = i:type_id";
		$this->prepare($sql)->exec(array('type_id' => $type_id, 'options' => json_encode($options)));
	}		
	
	public function saveFormat($type_id, $format)
	{
		$sql = 'SELECT CT_OPTIONS FROM '.$this->table. ' WHERE CT_ID = i:type_id';
		$options = $this->prepare($sql)->query(array('type_id' => $type_id))->fetchField('CT_OPTIONS');
		$options = json_decode($options, true);
		$this->addCacheCleaner(new DbCacher("CONTACTTYPE".$type_id));
		$this->addCacheCleaner(new DbCacher("CONTACTTYPEALL"));
		$options['fname_format'][0] = $format;
		$sql = "UPDATE ".$this->table." 
				SET CT_OPTIONS = s:options 
				WHERE CT_ID = i:type_id";
		$this->prepare($sql)->exec(array('type_id' => $type_id, 'options' => json_encode($options)));
	}
	
	/**
	 * Set the field required for the type
	 * 
	 * @param int $type_id
	 * @param int $field_id
	 * @param bool $required
	 * @return bool
	 */
	public function setRequired($type_id, $field_id, $required = 1)
	{
		$sql = 'SELECT CT_OPTIONS FROM '.$this->table. ' WHERE CT_ID = i:type_id';
		$options = $this->prepare($sql)->query(array('type_id' => $type_id))->fetchField('CT_OPTIONS');
		$options = json_decode($options, true);
		$fields = $options['fields'];
		$save = false;
		foreach ($fields as $id => $field) {
		    if ($field[0] == $field_id) {
		        $save = true;
		        $fields[$id][1] = (int)$required;
		        break;
		    }
		}
		if ($save) {
			$options['fields'] = $fields;		
			$this->addCacheCleaner(new DbCacher("CONTACTTYPE".$type_id));
			$this->addCacheCleaner(new DbCacher("CONTACTTYPEALL"));
			$sql = "UPDATE ".$this->table." 
					SET CT_OPTIONS = s:options 
					WHERE CT_ID = i:type_id";
			return $this->prepare($sql)->exec(array('type_id' => $type_id, 'options' => json_encode($options)));		    
		}
		return false;
	}
}

?>