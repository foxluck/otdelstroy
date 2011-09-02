<?php
/* @vars $smarty Smarty */
$smarty = &Core::getSmarty();
set_query('safemode=','',true);
//catalog database synchronization

//show new orders page if selected

//database synchronization
//affects only products and categories database! doesn't touch customers and orders tables

// generate SQL-file //

if (isset($_POST["export_db"])) //export database to SQL-file
{
	@set_time_limit(0);

	// write SQL insert statements to file 
	serProductAndCategoriesSerialization( DIR_TEMP."/database.sql" );

	$getFileParam = Crypt::FileParamCrypt( "GetDataBaseSqlScript", null );
	$smarty->assign( "getFileParam", $getFileParam );

	$smarty->assign( "sync_action", "export");
	$smarty->assign( "database_filesize", filesize(DIR_TEMP."/database.sql"));

}
elseif (isset($_POST["import_db"])) //execute sql-file
{
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		Redirect(set_query('&safemode=yes'));
	}

	@set_time_limit(0);

	//upload file
	if (isset($_FILES["db"]) && $_FILES["db"]["name"])
	{
		$db_name = DIR_TEMP."/file.db";
		$res = move_uploaded_file($_FILES["db"]["tmp_name"], $db_name);
		if ( $res )
		{

			SetRightsToUploadedFile( $db_name );

			DestroyReferConstraintsXML( DATABASE_STRUCTURE_XML_PATH );

			//clear products&categories database
			serDeleteProductAndCategories();

			//now plainly execute SQL file
					//serImport( $db_name );

			$f = implode("",file($db_name));
			$f = str_replace("insert into ", "INSERT INTO ", $f);
			$f = explode("INSERT INTO ",$f);
			for ($i=0; $i<count($f); $i++)
				if (strlen($f[$i])>0)
				{
					$f[$i] = str_replace(");",")",$f[$i]);
					db_query( "INSERT INTO ".$f[$i] );
				}

			CreateReferConstraintsXML( DATABASE_STRUCTURE_XML_PATH );

			unlink($db_name);
			$smarty->assign("sync_successful", 1);
		}else{
			$smarty->assign("sync_successful", 0);
		}

	} else $smarty->assign("sync_successful", 0);

	$smarty->assign("sync_action", "import");

	//update products count value if defined
	if (CONF_UPDATE_GCV == 1)
	{
		update_products_Count_Value_For_Categories(1);
	}
}

//set sub-department template
$smarty->assign("admin_sub_dpt", "catalog_dbsync.tpl.html");
?>