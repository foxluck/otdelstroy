<?php
	class WbsRecord {
		public function asArray () {
			$fields = $this->getOutputFields();
			$result = array();
			foreach ($fields as $cField) {
				$result[$cField] = $this->$cField;
			}
			return $result;
		}
		
		protected function getOutputFields() {
			throw new RuntimeException ("Not implement output fields for Record class");
		}
		
		protected function loadRow($row) {
			$fields = $this->getOutputFields();
			foreach ($fields as $cField) {
				if (isset($row[$cField]))
					$this->$cField = $row[$cField];
			}
		}
	}
?>