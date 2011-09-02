<?php
class AbstractModule extends Module {
	
	var $SingleInstallation = true;
		
	function getInterfacesParams(){
		
		if(!file_exists(DIR_MODULES.'/'.$this->ModuleDir.'/_methods'))return $this->Interfaces;
		
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
		
		$params = parent::getInterfaceParams($_int);
		if(!is_null($params))return $params;
		
		$xnInterfaces = new xmlNodeX();
		$xnInterfaces->renderTreeFromFile(DIR_MODULES.'/'.$this->ModuleDir.'/_methods/'.$_int.'.xml');
		$r_xnInterface = $xnInterfaces->getChildrenByName('interface');
		for($j=0, $cnt = count($r_xnInterface);$j<$cnt;$j++){
			
			$xnInterface = &$r_xnInterface[$j];
			/*@var $xnInterface xmlNodeX*/
			$xnName = &$xnInterface->getFirstChildByName('name');
			$xnKey = &$xnInterface->getFirstChildByName('key');
			$xnType = &$xnInterface->getFirstChildByName('type');
			if($xnKey->getData()==$_int){

				$this->__registerInterface($xnKey->getData(), $xnName->getData(), !is_null($xnType)?constant($xnType->getData()):'');
				return parent::getInterfaceParams($_int);
			}
		}
		
		
		return null;
	}
}
?>