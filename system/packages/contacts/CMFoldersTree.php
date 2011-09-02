<?php
	Kernel::incPackage("folders_tree");
	include_once("CMFolder.php");
	
	class CMFoldersTree extends WbsFoldersTree {
		private $app;
		protected $statData;
		
		public function __construct() {
			$user = CurrentUser::getInstance();
			parent::__construct($user, "CFOLDER", "CF_", "/ROOT/CM/FOLDERS");
		}
		
		protected function createNode($row) {
			$node = new CMFolder($this, $row);
			return $node;
		}
		
		public function getAllAvailableContacts($offset=0, $limit=false) {
			$folders = $this->getAvailableNodes();
			$ids = array ();
			foreach ($folders as $cFolder)
				$ids[] = $cFolder->Id;

			$sql = new CSelectSqlQuery("CONTACT");
			$sql->addConditions("CF_ID IN ('" . join("','", $ids) . "')");
			$sql->addConditions("C_EMAILADDRESS<>''");
			$sql->setGroupBy("C_FIRSTNAME, C_LASTNAME, C_EMAILADDRESS");
			$sql->setCustomOrderBy("C_FIRSTNAME ASC, C_LASTNAME ASC, C_EMAILADDRESS ASC");
			if($limit)
				$sql->setLimit($offset, $limit);

			return Wdb::getData($sql);
		}

		public function getNodeContacts($node, $offset=0, $limit=false) {
			$sql = new CSelectSqlQuery("CONTACT");
			$sql->addConditions("CF_ID='$node'");
			$sql->addConditions("C_EMAILADDRESS<>''");
			$sql->setGroupBy("C_FIRSTNAME, C_LASTNAME, C_EMAILADDRESS");
			$sql->setCustomOrderBy("C_FIRSTNAME ASC, C_LASTNAME ASC, C_EMAILADDRESS ASC");
			if($limit)
				$sql->setLimit($offset, $limit);
			return Wdb::getData($sql);
		}

		protected function getLoadNodesOrderBySql() {
			return "DF.CF_NAME ASC";
		}

	}
?>