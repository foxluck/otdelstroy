<?php

class ContactType 
{
	const TYPE_FIELD = 1;
	const TYPE_SECTION = 2;
	
	protected static $store = array();
	protected static $fields = array();
	protected static $dbfields = array(); 
	
	protected $id;
	protected $lang;
	protected $data = array();	
	
	public function __construct($id = 1)
	{
	    // Default type
	    if (!$id) {
	        $id = 1;
	    }
		$this->id = $id;
		self::getAllFields();
		
		// Read type options
		if (!isset(self::$store[$this->id])) {
			$contact_type_model = new ContactTypeModel();

			if (!(self::$store[$this->id] = $contact_type_model->getType($this->id))) {
				throw new Exception(_("Requested contact type is not found"));
			}

		}
		$this->data = self::$store[$this->id]; 
	}
	
	public static function clearStore()
	{
		self::$fields = array();
		self::$dbfields = array();
		self::$store = array();
	}
	
	public static function getDbTypeNames($type = false)
	{
	    $names =  array(
			'VARCHAR' => _s('Text (single line)'),
			'DATE' => _s('Date'),
			'NUMERIC' => _s('Numeric'),
			'MOBILE' => _s('Mobile phone'),
			'URL' => _s('URL'),
			'MENU' => _s('Menu'),
			'EMAIL' => _s('Email'),
			'TEXT' => _s('Text (multiple lines)'),
			'IMAGE' => _s('Image'),	
	    	'CHECKBOX' => _s('Checkbox'),
	    	'COUNTRY' => _s('Country'),			    
	    );
	    return $type ? $names[$type] : $names;
	}
	
	public function getTypeName($lang = false)
	{
	    if (!$lang && $lang !== null) {
	        $lang = User::getLang();
	    }
	    return $lang ? self::getName($this->data['name'], $lang) : $this->data['name'];
	}
	
	/**
	 * Returns all fields 
	 * 	   array ( id => desc, ...)
	 * @param $lang
	 * @return array
	 */
	public static function getAllFields($lang = false, $fields_type = false, $add_section_name = false, $advanced_fields = false, $skip_disabled = true)
	{
		if (!self::$fields) {
			$contact_type_model = new ContactTypeModel();
			self::$fields = $contact_type_model->getFields();
			foreach (self::$fields as $field) {
				if ($field['section']) {
					self::$dbfields[$field['dbname']] = $field;
				}
			}			
		}
		if (!$lang) {
			return self::$fields;
		} else {
			$fields = self::$fields;
			if ($advanced_fields) {
			    $fields += array(
			        '-2' => array(
			            'id' => -2,
			            'section' => -1,
			            'name' => _s('Contact ID'),
			            'dbname' => 'C_ID',
			            'type' => 'NUMERIC'  
			        ),
			        '-8' => array(
			            'id' => -8,
			            'section' => -1,
			            'name' => _s('Language'),
			            'dbname' => 'C_LANGUAGE',
			            'type' => 'VARCHAR'  
			        ),			        
			        '-3' => array(
			            'id' => -3,
			            'section' => -1,
			            'name' => _s('Adding date'),
			            'dbname' => 'C_CREATEDATETIME',
			            'type' => 'DATE'  
			        ),
			        '-5' => array(
			            'id' => -5,
			            'section' => -1,
			            'name' => _s('Adding application'),
			            'dbname' => 'C_CREATEAPP_ID',
			            'type' => 'VARCHAR'  
			        ),
			        '-6' => array(
			            'id' => -6,
			            'section' => -1,
			            'name' => _s('Adding method'),
			            'dbname' => 'C_CREATEMETHOD',
			        	'type' => 'VARCHAR'  
			        ),			        			              
			    );				        
			    if (User::isAdmin('CM') || User::hasAccess('UG')) {
				    $fields['-4'] = array(
				            'id' => -4,
				            'section' => -1,
				            'name' => _s('Added by'),
				            'dbname' => 'C_CREATENAME',
				            'type' => 'VARCHAR'    
				    );
			    }
			    if (User::hasAccess('UG')) {
			    	$fields['-7'] = array(
			    			'id' => -7,
			    			'section' => -1,
			    			'name' => _s('Login name'),
			    			'dbname' => 'U_ID',
			    			'type' => 'VARCHAR'
			    	);
			    }
			}
			foreach ($fields as $key => $field) {
				if ($skip_disabled && $field['dbname'] == 'C_MIDDLENAME') {
					// Check Person
					$contact_type = new ContactType(1);
					$dbfields = $contact_type->getTypeDbFields();
					if (!in_array($field['dbname'], $dbfields)) {
						// Skip this field
						unset($fields[$key]);
						continue;
					}
				}
				if (self::TYPE_FIELD == $fields_type && !$field['section']) {
					unset($fields[$key]);
					continue;
				}
				if (self::TYPE_SECTION == $fields_type && $field['section']) {
					unset($fields[$key]);
					continue;
				}		
				$field['name'] = self::getName($field['name'], $lang);
				if ($add_section_name && $field['section'] && $field['section'] != self::getMainSection()) {
				    if ($field['section'] < 0) {
				        $section_name = _s('System');
				    } else {
                        $section_name = self::getName(self::$fields[$field['section']]['name'], $lang);
				    }
					$field['name'] = $field['name'] ? ($section_name ? $section_name . " — ".$field['name'] : $field['name']) : $section_name;
				}
				$fields[$key] = $field;
			}
			return $fields;
		}
	}
	
	public static function getDbFields()
	{
		if (!self::$dbfields) {
			self::getAllFields();
		}
		return array_keys(self::$dbfields);
	}
	
	
	/**
	 * Returns array of the contact types
	 * 
	 * @return array
	 */
	public static function getTypeNames($lang = false)
	{
	    if ($lang === false) {
	        $lang = User::getLang();
	    }
		$contact_type_model = new ContactTypeModel();
		$types = $contact_type_model->getTypeNames();
		foreach ($types as $key => $name) {
			$types[$key] = $lang === null ? $name : self::getName($name, $lang);
		}
		return $types;
	}
	
	/**
	 * Returns all types
	 * 
	 * @param $lang - lang which used for the type names
	 * @return array
	 */
	public static function getTypes($lang)
	{
		$contact_type_model = new ContactTypeModel();
		$types = $contact_type_model->getTypes();
		foreach ($types as $type_id => $type) {
			// Save to store type info
			self::$store[$type_id] = $type;
			$type['name'] = self::getName($type['name'], $lang);
			$fields = array();
			foreach ($type['fields'] as $field) {
				$fields[$field[0]] = array(
					'id' => $field[0],
					'required' => (isset($field[1]) && $field[1] ? 1 : 0),
					'unique' => (isset($field[2]) && $field[2] ? 1 : 0)  
				);	
			}
			$type['fields'] = $fields;
			$types[$type_id] = $type;
		}
		return $types;
	}
	
	public static function getFieldsNames($lang = false, $add_section_name = true, $without_image = false, $advanced_fields = false)
	{
		if (!$lang) {
			$lang = User::getLang();
		}
		$fields = self::getAllFields($lang, self::TYPE_FIELD, $add_section_name, $advanced_fields);
		$result = array();
		foreach ($fields as $field) {
		    if (!$without_image || $field['type'] != 'IMAGE') {
			    $result[$field['dbname']] = $field['name'];
		    }
		}
		return $result;
	}
	
	public function getFields($section_id = false)
	{
		$fields = array();
		$type = $this->getType();
		foreach ($type['fields'] as $n => $section) {
		    if (!$section_id || $section['id'] == $section_id) {
				foreach ($section['fields'] as $field_info) {
					$fields[$field_info['id']] = $field_info;
				}
		    }
		}
		return $fields;	
	}
	
	public function fieldExists($field_id)
	{
		$fields = $this->getFields();
		return isset($fields[$field_id]);
	}
	
	public static function getFieldId($dbname, $advanced = false) 
	{
		if (!self::$fields) {
			self::getAllFields();
		}		
		$r = isset(self::$dbfields[$dbname]) ? self::$dbfields[$dbname]['id'] : false;
		if (!$r && $advanced) {
		    $fields = array(
		        'C_ID' => '-2',
		        'C_CREATEDATETIME' => '-3',
		        'C_CREATENAME' => '-4',
		    	'C_CREATEAPP_ID' => '-5',
		    	'C_CREATEMETHOD' => '-6',
		    	'U_ID' => '-7'
		    );
		    return isset($fields[$dbname]) ? $fields[$dbname] : false; 
		}	
		return $r;
	}
	
	public static function getFieldByDbName($dbname, $lang = false)
	{

	    if ($dbname == 'C_FULLNAME') {
	    	if ($lang && $lang != User::getLang()) {
	            GetText::load($lang, SYSTEM_PATH . "/locale", 'system', false);
	        }	        
	        return array(
	            'id' => 0,
	            'dbname' => 'C_FULLNAME',
	            'name' => _s('Full name'),
	            'type' => 'VARCHAR',
	            'options' => 255
	        );
			if ($lang && $lang != User::getLang()) {
		        GetText::load(User::getLang(), SYSTEM_PATH . "/locale", 'system', false);
		    }	    
	    }
		$field_id = self::getFieldId($dbname);
		return $field_id ? self::getField($field_id, $lang) : false;
	}
	
	
	/**
	 * Returns main section id
	 * 
	 * @return int
	 */
	public static function getMainSection()
	{
		if (!self::$fields) {
			self::getAllFields();
		}		
		return self::$fields[self::getFieldId('C_FIRSTNAME')]['section'];
	}

	
	public function getMainFields($all = true)
	{
	    if ($all) {
	        $matches = array();
	        preg_match_all("/!([0-9]+)!/ui", $this->data['fname_format'][0], $matches);
	        return $matches[1]; 
	    } else {
		    return $this->data['fname_required'] ? $this->data['fname_required'] : array();
	    }
	}
	
	
	public function getPhotoField($check_type = true, $dbname = false)
	{
	    $photo = false;
	    $main_section = self::getMainSection();	    
        $fields = self::getAllFields();
        foreach ($fields as $f) {
            if ($f['section'] == $main_section && $f['type'] == 'IMAGE') {
                $photo = $dbname ? $f['dbname'] : $f['id'];
                $photo_id = $f['id'];
                break;
            }
        }
        
        if (!$check_type || !$photo) {
            return $photo;
        } else {
		    $type = $this->getType();
		    foreach ($type['fields'] as $section) {
		        if ($section['id'] == $main_section) {
	    	        foreach ($section['fields'] as $field) {
	    	            if ($field['type'] == 'IMAGE') {
	    	                if ($field['id'] == $photo_id) {
	    	                    return $photo;
	    	                } else {
	    	                    return false;
	    	                }
	    	            }       
	    	        }
		        }
		    }
        }
	    return false;
	}
	
	public function getMobileFields()
	{
		$result = array();
	    $type = $this->getType();
	    foreach ($type['fields'] as $section) {
            foreach ($section['fields'] as $field) {
                if ($field['type'] == 'MOBILE') {
                    $result[] = $field['dbname'];
                }       
            }
	    }
	    if (!$result) {
		    // If field type MOBILE not found
		    $field_id = self::getFieldId('C_MOBILEPHONE');
		    if ($field_id) {
		    	$result = array('C_MOBILEPHONE');
		    }
	    } 
	    return $result;
	}
	/**
	 * Returns ids of the required fields (with value = $v) of the current contact type
	 * 
	 * @param int $v  -  value (1 - strongly required, 2 - conditionally required)
	 * @return array
	 */
	public function getRequiredFields()
	{
		$result = array();
		$fields = $this->getFields(true);
		foreach ($fields as $field) {
			if ($field['required']) {
				$result[] = $field['id'];			
			}
		}
		return $result;
	}
	
	public function getUniqueFields()
	{
		$result = array();
		$fields = $this->getFields(true);
		foreach ($fields as $field) {
			if ($field['unique']) {
				$result[] = $field['id'];			
			}
		}
		return $result;
	}
	
	public function getTypeDbFields()
	{
	    $result = array();
		foreach ($this->data['fields'] as $field_info) {
			$field_info = self::getFieldInfo($field_info);
			$result[] = $field_info['dbname'];
		}
		return $result;
	}
	
	/**
	 * Returns type description
	 * 
	 * @param string $lang
	 * @param bool $empty_sections - include in the fields empty sections or not
	 * @return array
	 */
	public function getType($lang = false, $empty_sections = false)
	{
		if (!$lang) {
			$lang = User::getLang();
		}
		$result = $this->data;
		//$result['name'] = self::getName($this->data['name'], $lang);		
		$result['fields'] = array();
		
        if ($empty_sections) {
    	    $sections = self::getAllFields($lang, ContactType::TYPE_SECTION);
    	    foreach ($sections as $section) {
    	        $section['fields'] = array();
    	        $result['fields'][$section['id']] = $section;
    	    }
    	}		
	
		foreach ($this->data['fields'] as $field_info) {
			$field_info = self::getFieldInfo($field_info, $lang);
			if (!$field_info) continue;
			// if section
			if (!$field_info['section'] && !isset($result['fields'][$field_info['id']])) {
			    $result['fields'][$field_info['id']] = $field_info;
			    $result['fields'][$field_info['id']]['name'] = self::getName($field_info['name'], $lang);
			    $result['fields'][$field_info['id']]['fields'] = array();
			}
			// if field 
			elseif ($field_info['section'] && !isset($result['fields'][$field_info['section']])) {
				$result['fields'][$field_info['section']] = self::$fields[$field_info['section']];
				$result['fields'][$field_info['section']]['name'] = self::getName($result['fields'][$field_info['section']]['name'], $lang);
				$result['fields'][$field_info['section']]['fields'] = array();
			}
			if ($field_info['section']) {
			    $result['fields'][$field_info['section']]['fields'][$field_info['id']] = $field_info;
			}
		}
		uasort($result['fields'], array($this, 'sortFields'));
		foreach ($result['fields'] as $i => $section) {
		    // delete empty sections
		    if (!$empty_sections && !$section['fields']) {
		        unset($result['fields'][$i]);
		        continue;
		    }
		    if (!is_array($result['fields'][$i]['fields'])) {
		        $result['fields'][$i]['fields'] = array($result['fields'][$i]['fields']);
		    }
			uasort($result['fields'][$i]['fields'], array($this, 'sortFields'));
		}		
		return $result;
	}
	
	public function sortFields($a, $b) 
	{
		if ($a['sorting'] == $b['sorting']) {
			return 0;
		}
		return ($a['sorting'] < $b['sorting']) ? -1 : 1;
	}
		
	public static function getName($object, $lang, $use_default = true) 
	{	
		if (is_array($object)) {
			if (isset($object[$lang])) {
				return $object[$lang];	
			}
			elseif ($use_default && isset($object['all'])) {
				return _s($object['all']);
			}
			elseif ($use_default && isset($object['eng'])) {
				return $object['eng'];
			} elseif ($use_default) {
			    return array_shift($object);
			}
		}
		if (is_string($object)) {
		    $old_lang = GetText::getLang();
    		if (strlen($lang) > 2) {
    			$lang = substr($lang, 0, 2);
    		}
		    if ($old_lang == $lang) {
		        return _s($object);
		    }
		    // @todo: Move to system
		    GetText::load($lang, SYSTEM_PATH . "/locale", 'system', false);
			$result = _s($object);
			GetText::load($old_lang, SYSTEM_PATH . "/locale", 'system', false);
			return $result;
		}
		return "";
	}
	
	protected static function getFieldInfo($field, $lang = false)
	{
	    if (isset(self::$fields[$field[0]])) {
    		$result = self::$fields[$field[0]];
    		if ($lang) {
    		    $result['name'] = self::getName($result['name'], $lang);
    		}
    		$result['required'] = isset($field[1]) ? $field[1] : 0;
    		$result['unique'] = isset($field[2]) ? $field[2] : 0;
    		return $result;
	    } else {
	        return array();
	    }
	}
	
	public static function getValue($field, $value, $escape = false)
	{

		$result = "";
		switch ($field['type']) {
				case "EMAIL":
					$result = array();
					if ($value) {
						if (!is_array($value)) {
							$value = array($value);
						}
						foreach ($value as $email) {
							$result[] = (string)$email;
						}
					} 
					break; 
				case 'COUNTRY': 
					if ($escape) {
						$countries = Wbs::getCountries();
						$result = isset($countries[$value]) ? $countries[$value] : "";
					} else {
						$result = $value;
					} 
					break;
				case 'CHECKBOX':
					if ($escape && $escape !== 2) {
						$result = (bool)$value ? _s('Yes') : _s('No');
					} else {
						$result = $value;
					}
					break;
				case "DATE" :
					if ($value) {
					    $result = WbsDateTime::fromMySQL($value);
					} 
					break;
				case "NUMERIC" :
					if (strlen($value)) {
						$result = round($value, (int) $field['options']);
						$result = str_replace(",", ".", $result);
					} 
					break;
				case "IMAGE" :
					$image = ContactsModel::parseImageXML($value);
					if ($image['DISKFILENAME']) {
						$filename = base64_decode($image['DISKFILENAME']);
						$path = (strpos($filename, '/') !== false) ? $filename : "contacts/".$filename;
						$path = Wbs::getDbkeyObj()->files()->getAppAttachmentPath("cm", $path);
					
						$ext = $image['TYPE'];
						
						if (file_exists($path.".".$ext)) {
							$nocache = @filemtime($path.".".$ext);
						} else {
							$nocache = time();
						}
						$result = Url::get('/common/html/scripts/thumb.php?nocache='.$nocache."&basefile=".base64_encode($path)."&ext=".base64_encode($ext));
					}
					break;
				default:
					$result = $escape ? htmlspecialchars($value) : (string) $value;
		}	
		if (!$result) {
		    $result = "";
		}
		return $result;
	}
	
    /**
     * Validate value of the field
     * Returns true or error description
     *
     * @param $field
     * @param $value
     * @return string
     */
    public static function validateValue($field, $value, $check = false)
    {
    	$value = trim($value);
    	// Check required field
    	if ($check && $field['required'] == 1 && !$value) {
    		throw new Exception(_s("This field is required"));
    	}
		if (!strlen($value)) {
			if ($field["type"] == 'CHECKBOX') {
				return 0;
			} else {
				return null;
			}
		}
		switch ($field["type"]) {
			case "TEXT":
				break;
			case "VARCHAR":
				if ($field["options"] && mb_strlen($value) > $field["options"]) {
					throw new Exception(_s("Number of characters can not exceed ") . $field["options"] . ".");
				}
				break;
			case "MOBILE":
			    $phone = preg_replace("/[\s\(\)+-]/ui", "", $value);
			    if ($phone && !preg_match("/^[0-9]{7,12}$/ui", $phone)) {
			        throw new Exception(_s('Mobile phone must contain from 7 to 12 numbers'));
			    }
			    break;
			case "URL":
				$url = parse_url($value);
				$scheme = isset($url['scheme']) ? $url['scheme'] : 'http';
				if (isset($url['host'])) {
					$host = $url['host'];
					$path = isset($url['path']) ? $url['path'] : '/';
				} elseif (isset($url['path'])) {
					$urls = explode("/", $url['path'], 2);
					$host = $urls[0];
					$path = isset($urls[1]) ? "/".$urls[1] : "/";
				} else {
					throw new Exception(_s("Incorrect URL"));
				}
				if (!preg_match("/^[a-z0-9\._-]{1,30}\.[a-z]{2,4}$/ui", $host, $matches)) {
					throw new Exception(_("Incorrect URL"));
				}
				$query = isset($url['query']) ? "?" . $url['query'] : '';
				$fragment = isset($url['fragment']) ? "#" . $url['fragment'] : ""; 
				$value = $scheme."://".$host.$path.$query.$fragment;
				break;
				
			case "EMAIL": 
				if (!preg_match("/^[a-zа-я0-9_\.-]{1,50}\@[a-zа-я0-9_\.-]{1,100}\.[a-z]{1,4}$/ui", $value, $matches)) {
					throw new Exception(_s("Incorrect email"));
				}
				break;
			case "CHECKBOX":
				$value = $value ? 1 : 0;
				break;
							
			case "DATE":
				$value = WbsDateTime::toMySQL($value);
				if (!$value) {
					throw new Exception(_s("Incorrect date"));
				}
				break;
				
			case "NUMERIC":
				if (!is_numeric($value)) {
					throw new Exception(_s("This field must be numeric"));
				} else {				    
					$value = round($value, $field['options']);
					$value = str_replace(",", ".", $value);
				}
				break;
			
			case "MENU":
				if (!in_array($value, $field['options'])) {
					throw new Exception(_s("Error"));
				} 
				break;
		}
		return $value;
    }
	
	public function getDisplayName($contact_info, $n = false, $no_tags = false, $first = false)
	{
		if ($n !== false) {
			$formats = array($this->data['fname_format'][$n]);
		} else {
			$formats = $this->data['fname_format'];
		}
		
		if (!is_array($formats)) {
			$formats = array($formats);
		}
		
		foreach ($formats as $format) {
			if ($first) {
				if (preg_match("#(<[^/][^>]*>[^!]*!".$first."![^<]*</[^>]*>)#si", $format, $matches)) {
					$format = preg_replace("#>[^!]*!#", ">!", $matches[1])." ".str_replace($matches[1], "", $format);
				} else {
					$format = "!".$first."! ".preg_replace("/(.*)?([^!>\s]*!".$first."![^!\s<]*)/usi", "$1", $format);
				}					
			}
			$codes = preg_split("/(<[^>]*>|_?[^0-9\s]?_?![1-9]+![^0-9\s]?)\s?/u", $format, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			$name = array();
			foreach ($codes as $code) {
				if (preg_match('/!([0-9]+)!/u', $code, $matches) && ($field_id = $matches[1]) && ($field_info = self::$fields[$field_id])) {
					$data = isset($contact_info[$field_info['dbname']]) ? $contact_info[$field_info['dbname']] : '';
					if ($data) {
						if (strpos($code, "_!")) {
							$code = str_replace("_", " ", $code);
							$c = count($name);
							$name[$c ? $c - 1 : 0] .= str_replace("!".$field_id."!", $data, $code);
						} elseif (strpos($code, "!.")) {
						    $name[] = str_replace("!".$field_id."!", mb_substr($data, 0, 1), $code);
						} else {
							$name[] = str_replace("!".$field_id."!", $data, $code);
						}

					} else {
						if (strip_tags($code) != $code) {
							$name[] = str_replace("!".$field_id."!", "", $code);
						}
					}				
				} else {
					if (trim($code)) {
						$name[] = trim($code); 
					}
				}
			}
			$name = implode(" ", $name);
			if (trim($name)) {
				return trim($no_tags ? strip_tags($name) : $name);
			}
		}
		return "";
	}
	
	/**
	 * Returns field's info 
	 * 
	 * @param $field_id
	 * @return array
	 */
	public static function getField($field_id, $lang = false)
	{
		if (!self::$fields) {
			self::getAllFields($lang);
		}
		if (isset(self::$fields[$field_id])) {
			$field = self::$fields[$field_id];
			if ($lang) {
			    $field['name'] = self::getName($field['name'], $lang);
			}
			return $field;
		} else {
		    return array();
		}
	}
	
	public static function getFieldName($field_id, $lang = false)
	{
		if (!self::$fields) {
			self::getAllFields($lang);
		}
		if (!$lang) {
			$lang = User::getLang();
		}
		if (isset(self::$fields[$field_id])) {
			$field = self::$fields[$field_id];
			return self::getName($field['name'], $lang);
		} else {
		    return false;
		}
	}
	
	public static function getDbName($field_id)
	{
	    $field_info = self::getField($field_id);
	    return $field_info['dbname'];
	}
	
	public static function getNextDbName()
	{
		$fields = self::getAllFields();
		$n = 0;
		foreach ($fields as $field)	{
			if ($field['dbname']) {
				if (preg_match("!^C_([0-9]+)$!si", $field['dbname'], $matches)) {
					$n = max(array($n, $matches[1]));	
				}
			} 
		}
		$n++;
		return "C_".$n;
	}
	
	public static function getDbType($type)
	{
		switch ($type[0]) {
			case 'VARCHAR':
			    return 'VARCHAR('.($type[1] ? $type[1] : "255" ).')';
			case 'URL':
			case 'MENU':
			case 'EMAIL':
			case 'MOBILE':
				return 'VARCHAR(255)';
			case 'COUNTRY':
				return 'CHAR(3)';
			case 'DATE': 
				return 'DATE';
			case 'NUMERIC':
				return 'FLOAT';
			case 'CHECKBOX':
				return 'TINYINT(1)';
			case 'IMAGE':				
			case 'TEXT':
			default:
				return 'TEXT';
		}
	}
	
	public static function addField($type, $dbname = false) 
	{
	    if (!$dbname) {
		    $dbname = self::getNextDbName();
	    } elseif (substr($dbname, 0, 2) != 'C_') {
	        $dbname = 'C_'.$dbname;
	    }
	    if (self::getFieldId($dbname)) {
	        throw new Exception(_s('This database name is already in use'));
	    }
		$type = self::getDbType($type);
		$contact_type_model = new ContactTypeModel();
		$sql = "ALTER TABLE `CONTACT` ADD `".$dbname."` ".$type." NULL";
		$contact_type_model->exec($sql);
		return $dbname;
	}
	
	public static function editField($old_dbname, $dbname, $old_type, $type)
	{
		$old_type = self::getDbType($old_type);
		$type = self::getDbType($type);
		if ($type != $old_type || $old_dbname != $dbname) {
			$contact_type_model = new ContactTypeModel();
			$sql = "ALTER TABLE `CONTACT` CHANGE `".$old_dbname."` `".$dbname."` ".$type." NULL";
			$contact_type_model->exec($sql);
		} 
		return true;
	}
	
	public static function deleteField($dbname) 
	{
		$contact_type_model = new ContactTypeModel();
		$sql = "ALTER TABLE `CONTACT` DROP `".$dbname."`";
		$contact_type_model->exec($sql);	
	}
}
?>