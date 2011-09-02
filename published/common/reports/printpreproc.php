<?php
	require_once( WBS_SMARTY_DIR.'/Smarty.class.php');

	class print_preprocessor extends Smarty 
	//
	// Printable reports class
	//
	{

		function print_preprocessor( $app_id, $langConsts, $language ) 
		{
			global $PHP_SELF;
			global $currentUser;
			global $html_encoding;
			global $SID;

			$this->Smarty();

			$safeMode = ini_get( 'safe_mode' );
			if ( $safeMode )
				$this->use_sub_dirs = false;
			else
				$this->use_sub_dirs = true;

			$this->template_dir = sprintf( "%spublished/%s/reports", WBS_DIR, $app_id );
			$this->compile_id = "print".$app_id;

			$this->compile_dir = sprintf( '%s/compiled', WBS_SMARTY_DIR );
			$this->cache_dir = sprintf( '%s/cache', WBS_SMARTY_DIR );

			$this->left_delimiter = '<?';
			$this->right_delimiter = sprintf('%s>', "?");
			$this->force_compile = false;

			$this->assign('loc_str', $langConsts);
			$this->assign('kernelStrings', $langConsts);
			$this->assign('language', $language);
			$this->assign('scriptName', basename($PHP_SELF));
			$this->assign('currentUser', $currentUser); 
			$this->assign('html_encoding', $html_encoding);
			$this->assign('SID', $SID);

			$companyName = getCompanyName();
			if (!strlen($companyName))
				$companyName = WBS_NAME;

			$this->assign('companyName', $companyName);
		}

		function getReport( $templateName ) 
		//
		// Returns a string containing HTML code
		//
		//		Parameters:
		//			$templateName - template file name
		//
		//		Returns string
		//
		{
			ob_start();
			$this->display( $templateName );
			$html = ob_get_contents();

			ob_end_clean();

			return $html;
		}

		function display( $template, $cache_id = null, $compile_id = null ) 
		{
			global $silentMode;

			$prevModeValue = $silentMode;

			$silentMode = true;
			parent::display( $template, $cache_id, $compile_id );

			$silentMode = $prevModeValue;
		}

	}
?>