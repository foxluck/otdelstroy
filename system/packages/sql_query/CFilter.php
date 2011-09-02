<?
	class CFilter {
		
		var $conditionsList;
		var $selectFieldsStr;
	
		function __construct ($conditionsList = array()) {
			$this->conditionsList= $conditionsList;
		}
		
		function AddConditions ($field, $value = false) {
			if ($value === false) 
				$this->conditionsList[] = $field;		
			else
				$this->conditionsList[] = $field . "='" . $value . "'";
		}
		
		function GetConditions () {
			return $this->conditionsList;
		}
		
		function SetSelectFields ($selectFields) {
			if (is_array($selectFields))
				$this->selectFieldsStr = join(",", $selectFields);
			else
				$this->selectFieldsStr = $selectFields;
		}
		
	}
?>