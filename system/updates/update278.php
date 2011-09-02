<?php
	
    $res = mysql_query("SHOW TABLES");
    $tables = array();
    while ($row = mysql_fetch_array($res)) {
        $tables[] = $row[0];
    }
    
    if (!in_array("USER_SETTINGS", $tables)) {
        $sql = "CREATE TABLE `USER_SETTINGS` (
                 `U_ID` varchar(20) NOT NULL,
                 `APP_ID` char(2) NOT NULL,
                 `NAME` varchar(255) NOT NULL,
                 `VALUE` text NOT NULL,
                 PRIMARY KEY  (`U_ID`,`APP_ID`,`NAME`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        
        @mysql_query($sql);
    }
    
    $users = array();
    $res = mysql_query("SELECT * FROM WBS_USER");
    while ($row = mysql_fetch_array($res)) {
        $users[] = $row;
    }
    
    if (is_array($users)) {    
        foreach ($users as $u) {
            $settings = @simplexml_load_string($u['U_SETTINGS']);
            if ($settings) {
                foreach ($settings->attributes() as $name => $value) {
                    $sql = "REPLACE USER_SETTINGS SET U_ID = '%s', APP_ID = '%s', NAME = '%s', VALUE = '%s'";
                    $sql = sprintf($sql, $u['U_ID'], "", $name, $value);
                    @mysql_query($sql);
                }

		$apps = Wbs::getDbkeyObj()->getApplicationsList();
            
                foreach ($apps as $appId) {
                    // Because dd settings is bad
                    if ($appId != 'DD' && isset($settings->$appId)) {
                        foreach ($settings->$appId->attributes() as $name => $value) {
                            $sql = "REPLACE USER_SETTINGS SET U_ID = '%s', APP_ID = '%s', NAME = '%s', VALUE = '%s'";
                            $sql = sprintf($sql, $u['U_ID'], $appId, $name, $value);
                            @mysql_query($sql);
                            
                        }
                        foreach ($settings->$appId->children() as $child) {
                            foreach ($child->attributes() as $name => $value) {
                                $sql = "REPLACE USER_SETTINGS SET U_ID = '%s', APP_ID = '%s', NAME = '%s', VALUE = '%s'";
                                $sql = sprintf($sql, $u['U_ID'], $appId, $name, $value);
                                @mysql_query($sql);
                            }                            
                            if ($child->getName() == "FOLDERSVIEW") {
                                foreach ($child as $folder) {
                                    $folderId = $folder['ID'];
                                    $viewMode = $folder['VIEWMODE'];
                                    if ($folderId) {
                                        $sql = "REPLACE INTO USER_SETTINGS SET U_ID = '%s', APP_ID = '%s', NAME = '%s', VALUE = '%s'";
                                        $sql = sprintf($sql, $u['U_ID'], $appId, "FOLDERVIEW_".$folderId, $viewMode);
                                        @mysql_query($sql);
                                    }
                                }
                            } 
                        }
                    }
                    // Remove old settings of DD, because it's bad
                    elseif ($appId == 'DD') {
                        unset($settings->DD);
                        $sql = "UPDATE WBS_USER SET U_SETTINGS = '%s' WHERE U_ID = '%s'";
                        $sql = sprintf($sql, $settings->asXML(), $u['U_ID']);
                        @mysql_query($sql);
                    }
                }
            }
        }
    }
?>