<?php
	class DDListWidgetType extends WidgetType {
		
		function DDListWidgetType () {
			$this->id = "DDList";
			$this->applications = array ("DD");
			
			$this->fieldsData = 
			array (
				"FILES" => array ("type" => "SUBTYPE", "place" => "files", "file" => "files.htm"),
				"FOLDERS" => array ("type" => "SUBTYPE", "place" => "files", "file" => "folder.htm"),
				"TITLEBGCOLOR" => array ("type" => "color", "place" => "color", "default" => "#99CCFF"),
				"TITLECOLOR" => array ("type" => "color", "place" => "color", "default" => "#000000"),
				"BODYBGCOLOR" => array ("type" => "color", "place" => "color", "default" => "#FFFFEA"),
				"WIDTH" => array ("type" => "width", "place" => "size", "default" => 600),
				"HEIGHT" => array ("type" => "width", "place" => "size", "default" => 400),
				"VIEW_MODE" => array ("type" => "select"), // FOR LinkType
				"VIEWMODE" => array ("type" => "select", "place" => "view", "default" => "grid", "values" => array ("grid", "list")),
				"FILEICON" => array ("type" => "select", "place" => "view", "default" => "small", "values" => array ("no","small","large", "thumbnail")),
				"SHOWTITLES" => array ("type" => "checkbox", "default" => 1, "place" => "view"),
				"SHOWDESC" => array ("type" => "checkbox", "default" => 1, "place" => "view"),
				"SHOWSIZE" => array ("type" => "checkbox", "default" => 1, "place" => "view"),
				"SHOWDATE" => array ("type" => "checkbox", "default" => 1, "place" => "view"),
				"SHOWDOWNLOADLINK" => array ("type" => "checkbox", "default" => 1, "place" => "view"),
				"SHOWBORDER" => array ("type" => "checkbox", "default" => 1, "place" => "view"),
				"SORTING" => array ("type" => "select", "place" => "view", "default" => "filename", "values" => array ("filename", "dateasc", "datedesc")),
			);
			
			parent::WidgetType ();
			
			$this->fieldsPlaces = array (
				"general" => array("title" => $this->strings["place_general_title"]),
				"color" => array("title" => $this->strings["place_color_title"]),
				"view" => array("title" => $this->strings["place_view_title"]),
				"size" => array("title" => $this->strings["place_size_title"]),
				"files" => array("title" => $this->strings["place_files_title"])
			);
			
			$this->addSubtype(new DDListLinkSubtype($this));
			$this->addSubtype(new DDListInplaceSubtype($this));
		}
	}
?>