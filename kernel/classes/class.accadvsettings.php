<?php
	class AccAdvSettings{
		
		var $DBKEY;
		
		function AccAdvSettings($dbkey) {
			$this->DBKEY = $dbkey;
		}
		
		function GetSettingsNode ($dom, $xpath, $createIfNotExists = false) {
			$settingsNode = &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_ADVSETTINGS );
			if ( !$settingsNode || !count($settingsNode->nodeset) && $createIfNotExists){
				$databaseNode = &xpath_eval( $xpath, "/".HOST_DATABASE );
				$settingsNode = create_addElement( $dom, $databaseNode->nodeset[0], HOST_ADVSETTINGS );
			}else
				$settingsNode = &$settingsNode->nodeset[0];
			return $settingsNode;
		}
		
		function SetParam ($name, $value) {
			$filePath = sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($this->DBKEY) );
			$dom = @domxml_open_file( realpath($filePath) );
			if ( !$dom )return false;
			$xpath = xpath_new_context($dom);
			
			$paramNodes =  &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_ADVSETTINGS . "/" . HOST_ADVPARAM . "[@name='" . $name . "']");
			
			if (!($paramNodes && count($paramNodes->nodeset))) {
				$settingsNode = $this->GetSettingsNode ($dom, $xpath, true);
				$paramNode = @create_addElement( $dom, $settingsNode, HOST_ADVPARAM );
				$paramNode->set_attribute("name", $name);
			} else
				$paramNode = $paramNodes->nodeset[0];
			
			$paramNode->set_attribute ("value", $value);
			
			@$dom->dump_file($filePath, false, true);
			
		}
		
		function GetParam ($name) {
			$filePath = sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($this->DBKEY) );
			$dom = @domxml_open_file( realpath($filePath) );
			if ( !$dom )
				return false;
			$xpath = xpath_new_context($dom);
			$paramNodes =  &xpath_eval( $xpath, "/".HOST_DATABASE."/".HOST_ADVSETTINGS . "/" . HOST_ADVPARAM . "[@name='" . $name . "']");
			if ($paramNodes && count($paramNodes->nodeset)) {
				$paramNode = $paramNodes->nodeset[0];				
				return $paramNode->get_attribute ("value");
			}
			else
				return false;
		}
			
		
		
		
	}
?>