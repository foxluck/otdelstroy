<?php
class Core{
	
	static function &getSmarty(){
		
		global $smarty;
		return $smarty;
	}
	
	static function &getCurrentDivision(){
		
		global $CurrDivision;
		return $CurrDivision;
	}
	
	static function &getdbHandler(){
		
		global $DB_tree;
		return $DB_tree;
	}
	
	static function getSetupValue($_Key){
		
		global $_GLOBAL_SETUPS;
		if (isset($_GLOBAL_SETUPS[$_Key])) {
			
			return $_GLOBAL_SETUPS[$_Key];
		}else return null;
	}

	static function &getConnectedModules(){
		
		global $ConnectedModules;
		return $ConnectedModules;
	}
}
?>