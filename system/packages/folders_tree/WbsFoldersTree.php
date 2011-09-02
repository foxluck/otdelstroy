<?php
	class WbsFoldersTree {
		protected $tableName;
		protected $idField;
		protected $parentIdField;
		private $user;
		private $rightsPath;
		protected $nodes;
		
		public $prefix;
		private $rootNode;
		
		
		public function __construct($user, $tableName, $prefix, $rightsPath) {
			$this->tableName = $tableName;
			$this->prefix = $prefix;
			$this->idField = $prefix . "ID";
			$this->parentIdField = $prefix . "ID_PARENT";
			$this->statusField = $prefix . "STATUS";
			$this->rightsPath = $rightsPath;
			$this->user = $user;
		}
		
		
		public function loadTree() {
			$this->loadFromList($this->loadTreeNodesList());
		}
		
		private function loadTreeNodesList() {
			$sql = $this->getLoadNodesSqlQuery ();
			$rows = Wdb::getData($sql);
			return $rows;
		}
		
		private function loadFromList($rows) {
			$this->rootNode = $this->createRootNode();
			
			$nodesList = array ();
			$nodesList[$this->rootNode->Id] = $this->rootNode;
			foreach ($rows as $cRow) {
				$node = $this->createNode($cRow);
				$node->initChildren();
				$nodesList[$node->Id] = $node;
			}
			foreach ($nodesList as $cNode) {
				if ($cNode->isRoot())
					continue;
				$parentNode = $nodesList[$cNode->ParentId];
				if (!$parentNode)
					throw new RuntimeException ("Cannot find node " . $cNode->ParentId . " witch defined as parent for " . $cNode->Id);
				$parentNode->appendChild($cNode);
			}
			$this->nodes = $nodesList;
		}
		
		public function getRootNode() {
			return $this->rootNode;
		}
		
		public function createRootNode () {
			return new WbsFolderRootNode ($this);			
		}
		
		public function getAvailableNodes($parentNode = null, $level = 0) {
			if ($parentNode === null)
				$parentNode = $this->getRootNode();
			$parentNode->Level = $level;

			$res = array ();
			if (!$parentNode->isAvailable())
				return array();
			$res[] = $parentNode;
			$children = $parentNode->getChildren();
			foreach ($children as $cChild) {
				$res = array_merge($res, $this->getAvailableNodes($cChild, $level +1));
			}
			return $res;
		}
		
		public function getNodeRow($id) {
			$sql = $this->getLoadNodesSqlQuery($id);			
			$row = Wdb::getRow($sql);
			return $row;
		}
		
		public function getNode($id) {
			if (!$id)
				throw new RuntimeException("Not setted id for getNode");
			
			if (!empty($this->nodes[$id]))
				return $this->nodes[$id];
			
			$row = $this->getNodeRow($id);
			$node = $this->createNode($row);
			$this->nodes[$id] = $node;
			return $node;
		}
		
		public function updateNode($id, $data, $fields) {
			if ($fields == null)
				$fields = array_keys($data);
			$sql = new CUpdateSqlQuery ($this->tableName);
			$sql->addFields($data, $fields);
			$sql->addConditions($this->idField, $id);
			Wdb::runQuery($sql);
		}
		
		protected function getLoadNodesOrderBySql () {
			return "DF.FOLDER_ID_FIELD ASC";
		}
		
		
		// if folderId is null - return's query for all nodes
		private function getLoadNodesSqlQuery($folderId = null) {
			$sql = "SELECT 
				DF.* , 
				IF (NOT(AL.LINK_AR_PATH IS NULL),
					BIT_OR( DA2.AR_VALUE ) | IF (DAC2.AR_VALUE IS NULL , 0, DAC2.AR_VALUE),
					BIT_OR( DA.AR_VALUE ) | IF (DAC.AR_VALUE IS NULL , 0, DAC.AR_VALUE)) 
				AS USER_RIGHTS
			FROM TREE_FOLDER_TABLE DF 
				LEFT JOIN UGROUP_USER UGU ON UGU.U_ID = 'USER_ID_FIELD' 
				LEFT JOIN UG_ACCESSRIGHTS DA ON DA.AR_OBJECT_ID = DF.FOLDER_ID_FIELD AND DA.AR_ID = UGU.UG_ID AND DA.AR_PATH = 'RIGHTS_PATH' 
				LEFT JOIN U_ACCESSRIGHTS DAC ON DAC.AR_OBJECT_ID = DF.FOLDER_ID_FIELD AND DAC.AR_ID = 'USER_ID_FIELD' AND DAC.AR_PATH = 'RIGHTS_PATH' 
				LEFT JOIN ACCESSRIGHTS_LINK AL ON (AL.AR_PATH='RIGHTS_PATH' AND AL.AR_OBJECT_ID=DF.FOLDER_ID_FIELD)
				LEFT JOIN U_ACCESSRIGHTS DAC2 ON (DAC2.AR_OBJECT_ID = AL.LINK_AR_OBJECT_ID AND DAC2.AR_PATH = AL.LINK_AR_PATH AND DAC2.AR_ID = 'USER_ID_FIELD' )
				LEFT JOIN UG_ACCESSRIGHTS DA2 ON (DA2.AR_OBJECT_ID = AL.LINK_AR_OBJECT_ID AND DA2.AR_PATH = AL.LINK_AR_PATH AND DA2.AR_ID = UGU.UG_ID )
			WHERE
				DF.FOLDER_STATUS_FIELD = 'FOLDER_STATUS' 
				ID_SQL
			GROUP BY DF.FOLDER_ID_FIELD
			ORDER BY ORDER_BY_SQL
			";
			
			$orderBySql = $this->getLoadNodesOrderBySql();
			
			$sql = str_replace("USER_ID_FIELD", $this->user->getId(), $sql);
			$sql = str_replace("RIGHTS_PATH", $this->rightsPath, $sql);
			$sql = str_replace("FOLDER_STATUS_FIELD", $this->statusField, $sql);			
			$sql = str_replace("FOLDER_STATUS", 0, $sql);			
			$idSql = ($folderId) ? "AND FOLDER_ID_FIELD='$folderId'" : "";				
			$sql = str_replace("ID_SQL", $idSql, $sql);
			$sql = str_replace("ORDER_BY_SQL", $orderBySql, $sql);
			$sql = str_replace("FOLDER_ID_FIELD", $this->idField, $sql);
			$sql = str_replace("TREE_FOLDER_TABLE", $this->tableName, $sql);
			
			return $sql;			
		}
	}
?>