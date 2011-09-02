<?php
	// Base class for tree folder
	class WbsTreeFolder extends WbsFolder {
		public $ParentId;
		protected $tree;
		protected $children;
		protected $branchMaxRights = null;
		
		public function __construct($dataModel, $row = null) {
			$this->tree = $dataModel->getFoldersTree();
			parent::__construct($dataModel, $row);
		}		
		
		public function asArray () {
			$row = parent::asArray ();
			$row["PARENT_ID"] = $this->ParentId;
			return $row;
		}		
		
		// Return user rights summ for this node and all nodes in subtree
		protected function getBranchMaxRights() {
			if ($this->branchMaxRights)
				return $this->branchMaxRights;
			$result = $this->Rights;
			foreach ($this->children as $cNode)
				$result = max($result, $cNode->getBranchMaxRights());
			return $result;
		}
		
		public function loadRow($row) {
			$this->ParentId = $row[$this->tree->prefix . "ID_PARENT"];
			parent::loadRow($row);
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
				
		public function appendChild($childNode) {
			$this->children[] = $childNode;
			$this->changed();
		}
		
		public function initChildren() {
			$this->children = array ();
		}
		
		private function changed() {
			$this->branchMaxRights = null;
		}
		
		public function isAvailable() {
			$branchMaxRights = $this->getBranchMaxRights();
			return $branchMaxRights > 0;
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
	}
	
?>