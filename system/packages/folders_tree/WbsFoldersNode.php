<?php
	class WbsFolderNode {
		protected $tree;
		public $Id;
		public $ParentId;
		public $Name;
		public $Status;
		public $Rights;
		private $rightsSumm = null;
		protected $isRoot;
		
		private $children;
		
		public function __construct($tree, $row) {
			$this->tree = $tree;
			$this->loadRow($row);
			$this->children = null;
		}
		
		public function loadRow($row) {
			$tree = $this->tree;
			$this->Id = $row[$tree->prefix . "ID"];
			$this->ParentId = $row[$tree->prefix . "ID_PARENT"];
			$this->Name = $row[$tree->prefix . "NAME"];
			$this->Status = $row[$tree->prefix . "STATUS"];
			$this->Rights = $row["USER_RIGHTS"];
		}
		
		public function update($data, $fields = null) {
			$this->tree->updateNode($this->Id, $data, $fields);
			$this->reload();
		}
		
		
		public function reload() {
			$row = $this->tree->getNodeRow($this->Id);
			$this->loadRow($row);
		}
		
		// if rights type is null check for not null rights
		// if user user is null- check for current tree user
		public function checkUserRights($rightsType = null, $user = null) {
			return true;
			throw new RuntimeException ("User have no rights to this folder");
		}
				
		public function initChildren() {
			$this->children = array ();
		}
		
		public function appendChild($childNode) {
			$this->children[] = $childNode;
			$this->changed();
		}
		
		private function changed() {
			$this->rightsSumm = null;
		}
		
		public function isAvailable() {
			$rightsSumm = $this->getRightsSumm();
			return $rightsSumm > 0;
		}
		
		public function isRoot() {
			return $this->isRoot;
		}
		
		public function getChildren() {
			if ($this->children === null)
				$this->loadChildren();
			return $this->children;
		}
		
		public function hasChildren() {
			if ($this->children === null)
				$this->loadChildren();
			return sizeof($this->children) > 0;
		}
		
		public function loadChildren() {
			throw new NotImplementedException();
		}
		
		public function asArray () {
			$row = array (
				"ID" => $this->Id,
				"PARENT_ID" => $this->ParentId,
				"NAME" => $this->Name,
				"STATUS" => $this->Status,
				"RIGHTS" => $this->Rights
			);
			return $row;
		}
		
		// Return user rights summ for this node and all nodes in subtree
		public function getRightsSumm() {
			if ($this->rightsSumm)
				return $this->rightsSumm;
			$result = $this->Rights;
			if ($this->children) {
				foreach ($this->children as $cNode) {
					$result += $cNode->getRightsSumm();
				}
			}
			return $result;
		}
		
		public function getRightsStr($short = false) {
			return RightsMask::getRightsStr($this->Rights, $short);
		}
	}
	
	class WbsFolderRootNode extends WbsFolderNode {
		public function __construct($tree) {
			$this->Id = "ROOT";
			$this->Name = "Available Folders";
			$this->tree = $tree;
			$this->isRoot = true;
			$this->children = null;
		}
		
		public function getRightsStr($short = false) {
			return "";
		}
	}
?>