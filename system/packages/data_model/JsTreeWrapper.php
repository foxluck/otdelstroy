<?php
	class JsTreeWrapper {
		
		public function getTreeNodesJs($tree) {
			$rootNode = $tree->getRootNode();
			return $this->getNodeJs($rootNode);
		}
		
		protected function getNodeParams($node) {
			return array ($node->Id, $node->Name, $node->Rights);
		}
		
		public function getNodeJs($node) {
			$params = $this->getNodeParams($node);
			foreach ($params as &$cParam) {
				$cParam = "'" . $this->filterStr($cParam) . "'";
			}
			if (!$node->isAvailable()) {
				if ($node->isRoot()) {
					return "[" . join(",", $params) . "]";
				} else {
					return "";			
				}
			}
			if ($node->hasChildren()) {
				$children = $node->getChildren();
				$childrenJs = array ();
				foreach ($children as $cChild) {
					$childJs = $this->getNodeJs($cChild);
					if ($childJs)
						$childrenJs[] = $childJs;
				}
				$params[] = "[" . join(",", $childrenJs) ."]";
			}				
			$str = "[" . join(",", $params) . "]";
			return $str;
		}
		
		public function filterStr($str) {
			$str = str_replace("\\", "\\\\", $str);
			$str = str_replace("'", "\\'", $str);
			return $str;
		}
		
	}
?>