<?php
	// Base class for any webasyst-folder (group, ablum)
	class WbsFolder {
		public $Id;
		public $Name;
		public $Rights;
		protected $isRoot;
		
		protected $dataModel;
		protected $status;
		
		public function __construct($dataModel, $row = null) {
			$this->dataModel = $dataModel;
			if ($row)
				$this->loadRow($row);
			$this->isRoot = false;
		}
		
		public function loadRow($row) {
			$this->Id = $row[$this->tree->prefix . "ID"];
			$this->Name = $row[$this->tree->prefix . "NAME"];
			$this->Status = $row[$this->tree->prefix . "STATUS"];
			$this->Rights = $row["USER_RIGHTS"];
		}
		
		public function getRightsStr($short = false) {
			return RightsMask::getRightsStr($this->Rights, $short);
		}
		
		public function asArray () {
			$row = array (
				"ID" => $this->Id,
				"NAME" => $this->Name,
				"STATUS" => $this->Status,
				"RIGHTS" => $this->Rights
			);
			return $row;
		}
		
		public function getRights() {
			return $this->Rights;
		}
		
		public function canRead() {
			return RightsMask::hasReadRights($this->Rights);
		}
		
		public function canWrite() {
			return RightsMask::hasWriteRights($this->Rights);
		}
		
		public function isFullRights() {
			return RightsMask::hasFolderRights($this->Rights);
		}
		
		public function update($data, $fields = null) {
			$this->tree->updateNode($this->Id, $data, $fields);
			$this->reload();
		}
	}
?>