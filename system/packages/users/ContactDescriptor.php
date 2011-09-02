<?php

	include_once 'contacts.const.php';

	/**
	 * Class type descriptor
	 * It's used classes DOMDocument and DOMXpath (PHP 5) for read xml
	 *
	 */
	class ContactDescriptor 
	{
		
		/**
		 * Static Contact type description
		 *
		 * @var array
		 */
		protected static $type_desc = false;
		
		/**
		 * Returns values of all node attributes as associative array
		 *
		 * @param  $node - XML document node
		 * @return array
		 */
		protected static function getAttributes( &$node )
		{
			$attrs = $node->attributes;
			$result = array();
			foreach ($attrs as $name => $value) {
				$result[$name] = $value->nodeValue;
			}
			return $result;
		}
		
		/**
		 * Returns contact description group or field name
		 *
		 * @param $xpath - xpath Object
		 * @param $element - element Object
		 * @param $nameNodeName - name of the element
		 * @param $language - User's language
		 * @return string or null
		 */
		protected static function getElementDescription( &$xpath, &$element, $nameNodeName, $language )
		{
			// Find name element
			$nameNodeElement = $xpath->query($nameNodeName, $element);
	
			if ( !$nameNodeElement->length ) {
				return null;
			}
	
			$nameNodeElement = $nameNodeElement->item(0);
	
			// Find name language element
			$languageElement = $xpath->query($language, $nameNodeElement );
	
			// Return language element value, if language element exists
			$langElementExists = $languageElement->length;
			if ( $langElementExists ) {
				$languageElement = $languageElement->item(0);
				$fieldNameExists = strlen( $languageElement->attributes->getNamedItem(CONTACT_NAMEVALUE)->nodeValue);
			}
	
			if ( $langElementExists && $fieldNameExists ) {
				return base64_decode( $languageElement->attributes->getNamedItem(CONTACT_NAMEVALUE)->nodeValue );
			} else {
				// Find English name element
				$languageElement = $xpath->query(LANG_ENG, $nameNodeElement );
	
				if ( $languageElement->length ) {
					return null;
				}
	
				$languageElement = $languageElement->item(0);
				return base64_decode( $languageElement->attributes->getNamedItem(CONTACT_NAMEVALUE)->nodeValue );
			}
		}

		
		/**
		 * Returns description of the contact fields and groups
		 *
		 * @param int $CT_ID - contact type
		 * @return array
		 */
		public static function get($CT_ID) 
		{
			$language = CurrentUser::getLanguage();
			
			if (self::$type_desc !== false) {
				return self::$type_desc;
			}
			
			$sql = new CSelectSqlQuery("CTYPE");
			$sql->setSelectFields("CT_ID, CT_SETTINGS");
			$sql->addConditions("CT_ID", $CT_ID);
			$type_data = Wdb::getRow($sql);
			
			
			if (! $type_data['CT_ID'] ) {
				throw new RuntimeException("Requested contact type is not found");
			}
			
			$result = array ();
			
			$dom = new DOMDocument("1.0", "utf-8");
			try {
				$dom->loadXML($type_data ['CT_SETTINGS']);
			} catch (Exception $e) {
				throw new RuntimeException("Error processing XML data");
			}

			if (!$dom) {
				throw new RuntimeException("Error processing XML data");
			}
						
			$xpath = new DOMXPath($dom);
			$groups = $xpath->query("/TYPE/FIELDGROUP");

			
			foreach ( $groups as $group ) {
				$group_data = self::getAttributes ( $group );
				
				// Check if field name elements exists
				$longNameElement = $xpath->query(CONTACT_FIELDGROUP_LONGNAME, $group );
				if (!$longNameElement->length) {
					
					// Load name from localization strings
					$group_data [CONTACT_FIELDGROUP_LONGNAME] = ucfirst(trim(str_replace(array("_", "app ", "group title", "group"), array(" ", ""), $group_data [CONTACT_FIELDGROUP_LONGNAME]))); 
					$group_data [CONTACT_FIELDGROUP_SHORTNAME] = _($group_data [CONTACT_FIELDGROUP_SHORTNAME]);
				} else {
					// Load name from section description
					$group_data [CONTACT_FIELDGROUP_LONGNAME] = self::getElementDescription ( $xpath, $group, CONTACT_FIELDGROUP_LONGNAME, $language );
					$group_data [CONTACT_FIELDGROUP_SHORTNAME] = self::getElementDescription ( $xpath, $group, CONTACT_FIELDGROUP_SHORTNAME, $language);
					if (!$group_data [CONTACT_FIELDGROUP_SHORTNAME] ) {
						$group_data [CONTACT_FIELDGROUP_SHORTNAME] = $group_data[CONTACT_FIELDGROUP_LONGNAME];
					}
				}
				
				$groupFields = array ();
				
				$fields = $xpath->query("FIELD", $group );
				foreach ( $fields as $field ) {
					
					$fieldDesc = self::getAttributes ( $field );
					// Check if field name elements exists
					$longNameElement =  $xpath->query( CONTACT_FIELDGROUP_LONGNAME, $field );
					if (! $longNameElement->length) {
						// Load name from localization strings

						$fieldDesc [CONTACT_FIELDGROUP_LONGNAME] = ucfirst(str_replace("app_longname_", "", $fieldDesc[CONTACT_FIELDGROUP_LONGNAME]));
						$fieldDesc [CONTACT_FIELDGROUP_SHORTNAME] = _($fieldDesc [CONTACT_FIELDGROUP_SHORTNAME]);
					} else {
						// Load name from field description
						$fieldDesc [CONTACT_FIELDGROUP_LONGNAME] = self::getElementDescription ( $xpath, $field, CONTACT_FIELDGROUP_LONGNAME, $language);
						$fieldDesc [CONTACT_FIELDGROUP_SHORTNAME] = self::getElementDescription ( $xpath, $field, CONTACT_FIELDGROUP_SHORTNAME, $language);
						if (!$fieldDesc [CONTACT_FIELDGROUP_SHORTNAME] ) {
							$fieldDesc [CONTACT_FIELDGROUP_SHORTNAME] = $fieldDesc [CONTACT_FIELDGROUP_LONGNAME];
						}
					}
					
					if (! isset ( $fieldDesc [CONTACT_REQUIRED] ))
						$fieldDesc [CONTACT_REQUIRED] = false;
					
					if (! isset ( $fieldDesc [CONTACT_REQUIRED_GROUP] ))
						$fieldDesc [CONTACT_REQUIRED_GROUP] = null;
					
					$groupFields [$fieldDesc [CONTACT_FIELDID]] = $fieldDesc;
				}
				
				$group_data [CONTACT_FIELDS] = $groupFields;
				
				$result [] = $group_data;
			}
			self::$type_desc = $result;
			
			return self::$type_desc;
		}
		
		/**
		 * Returns fields descriptions
		 * 
		 * @param int $group_id
		 * 
		 * @return array 
		 */
		public static function getFields($group_id = false, $add_group_info = true)
		{
			if (!self::$type_desc) {
				self::get(CONTACT_BASIC_TYPE);
			}
			
			$result = array();
	
			foreach( self::$type_desc as $group_data )
				if (!$group_id || $group_data[CONTACT_GROUPID] == $group_id) {
					foreach( $group_data[CONTACT_FIELDS] as $field_id => $field_data ) {
						if ($add_group_info) {
							$field_data[CONTACT_FIELDGROUPID] = $group_data[CONTACT_GROUPID];
							$field_data[CONTACT_FIELDGROUPNAME] = $group_data[CONTACT_FIELDGROUP_LONGNAME];
						}
						$result[$field_id] = $field_data;
					}
				}
			return $result;
		}
		
	}
	

?>