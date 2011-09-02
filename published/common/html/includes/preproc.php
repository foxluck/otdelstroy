<?php
	require_once( WBS_SMARTY_DIR.'/Smarty.class.php');

	class php_preprocessor extends Smarty {

	function php_preprocessor( $templateName, $langConsts, $language, $APP_ID, $noInitialize = false ) {

			global $PHP_SELF;
			global $currentUser;
			global $commonAppScript;
			global $init_required;
			global $html_encoding;
			global $DB_KEY;
			global $styleSet;
			global $monthFullNames;
			global $shortWeekDays;
			global $SCR_ID;
			global $databaseInfo;
			
			if (!$noInitialize) {
				$oldTemplate = isset($databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE']) && $databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE'];

				$templateName = ( $templateName == "qppublic" ) ? "classic" : ( $oldTemplate ? "classic" : "cssbased" );

				if ( !( isset($init_required) && !$init_required ) ) {
					//$theme = readUserCommonSetting( $currentUser, SCREEN_THEME );
					//$layout = readUserCommonSetting( $currentUser, SCREEN_LAYOUT );
					$layout = "topmenu";
					//$corners = readUserCommonSetting( $currentUser, "CORNERS" );
					$corners = null;
					$logo = readUserCommonSetting( $currentUser, SCREEN_LOGO );
					
					//if ( !strlen($theme) )
						//$theme = strlen($styleSet) ? $styleSet : HTML_DEFAULT_STYLESET;

					//if ( !strlen($layout) )
						//$layout = 'topmenu2';
					
					if ( !strlen($corners) )
						$corners = 'straight';
				} else
				{
					$theme = null;
					$layout = null;
					$logo = null;
					$corners = null;
				}

				if ( $APP_ID == 'wbsadmin' )
					$templateName = 'classic';
			}

				$this->Smarty();

				$safeMode = ini_get( 'safe_mode' );
				if ( $safeMode )
					$this->use_sub_dirs = false;
				else
					$this->use_sub_dirs = true;

				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
					$this->use_sub_dirs = false;

				$this->compile_id = "common";

				if ( isset($commonAppScript) && $commonAppScript )
				{
					$this->template_dir = fixPathSlashes( sprintf( "%spublished/common/html/%s", WBS_DIR, $templateName ) );
				}
				else
				{
					$this->template_dir = fixPathSlashes( sprintf( "%spublished/%s/html/%s", WBS_DIR, $APP_ID, $templateName ) );
					$this->compile_id = $APP_ID;
				}

				if ( strlen($styleSet) && $templateName != "cssbased" )
					$this->config_dir = fixPathSlashes( sprintf( "%spublished/common/html/%s/stylesets/%s", WBS_DIR, $templateName, $styleSet ) );
				else
					$this->config_dir = fixPathSlashes( sprintf( "%spublished/common/html/%s", WBS_DIR, $templateName ) );

				if ( !$oldTemplate )
					$this->compile_dir = sprintf( '%s/compiled', WBS_SMARTY_DIR );
				else
					$this->compile_dir = sprintf( '%s/compiled/old', WBS_SMARTY_DIR );

				$this->cache_dir = sprintf( '%s/cache', WBS_SMARTY_DIR );

				$this->plugins_dir = array( WBS_DIR."kernel/includes/smarty/plugins" );

				$this->left_delimiter = '<?';
				$this->right_delimiter = sprintf('%s>', "?");

				if ( defined('WBS_DEBUGMODE') ) {
					if ( WBS_DEBUGMODE )
						$this->force_compile = true;
					else
						$this->force_compile = false;
				} else
					$this->force_compile = false;
			
			$this->assign('language', $language);
			if (!$noInitialize) {
				$this->assign('loc_str', $langConsts);
				$this->assign('kernelStrings', $langConsts);
				$this->assign('scriptName', basename($PHP_SELF));
				$this->assign(HTML_STYLESET, $styleSet);
				$this->assign('currentUser', $currentUser);
				$this->assign('curAPP_ID', $APP_ID);
				$this->assign('simpleAjaxDelimiter', SIMPLE_AJAX_DELIMITER);

				if ( isset($databaseInfo[HOST_DBSETTINGS][HOST_TEMPORARY]) && $databaseInfo[HOST_DBSETTINGS][HOST_TEMPORARY] )
					$this->assign('temporary_account', 1);
				
				if ( !empty($databaseInfo[HOST_UNCONFIRMED]) ) {
					$this->assign('account_unconfirmed', 1);
				}
				
				$this->assign( 'showBillingAlert', showBillingAlert() && hasAccountInfoAccess($currentUser) );

				//$this->assign('theme', $theme);
				$this->assign('layout', $layout);
				$this->assign('corners', $corners);
				$this->assign('logoType', $logo);
				
				global $_GET;
				$ajaxAccess = (isset($_GET["ajaxAccess"]) ||  isset($_POST["ajaxAccess"]));
				$this->assign("ajaxAccess", $ajaxAccess);
				
				$this->assign('html_encoding', 'utf-8');
				$this->assign( "DB_KEY", base64_encode($DB_KEY) );

				if ( !( isset($init_required) && !$init_required ) ) {
					if ( isAdministratorID( $currentUser ) ) {
						$adminData = loadAdminInfo();
						$menuLanguage = $adminData[LANGUAGE];
					} else
						$menuLanguage = readUserCommonSetting( $currentUser, LANGUAGE );

					$srcMenuLinks = sortAppScreenList( listUserScreens( $currentUser ) );
					$menuLinks = assignMenuLinks( $srcMenuLinks, $menuLanguage, true );
					$targets = assignMenuLinks( $srcMenuLinks, $menuLanguage, true, true );

					$this->assign( MENULINKS, $menuLinks );
					$this->assign( "menu_targets", $targets );

					$userName = getUserName($currentUser);
					if ( strlen($userName) )
						$this->assign('currentUserName', getUserName($currentUser, true));
					else
						$this->assign('currentUserName', ucfirst(strtolower($currentUser)));

					$companyName = getCompanyName();
					if (!strlen($companyName))
						$companyName = WBS_DEF_NAME;

					$this->assign('companyName', prepareStrToDisplay($companyName, true));
				}

				$this->config_load( "template.conf" );

				// Calendar support
				//
				if ( defined('DATE_DISPLAY_FORMAT')  )
				{
					$this->assign('dateformat', DATE_DISPLAY_FORMAT );
					$weekdayNames = array();
					$monthNames = array();

					for ( $i = 0; $i <= 11; $i++ ) {
						$idx = $monthFullNames[$i];
						$monthNames[] = $langConsts[$idx];
					}

					for ( $i = 0; $i <= 6; $i++ ) {
						$idx = $shortWeekDays[$i];
						$weekdayNames[] = $langConsts[$idx];
					}

					$calendarStrings = array(
												"today" => $langConsts['app_cldtoday_text'],
												"wk" => $langConsts['app_cldwk_text'],
												"wk_tip" => $langConsts['app_cldweelnum_text'],
												"close" => $langConsts['app_cldclose_text'],
												"prevyear" => $langConsts['app_cldprevyear_text'],
												"nextyear" => $langConsts['app_cldnextyear_text'],
												"prevmonth" => $langConsts['app_cldprevmon_text'],
												"nextmonth" => $langConsts['app_cldtnextmon_text']
											);
					$this->assign('calWBSLocalDate', displayDate( convertTimestamp2Local( time() ) ) );

					$this->assign('monthNames', $monthNames );
					$this->assign('weekdayNames', $weekdayNames );
					$this->assign('calendarStrings', $calendarStrings );
				}

				// Field grouping support
				//
				$groupColorsPresets = array( '#006600', '#CC6600', '#000099', '#990099' );
				$this->assign('groupColorsPresets', $groupColorsPresets );
				$this->assign('maxPresetColor', 3 );
				$this->assign('session_name', ini_get('session.name') );
				$this->assign('session_id', session_id() );
				$this->assign('trans_sid', ini_get('session.use_trans_sid') );
				$this->assign('SCR_ID', $SCR_ID );

				if ( strlen($styleSet) && $templateName != "cssbased" )
					$helpURL = 'help.php';
				else
					$helpURL = '../../../common/html/scripts/help.php';

				if ( ini_get('session.use_trans_sid') )
					$helpURL .= '?'.ini_get('session.name')."=".session_id();

				$this->assign('helpURL', $helpURL );

				// Logo
				//
				$showLogo = false;
				if ( !( isset($init_required) && !$init_required ) ) {
					$logoPath = getKernelAttachmentsDir();
					$logoPath .= "/logo.gif";
					$showLogo = file_exists($logoPath);
					$this->assign('showLogo', $showLogo );
					if ($showLogo) {
						$this->assign ('logoTime', filemtime($logoPath));
					}
				}
				
				// Inplace screen
				//
				$inplaceScreen = false;
				if (isset($_GET["inplaceScreen"]))
					$inplaceScreen = $_GET["inplaceScreen"];
				if (isset($_POST["inplaceScreen"]))
					$inplaceScreen = $_POST["inplaceScreen"];
				$this->assign ("inplaceScreen", $inplaceScreen);
				
				$showCompanyTop = ((empty($databaseInfo[HOST_ADVSETTINGS][SHOW_COMPANYTOP]) && $showLogo) || (!empty($databaseInfo[HOST_ADVSETTINGS][SHOW_COMPANYTOP]) && $databaseInfo[HOST_ADVSETTINGS][SHOW_COMPANYTOP] == "yes"));
				// Show Company Top
				$this->assign ('showCompanyTop', $showCompanyTop);
			}
		}

		function display( $template, $cache_id = null, $compile_id = null )
		{
			global $silentMode;

			$prevModeValue = $silentMode;

			$_SESSION['LAST_PAGE_VIEWED'] = basename($_SERVER['PHP_SELF']);
			$silentMode = true;
			parent::display( $template, $cache_id, $compile_id );

			$silentMode = $prevModeValue;
		}

	}
?>