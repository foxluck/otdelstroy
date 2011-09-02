<?php
	class WbsTreeRootFolder extends WbsTreeFolder {
		public function __construct($tree) {
			$this->Id = "ROOT";
			if ($tree->getRootFolderLabel())
				$this->Name = $tree->getRootFolderLabel();
			$this->tree = $tree;
			$this->isRoot = true;
			$this->children = null;
		}
		
		public function getRightsStr($short = false) {
			return "";
		}
	}
?>