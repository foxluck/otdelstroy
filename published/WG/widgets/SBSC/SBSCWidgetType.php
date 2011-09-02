<?php
	class SBSCWidgetType extends WidgetType {
		
		function SBSCWidgetType () {
			$this->id = "SBSC";
			$this->applications = array ("CM");
			$this->rights = array (array("app_id" => "CM", "name" => "CANTOOLS"));
			
			$this->fieldsData = array (
				"WIDTH" => array ("type" => "width", "default" => 180, "min" => 100, "max" => 800, "place" => "size"), 
				"TITLE" => array ("type" => "string",  "place" => "text"),
				"TITLE_bgcolor" => array ("type" => "color", "default" => "#999999", "place" => "color"),
				"TITLE_color" => array ("type" => "color", "default" => "#FFFFFF", "place" => "color"),
				"BGCOLOR" => array ("type" => "color", "default" => "#F0F0F0", "place" => "shortform", "place" => "color"),
				"SAVEBTN" => array ("type" => "string", "place" => "text"),
				"SIGNUPTEXT" => array ("type" => "text", "rows" => 3, "place" => "text"),
				"CMFIELDS" => array ("type" => "subtype", "file" => "custom.htm", "default" => "C_FIRSTNAME,C_LASTNAME,C_EMAILADDRESS,C_COMPANY,C_DEPARTMENT,C_JOBTITLE" , "place" => "fields"),
				"CMFIELDSLABELS" => array ("type" => "subtype", "place" => "fields"),
				"PHOTOFIELDS" => array ("type" => "subtype", "file" => "photo.htm", "place" => "fields"),
				"FOLDER" => array("type" => "subtype", "file" => "folder.htm", "place" => "shortform", "place" => "general"),
				"DOPTIN" => array ("type" => "checkbox", "place" => "shortform", "place" => "confirmation"),
				"EMAILTEXT" => array ("type" => "text", "rows" => 3, "place" => "text", "place" => "confirmation"),
			);
					
			parent::WidgetType ();
			
			$this->fieldsPlaces = array (
				"general" => array("title" => $this->strings["place_general_title"]),
				"text" => array("title" => $this->strings["place_text_title"]),
				"color" => array("title" => $this->strings["place_color_title"]),
				"size" => array("title" => $this->strings["place_size_title"]),
				"fields" => array("title" => $this->strings["place_fields_title"]),
				"confirmation" => array("title" => $this->strings['place_confirmation_title'])
			);
			
			$this->addSubtype(new SBSCSimpleSubtype($this));
			$this->addSubtype(new SBSCMainSubtype($this));
			$this->addSubtype(new SBSCPhotoSubtype($this));
			$this->addSubtype(new SBSCCustomSubtype($this));
		}
		
		function getWidgetEmbInfo ($widgetData, $subtypeId = "") {
			$info = parent::getWidgetEmbInfo($widgetData, $subtypeId);
			if ($subtypeId == 'CUSTOM' && $this->subtypes[$subtypeId]) {
				$subtypeObj = $this->subtypes[$subtypeId];
				$widgetParams = $subtypeObj->getRealParams($widgetData);
				$dbfields = explode(',', $widgetParams['CMFIELDS']);
				$fields = array();
				foreach ($dbfields as $dbfield) {
					if ($field_info = ContactType::getFieldByDbName($dbfield, $widgetData['WG_LANG'])) {
						$fields[] = $field_info;
					}
				}
				$file_exists = false;
				$code = '<input type="hidden" value="signup" name="action" />';
				foreach ($fields as $f) {
					if ($f['type'] == 'IMAGE') {
						$file_exists = true;
					}
					$code .= $f['name'].'<br /><input type="text" name="'.$f['dbname'].'" /><br />';
				}	
				$info['html_code'] = '<form '.($file_exists ? 'enctype="multipart/form-data"' : '').' method="post" action="'.$info['src'].'">'.$code;				
				$info['html_code'] .= '<input type="submit" value="'.$widgetParams['SAVEBTN'].'" /></form>';	
			}
			return $info;		
		}		
	}
?> 