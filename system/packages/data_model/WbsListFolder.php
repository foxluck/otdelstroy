<?php
	// Base class for list folder
	class WbsListFolder extends WbsFolder {
		/**
		 * @var WbsFoldersList
		 */
		protected $list;
		
		public function __construct($dataModel, $row = null) {
			$this->list = $dataModel->getFoldersList();
			parent::__construct($dataModel, $row);
		}		

		public function reload() {
			$row = $this->list->getNodeRow($this->Id);
			$this->loadRow($row);
		}

		public function isAvailable() {
			return $this->Rights > 0;
		}
		
	}
	
?>