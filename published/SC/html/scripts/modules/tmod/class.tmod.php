<?php
class tmod extends Module {
	
	function getInterface($_Interface){
		
		include(DIR_MODULES.'/'.$this->ModuleDir.'/_methods/'.$_Interface.'.php');
	}
	
	function getInterfacesParams(){
		
		$dh = opendir(DIR_MODULES.'/'.$this->ModuleDir.'/_methods');
		while ($file = readdir($dh)){
			
			if(!preg_match('|\.xml$|i',$file))continue;
			$xmlInterfaces = new xmlNodeX();
			$xmlInterfaces->renderTreeFromInner(file_get_contents(DIR_MODULES.'/'.$this->ModuleDir.'/_methods/'.$file));
			$xmlInterfaces = $xmlInterfaces->xPath('/interfaces/interface');
			$TC = count($xmlInterfaces);
			for($j=0;$j<$TC;$j++){
				
				list($xmlName) = $xmlInterfaces[$j]->xPath('/interface/name');
				list($xmlKey) = $xmlInterfaces[$j]->xPath('/interface/key');
				$this->Interfaces[$xmlKey->getData()] = array(
					'key' => $xmlKey->getData(),
					'name' => $xmlName->getData(),
				);
			}
		}
		return $this->Interfaces;
	}
	
	function getInterfaceParams($_int){
		
		$xmlInterfaces = new xmlNodeX();
		$xmlInterfaces->renderTreeFromInner(file_get_contents(DIR_MODULES.'/'.$this->ModuleDir.'/_methods/'.$_int.'.xml'));
		$xmlInterfaces = $xmlInterfaces->xPath('/interfaces/interface');
		$TC = count($xmlInterfaces);
		for($j=0;$j<$TC;$j++){
			
			list($xmlName) = $xmlInterfaces[$j]->xPath('/interface/name');
			list($xmlKey) = $xmlInterfaces[$j]->xPath('/interface/key');
			if($xmlKey->getData()==$_int){
				return  array(
					'key' => $xmlKey->getData(),
					'name' => $xmlName->getData(),
				);
			}
		}
		return null;
	}
}
?>