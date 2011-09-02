<?php
	
	class DDUploaderWidgetType extends WidgetType {
		
		function DDUploaderWidgetType () {
			$this->id = "DDUploader";
			$this->applications = array ("DD");
			
			$this->fieldsData = 
			array (
				"FOLDER" => array ("type" => "SUBTYPE", "place" => "files", "file" => "folder.htm"),
				
				//"SHOWBORDER" => array ("type" => "checkbox", "default" => true, "place" => "layout"),
				"BORDERCOLOR" => array("type" => "color", "default" => "#999999", "place" => "color"),
				//"TITLE" => array ("type" => "string", "default" => "", "place" => "layout"),
				"TITLECOLOR" => array("type" => "color", "default" => "#FFFFFF", "place" => "color"),
				"BGCOLOR" => array ("type" => "color", "default" => "#F0F0F0", "place" => "color"),
				
				"WIDTH" => array ("type" => "width", "default" => 280, "min" => 240, "max" => 1200, "place" => "size"), 
				"HEIGHT" => array ("type" => "width", "default" => 280, "min" => 100, "max" => 1200, "place" => "size"), 
				//"VISIBLEFILES" => array ("type" => "subtype", "file" => "visiblefiles.htm", "default" => 7, "min" => 2, "max" => 20, "place" => "size"), 
				
					
				"MAXFILESCOUNT" => array ("type" => "int",  "default" => "20","place" => "files"),
				"MAXFILESIZE" => array ("type" => "int",  "default" => "2","place" => "files"),
				"CANDELETE" => array ("type" => "checkbox", "default" => true, "place" => "files")
			);
			
			parent::WidgetType ();
			$this->fieldsPlaces = array (
				"general" => array("title" => $this->strings["place_general_title"]),
				"color" => array("title" => $this->strings["place_color_title"]),
				"size" => array("title" => $this->strings["place_size_title"]),
				"files" => array("title" => $this->strings["place_files_title"])
			);
			
			
			$this->addSubtype(new DDUploaderInplaceSubtype($this));
		}
	}
?>