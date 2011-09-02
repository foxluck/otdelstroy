<?php
	class JsListWrapper {
		
		public function getListNodesJs($list, $keys = false) {
			$nodesJs = array();
			foreach ($this->getNodes($list) as $node) {
				$nodeJs = $this->getNodeJs($node, $keys);
				if ($nodeJs) {
					$nodesJs[] = $nodeJs;
				}
			}
			return "[" . join(",", $nodesJs) ."]";
		}		

		public function getNodes($list)
		{
			return $list->getAvailableNodes();
		}
		
		public function getNodeJs($node, $keys = false) {
			$params = $this->getNodeParams($node);
			if (array_diff(array_keys($params), array_keys(array_values($params)))) {
				$keys = true;
			}
			foreach ($params as $key => &$cParam) {
				if (is_array($cParam)) {
					$cParam = $this->getNodeJs($cParam); 
				} else {
				    if (!$keys && is_numeric($key)) {
					    $cParam = '"' . $this->filterStr($cParam) . '"';
				    } else {
				        $cParam = '{"key":"'.$key.'", "value":"'.$this->filterStr($cParam) . '"}';
				    }
				}
			}
			$str = "[" . join(",", $params) . "]";
			return $str;
		}		

		protected function getNodeParams($node) {
			return array ($node->Id, $node->Name, '');
		}
		
		public function filterStr($str) {
			$str = str_replace("\\", '\\\\', $str);
			$str = str_replace("\r\n", '\\n', $str);
			$str = str_replace("\n", '\\n', $str);			
			$str = str_replace('"', '\\"', $str);
			return $str;
		}
		
	}
?>