<?php
$NodeID = 0;

class xmlNodeX{
	
	var $ID;
	var $Name;
	var $Data;
	var $Attributes 	= array();
	/**
	 * Enter description here...
	 *
	 * @var xmlNodeX
	 */
	var $ParentNode;
	var $ChildNodes 	= array();
	var $ParserResource;
	var $parsingNode;
	
	function xmlNodeX($_Name = '', $_Attributes = array(), $_Data = '' ){
		
		$this->Name 		= $_Name;
		$this->Attributes 	= is_array($_Attributes)?$_Attributes:array();
		$this->Data 		= $_Data;
	}
	
	function getName(){
		
		return $this->Name;
	}
	
	function getAttribute($_Name){
		
		if (isset($this->Attributes[$_Name])) {
			
			return $this->Attributes[$_Name];
		}else return null;
	}
	
	function getAttributes(){
		
		return $this->Attributes;
	}
	
	function getData(){
		
		return $this->Data;
	}
	
	function &getChildNodes(){
		
		return $this->ChildNodes;
	}
	
	/**
	 * Create child node
	 *
	 * @param string $_Name
	 * @param array $_Attributes
	 * @param string $_Data
	 * @return xmlNodeX
	 */
	function &createChildNode($_Name, $_Attributes = array(), $_Data = ''){
		
		$_ChildNode = new xmlNodeX($_Name, $_Attributes, $_Data);
		$_ChildNode = &$_ChildNode;
		
		$this->addChildNode($_ChildNode);
		return $_ChildNode;
	}

	/**
	 * Create child node
	 *
	 * @param string $_Name
	 * @param array $_Attributes
	 * @param string $_Data
	 * @return xmlNodeX
	 */
	function &child($_Name, $_Attributes = array(), $_Data = ''){
		
		$child = &$this->createChildNode($_Name, $_Attributes, $_Data);
		return $child;
	}
		
	/**
	 * Enter description here...
	 *
	 * @param xmlNodeX $_ChildNode
	 */
	function addChildNode(&$_ChildNode){
		
		global $NodeID;
		$_ChildNode->ID = ++$NodeID;
		$_ChildNode->setParentNode($this);
		$this->ChildNodes[] = &$_ChildNode;
	}
	
	/**
	 * Set parent node
	 *
	 * @param unknown_type $ParentNode
	 */
	function setParentNode(&$ParentNode){
		
		$this->ParentNode = &$ParentNode;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param xmlNodeX $NewParentNode
	 */
	function moveNode(&$NewParentNode){
		
		$OldParentNode = &$this->ParentNode;
		$OldParentNode->removeChildNode($this);

		$NewParentNode->addChildNode($this);
	}
	
	/**
	 * Remove child node
	 *
	 * @param xmlNodeX $RemoveNode
	 */
	function removeChildNode(&$RemoveNode){
		
		$TC = count($this->ChildNodes);
		for ($i=0;$i<$TC;$i++){
			
			$ChildNode = &$this->ChildNodes[$i];
			/* @var $ChildNode xmlNodeX */
			if($ChildNode->ID == $RemoveNode->ID){

				array_splice($this->ChildNodes, $i, 1);
				unset($RemoveNode->ParentNode);
				break;
			}
		}
	}
	
	/**
	 * Return parent node
	 *
	 * @return xmlNodeX
	 */
	function &getParentNode(){
		
		return $this->ParentNode;
	}

	function getNodeXML($_Level = -1, $Tabbed = false, $disableCDATA = false){
		
		$_Level++;
		$_attrs = array();
		foreach ( $this->Attributes as $_Key=>$_Val ){
			
			$_attrs[] = $_Key.'="'.xHtmlSpecialChars($_Val).'"';
		}
		
		$_ChildrenXMLs = array();
		
		$_ChildNodesNum = count($this->ChildNodes);

		foreach ($this->ChildNodes as $i=>$ChildNode){
			
			if(!($this->ChildNodes[$i] instanceof xmlnodex))continue;
			$_ChildrenXMLs[] = $this->ChildNodes[$i]->getNodeXML($_Level, $Tabbed, $disableCDATA);
		}
			
		return ($Tabbed?str_repeat("\n",intval($_Level>0)).str_repeat("\t", $_Level):'').
			"<{$this->Name}".(count($_attrs)?" ".implode(" ", $_attrs):'').">".($this->Data?($disableCDATA?$this->Data:"<![CDATA[".($this->Data)."]]>"):"").
			(count($_ChildrenXMLs)?implode("",$_ChildrenXMLs).
			($Tabbed?"\n".str_repeat("\t", $_Level):'')
			:'').
			"</{$this->Name}>";
	}
	
	function _replaceSpecialChars($_Data){
	
		$_Data = str_replace('&','&amp;', $_Data);
		return str_replace(array('<','>'), array('&lt;','&gt;'), $_Data);
	}

	function renderTreeFromFile($FileName){
		
		if(!file_exists($FileName))return false;
		$this->renderTreeFromInner(file_get_contents($FileName));
	}
	
	function renderTreeFromInner($_Inner){
		
		$this->ParserResource = xml_parser_create ();
		xml_parser_set_option($this->ParserResource, XML_OPTION_CASE_FOLDING, false);
		xml_set_object($this->ParserResource, $this);
		xml_set_element_handler($this->ParserResource, "_tagOpen", "_tagClosed");
		
		xml_set_character_data_handler($this->ParserResource, "_tagData");
		
		$_Inner = xml_parse($this->ParserResource,$_Inner );
		if(!$_Inner) {
			PEAR::raiseError(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->ParserResource)),
				xml_get_current_line_number($this->ParserResource)));
		}
              
		xml_parser_free($this->ParserResource);
	}
	
	function _tagOpen($parser, $name, $attrs){
		
		if(!isset($this->parsingNode)){
			
			$this->parsingNode = &$this;
			$this->Name = $name;
			$this->Attributes = $attrs;
		}else {
			
			$_tParent = &$this->parsingNode;
			$this->parsingNode = &$_tParent->createChildNode($name, $attrs);
		}
	}
	
	function _tagData($parser, $tagData){
		
		if(trim($tagData)||$this->parsingNode->Data){
			
			$this->parsingNode->Data .= $tagData;
		}
	}
	
	function _tagClosed($parser, $name){
		
		if(!$this->parsingNode->getParentNode())
			unset($this->parsingNode);
		else
			$this->parsingNode = &$this->parsingNode->getParentNode();
	}

	function getChildrenByName($_Name){
		
		$_TC = count($this->ChildNodes);
		$Nodes = array();
		for ( $j = 0; $j<$_TC; $j++){
			
			if(!($this->ChildNodes[$j] instanceof xmlnodex))continue;
			if ($this->ChildNodes[$j]->getName() == $_Name){
				
				$Nodes[] = &$this->ChildNodes[$j];
			}
		}
		
		return $Nodes;
	}
	
	function getChildData($_ChildName){
		
		$children = $this->getChildrenByName($_ChildName);
		foreach($children as $_child){
			
			return $_child->getData();
		}
		return '';
	}

	/**
	 * Enter description here...
	 *
	 * @param string $ChildName
	 * @return xmlNodeX
	 */
	function &getFirstChildByName($ChildName){
		
		$r_Children = $this->getChildrenByName($ChildName);
		if(!count($r_Children)){
			$r_Children = null;
			return $r_Children;
		}
		
		return $r_Children[0];
	}
	
	/**
	 * Now only /xxx/xxxx/xxxxx
	 *
	 * @param unknown_type $_xPath
	 * @return array
	 */
	function xPath($_xPath){
		
		$TagNames = explode('/', $_xPath);
		$_TagName = '';
		$Nodes = array();
		while (count($TagNames)){
			
			$_TagName = array_shift($TagNames);
			if(!$_TagName)continue;

			$Ignore = false;
			if(preg_match('/\[(.*?)\]/', $_TagName, $SubPatterns)){
			
				$_TagName = preg_replace('/\[.*?\]/', '', $_TagName);
				$r_tAttributes = explode(',', $SubPatterns[1]);
				foreach ($r_tAttributes as $_Attribite){
					
					$_Attribite = explode('=', $_Attribite);
					$AttributeName = preg_replace('/^\@/','', $_Attribite[0]);
					$AttributeValue = preg_replace('/^"(.*?)"$/','$1', $_Attribite[1]);
					$n_atr = $this->getAttribute($AttributeName);
					
					if(!( $n_atr == $AttributeValue | '"'.$n_atr.'"' == $AttributeValue | '\''.$n_atr.'\'' == $AttributeValue)){
						
						$Ignore = true;
						break;
					}
				}
			}
			
			if(!count($TagNames) && $_TagName==$this->getName() && !$Ignore){

				$r_t = array(&$this);
				return $r_t;
			}

			list($chTagName) = $TagNames;

			$r_Attributes = array();
			if(preg_match('/\[(.*?)\]/', $chTagName, $SubPatterns)){
				
				$chTagName = preg_replace('/\[.*?\]/', '', $chTagName);
				$r_tAttributes = explode(',', $SubPatterns[1]);
				foreach ($r_tAttributes as $_Attribite){
					
					$_Attribite = explode('=', $_Attribite);
					$r_Attributes[preg_replace('/^\@/','', $_Attribite[0])] = preg_replace('/^"(.*?)"$/','$1', $_Attribite[1]);
				}
			}
			
			$ChildNodes = $this->getChildrenByName($chTagName);
			
			$_TC = count($ChildNodes);
			for($n = 0; $n<$_TC; $n++){

				$Ignore = false;
				foreach ($r_Attributes as $AttributeName => $AttributeValue){
					
					$n_atr = $ChildNodes[$n]->getAttribute($AttributeName);
					if(!( $n_atr == $AttributeValue | '"'.$n_atr.'"' == $AttributeValue | '\''.$n_atr.'\'' == $AttributeValue)){
					
						$Ignore = true;
						break;
					}
				}
				if($Ignore)continue;
				
				$Nodes = array_merge($Nodes, $ChildNodes[$n]->xPath('/'.implode('/', $TagNames)));
			}
			break;
		}
		
		return $Nodes;
	}
	
		
	function saveToFile($FileName, $Tabbed = false, $encoding = 'ISO-8859-1'){
		
		$fp = fopen($FileName, 'w');
		fwrite($fp, '<?xml version="1.0" encoding="'.$encoding.'"?>'."\r\n".$this->getNodeXML(-1, $Tabbed));
		fclose($fp);
	}

	function setData($Data){
		
		$this->Data = $Data;
	}

	/**
	 * Set or get attribute
	 *
	 * @param string $k
	 * @param string $v
	 */
	function attribute($k, $v = null){
		
		if(!is_null($v)){
			
			$this->Attributes[$k] = $v;
		}
		return $this->getAttribute($k);
	}
}
?>