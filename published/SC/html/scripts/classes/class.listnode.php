<?php
if(class_exists('listnode'))return false;

/**
 * List
 * @version 1.0
 */
class ListNode{
	
	/**
	 * Refernce to the previous node
	 *
	 * @var ListNode
	 */
	var $PreviousNode = null;
	/**
	 * Reference to the next node
	 *
	 * @var ListNode
	 */
	var $NextNode = null;
	/**
	 * Data
	 *
	 * @var mixed
	 */
	var $Data;
	/**
	 * Node unique key
	 *
	 * @var string
	 */
	var $Key;
	
	/**
	 * Constractor
	 *
	 * @param mixed $Data - node data
	 * @return ListNode
	 */
	function ListNode($Key = '', $Data = null){
		
		$this->setData($Data);
		$this->setKey($Key);
		$this->PreviousNode = null;
		$this->NextNode = null;
	}
	
	function setData(&$Data){
		
		$this->Data = $Data;
	}
	
	function setKey($Key){
		
		$this->Key = $Key;
	}
	
	function &getDataReference(){
		
		return $this->Data;
	}
	
	function getData(){
		
		return $this->Data;
	}
	
	function getKey(){
		
		return $this->Key;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param ListNode $NextNode
	 */
	function assignNextNode(&$NextNode){
		
		if(!is_null($this->NextNode)){
			
			$this->NextNode->PreviousNode = &$NextNode;
			$NextNode->NextNode = &$this->NextNode;
		}
		$this->NextNode = &$NextNode;
		$NextNode->PreviousNode = &$this;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param ListNode $PreviousNode
	 */
	function assignPreviousNode(&$PreviousNode){

		if(!is_null($this->PreviousNode)){
			
			$this->PreviousNode->NextNode = &$PreviousNode;
			$PreviousNode->PreviousNode = &$this->PreviousNode;
		}
		$this->PreviousNode = &$PreviousNode;
		$PreviousNode->NextNode = &$this;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $Key
	 * @param unknown_type $Data
	 * @return ListNode
	 */
	function &createNextNode($Key = '', $Data = ''){
		
		$ListNode = new ListNode($Key, $Data);
		$this->assignNextNode($ListNode);
		return $ListNode;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown_type $Key
	 * @param unknown_type $Data
	 * @return ListNode
	 */
	function &createPreviousNode($Key = '', $Data = ''){
		
		$ListNode = new ListNode($Key, $Data);
		$this->assignPreviousNode($ListNode);
		return $ListNode;
	}
	
	function printNextNodes(){
		
		$t = 10000;
		
		$NextNode = $this->NextNode;
		while (!is_null($NextNode)&&0<$t--){
			
			print $NextNode->printNode().'<br />';
			$NextNode = $NextNode->NextNode;
		}
	}
	
	function printPreviousNodes(){
		
		$t = 10000;
		
		$PreviousNode = $this->PreviousNode;
		while (!is_null($PreviousNode)&&0<$t--){
			
			print $PreviousNode->printNode().'<br />';
			$PreviousNode = $PreviousNode->PreviousNode;
		}
	}
	
	function printNode(){
		
		print '- '.$this->Key.' = '.$this->Data;
	}
	
	/**
	 * Search node by key
	 *
	 * @param string $Key
	 * @return ListNode
	 */
	function &findNode($Key){
		
		if($this->getKey()==$Key){
			
			return $this;
		}
		$FoundNode = null;
		
		$t = 10000;
		
		$PreviousNode = &$this->PreviousNode;
		while (!is_null($PreviousNode)&&0<$t--){
			
			if($PreviousNode->getKey() == $Key){
				
				$FoundNode = &$PreviousNode;
				break;
			}
			$PreviousNode = &$PreviousNode->PreviousNode;
		}
		
		$t = 10000;
		
		$NextNode = &$this->NextNode;
		while (!is_null($NextNode)&&0<$t--){
			
			if($NextNode->getKey() == $Key){
				
				$FoundNode = &$NextNode;
				break;
			}
			$NextNode = &$NextNode->NextNode;
		}
		
		return $FoundNode;
	}
	
	/**
	 * Create node list and return reference to the first node
	 *
	 * @param array $r_NodesInfo
	 * @return ListNode
	 */
	function &importNodes($r_NodesInfo){
		
		$ListNode = new ListNode();
		$FirstNode = &$ListNode;
		$TC = count($r_NodesInfo);
		
		foreach ($r_NodesInfo as $Key=>$Data){
			
			$ListNode->setData($Data);
			$ListNode->setKey($Key);
			
			$TNode = &$ListNode;
			unset($ListNode);
			$TC--;
			if($TC>0){
				
				$ListNode = &$TNode->createNextNode();
				unset($TNode);
			}
		}
		
		return $FirstNode;
	}

	/**
	 * Return reference to thefirst node in list
	 *
	 * @return ListNode
	 */
	function &getFirstNode(){
		
		$Node = &$this;
		while (!is_null($Node->PreviousNode)){
			
			$Node = &$Node->PreviousNode;
		}
		return $Node;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return ListNode
	 */
	function &getLastNode(){
		
		$Node = &$this;
		while (!is_null($Node->NextNode)){
			
			$Node = &$Node->NextNode;
		}
		return $Node;
	}

	/**
	 * Remove current node from list
	 *
	 */
	function deleteFromList(){
		
		if(!is_null($this->PreviousNode)){
			
			if(!is_null($this->NextNode)){
				$this->PreviousNode->NextNode = &$this->NextNode;
			}else{
				$this->PreviousNode->NextNode = null;
			}
		}
		
		if(!is_null($this->NextNode)){
			
			if(!is_null($this->PreviousNode)){
				$this->NextNode->PreviousNode = &$this->PreviousNode;
			}else {
				$this->NextNode->PreviousNode = null;
			}
		}
		unset($this);
	}
	
	function printChain($Direction = '>'){

		switch ($Direction){
			default:
			case '>':
				$Node = &$this->getFirstNode();
				print '-&gt;'.$Node->Key;
				
				while (!is_null($Node->NextNode)){
					
					$Node = &$Node->NextNode;
					print '-&gt;'.$Node->Key;
				}
				break;
			case '<':
				$Node = &$this->getLastNode();
				print $Node->Key.'&lt;-';
				
				while (!is_null($Node->PreviousNode)){
					
					$Node = &$Node->PreviousNode;
					print $Node->Key.'&lt;-';
				}
				break;
		}
	}
}
?>