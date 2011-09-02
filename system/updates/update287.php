<?php

// Connect to database
$model = new DbModel();

$sql = "SELECT C_PHOTO FROM CONTACT WHERE 0";
if (!mysql_query($sql)) {
    @mysql_query("ALTER TABLE CONTACT ADD C_PHOTO TEXT NULL");
}

$sql = "SELECT C_COMPANY FROM CONTACT WHERE 0";
if (!mysql_query($sql)) {
    @mysql_query("ALTER TABLE CONTACT ADD C_COMPANY VARCHAR(255) NULL");
}

$contacts = $model->query("SELECT * FROM CONTACT WHERE C_ID = 0")->fetchAll();
if ($contacts) {
	@mysql_query("DELETE FROM CONTACT WHERE C_ID = 0");
	@mysql_query("ALTER TABLE `CONTACT` CHANGE `C_ID` `C_ID` INT( 11 ) NOT NULL AUTO_INCREMENT");
}

?>
