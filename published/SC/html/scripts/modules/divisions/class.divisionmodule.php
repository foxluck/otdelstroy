<?php
class Division{
	
	var $Name = '';
	var $Key = '';
	var $UnicKey = '';
	var $ID = 0;
	var $ParentID = 0;
	var $Enabled = 0;
	var $Priority = 0;
	var $BranchNum = 0;
	var $Template = '';
	var $MainTemplate = '';
	var $LinkDivisionUKey = '';
	var $CustomSettings = array();
	
	function Division($_ID = 0){
		
		$this->load($_ID);
	}
	
	function setName($_Name){
		
		$this->Name = $_Name;
	}
	
	function setKey($_Key){
		
		$this->Key = $_Key;
	}
	
	function setUnicKey($_UnicKey){
		
		$this->UnicKey = $_UnicKey;
	}
	
	function setParentID($_ParentID){
		
		$this->ParentID = $_ParentID;
	}
	
	function setEnabled($_Enabled){
		
		$this->Enabled = $_Enabled;
	}
	
	function setPriority($_Priority){
		
		$this->Priority = $_Priority;
	}

	function setTemplate($_Template){

		$this->Template = $_Template;
	}

	function setLinkDivisionUKey($_LinkUKey){
		
		$this->LinkDivisionUKey = $_LinkUKey;
	}
	
	function setCustomSetting($_Key, $_Value){
		
		$this->CustomSettings[$_Key]['xValue'] = $_Value;
	}
	
	function getCustomSetting($_Key){
		
		if(isset($this->CustomSettings[$_Key]))return $this->CustomSettings[$_Key]['xValue'];
		return null;
	}
	
	function getChildDivisionsNumber(){
		
		$sql = '
			SELECT COUNT(*) FROM ?#DIVISIONS_TBL WHERE xParentID=?
		';
		list($this->BranchNum) = db_fetch_row(db_phquery($sql, $this->ID));
		return $this->BranchNum;
	}
	
	function save(){
		
		if($this->ID){
			
			$sql = '
				UPDATE ?#DIVISIONS_TBL
				SET xName=?,xKey=?,xUnicKey=?,xParentID=?,xEnabled=?,xPriority=?, xTemplate=?, xLinkDivisionUKey=?
				WHERE xID=?
			';
			$sql = sql_placeholder($sql, $this->Name, $this->Key, $this->UnicKey, $this->ParentID, $this->Enabled, $this->Priority, $this->Template, $this->LinkDivisionUKey, $this->ID);
		}else {
			
			$sql = '
				INSERT INTO ?#DIVISIONS_TBL
				(xName, xKey, xUnicKey, xParentID, xEnabled, xPriority)
				VALUES(?,?,?,?,?,?)
			';
			$sql = sql_placeholder($sql, $this->Name, $this->Key, $this->UnicKey, $this->ParentID, $this->Enabled, $this->Priority);
		}
		
		db_query($sql);
		if(!$this->ID)$this->ID = db_insert_id();
		
		foreach ($this->CustomSettings as $_CustSetting){
			
			$sql = '
				UPDATE ?#DIVISION_CSETTINGS_TBL SET xValue=? WHERE xDivisionID=? AND xSettingID=?
			';
			db_phquery($sql, $_CustSetting['xValue'], $this->ID, isset($_CustSetting['xSettingID'])?$_CustSetting['xSettingID']:'');
		}
		
		$this->flushCache();
	}
	
	function load($_ID = 0){
		
		if(!$_ID)return false;
		$cache = Cache::getInstance(__CLASS__.'.'.$_ID);
		if(!$Row = $cache->get('row')){
			$sql = 'SELECT * FROM ?#DIVISIONS_TBL WHERE xID=?';
			$Row = db_fetch_assoc(db_phquery($sql, $_ID));
			$cache->set('row',$Row);
		}
		if(!isset($Row['xID']))return false;
		$this->ID = $Row['xID'];
		$this->Name = $Row['xName'];
		$this->Key = $Row['xKey'];
		$this->UnicKey = $Row['xUnicKey'];
		$this->ParentID = $Row['xParentID'];
		$this->Enabled = $Row['xEnabled'];
		$this->Priority = $Row['xPriority'];
		$this->Template = $Row['xTemplate'];
		$this->LinkDivisionUKey = $Row['xLinkDivisionUKey'];
		
		if($this->Template === ''){
			
			$ParentDiv = DivisionModule::getDivision($this->ParentID);
			$this->MainTemplate = $ParentDiv->MainTemplate;
		}else {
			
			$this->MainTemplate = $this->Template;
		}
		
		return true;
	}

	function loadCustomSettings(){
		
		$this->CustomSettings = array();
		$sql = '
			SELECT xSettingID, xName, xKey, xValue FROM ?#DIVISION_CSETTINGS_TBL WHERE xDivisionID=?
		';
		$Result = db_phquery($sql, $this->ID);
		while ($_Row = db_fetch_row($Result)) {
			
			$this->CustomSettings[$_Row['xKey']] = array(
				'xSettingID' => $_Row['xSettingID'],
				'xName' 	=> $_Row['xName'],
				'xKey' 	=> $_Row['xKey'],
				'xValue' 	=> $_Row['xValue'],
				);
		}
	}
	
	function delete(){
		
		$sql = '
			DELETE FROM ?#DIVISIONS_TBL WHERE xID=?
		';
		db_phquery($sql, $this->ID);
		
		$this->flushCache();
	}
	
	function getName(){
		
		return $this->Name;
	}
	
	function getID(){
		
		return $this->ID;
	}
	
	function getInterface(){
		
		return $this->Interface;
	}

	function getLevel(){
		
		$_ID = $this->ParentID;
		$Level = 0;
		do{
			
			$Level++;
			$_Div = DivisionModule::getDivision($_ID);
			$_ID = $_Div->ID;
		}while ($_Div->ID);
		return $Level;
	}
	
	function getUnicKey(){
		return $this->UnicKey;
	}
	
	function isBranchOf($_tID){
		
		$_ID = $this->ParentID;
		do{
			
			if($_tID == $_ID)return true;
			$_Div = DivisionModule::getDivision($_ID);
			$_ID = $_Div->ParentID;
		}while ($_Div->ID);
		return false;
	}

	function isChild($child_division_id){
		
		$Division = DivisionModule::getDivision($child_division_id);
		return $Division->isBranchOf($this->getID());
	}
	
	function getBreadsToID($_tID){
		
		$Breads = array();
		$_ID = $this->ParentID;
		$limit = 100;
		do{
			
			$_Div = DivisionModule::getDivision($_ID);
			if(!isset($_Div))break;
			$Breads[] = $_Div;
			if($_tID == $_ID)break;
			$_ID = $_Div->ParentID;
			if ($limit--<0) {
				break;
			}
		}while ($_Div->ID && $_ID);
		if($_tID != $_ID)return null;
		else return array_reverse($Breads);
	}

	function addInterface($_Interface, $_Priority = 0){
		
		if($this->ID){
			
			$sql = '
				INSERT ?#DIVISION_INTERFACE_TBL (xDivisionID, xInterface, xPriority) VALUES(?,?,?)
			';
			db_phquery($sql, $this->ID, $_Interface, $_Priority);
		}else {
			print 'Warning: Division didn`t safe!';
		}
		
		$this->flushCache();
	}
	
	function updateInterfacePriority($_Interface, $_Priority){
		
		$sql = '
			UPDATE ?#DIVISION_INTERFACE_TBL SET xPriority=?
			WHERE xDivisionID=? AND xInterface=?
		';
		db_phquery($sql, $_Priority, $this->ID, $_Interface);
		$this->flushCache();
	}
	
	function updateInterface($_Interface, $_Params){
		
		$sql = '
			UPDATE ?#DIVISION_INTERFACE_TBL SET ?%
			WHERE xDivisionID=? AND xInterface=?
		';
//		print sql_placeholder_ex($sql, $_Params, $this->ID, $_Interface);die;
		db_phquery($sql, $_Params, $this->ID, $_Interface);
		
		$this->flushCache();
	}
	
	function flushCache()
	{
		Cache::resetList(array(__CLASS__.'.'.$this->ID,__CLASS__.'.'.'branch_divs'));
	}
	
	function deleteInterface($_Interface){
		
		$sql = '
			DELETE FROM ?#DIVISION_INTERFACE_TBL WHERE xDivisionID=? AND xInterface=?
		';
		db_phquery($sql, $this->ID, $_Interface);
		$this->flushCache();
	}
	
	function getInterfaces($_Params = array()){
		$cache = Cache::getInstance(__CLASS__.'.'.$this->ID);
		$key = isset($_Params['xInheritable'])?'inheritable':'primary';
		$Interfaces = $cache->get($key);
		if(!is_array($Interfaces)){
			$Interfaces = array();
			
			$sql = '
				SELECT xInterface,xInheritable,xPriority FROM ?#DIVISION_INTERFACE_TBL WHERE xDivisionID=?
				'.(isset($_Params['xInheritable'])?' AND xInheritable='.xEscapeSQLstring($_Params['xInheritable']):'').'
				ORDER BY xPriority DESC, xInterface ASC
			';
			$Result = db_phquery($sql, $this->ID);
			while ($_Row = db_fetch_row($Result)) {
				
				if(preg_match('/^([0-9]+)\_(.+)/', $_Row['xInterface'], $SubPatt)){
					
					$_Row['ModConfigID'] = $SubPatt[1];
					$_Row['key'] = $SubPatt[2];
					$_Row['name'] = $SubPatt[2];
					
					$Interfaces[] = $_Row;
				}
			}
			$cache->set($key,$Interfaces);
		}
		return $Interfaces;
	}

	function getInheritableInterfaces(){
		
		$Interfaces = array();
		$BreadDivisions = $this->getBreadsToID(0);		
		$TC = count($BreadDivisions);
		for ($j=0;$j<$TC;$j++){
			
			$_tInts = $BreadDivisions[$j]->getInterfaces(array('xInheritable'=>1));
			if(count($_tInts))$Interfaces = array_merge($Interfaces, $_tInts);
		}
		return $Interfaces;
	}
	
	function addCustomSetting($_Name, $_Key){
		
		$sql = '
			INSERT ?#DIVISION_CSETTINGS_TBL (xDivisionID, xName, xKey) VALUES(?,?,?)
		';
		db_phquery($sql, $this->ID, $_Name, $_Key);
	}
}

class DivisionModule{

	function getDivisionIDsWithInterface($_Interface){
		
		$dbHandler = Core::getdbHandler();
		$sql = '
			SELECT xDivisionID FROM ?#DIVISION_INTERFACE_TBL WHERE xInterface = ?
		';
		$Result = $dbHandler->ph_query($sql,$_Interface);
		$DivIDs = array();
		while ($_Row = $Result->fetchAssoc()){
			
			$DivIDs[] = $_Row['xDivisionID'];
		}
		return $DivIDs;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param int $_ID
	 * @return Division
	 */
	static function &getDivision($_ID)
	{
		$Div = new Division($_ID);
		return $Div;
	}
	
	/**
	 * @param string $_UnicKey
	 * @return Division
	 */
	static function &getDivisionByUnicKey($_UnicKey)
	{
		return self::getDivision(DivisionModule::getDivisionIDByUnicKey($_UnicKey));
		$Div = new Division(DivisionModule::getDivisionIDByUnicKey($_UnicKey));
		return $Div;
	}
	
	static function getDivisionIDByUnicKey($_UnicKey){
		
		/**
		 * CCACHE
		 */
		static $divisions_ids = array();
		if(isset($divisions_ids[$_UnicKey])){
			return $divisions_ids[$_UnicKey];
		}
		
		$sql = 'SELECT xID FROM ?#DIVISIONS_TBL WHERE xUnicKey=?';
		$divisions_ids[$_UnicKey] = db_phquery_fetch(DBRFETCH_FIRST, $sql, $_UnicKey);
		return $divisions_ids[$_UnicKey];
	}
	
	static function getDivisionIDByName($_Name){
		static $divisions_ids;
		if(isset($divisions_ids[$_Name])){
			return $divisions_ids[$_Name];
		}
		
		$sql = '
			SELECT xID FROM ?#DIVISIONS_TBL WHERE xName=?
		';
		$divisions_ids[$_Name] = db_phquery_fetch(DBRFETCH_FIRST, $sql, $_Name);
		return $divisions_ids[$_Name];
	}
	
	static function &getChildDivisions($_ID, $_Params = null){

		$t = &DivisionModule::getBranchDivisions($_ID, $_Params);
		return $t; 
	}
	
	static function &getBranchDivisions($_ID, $_Params = null)
	{
		
		/**
		 * CCACHE
		 */
		$cache_key = $_ID.(isset($_Params['xEnabled'])&&$_Params['xEnabled']?'::1':'::0');
		$cache = Cache::getInstance(__CLASS__.'.'.'branch_divs');
		$branch_divs = $cache->get($cache_key);
		if(!is_array($branch_divs)){
			$branch_divs = array();
			$sql = '
				SELECT * FROM ?#DIVISIONS_TBL WHERE xParentID=? '.(isset($_Params['xEnabled'])&&$_Params['xEnabled']?' AND xEnabled=1':'').'
				ORDER BY xPriority DESC, xName ASC
			';
	
			$Result = db_phquery($sql, $_ID?$_ID:0);
			$_Ind = 0;
			while ($_Row = db_fetch_row($Result)){
				
				$branch_divs[$_Ind] = new Division();
				$branch_divs[$_Ind]->ID = $_Row['xID'];
				$branch_divs[$_Ind]->Name = $_Row['xName'];
				$branch_divs[$_Ind]->Key = $_Row['xKey'];
				$branch_divs[$_Ind]->UnicKey = $_Row['xUnicKey'];
				$branch_divs[$_Ind]->ParentID = $_Row['xParentID'];
				$branch_divs[$_Ind]->Enabled = $_Row['xEnabled'];
				$branch_divs[$_Ind]->Priority = $_Row['xPriority'];
				$branch_divs[$_Ind]->LinkDivisionUKey = $_Row['xLinkDivisionUKey'];
				$_Ind++;
			}
			
			$cache->set($cache_key, $branch_divs);
		}
		return $branch_divs;
	}
	
	function addDivision($_Division){
		
	}
	
	function ActionsHandler(){
		
		$Action = isset($_POST['fACTION'])?$_POST['fACTION']:'';
		$Action = $Action?$Action:(isset($_GET['fACTION'])?$_GET['fACTION']:'');
		
		switch ($Action){
			case 'DIVISION_UP':
				if(!isset($_GET['divisionID']))RedirectSQ('fACTION=&divisionID=');
				$Division = new Division($_GET['divisionID']);
				$Brothers = DivisionModule::getChildDivisions($Division->ParentID);
				$TC = count($Brothers);
				for ($j=0;$j<$TC;$j++){
					
					if($Brothers[$j]->getID()==$Division->getID()){
						
						$Brothers[$j]->setPriority(($TC-$j+1)*2+1);
					}else {
						
						$Brothers[$j]->setPriority(($TC-$j)*2);
					}
					$Brothers[$j]->save();
				}
				RedirectSQ('fACTION=&divisionID=');
				break;
			case 'DIVISION_DOWN':
				if(!isset($_GET['divisionID']))RedirectSQ('fACTION=&divisionID=');
				$Division = new Division($_GET['divisionID']);
				$Brothers = DivisionModule::getChildDivisions($Division->ParentID);
				$TC = count($Brothers);
				for ($j=0;$j<$TC;$j++){
					
					if($Brothers[$j]->getID()==$Division->getID()){
						
						$Brothers[$j]->setPriority(($TC-$j-1)*2-1);
					}else {
						
						$Brothers[$j]->setPriority(($TC-$j)*2);
					}
					$Brothers[$j]->save();
				}
				RedirectSQ('fACTION=&divisionID=');
				break;
			case 'NEWDIV':
				$newDiv = new Division();
				$newDiv->setName($_POST['fDATA']['xName']);
				$newDiv->setParentID($_POST['fDATA']['xParentID']);
				$newDiv->save();
				RedirectSQ('?ukey=div_tree');
				break;
			case 'SAVEDIV':
				$newDiv = new Division($_POST['fDATA']['xID']);
				$newDiv->setParentID($_POST['fDATA']['xParentID']);
				$newDiv->setPriority($_POST['fDATA']['xPriority']);
				$newDiv->setTemplate($_POST['fDATA']['xTemplate']);
				$newDiv->setName($_POST['fDATA']['xName']);
				$newDiv->setKey($_POST['fDATA']['xKey']);
				$newDiv->setUnicKey($_POST['fDATA']['xUnicKey']);
				$newDiv->setLinkDivisionUKey($_POST['fDATA']['xLinkDivisionUKey']);
				$newDiv->setEnabled(isset($_POST['fDATA']['xEnabled']));
				if(isset($_POST['fDATA']['CustomSettings'])){
					
					foreach ($_POST['fDATA']['CustomSettings'] as $_Key=>$_Value){
						
						$newDiv->setCustomSetting($_Key, $_Value);
					}
				}
				$newDiv->save();
				if(isset($_POST['fDATA']['newInterface'])&&$_POST['fDATA']['newInterface']){
					
					$newDiv->addInterface($_POST['fDATA']['newInterface'], 0);
				}
				
				if(isset($_POST['fDATA']['IntsDATA'])){
					
					foreach ($_POST['fDATA']['IntsDATA'] as $_Interface=>$_Data){
						
						$newDiv->updateInterface($_Interface, array('xPriority'=>$_Data['Priority'],'xInheritable'=>isset($_Data['Inheritable'])));
					}
				}
				
				if($_POST['fDATA']['newSetting']['xName']&&$_POST['fDATA']['newSetting']['xKey']){
					
					$newDiv->addCustomSetting($_POST['fDATA']['newSetting']['xName'],$_POST['fDATA']['newSetting']['xKey']);
				}
				
				Redirect(set_query('sub='));
				break;
			case 'DELETEDIV':
				if(isset($_GET['delete_did'])){
					
					$newDiv = new Division($_GET['delete_did']);
					$newDiv->delete();
				}
				Redirect(set_query('fACTION=&delete_did='));
				break;
			case 'DELETE_INTERFACE':
				if(isset($_GET['delete_int'])){
					
					$newDiv = new Division($_GET['edid']);
					$newDiv->deleteInterface(urldecode($_GET['delete_int']));
				}
				Redirect(set_query('fACTION=&delete_int='));
				break;
		}
	}

	/**
	 * disconect interfaces from divisions
	 *
	 * @param array $_Interfaces  = array(&lt;ModuleConfigID&gt;=&gt;array(&lt;InterfaceKey&gt;))
	 */
	function disconnectInterfaces($_Interfaces){
		
		$tInts = array();
		foreach ($_Interfaces as $_ModuleConfigID=>$_Ints){
			if(!is_array($_Ints)){
				$_Ints = array($_Ints);
			}
			foreach($_Ints as $_Int){
				$tInts[] = $_ModuleConfigID.'_'.$_Int;
			}
		}
		
		$sql = '
			DELETE FROM ?#DIVISION_INTERFACE_TBL WHERE xInterface IN (?@)
		';
		if(count($tInts)){
			$dbHandler = Core::getdbHandler();
			$dbHandler->ph_query($sql, $tInts);
		}
	}
}
?>