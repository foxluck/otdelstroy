<?php
	define( "WBS_MODULES", "modules" );
	define( "WBS_MODULES_MODULE", "module" );
	define( "WBS_MODULES_CLASS", "class" );

	define( "WBS_VALUES", "values" );
	define( "WBS_VALUE", "value" );

	function getModuleFullFileName( $class, $filename )
	//
	// Returns a full name of a file proceeding from a class of the module
	//
	//		Returns full filename
	{
		$filePath = fixPathSlashes( sprintf( "%s/{$class}", WBS_MODULES_DIR ) );

		return $filePath."/".$filename;
	}

	function _testExtension( $filename, $extension )
	//
	// Checks is extension of filename equal $extension
	//
	//		Returns boolean
	{
		if ( $extension == null || trim($extension) == "" )
			 return true;

		$i=strlen($filename)-1;
		for( ; $i >= 0; $i-- )
		{
			if ( $filename[$i] == '.' )
				 break;
		}

		if ( $filename[$i] != '.' )
			return false;
		else
		{
			 $ext = substr( $filename, $i+1 );
			 return ( strtolower($extension) == strtolower($ext) );
		}
	}

	function getFilesInDirectory( $dir, $extension = "" )
	//
	// Gets all files in directory with selected extension.
	//
	//		Returns array of filenames
	{
		$dh  = opendir($dir);
		$files = array();

		while (false !== ($filename = readdir($dh)))
		{
			if ( $filename != "." && $filename != ".." )
			{
				if ( _testExtension($filename,$extension) )
					$files[] = $filename;
			}
		}
		return $files;
	}

	class wbsModules
	{
		var $classes;
		var $strings;

		function wbsModules( $strings = null )
		{
			$this->classes = array();

			$this->strings = $strings;
		}


		function setStrings( $strings = null )
		//
		//
		//
		{
			$this->strings = $strings;
		}

		function getDefaultModule( $classId )
		//
		//	Gets default module in $classId class
		//
		{
			$error_not_found =is_null( $this->strings ) ? "Module class not found" : $this->strings["app_mdl_class_notfound_error"];

			if ( !isset( $this->classes[$classId] ) )
				return PEAR::raiseError( $error_not_found, ERRCODE_APPLICATION_ERR );

			return $this->classes[$classId]->getDefaultModule( );
		}

		function setDefaultModule( $classId, $moduleId )
		//
		//	Sets default module in $classId class
		//
		{
			$error_not_found =is_null( $this->strings ) ? "Module class not found" : $this->strings["app_mdl_class_notfound_error"];

			if ( !isset( $this->classes[$classId] ) )
				return PEAR::raiseError( $error_not_found, ERRCODE_APPLICATION_ERR  );

			return $this->classes[$classId]->setDefaultModule( $moduleId );
		}

		function &getClassesList( )
		//
		//	Gets full class list
		//
		{
			return $this->classes;
		}

		function &getClass( $classId )
		//
		//	Gets $classId class object from class list
		//
		{
			$error_not_found =is_null( $this->strings ) ? "Module class not found" : $this->strings["app_mdl_class_notfound_error"];

			if ( !isset( $this->classes[$classId] ) )
				return PEAR::raiseError( $error_not_found, ERRCODE_APPLICATION_ERR  );

			return $this->classes[$classId];
		}

		function disableClass( $classId )
		//
		//	Disable $classId for using
		//
		{
			$error_not_found =is_null( $this->strings ) ? "Module class not found" : $this->strings["app_mdl_class_notfound_error"];

			if ( !isset( $this->classes[$classId] ) )
				return PEAR::raiseError( $error_not_found, ERRCODE_APPLICATION_ERR  );

			return $this->classes[$classId]->disable( );
		}

		function &enableClass( $classId )
		//
		//	Disable $classId for using
		//
		{
			$error_not_found =is_null( $this->strings ) ? "Module class not found" : $this->strings["app_mdl_class_notfound_error"];

			if ( !isset( $this->classes[$classId] ) )
				return PEAR::raiseError( $error_not_found, ERRCODE_APPLICATION_ERR  );

			return $this->classes[$classId]->enable( );
		}

		function &getClassModules( $classId )
		//
		//	Gets modules list from $classId class
		//
		{
			$error_not_found =is_null( $this->strings ) ? "Module class not found" : $this->strings["app_mdl_class_notfound_error"];

			if ( !isset( $this->classes[$classId] ) )
				return PEAR::raiseError( $error_not_found, ERRCODE_APPLICATION_ERR  );

			return $this->classes[$classId]->getList( );
		}

		function &getModulesList( $xml )
		//
		// Returns associative array of all modules ordered by class.
		//
		{
			global $modulesClasses;

			$mClasses = $this->classes;

			$modules = array();

			foreach( $mClasses as $class=>$value )
			{
				$temp = $value->getModulesList( );
				$modules[$class] = $temp;
			}

			return $modules;
		}

		function load( )
		//
		//	Loads modules into WebAsyst
		//
		{
			$filePath = sprintf( "%s/wbsmodules.xml", WBS_MODULES_DIR );

			$dom = domxml_open_file( realpath($filePath) );
			if ( !$dom )
				return false;

			$xpath = xpath_new_context($dom);


			if ( !( $xmlpath = xpath_eval($xpath, "/".WBS_MODULES."/".WBS_MODULES_CLASS ) ) )
				return false;

			foreach( $xmlpath->nodeset as $classNode )
			{
				if ( !method_exists ( $classNode, "tagname") )
					continue;

				if ( $classNode->tagname() != WBS_MODULES_CLASS )
					continue;

				$params = getXMLAttributes( $classNode );

				$class = new wbsModulesClass( $classNode, $params[ "ID" ], $params[ "NAME" ], $this );

				$this->classes[$class->id] = $class;
			}
		}
	}

	class wbsModulesClass
	{
		var $id;
		var $name;
		var $modules;
		var $default;
		var $disabled;

		var $parent;

		function wbsModulesClass( $xmlpath, $id, $name, &$parent )
		{
			$this->modules = array();
			$this->id = $id;
			$this->name = $name;
			$this->default = "";
			$this->disabled = true;

			$this->parent = $parent;

			$this->load( $xmlpath );
		}

		function disable( )
		//
		//	Disable class
		//
		{
			$this->disabled = true;
		}

		function enable( )
		//
		//	Disable class
		//
		{
			$this->disabled = false;
		}

		function isDisabled( )
		//
		//	Is class disabled?
		//
		{
			return $this->disabled;
		}

		function &setDefaultModule( $moduleId )
		//
		//	Sets $moduleId module for default using in class
		//
		{
			$error =is_null( $this->parent->strings ) ? "%s module is not installed or disabled." : $this->parent->strings["app_mdl_default_notset_error"];

			if ( !isset( $this->modules[$moduleId] ) )
				return PEAR::raiseError( sprintf( $error, strtoupper( $this->id ) ), ERRCODE_APPLICATION_ERR );

			if ( $this->modules[$moduleId]->isInstalled() )
				return $this->default = $moduleId;

			return PEAR::raiseError( sprintf( $error, strtoupper( $this->id ) ), ERRCODE_APPLICATION_ERR );
		}

		function &getDefaultModule( )
		//
		//	Gets defualt module object
		//
		{
			$error =is_null( $this->parent->strings ) ? "%s module is not installed or disabled." : $this->parent->strings["app_mdl_default_notset_error"];

			if ( isset( $this->modules[$this->default] ) )
				return $this->modules[$this->default];

			return PEAR::raiseError( sprintf( $error, strtoupper( $this->id ) ), ERRCODE_APPLICATION_ERR );
		}

		function &getModule( $id )
		//
		//	Gets $id module object
		//
		{
			$error =is_null( $this->parent->strings ) ? "%s module is not installed or disabled." : $this->parent->strings["app_mdl_default_notset_error"];

			if ( isset( $this->modules[$id] ) )
				return $this->modules[$id];

			return PEAR::raiseError( sprintf( $error, strtoupper( $this->id ) ), ERRCODE_APPLICATION_ERR );;
		}

		function &getList( )
		//
		//	Gets array of modules objects
		//
		{
			return $this->modules;
		}

		function installModule( $id )
		//
		// Install $id module into module list
		//
		{
			$error_not_found =is_null( $this->parent->strings ) ? "Module not found" : $this->parent->strings["app_mdl_notfound_error"];
			$error_installed =is_null( $this->parent->strings ) ? "Module already installed" : $this->parent->strings["app_mdl_alr_installed_error"];

			if ( !isset( $this->modules[$id] ) )
				return PEAR::raiseError( $error_not_found, ERRCODE_APPLICATION_ERR );

			if ( $this->modules[$id]->isInstalled() )
				return  PEAR::raiseError( $error_installed, ERRCODE_APPLICATION_ERR );

		 	$this->modules[$id]->installed = true;

			return true;
		}

		function uninstallModule( $id )
		//
		// UnInstall $id module from module list
		//
		{
			$error_not_found =is_null( $this->parent->strings ) ? "Module not found" : $this->parent->strings["app_mdl_notfound_error"];
			$error_installed =is_null( $this->parent->strings ) ? "Module already installed" : $this->parent->strings["app_mdl_alr_installed_error"];

			if ( !isset( $this->modules[$id] ) )
				return PEAR::raiseError( $error_not_found, ERRCODE_APPLICATION_ERR );

			if ( !$this->modules[$id]->isInstalled() )
				return  PEAR::raiseError( $error_installed, ERRCODE_APPLICATION_ERR );

			$this->modules[$id]->installed = false;

			return true;
		}

		function load( $xmlpath )
		//
		// Loads class of modules into WebAsyst
		//
		{
			$this->classes = array();

			$files = $this->getModulesXMLList( );

			foreach( $files as $key=>$item )
			{
				$fullFilename = getModuleFullFileName( $this->id, $item );
				
				if ($cacheValue = getGlobalCacheValue("WBSPARAMS", $fullFilename)) {
					$params = $cacheValue;
				} else {
					$xml = file_get_contents( $fullFilename );
					$params = new wbsParameters( $xml );
					setGlobalCacheValue ("WBSPARAMS", $fullFilename, $params);
				}
				$mName = substr( $item, 0, strpos( $item, '.' ) );

				$module = new wbsModule( $mName, $this->id );

				$module->setDescription( $params->get( "module_description" ) );
				$module->setParams( $params );

				$this->modules[$mName] = $module;
			}

			foreach( $xmlpath->child_nodes() as $modulePath )
			{
				if ( !method_exists ( $modulePath, "tagname") )
					continue;
				
				if ( $modulePath->tagname() != WBS_MODULES_MODULE )
					continue;

				$params = getXMLAttributes( $modulePath );
				$moduleId = $params[ "ID" ];

				if ( !in_array( $moduleId, array_keys( $this->modules ) ) )
					continue;

				$this->modules[$moduleId]->loadParametersXMLPath( $modulePath );

				$this->modules[$moduleId]->installed = true;
			}

			foreach( $this->modules as $key=>$item )
			{
				if ( !$item->isInstalled() )
					continue;

				$phpFile = getModuleFullFileName( $this->id, $key.".php" );

				if ( file_exists( $phpFile ) )
				{
					include( $phpFile );

					$moduleInstance = new $key;

					$moduleInstance->setParams( $item->params );

					$this->modules[$key]->assignInstance( $moduleInstance );
				}
			}

		}

		function getModulesXMLList( )
		//
		// Returns array of XML description files for the class
		//
		{
			$filePath = fixPathSlashes( sprintf( "%s/{$this->id}", WBS_MODULES_DIR ) );

			return getFilesInDirectory( $filePath , "xml" );
		}
	}

	class wbsModule
	{
		var $id;
		var $title;
		var $class;
		var $descr;
		var $params;
		var $installed = false;
		var $instance = null;

		function wbsModule( $id, $class )
		{
			$this->installed = false;
			$this->id = $id;
			$this->class = $class;
		}

		function loadParametersXMLPath( $xmlpath )
		//
		// Loads module's parameters XML file
		//
		{
			foreach( $xmlpath->child_nodes() as $valuePath )
			{

				if ( !method_exists ( $valuePath, "tagname") )
					continue;

				if ( $valuePath->tagname() != WBS_VALUES )
					continue;

				$this->params->loadFromXMLNodesArray( $valuePath->child_nodes(), "", $performDataChecking = true, $parameters = null );

				break;
			}
		}

		function getId( )
		//
		// Gets module id
		//
		{
			return $this->id;
		}

		function assignInstance( &$instance )
		//
		// Assigns module instance
		//
		{
			$this->instance = $instance;
		}

		function &getInstance( )
		//
		// Gets module instance
		//
		{
			return $this->instance;
		}

		function isInstalled( )
		//
		//	Is module installed into system?
		//
		{
			return $this->installed;
		}

		function setDescription( $descr )
		//
		//	Sets module description
		//
		{
			$this->descr = $descr;
		}

		function setParams( &$params )
		//
		//	Sets module parameters
		//
		{
			$this->params = $params;
		}

		function getDescriptionArray()
		//
		//	Gets module description array
		//
		{
			$result["CLASS"] = $this->class;
			$result["MODULE"] = $this->id;
			$result["INSTALLED"] = $this->installed;
			$result["PARAMS"] = $this->params;
			$result["DESCR"] = $this->descr;

			return $result;
		}
	}

	function modules_dumpModulesInfo( $strings = null )
	//
	// Dumps loaded modules info into configuration xml file
	//
	{
		global $WBS_MODULES;

		$filePath = sprintf( "%s/wbsmodules.xml", WBS_MODULES_DIR );

		$dom = @domxml_new_doc("1.0");

		if ( !$dom )
			return PEAR::raiseError( is_null( $strings ) ? "Error processing XML data" : $strings["app_errxml_message"] );

		$root = @create_addElement( $dom, $dom, "modules" );

		$classes = $WBS_MODULES->getClassesList();

		foreach( $classes as $class_id=>$class )
		{
			$class_xml = @create_addElement( $dom, $root, "class" );

			$class_xml->set_attribute( "ID", $class->id );
			$class_xml->set_attribute( "NAME", $class->name );

			foreach( $class->modules as $module_id=>$module )
			{
				if ( !$module->isInstalled() )
					continue;

				$module_xml = @create_addElement( $dom, $class_xml, "module" );

				$module_xml->set_attribute( "ID", $module->id );
				$module_xml->set_attribute( "TITLE", $module->title );

				$module->params->addValuesToDOMNode( $dom, $module_xml );
			}

		}

		@$dom->dump_file($filePath, false, true);
	}

	function loadDBModules( )
	//
	// Loads modules information from database profile and sets default class modules
	//
	{
		global $DB_KEY;
		global $WBS_MODULES;

		$filePath = sprintf( "%sdblist/%s.xml", WBS_DIR, strtoupper($DB_KEY) );

		if ( !file_exists($filePath) )
			return;

		$dom = domxml_open_file( realpath($filePath) );

		$xpath = xpath_new_context($dom);

		if ( !( $modules = xpath_eval($xpath, "/DATABASE/MODULES/ASSIGN") ) )
			return;

		foreach( $modules as $module )
		{
			$params = getXMLAttributes( $module );

			$WBS_MODULES->setDefaultModule( $params["CLASS"], $params["MODULEID"] );
		}

	}

?>
