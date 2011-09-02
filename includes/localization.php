<?php
function getLanguage(){
	if ( version_compare(PHP_VERSION,'5','>=') ){
		//require_once( "domxml-php4-to-php5.php" );
	}
	//update language list
	$sourcePath = "./settings.xml";
	$targetPath = "./kernel/wbs.xml";
	if(extension_loaded('dom')&&file_exists($targetPath)&&file_exists($sourcePath)){
		$langSource = array();
		$langSourceNames = array();
		$content = file( $sourcePath );
		$content = implode( '', $content );
		if(@get_magic_quotes_gpc()){
			$content = stripslashes($content);
		}
		$dom = @domxml_open_mem( $content );
		if ($dom ){
			$xpath = @xpath_new_context($dom);
			$nodePath = '/WBS/LANGUAGES/LANGUAGE';
			if (( $langsnode = xpath_eval($xpath, $nodePath) ) ){
				foreach($langsnode->nodeset as $langnode){
					$langSource[] = $langnode->get_attribute( 'ID' );
					$langSourceNames[] = $langnode->get_attribute( 'NAME' );
				}
			}
			unset($dom);
			$content = file( $targetPath );
			$content = implode( '', $content );
			if(@get_magic_quotes_gpc()){
				$content = stripslashes($content);
			}
			$dom = @domxml_open_mem( $content );
			if ($dom ){
				$xpath = @xpath_new_context($dom);
				$nodePath = '/WBS/LANGUAGES/LANGUAGE';
				if (( $langsnode = xpath_eval($xpath, $nodePath) ) ){
					foreach($langsnode->nodeset as $langnode){
						$langTarget = $langnode->get_attribute( 'ID' );
						if(($key=array_search($langTarget,$langSource))!==false){
							unset($langSource[$key]);
							unset($langSourceNames[$key]);
						}
					}
					$parent = $langnode->parent_node();
					$first = isset($langsnode->nodeset[0])?$langsnode->nodeset[0]:false;
					foreach($langSource as $key => $langID){
						$element = $dom->create_element( 'LANGUAGE' );
						$newLangNode = ($first===false)?$parent->append_child( $element):$parent->insert_before( $element,$langsnode->nodeset[0]);						
						
						if($newLangNode){
							$newLangNode->set_attribute('ID',$langID);
							$newLangNode->set_attribute('NAME',$langSourceNames[$key]);
						} 	
					}
	
					if(count($langSource)){
						$dom->dump_file( $targetPath, false, true );
					}
				}
				unset($dom);
			}
		}
	}
	global $lang;
	
	
	$lang='eng';
	if(extension_loaded('dom')){
		$configPath = "kernel/wbs.xml";
		if ( !@file_exists($configPath) )
		$configPath= "settings.xml";
		if(file_exists($configPath)){
			
			$content = file( $configPath );
			$content = implode( '', $content );
			if(@get_magic_quotes_gpc()){
				$content = stripslashes($content);
			}
			
			$errStr = null;
			$dom = @domxml_open_mem( $content );

			if ($dom ){
				$xpath = @xpath_new_context($dom);
				$nodePath = '/WBS/LANGUAGES/LANGUAGE';
				if (( $langsnode = xpath_eval($xpath, $nodePath) ) ){
					if ( count($langsnode->nodeset) ){
						$langnode = $langsnode->nodeset[0];
						$lang = $langnode->get_attribute( 'ID' );
					}
				}
			}
		}
	}
	$lang = strtolower($lang);



	$locStrings=array(
	'eng'=>array(
	'inst'=>'Install',
	'updt'=>'Update',
	'upgrd'=>'Upgrade',
	'err_openverfile'=>'Error opening version file',
	'inv_ver_file'=>'Invalid version file',
	'ver_inf_not_found'=>'Installation information not found',
	'err_parse_ver_file'=>'Error parsing version file',
	'unable_to_extract'=>'Unable to extract files',
	'unable_to_create_directory'=> 'Unable create directory',
	'unable_to_open'=>'Unable to open file for writing (permission denied)',
	'wb_log'=>'WebAsyst Installation Log',
	'inst_date'=>'Install date:',
	'wba_url'=>'WebAsyst Installer URL',
	'login_url'=>'Login URL',
	'file_not_found'=>'Could not find update.xml file',
	'err_read_db_prof'=>'Error reading database profile',
	'err_op_db_profile'=>'Error opening database profile',
	'err_op_dblist'=>'Error opening database list',
	'err_op_sys_set'=>'Error opening system settings file',
	'no_server_found'=>'No MySQL server found on %s',
	'err_create_log'=>'Error creating update log file. Operation aborted.',
	'upd_start'=>'Update started: %s',
	'old_vers'=>'Your current WebAsyst version: %s',
	'new_vers'=>'New version: %s',
	'upd_progress'=>'Updating software...',
	'start'=>'Started: %s',
	'upd_err'=>'Software update: <font color=red>Error</font>',
	'upd_success'=>'Software update: <font color=green>Successful</font>',
	'complete'=>'Complete',
	'upd_db_structure'=>'Updating database...',
	'no_upd_meta_req'=>'Database update is not required',
	'upd_db_structure_det'=>'Updating database...',
	'update_db'=>'Updating database: %s',
	'err_connect_to_mysql'=>'Error connecting to MySQL server at %s',
	'err'=>'Error',
	'err_select_db'=>'Error connecting to database %s',
	'upd_app'=>'Updating application %s',
	'success'=>'Success',
	'skipped'=>'Skipped',
	'upd_complete'=>'Update completed successfully',
	'finish'=>'Finish',
	'continue'=>'Continue &gt;&gt;',
	'extract_info'=>'<p><b>WebAsyst files have been successfully extracted.</b></p><p>To complete installation you need to:<p>Setup MySQL server & Create WebAsyst working database<p><br>Click the button below to proceed.',
	'upd_success_complete'=>'Update completed successfully',
	'wba_url_lbl'=>'WebAsyst Installer:',
	'db_url_lbl'=>'Login to WebAsyst:',
	'wa_upgr_ver'=>'Your WebAsyst installation has been upgraded to version ',
	'open_log'=>'View complete meta data update log',
	'inst_aborted'=>'Installation aborted.',
	'no_prev_ver'=>'No previously installed WebAsyst components were found in the current folder.',
	'extract_desc'=>'Press "Continue" button to extract WebAsyst files.',
'note_repair'=>'<b>IMPORTANT NOTE</b>:<br><br> You already have WebAsyst scripts installed in the current folder. Existing WebAsyst scripts have <b>the same version number</b> as the scripts you are going to install. It\'s not necessary to run this installation script in the current folder unless you intentionally want to update missed or corrupted script files.<br><b>We strongly recommend you to make backup copies of existing WebAsyst scripts and databases.</b><br><br> Click the button below to update WebAsyst scripts.',
'note_no_vers'=>'<p><b>IMPORTANT NOTE</b>: You already have WebAsyst scripts installed in the current folder. Existing WebAsyst scripts have <b>no version number</b>. Possible reason:</p> <ol><li>Version information is missing in wbs.xml file.</li><li>Scripts were obtained before we launched WebAsyst version numbering technology.</li></ol><p>As we can not define your current version number, your database(s) structure (metadata) <b>will not be updated</b>. It may cause incompatibility of the scripts installed by this installation procedure with your existing database(s). If it occurs, you must manually update your existing database(s) structure.</p> <p><b>We strongly recommend you to make backup copies of your existing WebAsyst scripts and databases.</b></p> <p>Click the button below to update WebAsyst scripts.</p>',
	'note_inst'=>'<p><b>IMPORTANT NOTE</b>: You have WebAsyst scripts already installed in the current folder.</p>',
	'curr_wa_ver'=>'Existing WebAsyst version:',
	'install_wa_ver'=>'WebAsyst version to be installed:',
	'upd_db_list_label'=>'During the installation process, we will upgrade metadata (database structure) for the following database(s):',
	'upd_db_count'=>'Total %s database(s) to be upgraded',
	'upd_db_full_list_link'=>'view full list of databases in new window',
	'upd_meta_info'=>'View database %s update instructions',
	'backup_notice'=>'We strongly recommend you to make backup copies of all existing WebAsyst scripts and databases.',
	'upgrade_db_btn_desc'=>'Click the button below to upgrade WebAsyst scripts and database(s) structure.',
	'no_db_meta'=>'Database update is not required.',
	'upd_not_actual'=>'You have WebAsyst scripts already installed in the current folder and they have newer version number than the scripts you wish to install.',
	'inst_ver'=>'WebAsyst version to be installed:',
	'inst_canceled'=>'Installation cancelled.',
	'sys_req_list'=>'System requirements',
	'php_version'=>'PHP 5.0.5 or higher:',
	'php_safemode'=>'PHP safemode must be disabled',
	'php_ext'=>'Required PHP extensions:',
	'php_ext_mb_string'=>' — mbstring',
	'php_ext_mysql'=>' — mysql',
	'php_ext_domxml'=>' — DOM XML',
	'php_ext_simplexml'=>' — SimpleXML',
	'php_ext_gd'=>' — GD',
	'php_ext_warning'=>'Warning:',
	'php_ext_gd_warning'=>'Image processing functions will be disabled.',
	'php_ext_zlib'=>'— ZLib (optional)',
	'php_ext_zlib_warning'=>'Archive proccessing functions will be disabled.',
	'php_ext_gettext'=>' — gettext (optional)',
	'php_ext_gettext_warning'=>'Multilanguage futures will be disabled',
	'java_enabled'=>'JavaScript must be enabled:',
	'mysql_version'=>'MySQL 4.0 or higher',
	'write_rights'=>'The installation directory must be enabled for writing:',
	'note'=>'Note:',
	'write_rights_note'=>'Permission value for UNIX-based server is usually 775.',
	'write_rights_note2'=>'Please contact your host service provider and refer to the WebAsyst Installation Guide for  details.',
	'view_php_info'=>'View PHP info',
	'view_new_window'=>'In new window.',
	'sys_req_sat'=>'Server fully satisfies WebAsyst system requirements.',
	'sys_req_not_sat'=>'Server does not satisfy WebAsyst system requirements.',
	'inst_aborted_cap'=>'INSTALLATION ABORTED',
	'license_label'=>'License Agreement',
	'license_accept'=>'I understand and accept terms and conditions of this License Agreement.',
	'inst_suc_comp'=>'Installation procedure successfully completed.',
	'press_button_to_login_wa'=>'Login to WebAsyst',
	'admin'=>'Administrator',
	'install_canceled'=>'Installation procedure was cancelled',
	'resume'=>'Resume',
	'step'=>'Step',
	'wa_inst_wizard'=>'WebAsyst Installation Wizard',
	'check_sys_req'=>'Checking system requirements',
	'search_installed'=>'Searching for installed components',
	'extracting_files'=>'Extracting files',
	'welcome'=>'WebAsyst Installation Wizard',
	'wa_inst_guide'=>'WebAsyst Installation Instructions',
	'wa_installer'=>'WebAsyst Installer',
	'installer'=>'Installer',
	
	///	
	'please_try_again'=>'Сервер не отвечает, попробуйте запустить установку с самого начала (установка будет продолжена с момента остановки)',
//	''=>'',
	'extracting_auto_name'=>'<span style="font-size:120%;font-weight:bolder;">Automated installation</span>',
	'extracting_auto'=>'Extracting files...',
	'extracting_auto_description'=>'<p>Archive with WebAsyst PHP scripts (wbs.tgz) will be uncompressed automatically (2—5 minutes).',
	

	'extracting_manual_name'=>'<span style="font-size:120%;font-weight:bolder;">Manual installation</span>',
	'extracting_manual_description'=>'<p>Manual installation may be required if automated installation could not be implemented due to server
system limitations (e.g. if PHP scripts execution time is limited).
You will need to manually uncompress wbs.tgz on your local computer, upload files to your server and
properly set file access permissions (chmod).</p>',
	'extracting_manual'=>'<strong>You will have to install WebAsyst manually</strong>:
<ol>
<li>On your computer navigate to the folder where you extracted WebAsyst archive (WebAsyst root
folder);</li>
<li><i>Uncompress entire wbs.tgz archive in this folder</i> (usually this is done by installer automatically,
but your server configuration did not allow this); inside root folder new directories and files will appear —
published/, installer/, kernel/ and others;</li>
<li><i>Upload all extracted files</i> from your computer to the remote server by FTP (overwriting existing
files is not neccessary);</li>
<li>Enable write permissions for following folders (set chmod 775 or 777):
<ul>
<li>published/</li>
<li>kernel/</li>
<li>kernel/includes/</li>
<li>kernel/includes/smarty/</li>
<li>temp/</li>
</ul>
</li>
<li>Click "Continue" button below — installation wizard will check all uploaded files and proceed to MySQL
settings installation step.</li>
</ol>',
	'extracting_no_auto'=>'Automated installation is not possible on this server due to system restrictions.<p>Only manual installation option is possible for this server.</p>',
	
	'force_chmod'=>'Force set chmod for every unpacked file',
	'force_chmod_description'=>'(use force chmod only if server default chmod settings restrict access to newly created files)',

	'installer'=>'Installer',

	'step1'=>'License',
	'step2'=>'System validation',
	'step3'=>'Extract files',
	'step4'=>'MySQL',
	'step5'=>'Done!',
	'step3_title'=>'Extract WebAsyst files',
	),
	'rus'=>array(
	'inst'=>'Установить',
	'updt'=>'Обновить',
	'upgrd'=>'Обновить',
	'err_openverfile'=>'Ошибка чтения файла с номером версии',
	'inv_ver_file'=>'Неверный формат файла с номером версии',
	'ver_inf_not_found'=>'Информация об установки не найдена',
	'err_parse_ver_file'=>'Ошибка чтения файла с номером версии',
	'unable_to_extract'=>'Ошибка при распаковке архива со скриптами',
	'unable_to_create_directory'=> 'не удалось создать директорию',
	'unable_to_open'=>'Ошибка при попытке перезаписать файл',
	'wb_log'=>'Журнал установки и обновления WebAsyst',
	'inst_date'=>'Дата установки:',
	'wba_url'=>'WebAsyst Installer',
	'login_url'=>'Вход в WebAsyst',
	'file_not_found'=>'Не найден файл update.xml',
	'err_read_db_prof'=>'Ошибка чтения файла профиля базы данных',
	'err_op_db_profile'=>'Ошибка чтения профиля базы данных',
	'err_op_dblist'=>'Ошибка чтения списка баз данных',
	'err_op_sys_set'=>'Ошибка чтения файла с системными настройками',
	'no_server_found'=>'MySQL-cервер на %s не найден',
	'err_create_log'=>'Ошибка создания файла с журналом обновлений. Операция прервана.',
	'upd_start'=>'Начало обновления: %s',
	'old_vers'=>'Текущая версия WebAsyst: %s',
	'new_vers'=>'Новая версия: %s',
	'upd_progress'=>'Обновление WebAsyst...',
	'start'=>'Начало обновления: %s',
	'upd_err'=>'Обновление WebAsyst: <font color=red>Ошибка</font>',
	'upd_success'=>'Обновление WebAsyst: <font color=green>Успешно</font>',
	'complete'=>'Завершено',
	'upd_db_structure'=>'Обновление базы данных...',
	'no_upd_meta_req'=>'Обновление базы данных не требуется',
	'upd_db_structure_det'=>'Обновление базы данных...',
	'update_db'=>'Обновление базы данных: %s',
	'err_connect_to_mysql'=>'Ошибка подключения к серверу MySQL',
	'err'=>'Ошибка',
	'err_select_db'=>'Ошибка подключения к базе данных %s',
	'upd_app'=>'Обновление приложения %s',
	'success'=>'Успешно',
	'skipped'=>'Пропущена',
	'upd_complete'=>'Обновление успешно завершено',
	'finish'=>'Завершено',
	'continue'=>'Продолжить &gt;&gt;',
	'extract_info'=>'<p><b>Файлы WebAsyst успешно распакованы.</b></p> <p>Для завершения установки необходимо:<p><ol><li>Ввести настройки подключения к MySQL<li>Создать новую базу данных или выбрать существующую</ol><p><br>Нажмите для продолжения:',
	'upd_success_complete'=>'Обновление успешно завершено',
	'wba_url_lbl'=>'Адрес для доступа к <strong>WebAsyst Installer</strong>',
	'db_url_lbl'=>'Адрес входа в WebAsyst',
	'wa_upgr_ver'=>'WebAsyst был успешно обновлен до версии ',
	'open_log'=>'Показать журнал изменений структуры данных',
	'inst_aborted'=>'Установка прервана.',
	'no_prev_ver'=>'В текущей папке не найдено ни одного установленного приложения WebAsyst.',
	'extract_desc'=>'Нажмите «Продолжить» для начала установки.',
	'note_repair'=>'<p><b>ВАЖНО</b>:<br><br> В текущей папке на сервере уже установлены скрипты WebAsyst <strong>этой же версии</strong>. Если вы хотели бы переустановить WebAsyst (например, в случае повреждения каких-то файлов), нажмите «Продолжить», .</p> <p><b>Мы настоятельно рекомендуем сделать резервную копию всех файлов и базы данных текущей установки</b>, чтобы вы смогли восстановить данные в случае сбоя.</p>',
	'note_no_vers'=>'<p><b>ВАЖНО</b>:<br><br> В текущей папке на сервере уже установлены скрипты WebAsyst. Номер версии установленных скриптов определить <i>невозможно</i>. Возможные причины:</p> <ol><li>Информация о версии отсутствует в файле wbs.xml.</li><li>Текущая версия была установлена до того, как была введена нумерацию версий.</li></ol><p>В связи с тем, что мы не можем определить номер версии вашей установки, <strong>структура базы данных не будет обновлена</strong>. Это может привести к несовместимости работы структуры вашей базы данных и обновленных скриптов WebAsyst. В случае несовместимовти вам необходимо будет самостоятельно обновить структуру базы данных.</p> <p><b>Мы настоятельно рекомендуем сделать резервную копию всех файлов и базы данных вашей текущей установки WebAsyst</b> сейчас, чтобы вы смогли восстановить данные в случае сбоя.</b></p><p>Нажмите «Продолжить», чтобы перейти к обновлению скриптов.</p>  ',
	'note_inst'=>'<p><b>ВАЖНО</b>: В текущей папке на сервере уже установлены скрипты WebAsyst.</p>',
	'curr_wa_ver'=>'Ваша текущая версия WebAsyst:',
	'install_wa_ver'=>'Будет установлена новая версия:',
	'upd_db_list_label'=>'В процессе установки обновления будут обновлены мета-данные следующих баз данных:',
	'upd_db_count'=>'Обновлено баз данных: %s',
	'upd_db_full_list_link'=>'показать полный список обновленных баз данных в новом окне',
	'upd_meta_info'=>'Посмотреть скрипты обновления базы данных %s',
	'backup_notice'=>'Мы настоятельно рекомендуем сделать резервную копию всех файлов и базы данных текущей установки, чтобы вы cмогли восстановить данные в случае сбоя.',
	'upgrade_db_btn_desc'=>'Нажмите «Продолжить» для начала установки.',
	'no_db_meta'=>'Обновление базы данных не требуется.',
	'upd_not_actual'=>'В текущей папке на сервере уже установлены скрипты WebAsyst <strong>более новой версии</strong>, чем вы устанавливаете.',
	'inst_ver'=>'Будет установлена новая версия:',
	'inst_canceled'=>'Установка отменена',
	'sys_req_list'=>'Системные требования',
	'php_version'=>'PHP 5.0.5 и выше:',
	'php_safemode'=>'PHP safemode должен быть выключен',
	'php_ext'=>'Необходимые расширения PHP:',
	'php_ext_mb_string'=>' — mbstring',
	'php_ext_mysql'=>' — mysql',
	'php_ext_domxml'=>' — DOM XML',
	'php_ext_simplexml'=>' — SimpleXML',
	'php_ext_gd'=>' — GD',
	'php_ext_warning'=>'Предупреждение:',
	'php_ext_gd_warning'=>'Функции автоматического уменьшения изображений будут отключены.',
	'php_ext_zlib'=>' — Zlib (не обязателен)',
	'php_ext_zlib_warning'=>'Функции создания и чтения zip-архивов будут отключены.',
	'php_ext_gettext'=>' — gettext (не обязателен)',
	'php_ext_gettext_warning'=>'Поддержка многоязычности будет работать не в полном объеме',
	'mysql_version'=>'MySQL 4.1 или выше',
	'java_enabled'=>'JavaScript включен в настройках браузера:',
	'write_rights'=>'Для директории, в которую установен WebAsyst, включены права на запись:',
	'note'=>'Замечание:',
	'write_rights_note'=>'В UNIX-системах необходимое значение прав на запись обычно 775.',
	'write_rights_note2'=>'По этому вопросу вы можете проконсультироваться в службе поддержки вашего хостинг-провайдера и в инструкциях по установке WebAsyst.',
	'view_php_info'=>'Посмотреть phpinfo',
	'view_new_window'=>'В новом окне.',
	'sys_req_sat'=>'Сервер полностью удовлетворяет системным требованиям WebAsyst.',
	'sys_req_not_sat'=>'Сервер не удовлетворяет системным требованиям WebAsyst.',
	'inst_aborted_cap'=>'УСТАНОВКА ПРЕРВАНА',
	'license_label'=>'Лицензионное Соглашение',
	'license_accept'=>'Я ознакомился с условиями Лицензионного Соглашения WebAsyst и полностью их принимаю.',
	'inst_suc_comp'=>'Установка успешно завершена.',
	'press_button_to_login_wa'=>'Вход в WebAsyst',
	'admin'=>'Администратор',
	'install_canceled'=>'Установка отменена',
	'resume'=>'Продолжить',
	'step'=>'Шаг',
	'wa_inst_wizard'=>'Мастер установки WebAsyst',
	'check_sys_req'=>'Проверка соответствия системным требованиям',
	'search_installed'=>'Поиск установленных компонентов',
	'extracting_files'=>'Распаковка архива со скриптами',
	'welcome'=>'Мастер установки WebAsyst',
	'wa_inst_guide'=>'Инструкции по установке WebAsyst',
	'wa_installer'=>'WebAsyst Installer',
	'extracting_auto'=>'Распаковка файлов...',
	'extracting_manual'=>'
<strong>Вам необходимо продолжить установку вручную</strong>:
<ol>
<li>На вашем компьютере перейдите в папку, в которую вы распаковали архив со скриптами
WebAsyst;</li>
<li><i>Полностью распакуйте архив wbs.tgz в корневой папке</i> (обычно это производится сервером
автоматически, но на вашей конфигурации этого сделать не удалось); в корневой папке должны
появиться директории published/, installer/, kernel/ и другие;</li>
<li>Загрузите <i>все новое содержимое корневой папки</i> на сервер по FTP (уже загруженные
файлы можно не перезаписывать);</li>
<li>Установите права на запись для папок:
<ul>
<li>published/</li>
<li>kernel/</li>
<li>kernel/includes/</li>
<li>kernel/includes/smarty/</li>
<li>temp/</li>
</ul>
</li>
<li>Нажмите «Продолжить» ниже — система проверит правильность произведенных действий, и вы
сможете продолжить.</li>
</ol>',
	'extracting_auto_name'=>'<font style="font-size:120%;font-weight:bolder;">Автоматическая установка</font>',
	'extracting_auto_description'=>'<p>Архив со скриптами приложений WebAsyst (wbs.tgz) будет распакован автоматически. Обычно это занимает 2—5 минут.</p>',
	'extracting_manual_name'=>'<span style="font-size:120%;font-weight:bolder;">Ручная установка</span>',
	'extracting_manual_description'=>'<p>Ручная установка может потребоваться, если автоматическая не сработала по причине системных ограничений, действующих на сервере.<p>Необходимо вручную распаковать архив wbs.tgz, закачать распакованные файлы по FTP на сервер и установить права на запись для некоторых папок.</p>',
	'extracting_more'=>'Если автоматическая установка не сработала, попробуйте <a %s>альтернативный вариант</a> (распаковку по частям)',
	'extracting_no_auto'=>'<p>Установка, в ходе которой архив со скриптами распаковывается автоматически, невозможна
на этом сервере в связи с действующими системными ограничениями.</p>
<p>На этом сервере возможна только ручная установка скриптов.</p>',
	'please_try_again'=>'Сервер не отвечает, попробуйте запустить установку с самого начала (установка будет продолжена с момента остановки)',
//	''=>'',
	
	'installer'=>'Installer',
	
	'force_chmod'=>'Устанавливать права (chmod) на все распакованные файлы',
	'force_chmod_description'=>'(используйте принудительную установку прав только если права, по умолчанию устанавливаемые сервером на новые файлы, не позволяют читать эти файлы)',

	
	'step1'=>'Лицензия',
	'step2'=>'Системные настройки',
	'step3'=>'Установка скриптов',
	'step4'=>'MySQL',
	'step5'=>'Готово!',
	'step3_title'=>'Распаковка скриптов WebAsyst',

	)
	);

	if(isset($locStrings[$lang])){
		$locStrings=$locStrings[$lang];
	}else{
		$locStrings=$locStrings['eng'];
	}
	return $locStrings;
}
?>