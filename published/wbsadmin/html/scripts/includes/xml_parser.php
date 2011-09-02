<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
	class XmlNode
	{
		var $parser;
		var $fp;
		var $currentXPath;
		var $currentIndex;
		var $xPathQuery;

		var $attributes;
		var $name;
		var $data;

		var $selectResult;

		var $innerXmlBeginIndex;
		var $innerXmlEndIndex;

		var $innerXml;

		function XmlNode()
		{
			$this->currentXPath=array();
			$this->currentIndex=0;

			$this->tmp = 0;
		}

		function SetXmlNodeAttributes( $attributes )
		{
			$this->attributes	= $attributes;
		}

		function SetXmlNodeName( $name )
		{
			$this->name			= $name;
		}

		function SetXmlNodeData( $data )
		{
			$this->data			.= $data;
			$this->tmp++;
		}

		function GetXmlNodeAttributes()
		{
			return $this->attributes;
		}

		function GetXmlNodeName()
		{
			return $this->name;
		}

		function GetXmlNodeData()
		{
			return $this->data;
		}

		function SetInnerXml($innerXml)
		{
			$this->innerXml = $innerXml;
		}

		function LoadInnerXmlFromFile($fileName)
		{
			$fp = fopen($fileName, "r");
			$this->innerXml = trim( fread( $fp, filesize($fileName) ) );
			fclose($fp);
		}

		function PrintXmlNode()
		{
			$str="";
			foreach($this->attributes as $key => $val)
			{
				$str .= $key."=".$val." ";
			}
			echo("&lt;".$this->name." ".$str." &gt;<b>DATA</b>'".$this->data."'<br>");
			echo("<b>Inner XML</b>");
			echo(str_replace("<","&lt;",$this->innerXml));
			echo("<br>");
		}

		function SelectNodes($xPathQuery)
		{
			$this->parser = xml_parser_create();
			xml_parser_set_option($this->parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
			xml_set_object($this->parser, $this);
			xml_set_element_handler($this->parser, "tag_open", "tag_close");
			xml_set_character_data_handler($this->parser, "cdata");

			$this->currentXPath=array();
			$this->currentIndex=0;
			$this->xPathQuery = $xPathQuery;
			$this->selectResult=array();
			xml_parse( $this->parser, $this->innerXml, true );
			return $this->selectResult;
		}

		function compareXPath()
		{
			$xPathQueryArray=explode( "/", $this->xPathQuery );
			if ( count($xPathQueryArray) != count($this->currentXPath) )
				return false;
			for($i=0; $i<count($this->currentXPath); $i++)
			{
				if ( strtoupper($xPathQueryArray[$i]) != strtoupper($this->currentXPath[$i]) )
					return false;
			}
			return true;
		}

		function tag_open($parser, $tag, $attributes) 
		{
			$this->currentXPath[ $this->currentIndex++ ] = $tag;
			if ( $this->compareXPath() )
			{
				$this->innerXmlBeginIndex = xml_get_current_byte_index( $this->parser );
				$newNode = new XmlNode();
				$newNode->SetXmlNodeAttributes( $attributes );
				$newNode->SetXmlNodeName( $tag );
				$this->selectResult[] = $newNode;
			}
		}

		function tag_close($parser, $tag)
		{
			unset( $this->currentXPath[ $this->currentIndex-- ] );
			if ( $this->compareXPath() )
			{
				$innerXmlEndIndex = xml_get_current_byte_index( $this->parser );

				$newInnerXml=substr( $this->innerXml, $this->innerXmlBeginIndex, 
					 $innerXmlEndIndex -  $this->innerXmlBeginIndex + 1 );

				$lastIndex = count( $this->selectResult ) - 1;

				$phpv = phpversion();

				//different result for PHP4 and PHP5
				if ( strncasecmp( $newInnerXml, "<".$tag, strlen( "<".$tag ) ) != 0 )
//				if (strstr($phpv,"5.") && $phpv[0] == '5')
					$this->selectResult[ $lastIndex ]->SetInnerXml("<".$tag.$newInnerXml.$tag.">");
				else
					$this->selectResult[ $lastIndex ]->SetInnerXml($newInnerXml.$tag.">");
			}
		}

		function cdata($parser, $cdata) 
		{
			if ( $this->compareXPath() )
			{
				$lastIndex = count( $this->selectResult ) - 1;
				if ( $lastIndex != -1 )
				{
					$this->selectResult[ $lastIndex ]->SetXmlNodeData( $cdata );//var_dump( $this->selectResult[ $lastIndex ] );
				}
			}
		}

	}
	
?>