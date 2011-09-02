<?php
	class PDListWidgetType extends WidgetType {
		
		function PDListWidgetType () {
			$this->id = "PDList";
			$this->applications = array ("PD");
			
			$this->fieldsData = 
			array (
				"FILES" => array ("type" => "SUBTYPE", "place" => "photos", "file" => "files.htm"),
				"FOLDERS" => array ("type" => "SUBTYPE", "place" => "photos", "file" => "folder.htm"),
				"SLIMAGESIZE" => array ("type" => "select", "values" => array ("256", "512", "1024"), "default" => "256", "place" => "size"),
				"SLWIDTH" => array ("type" => "width", "default" => 256, "min" => 96, "max" => 1024, "place" => "size"),
				"SLHEIGHT" => array ("type" => "height", "default" => 192, "min" => 96, "max" => 786, "place" => "size"),
				"GLIMGWIDTH" => array ("type" => "width", "default" => 256, "min" => 96, "max" => 1024, "place" => "size"),
				"GLIMGHEIGHT" => array ("type" => "height", "default" => 192, "min" => 96, "max" => 786, "place" => "size"),
				"IMAGESIZE" => array ("type" => "select", "values" => array ("96", "256", "512", "1024"), "default" => "96", "place" => "size"),
				"COLUMNSCOUNT" => array ("type" => "select", "values" => array (1,2,3,4,5,6,7,8,9,10), "default" => 3, "place" => "size"),
				"HEIGHT" => array ("type" => "height", "default" => "300", "place" => "size"),
				"GLHEIGHT" => array ("type" => "height", "default" => "450", "place" => "size"),
				"SLBODYBGCOLOR" => array ("type" => "color", "default" => "#F0F0F0", "place" => "color"),
				"BODYBGCOLOR" => array ("type" => "color", "default" => "#FFFFFF", "place" => "color"),
				"SHOWBORDER" => array ("type" => "checkbox", "default" => 1, "place" => "view"),
				"SLCPANEL" => array("type" => "select", "default" => "view", "values" => array ("disable", "view"), "place" => "view"),
				"SLAUTOPLAY" => array ("type" => "checkbox", "default" => 1, "place" => "view"),
				"SLSECONDS" => array ("type" => "select", "default" => 3, "values" => array (1,2,3,4,5,6,7,8,9,10), "place" => "view"),
				"SLONCLICK" => array ("type" => "select", "default" => "play", "values" => array("disable", "pause", "play"), "place" => "view"),
				"SHOWDESC" => array ("type" => "checkbox", "default" => 1, "place" => "view"),
				"GLONCLICK" => array ("type" => "select", "default" => "enlarge", "values" => array("disable", "enlarge"), "place" => "view"),
				"MAXFILESCOUNT" => array ("type" => "int",  "default" => "20","place" => "photos"),
				"MAXFILESIZE" => array ("type" => "int",  "default" => "2","place" => "photos")				
			);
			
			parent::WidgetType ();
			
			$this->fieldsPlaces = array (
				"general" => array("title" => $this->strings["place_general_title"]),
				"color" => array("title" => $this->strings["place_color_title"]),
				"view" => array("title" => $this->strings["place_view_title"]),
				"size" => array("title" => $this->strings["place_size_title"]),
				"photos" => array("title" => $this->strings["place_photos_title"])
			);
			
			$this->addSubtype(new PDListLinkSubtype($this));
			$this->addSubtype(new PDListGallerySubtype($this));
			$this->addSubtype(new PDListAlbumSubtype($this));
		}
	}
?>