<?php
//security fix
$sql = "SELECT `xID` FROM `SC_divisions` WHERE `xUnicKey`='bsettings' LIMIT 1";
if($res = mysql_query($sql)){
	if($id = mysql_fetch_array($res)){
		if($id = $id['xID']){
			$sql = "UPDATE `SC_divisions` SET `xParentID`={$id}  WHERE `xUnicKey`='configuration'";
			mysql_query($sql);
		}
	}
}
?>