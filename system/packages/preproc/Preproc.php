<?php
	class Preproc extends Smarty {
		
		public function __construct( $APP_ID = null) {
			$this->checkMode();
			
			$this->configDirs($APP_ID);
			
			$this->left_delimiter = '<?';
			$this->right_delimiter = sprintf('%s>', "?");
			
		}
		
		private function checkMode () {
			$safeMode = ini_get( 'safe_mode' );
			$this->use_sub_dirs =  !$safeMode;
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
				$this->use_sub_dirs = false;

			$this->force_compile = (defined('WBS_DEBUGMODE') && WBS_DEBUGMODE);
		}
		
		
		private function configDirs ($APP_ID) {
			$this->compile_id = (($APP_ID) ? $APP_ID : "common") . "2";
			$fs = Wbs::getSystemObj()->files();
			
			if ($APP_ID) {
				$this->template_dir = $fs->getPublishedPath($APP_ID . "/2.0/templates/");
				$this->config_dir = $fs->getPublishedPath("common/html/cssbased/");
			} else {
				$this->template_dir = $fs->getPublishedPath("common/templates/");
			}
			$this->compile_dir = sprintf( '%s/compiled', WBS_SMARTY_DIR );
			$this->cache_dir = sprintf( '%s/cache', WBS_SMARTY_DIR );
			$this->plugins_dir = $fs->getWbsPath("kernel/includes/smarty/plugins");
			if (defined("USE_GETTEXT") && USE_GETTEXT == 1) {
				$lang = mb_substr(CurrentUser::getLanguage(), 0, 2);
				$this->template_dir = $this->template_dir."/".$lang;
				$this->compile_id = $lang;
			}
		}
		
		public function getCommonPath($fileName = null) {
			return Wbs::getSystemObj()->files()->getPublishedPath("common/" . $fileName);
		}
		
		
		public function display( $template, $cache_id = null, $compile_id = null )
		{
			$this->assign ("preproc", $this);
			parent::display( $template, $cache_id, $compile_id );
		}
		
		public function displayScreen ($template) {
			$this->assign ("templateFilename", $template);
			$this->display(Wbs::getSystemObj()->files()->getPublishedPath("common/templates/wrapper.htm"));
		}
		
		public function getCommonUrl($filename) {
			return $this->getPublishedUrl("common/" . $filename);
		}
		
		public function getCssbasedUrl($filename) {
			return $this->getCommonUrl("html/cssbased/" . $filename);			
		}
		
		public function getAppUrl($app, $filename) {
			return Url::get("/".$app."/" . $filename);
		}
		
		public function getTemplatesUrl($filename) {
			return Url::get("/common/templates/" . $filename);
		}
		
		public function getPublishedUrl($filename = "") {
			return Url::get("/".$filename);
		}
		
		public function assignStringsToJs($section) {
			$strings = waLocale::getSectionStrings($section);

			$texts = array ();
			if ($strings) {
				if ($strings) {
					foreach ($strings as $cKey => $cValue) {
						$texts[] = $cKey . ":" . "'" . str_replace("'", "\\'", $cValue). "'";
					}
				}
			}
			return "{" . join (", ", $texts) . "}";
		}

	}
?>