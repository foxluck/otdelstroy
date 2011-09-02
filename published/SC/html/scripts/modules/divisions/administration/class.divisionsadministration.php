<?php
class DivisionsAdministration extends Module {
	
	function initInterfaces(){
		
		$this->Interfaces = array(
			'divisions' => array(
				'name' 	=> 'Настройки разделов',
				'type' => INTDIVAVAILABLE,
				),
			'bdivisionstree' => array(
				'name' 	=> 'Дерево разделов (администрирование)',
				'type' => INTDIVAVAILABLE,
				),
			'baddinterface' => array(
				'name' 	=> 'Добавить интерфейс',
				'type' => INTDIVAVAILABLE,
				),
		);
	}
	
	function _getAllSubDivisions($_ID, $_Level = 0){
		
		$_Level++;
		$Subs = array();
		$Divs = DivisionModule::getChildDivisions($_ID);
		$_TC = count($Divs);
		for ($y=0;$y<$_TC;$y++){
			
			$Subs[] = array(
				'name' => translate($Divs[$y]->Name),
				'id' => $Divs[$y]->ID,
				'level' => $_Level,
				'enabled' => $Divs[$y]->Enabled?'a_':'',
			);
			$Subs = array_merge($Subs, $this->_getAllSubDivisions($Divs[$y]->ID, $_Level));
		}
		return $Subs;
	}
	
	function _buildTree($_Elems, $_Converting, $_Settings){
	
		$out = '';
		$i = 0;
		$LevelItemsCounter = array();
		$CurrLevel = 1;
		$c = count($_Elems);
		for(; $i<$c; $i++){
			
			if(($i>0 && $_Elems[$i][$_Converting['Level']] > $_Elems[$i-1][$_Converting['Level']]) || !isset($LevelItemsCounter[1])){
				
				$CurrLevel = $_Elems[$i][$_Converting['Level']];
				$LevelItemsCounter[$CurrLevel] = 0;
				for($_i=$i; $_i<$c; $_i++){
					
					if($_Elems[$_i][$_Converting['Level']]<$CurrLevel)break;
					if($_Elems[$_i][$_Converting['Level']]==$CurrLevel)$LevelItemsCounter[$CurrLevel]++;
				}
			}
			
			$LevelItemsCounter[$_Elems[$i][$_Converting['Level']]]--;
			
			$ImgName = 'c';
			if(($i+1)<$c && $_Elems[$i+1][$_Converting['Level']] >= $_Elems[$i][$_Converting['Level']] && $LevelItemsCounter[$_Elems[$i][$_Converting['Level']]])
				$ImgName = 'v';
				
			$__Inj = '';
			for($__t=1; $__t<$_Elems[$i][$_Converting['Level']];$__t++){
				
				if($LevelItemsCounter[$__t])
					$__Inj .= '<div style="float:left"><img alt="" hspace="0" src="images_common/g.gif" /></div>';
				else
					$__Inj .= '<div style="float:left"><img alt="" hspace="0" src="images_common/g.gif" style="visibility:hidden;" /></div>';
			}
			
			$out .= '
				<div style="font-size:13px; height:20px">
				'.$__Inj.'<div style="float:left"><img hspace="0" src="images_common/'.$ImgName.'.gif" /></div><div style="float:left">'.str_replace(array('%NAME%','%ID%','%ENABLED%'), array($_Elems[$i][$_Converting['Name']], $_Elems[$i][$_Converting['ID']], $_Elems[$i][$_Converting['Enabled']]), $_Settings['Name']).'</div>&nbsp;
				</div>';
		}
		return $out;
	}
}	
?>