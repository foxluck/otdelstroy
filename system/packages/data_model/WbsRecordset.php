<?php
	class WbsRecordset {
		
		protected $records;
		protected $totalCount;
		protected $dataModel;
		
		public function __construct($dataModel) {
			$this->dataModel = $dataModel;
			
			$this->records = array();
			$this->totalCount = null;
		}
		
		public function getIds() {
			return array_keys($this->records);
		}
		
		public function isEmpty() {
			return ($this->getCount() == 0);
		}
		
		public function setTotalCount ($totalCount) {
			$this->totalCount = $totalCount;
		}
		
		public function getTotalCount () {
			return $this->totalCount;
		}
		
		public function getCount() {
			return sizeof($this->records);
		}
		
		public function add($record) {
			$this->records[$record->getId()] = $record;
		}
		
		public function getRecords() {
			return $this->records;
		}
		
		public function getRecordById($id) {
			return $this->records[$id];			
		}
		
		public function loadFromData($data) {
			foreach ($data as $cRow) {
				$record = $this->dataModel->createRecord($cRow);
				$this->add($record);
			}
		}
		
		public function asArray () {
			$result = array ();
			foreach ($this->records as $cRecord)
				$result[] = $cRecord->asArray ();
			return $result;			
		}
		
	}
	
?>