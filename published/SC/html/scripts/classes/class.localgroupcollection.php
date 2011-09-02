<?php
	class LocalGroupCollection extends Collection {
		
		function load(){
			
			$xnLocalGroups = new xmlNodeX();
			$xnLocalGroups->renderTreeFromFile(DIR_CFG.'/localgroup.xml');
			$r_xnLocalGroup = $xnLocalGroups->getChildrenByName('LocalGroup');
			foreach ($r_xnLocalGroup as $xnLocalGroup){
				
				
			}
		}
	}
?>