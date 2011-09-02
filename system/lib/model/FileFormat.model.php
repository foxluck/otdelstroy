<?php

class FileFormatModel extends DbModel
{
	protected $table = 'FILE_IMPORT_FORMAT';
	protected $id = 'FIF_ID';
	
	/**
	 * Returns file format info
	 * 	array(
	 * 		'FIF_ID' => ...,
	 * 		'FIF_LIST' => ...,
	 * 		'FIF_NAME' => ...,
	 * 		'FIF_SETTINGS' => array(
	 * 			'DELIMITER' => ..., // , ; \t etc.
	 * 			'FIRST_LINE' => ..., //save first line or not
	 * 			'FIELDS' => array(
	 * 				DBFIELD => REALFIELD,
	 * 				...
	 * 			)
	 * 		),
	 * 		'FIF_OWNER_U_ID' => ...
	 * 	)
	 * 
	 * @param $id
	 * @return array
	 */
	public function get($id) 
	{
		$sql = "SELECT * FROM ".$this->table." WHERE ".$this->id." = i:id";
		$data = $this->prepare($sql)->query(array('id' => $id))->fetchAssoc();
		$settings = explode("||", $data['FIF_SETTINGS']);
		$fields_array = explode("&&", $settings[2]);
		$fields = array();
		foreach ($fields_array as $field_info) {
			$field_info = explode("=>", $field_info);
			$fiedls[$field_info[0]] = $field_info[1];
		}
		$data['FIF_SETTINGS'] = array(
			'DELIMITER' => $settings[0],
			'FIRST_LINE' => $settings[1],
			'FIELDS' => $fiedls
		);
		return $data;
	}

	/**
	 * Returns list of the formats
	 * 	array(
	 * 		ID => NAME,
	 * 		...
	 * 	)
	 * @param $list
	 * @return array 
	 */
	public function getAll($list = false)
	{
		$sql = "SELECT * FROM ".$this->table;
		if ($list) {
			$sql .= " WHERE FIF_LIST = s:list";
		}
		$data = $this->prepare($sql)->query(array('list' => $list));
		$formats = array();
		foreach ($data as $row) {
			$formats[$row[$this->id]] = $row['FIF_NAME'];
		}
		return $formats;
	}
	
	/**
	 * Adds new file format and returns id of the new format
	 * 
	 * @param $list - list, example CONTACTS
	 * @param $name - name of the file format, ex. The BAT!
	 * @param $delimiter - ,;\t.
	 * @param $first_line - save first line or not === 0 
	 * @param $fields - array of the db fields and real fields in format
 	 * 	array(
	 * 		DBFIELD => REALFIELD,
	 * 		...
	 * 	)
	 * @return int
	 */
	public function add($list, $name, $delimiter, $first_line, $fields) 
	{
		$settings = $delimiter. "||" . (int) $first_line. "||";
		foreach ($fields as $db_field => $real_field) {
			if ($settings) {
				$settings .= '&&';
			}
			$settings = $db_field . '=>' . $real_field;
		}
		$sql = "INSERT INTO ".$this->table." 
				SET FIF_LIST = s:list, FIF_NAME = s:name, 
					FIF_SETTINGS = s:settings, FIF_OWNER_U_ID = s:user_id";
		return $this->prepare($sql)->query(array(
			'list' => $list,
			'name' => $name,
			'settings' => $settings,
			'user_id' => CurrentUser::getId() 
		))->lastInsertId();
	}
}
 

?>