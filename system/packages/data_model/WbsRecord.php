<?php
	class WbsRecord {
		protected $dataModel;
		public $Id;
		public $FolderId;
		
		public function __construct($dataModel) {
			$this->dataModel = $dataModel;
		}
		
		public function getId() {
			return $this->Id;
		}
		
		public function asArray () {
			$fields = $this->getOutputFields();
			$result = array();
			foreach ($fields as $cField) {
				$result[$cField] = $this->$cField;
			}
			return $result;
		}
		
		protected function getOutputFields() {
			return $this->getFields();
		}
		
		protected function loadRow($row) {
			$fields = $this->getFields();
			foreach ($fields as $cField) {
				if (isset($row[$cField]))
					$this->$cField = $row[$cField];
				else 
					$this->$cField = "";
			}
			$this->Id = $row[$this->getIdField()];
			$this->FolderId = $row[$this->getFolderField()];
		}
		
		public function getFolder() {
			$folder = $this->dataModel->getFolder($this->FolderId);
			return $folder;
		}
		
		public function getRights() {
			return $this->getFolder()->getRights();
		}
		
		public function canRead() {
			return $this->getFolder()->canRead();
		}
		
		public function canWrite() {
			return $this->getFolder()->canWrite();
		}
	}
?>