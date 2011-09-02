<?php

class DataTree{
	var $root = array('id'=>null,'parent'=>null,'children' => array(),'data'=>null);
	private $pNodes = array();
	private $maxNodeId = null;

	function __destruct(){
		unset($this->root);
		unset($this->pNodes);
		unset($this->callbackHandler);
	}

	function setData($data,$nodeId = null,$parentId = null){

		$node = &$this->getNode($nodeId,$parentId);
		$node['data'] = $data;
	}

	function getData($nodeId){
		$node = &$this->getNode($nodeId);
		return $node['data'];
	}

	function sortNodes($callback,$nodeId = 1)
	{
		if(isset($this->pNodes[$nodeId])){
			uasort($this->pNodes[$nodeId]['children'],$callback);
			foreach($this->pNodes[$nodeId]['children'] as $childNode){
				$this->sortNodes($callback,$childNode['id']);
			}
		}
	}

	function plainData($level = 0,$exportEmpty = false,&$result = null,&$node = null,$simple = false,$max_level = null)
	{
		if(!is_array($result)){
			$result = array();
		}
		if(is_null($max_level)||$max_level>0){
			if(!is_array($node)){
				$node = &$this->root;
			}elseif(($exportEmpty||$node['data'])){
				if($simple){
					$result[] =&$node['data'];
				}else{
					$result[] = array('id'=>&$node['id'],'level'=>$level,'data'=>&$node['data']);
				}
			}
			if(is_null($max_level)||$max_level>1){
				foreach($node['children'] as $childNode){
					if(count($childNode['children'])){
						$this->plainData($level+1,$exportEmpty,$result,$childNode,$simple,$max_level);
					}elseif(($exportEmpty||$childNode['data'])){
						if($simple){
							$result[] = &$childNode['data'];
						}else{
							$result[] = array('id'=>&$childNode['id'],'level'=>$level+1,'data'=>&$childNode['data']);
						}
					}
				}
			}
			if(!is_null($max_level)){
				$max_level--;
			}
		}

		return $result;
	}

	function &getNode($id = null,$parentId = null){
		if(is_null($id)){
			return $this->root;
		}
		$id = intval($id);
		if(!is_null($parentId)){
			$parentId = intval($parentId);
		}
		if(isset($this->pNodes[$id])){
			if($parentId&&($this->pNodes[$id]['parent']!=$parentId)){
				if($this->pNodes[$id]['parent']){
					$oldParentNode = $this->getNode($this->pNodes[$id]['parent']);
					unset($oldParentNode['children'][$id]);
				}else{
					unset($this->root['children'][$id]);
				}

				$this->pNodes[$id]['parent'] = $parentId;
				$newParentNode = &$this->getNode($parentId);
				$newParentNode['children'][$id] = &$this->pNodes[$id];
			}
			return $this->pNodes[$id];
		}else{
			return $this->addNode($id,$parentId);
		}
	}

	private function &addNode($id,$parentId = null){
		$node = array('id'=>$id,'parent'=>$parentId,'children'=>array(),'data'=>null);
		$parentNode = &$this->getNode($parentId);
		$parentNode['children'][$id] = &$node;
		$this->pNodes[$id] = &$node;
		if(!$this->maxNodeId||$id>$this->maxNodeId){
			$this->maxNodeId = $id;
		}
		return $node;
	}
	function getMaxNodeId(){
		return $this->maxNodeId;
	}
}

?>