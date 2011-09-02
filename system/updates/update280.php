<?php

if($r = @mysql_query("SELECT * FROM MMMESSAGE WHERE 0 LIMIT 1")) {

	if($res = @mysql_query('SELECT MMM_MSGSTATUS FROM MMMESSAGE WHERE 0'))
		@mysql_query('ALTER TABLE MMMESSAGE DROP MMM_STATUS');

	$queries = array(
		'ALTER TABLE MMMESSAGE DROP MMM_MSGID;',
		'ALTER TABLE MMMESSAGE DROP MMM_REPLYTO;',
		'ALTER TABLE MMMESSAGE DROP MMM_RETURNPATH;',
		'ALTER TABLE MMMESSAGE DROP MMM_CONTENT_TEXT;',
		'ALTER TABLE MMMESSAGE DROP MMM_MSGTYPE;',
		'ALTER TABLE MMMESSAGE DROP MMM_ENCODING;',
		'ALTER TABLE MMMESSAGE DROP MMM_SENDUSERNAME;',
		'ALTER TABLE MMMESSAGE DROP MMM_MODIFYUSERNAME;',
		"ALTER TABLE MMMESSAGE CHANGE MMM_MSGSTATUS MMM_STATUS INT(11) NOT NULL DEFAULT '0';",
		'ALTER TABLE MMMESSAGE CHANGE MMM_CREATEUSERNAME MMM_USERID varchar(20) NULL DEFAULT NULL;',

		'ALTER TABLE MMMESSAGE ADD MMM_DATETIME DATETIME DEFAULT NULL AFTER MMM_STATUS',
		"UPDATE MMMESSAGE SET MMM_DATETIME = GREATEST(IFNULL(MMM_CREATEDATETIME, ''), IFNULL(MMM_MODIFYDATETIME, ''), IFNULL(MMM_SENDDATETIME, ''))",
		'ALTER TABLE MMMESSAGE DROP MMM_CREATEDATETIME',
		'ALTER TABLE MMMESSAGE DROP MMM_MODIFYDATETIME',
		'ALTER TABLE MMMESSAGE DROP MMM_SENDDATETIME',

		'ALTER TABLE MMMESSAGE ADD MMM_TO TEXT NOT NULL AFTER MMM_FROM;',
		'ALTER TABLE MMMESSAGE ADD MMM_CC TEXT NOT NULL AFTER MMM_TO;',
		'ALTER TABLE MMMESSAGE ADD MMM_BCC TEXT NOT NULL AFTER MMM_CC;',
		'ALTER TABLE MMMESSAGE ADD MMM_LISTS varchar(255) default NULL AFTER MMM_BCC;',
		'ALTER TABLE MMMESSAGE ADD MMM_LEAD varchar(255) AFTER MMM_SUBJECT;',
		'ALTER TABLE MMMESSAGE ADD MMM_SIZE int(11) NOT NULL AFTER MMM_IMAGES;',
		"ALTER TABLE MMMESSAGE ADD MMM_APP_ID char(2) NOT NULL default 'MM';",
		'ALTER TABLE MMMESSAGE ADD MMM_HEADER text;',
		"UPDATE MMMESSAGE SET MMM_PRIORITY='3' WHERE MMM_PRIORITY='1';"
	);
	foreach($queries as $query)
		$res = @mysql_query($query);
} else {
	$query = "CREATE TABLE MMMESSAGE (
		MMM_ID int(11) NOT NULL default '0',
		MMF_ID varchar(255) NOT NULL default '0',
		MMM_STATUS int(11) NOT NULL default '0',
		MMM_DATETIME datetime default NULL,
		MMM_PRIORITY int(11) NOT NULL default '0',
		MMM_FROM varchar(128) default NULL,
		MMM_TO text,
		MMM_CC text,
		MMM_BCC text,
		MMM_LISTS varchar(255) default NULL,
		MMM_SUBJECT varchar(255) default NULL,
		MMM_LEAD varchar(255) default '',
		MMM_CONTENT text,
		MMM_ATTACHMENT text,
		MMM_IMAGES text,
		MMM_SIZE int(11) NOT NULL,
		MMM_USERID varchar(20) NOT NULL default '',
		MMM_APP_ID char(2) NOT NULL default 'MM',
		MMM_HEADER text,
		PRIMARY KEY (MMM_ID)
		) TYPE=MyISAM DEFAULT CHARSET=utf8;";
	$res = @mysql_query($query);
}

$queries = array(
	'DROP TABLE IF EXISTS MMMSENTTO;',
	"CREATE TABLE MMMSENTTO (
		MMM_ID int(11) NOT NULL default '0',
		MMMST_EMAIL varchar(100) NOT NULL default '',
		MMMST_STATUS varchar(255) NOT NULL default '0',
		PRIMARY KEY (MMM_ID, MMMST_EMAIL)
	) TYPE=MyISAM DEFAULT CHARSET=utf8;",

	'DROP TABLE IF EXISTS MMSENT;',
	"CREATE TABLE MMSENT (
		MMS_DATE date NOT NULL,
		MMS_COUNT int(11) default NULL,
		PRIMARY KEY (MMS_DATE)
	) TYPE=MyISAM DEFAULT CHARSET=utf8;"
);
foreach($queries as $query)
	$res = @mysql_query($query);


if($res = @mysql_query("SELECT MMM_ID, MMM_SENDTO FROM MMMESSAGE WHERE MMM_SENDTO<>'' AND MMM_SENDTO IS NOT NULL"))
{
	while($msg_row = @mysql_fetch_assoc($res))
	{
		$arr = unserialize(base64_decode($msg_row['MMM_SENDTO']));
					
		$MMM_ID = $msg_row['MMM_ID'];

		if($arr['CGLF']['LISTS'])
			$MMM_LISTS = join(', ', $arr['CGLF']['LISTS']);
		else
			$MMM_LISTS = '';
		
		if($arr['CGLF']['CONTACTS'])
		{
			$contacts = $arr['CGLF']['CONTACTS'];
			$to = array();
			foreach($contacts as $cont)
			{
										
				$r = mysql_query("SELECT C_FIRSTNAME, C_LASTNAME, C_EMAILADDRESS FROM CONTACT WHERE C_ID='$cont' AND C_EMAILADDRESS<>''");
				$row = mysql_fetch_array($r);
				$to[] = trim(trim($row['C_FIRSTNAME'].' '.$row['C_LASTNAME']).' <'.$row['C_EMAILADDRESS'].'>');
			}
			$MMM_TO = mysql_real_escape_string(join(', ', $to));
		}
		else
			$MMM_TO = '';
		
		mysql_query("UPDATE MMMESSAGE SET MMM_TO='$MMM_TO', MMM_LISTS='$MMM_LISTS' WHERE MMM_ID='$MMM_ID'");
	}
}
@mysql_query('ALTER TABLE MMMESSAGE DROP MMM_SENDTO');

?>