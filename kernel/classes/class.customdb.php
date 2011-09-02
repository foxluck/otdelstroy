<?php

class customdb {
    
    public static function getDBsettings($CONF, $DB) {
        //$this->conf = 'COMMONLOGBASE';
        $config = WBS_DIR . '/kernel/wbs.xml';
        if (file_exists($config))
            $sxml = simplexml_load_file($config);
        else return 'ERROR open config';
        
	$dsn = array(
		'phptype' => 'mysql',
		'username' => (string)$sxml->$CONF->ADMIN_USERNAME,
		'password' => (string)$sxml->$CONF->ADMIN_PASSWORD,
		'hostspec' => (string)$sxml->$CONF->HOST,
		'port'     => (string)$sxml->$CONF->PORT,
		'database' => (string)$sxml->$CONF->$DB,
	);
        return $dsn;
    }
}

?>