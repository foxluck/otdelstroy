<?php

class CSV 
{
	const MAX_FIELDS_COUNT = 100;
	
	protected $file = false;
	protected $handler;
	
	public static $delimiters = array(
		0 => array(",", "Comma"), 
		1 => array(";", "Semicolon"),
		2 => array("\t", "Tab"),
		//3 => array(".", "Period")
	);
	protected $quote = '"';
	protected $length = 4096; // Max length of the row in the csv file
	protected $extensions = array('txt', 'csv');
	
	protected $delimiter;
	protected $fields;
	protected $first_line = false;
	protected $format_id = false;
	public $encode = false;
	
	public static function getDelimiters()
	{
		$result = array();
		foreach (self::$delimiters as $k => $d) {
			$result[$k] = array($d[0], _s($d[1]));
		}
		return $result;
	}
	
	/**
	 * Constructor
	 * 
	 * @param $file - path to the source file
	 */
	public function __construct($format_id = false, $first_line = false, $delimiter = ",", $fields = false, $file = false)
	{
		if ($format_id) {
			$file_format_model = new FileFormatModel();
			$format = $file_format_model->get($format_id);
			$this->format_id = $format_id;		
			$this->first_line = $first_line ? $first_line : $format['FIF_SETTINGS']['FIRST_LINE'];
			$this->delimiter = $delimiter ? $delimiter : $format['FIF_SETTINGS']['DELIMITER'];
			$this->fields = $fields ? $fields : $format['FIF_SETTINGS']['FIELDS'];			
		} else {
			$this->first_line = $first_line;
			$this->delimiter = $delimiter;
			$this->fields = $fields;
		} 	
		if ($file) {
			$this->file = WBS_DIR."temp/".$file;
		}
	}
	
	/**
	 * Set Fields
	 * 
	 * @param $fields
	 */
	public function setFields($fields)
	{
		$this->fields = $fields;
	}
	
	public function getFields()
	{
		return $this->fields;
	}	

	/**
	 * Upload file and returns path to the file
	 * 
	 * @param $name
	 * @return string
	 */
	public function upload($name)
	{
		if (!isset($_FILES[$name]) || $_FILES[$name]['error']) {
			throw new Exception(_("Error uploading file"));
		}
		
		$file_info = explode(".", $_FILES[$name]['name']);
		if (!in_array(strtolower(end($file_info)), $this->extensions)) {
		    throw new UserException(_("Unknown extension *.").end($file_info));
		}
		
		$file = uniqid("csv").".csv";
		if (move_uploaded_file($_FILES[$name]['tmp_name'], WBS_DIR."temp/".$file)) {
			$this->file = WBS_DIR."temp/".$file;
		} else {
			throw new Exception(_('Error moving file'));
		}	
		return $file;
	} 
	
	/**
	 * Save text in the temp file (for import from text)
	 * 
	 * @param $content
	 * @return string
	 */
	public function saveContent($content)
	{
		$file = uniqid("csv").".csv";
		if (file_put_contents(WBS_DIR."temp/".$file, $content)) {
			$this->file = WBS_DIR."temp/".$file;
		} else {
			throw new Exception(_('Error moving file'));
		}	
		return $file;
	}
	
	/**
	 * Returns stat info about csv-file
	 * 	array(
	 * 		'DELIMITER' => ...,
	 * 		'FIELDS' => array(...), // fields in the first row
	 * 		'NUM_ROWS' => ... // Count of the rows in the file 
	 * 	)
	 * 
	 * @return array
	 */
	public function getInfo()
	{
		if (!$this->file || !file_exists($this->file)) {
			throw new Exception(_('File is not exists'));
		}
		$h = fopen($this->file, "r");
		if (!$h) {
			throw new Exception(_("Error open file"));
		}
		// Read the first string
		$string = fgets($h, $this->length);
		
		// Get delimiter
		if (!$this->delimiter) {
			$max_count_fields = 0;
			foreach(self::$delimiters as $i => $delimiter) {
				$delimiter = $delimiter[0];
				$count_fields = count(explode($delimiter, $string));
				if ($count_fields > $max_count_fields) {
					$this->delimiter = $delimiter;
					$max_count_fields = $count_fields;
				}
			}
		}
		
		// Read fields
		rewind($h);
		$records = array();
		$records[] = $fields = $this->encodeArray(fgetcsv($h, $this->length, $this->delimiter));
		$fields_count = count($fields);
				
		// Count lines in files
		$n = 0;
		if ($this->first_line) {
			$n = 1;
		}
		
		while ($n <= 10 && $string = $this->encodeArray(fgetcsv($h, $this->length, $this->delimiter)))	{
			if ($this->notEmptyArray($string)) {
				$records[] = $string;
				$count = count($string);
				if ($count > $fields_count) {
					$fields_count = $count;
				}
				$n++;
			} 	
		}
		
		if ($fields_count > self::MAX_FIELDS_COUNT) {
			throw new UserException(sprintf(_("Number of columns can not exceed %s"), self::MAX_FIELDS_COUNT));
		}
		
		if ($k = $fields_count - count($fields)) {
			for ($i = 0; $i < $k; $i++) {
				$fields[] = "";
			}
		}
		
		while ($data = fgetcsv($h, $this->length, $this->delimiter)) {
			// Count only not empty strings
			if ($data) {
	        	$n++;
			}
	    }		
	    
	    // Close file
	    fclose($h);

	    return array(
	    	'DELIMITER_INDEX' => $this->getDelimiterIndex($this->delimiter),
	    	'FIELDS' => $fields,
	    	'RECORDS' => $records,   
	    	'NUM_ROWS' => $n
	    );
	}
	
	protected function encodeArray($a)
	{
		if ($this->encode && is_array($a)) {
			foreach ($a as &$v) {
				$v = iconv($this->encode, "utf-8", $v);
			}
		}
		return $a;
	}
	
	protected function notEmptyArray($a)
	{
		if (!$a) {
			return false;
		}
		$t = false;
		foreach ($a as $v) {
			if (trim($v)) {
				$t = true;
			}
		}
		return $t;
	}
	
	public function getDelimiterIndex($delimiter)
	{
		foreach (self::$delimiters as $i => $d) {
			if ($d[0] == $delimiter) {
				return $i;
			}
		}
	}
	
	/**
	 * Read CSV-file and returns data
	 * 
	 * @param $limit
	 * @return array
	 */
	public function import($limit = 50)
	{
		if (!$this->handler) {
			$this->handler = fopen($this->file, "r");
			if (!$this->handler) {
				throw new Exception(_("Error open file"));
			}
			if (!$this->first_line) {
				$fields = $this->encodeArray(fgetcsv($this->handler, $this->length, $this->delimiter));
			}
		}
		$data = array();
		$i = 0;
		while (!feof($this->handler) && $limit > $i++) {
			$real_data = $this->encodeArray(fgetcsv($this->handler, $this->length, $this->delimiter));
			if (!$this->notEmptyArray($real_data)) {
				$i--;
				continue;
			}

			//print_r($real_data);
			$info = array();
			foreach ($this->fields as $field_index => $dbfield) {
				if ($dbfield) {
					$info[$dbfield] = $real_data[$field_index];
				}
			}
			$data[] = $info;
		}
		if (!$data) {
			fclose($this->handler);
			$this->handler = false;
			return false;
		}

		return $data;
	}
	
	/**
	 * Exports data
	 * Send headers and csvfile
	 * 
	 * @param $data - array of the exported data
	 * @param $filename - name of the file
	 * @param $return - send headers and file or not
	 */
	public function export($data, $filename = "contacts", $return = true)
	{
		if ($this->file) {
			// Open file
			$f = @fopen($this->file, "a+");
		} else {
			// Create file
			$this->file = WBS_DIR."temp/".uniqid("csv").".csv";
			$f = @fopen($this->file, "w+");
		}
		if (!$f) {
			throw new Exception(_("Cannot open file to write."));
		}

		fputcsv($f, array_values($this->fields), $this->delimiter);
		
		if ($data instanceof DbResultSelect) {
		    $contacts_model = new ContactsModel();
		    $all_emails = $contacts_model->getAllEmails();
		} else {
		    $all_emails = array();
		}
		
		Contact::useStore(false);
		foreach ($data as $row) {
		    $row = Contact::getInfo($row['C_ID'], $row);
		    if ($all_emails && isset($all_emails[$row['C_ID']])) {
		        $row['C_EMAILADDRESS'] = implode(", ", $all_emails[$row['C_ID']]);
		    } elseif (is_array($row['C_EMAILADDRESS'])) {
		        $row['C_EMAILADDRESS'] = implode(", ", $row['C_EMAILADDRESS']);
		    }
			// Replace \n to \r\n for windows users
			fseek($f, -1, SEEK_CUR);
			fwrite($f, "\r\n");
			$fields = array();
			foreach ($this->fields as $dbfield => $field) {
				// Encoding (if necessary)
				$fields[$dbfield] = $row[$dbfield];
			}
			fputcsv($f, $fields, $this->delimiter);			
		}
		Contact::useStore(true);
		fseek($f, -1, SEEK_CUR);
		fwrite($f, "\r\n");
		fclose($f);
		if ($return) {
			header("Content-Type: text/csv; charset=utf-8");
			header('Cache-Control: no-cache, must-revalidate');
			header("Accept-Ranges: bytes");
			header("Content-Length: ".filesize($this->file));		
			header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
			header("Connection: close");
			@readfile($this->file);			
			@unlink($this->file);	
			exit();
		}
	}
	
	public function saveFileFormat($format_name)
	{
		$file_format_model = new FileFormatModel();
		return $file_format_model->add("CONTACTS", $format_name, $this->delimiter, $this->first_line, $this->fields);
	}
}
?>