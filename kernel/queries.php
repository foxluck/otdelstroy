<?php
	
	//
	// Kernel DBSM-dependent queries
	//
	// Realization for mySQL
	//

	//
	// Currency
	//

	$qr_select_currency_count = "SELECT COUNT(*) FROM CURRENCY WHERE UPPER(CUR_ID)='!CUR_ID!'";

	//
	// System
	//

	$qr_select_db_info = "SHOW TABLE STATUS";

	$qr_select_server_version = "SELECT VERSION()";

	//
	// Host login queries
	//

	$qr_select_databases = "SHOW DATABASES";

	$qr_create_database = "CREATE DATABASE %s";

	$qr_createDBUser = "GRANT ALL PRIVILEGES ON %s.* TO %s@%s IDENTIFIED BY '%s' WITH GRANT OPTION";

	$qr_createDBReadonlyUser = "GRANT SELECT ON %s.* TO %s@%s IDENTIFIED BY '%s' WITH GRANT OPTION";

	$qr_flushPrivileges = "FLUSH PRIVILEGES";

	$qr_select_tables = "SHOW TABLES";

	$qr_delete_table = "DROP TABLE %s";

	$qr_select_mysqlusercnt = "SELECT COUNT(*) FROM user WHERE Host='%s' AND User='%s'";

	//
	// mySQL limit queries
	//

	$qr_limit_clause = "LIMIT %s, %s";
?>