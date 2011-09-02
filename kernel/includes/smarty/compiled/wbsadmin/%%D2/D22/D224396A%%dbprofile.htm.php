<?php /* Smarty version 2.6.26, created on 2011-09-02 13:22:06
         compiled from dbprofile.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'nl2br', 'dbprofile.htm', 31, false),array('modifier', 'translate', 'dbprofile.htm', 211, false),array('modifier', 'htmlsafe', 'dbprofile.htm', 296, false),array('modifier', 'cat', 'dbprofile.htm', 298, false),array('function', 'html_options', 'dbprofile.htm', 62, false),array('function', 'conditionalOutput', 'dbprofile.htm', 80, false),array('function', 'switchedOutput', 'dbprofile.htm', 87, false),)), $this); ?>
<!-- dbprofile.html -->
<?php echo '<div class="i-col-container"><div class="i-col70"><div style="padding-right: 20px;"><form action="'; 
 echo $this->_tpl_vars['formLink']; 
 echo '" method="post" enctype="multipart/form-data" name="cform">'; 
 $this->assign('invalidFieldMarkup', ' style="color: #FF0000;" '); 
 echo '<h2 class="page-title">'; 
 if ($this->_tpl_vars['multiDBKEY']): 
 echo '<a href="'; 
 echo @PAGE_DB_DBLIST; 
 echo '">'; 
 echo $this->_tpl_vars['waStrings']['dbl_page_names']; 
 echo '</a> &raquo; '; 
 endif; 
 echo ''; 
 echo $this->_tpl_vars['pageHeader']; 
 echo '</h2>'; 
 if ($this->_tpl_vars['action'] == 'edit'): 
 echo ''; 
 if ($this->_tpl_vars['hostData']['DBSETTINGS']['STATUS'] == 'DELETED'): 
 echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" class="settings-table"><tr><td><input type="submit" name="restorebtn" value="'; 
 echo $this->_tpl_vars['waStrings']['btn_restore']; 
 echo '">&nbsp; <input type="submit" name="removebtn" value="'; 
 echo $this->_tpl_vars['waStrings']['btn_remove']; 
 echo '" onClick="return confirm( \''; 
 echo $this->_tpl_vars['waStrings']['dbmgm_query_fdelete']; 
 echo '\' )"></td></tr><tr><td>&nbsp;</td></tr></table>'; 
 endif; 
 echo ''; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['errorStr']): 
 echo '<table cellspacing="0" cellpadding="0" border="0"><tr><td width="1">&nbsp;</td><td'; 
 echo $this->_tpl_vars['invalidFieldMarkup']; 
 echo '>'; 
 echo ((is_array($_tmp=$this->_tpl_vars['errorStr'])) ? $this->_run_mod_handler('nl2br', true, $_tmp) : smarty_modifier_nl2br($_tmp)); 
 echo '</td></tr><tr><td colspan=2>&nbsp;</td></tr></table>'; 
 endif; 
 echo ''; 
 if (! $this->_tpl_vars['fatalError']): 
 echo ''; 
 if (! $this->_tpl_vars['profileCreated']): 
 echo ''; 
 $this->assign('status', $this->_tpl_vars['hostData']['DBSETTINGS']['STATUS']); 
 echo '<table border="0" cellpadding="5" cellspacing="0" class="settings-table">'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<tr><td colspan="3" class="form-required-field">'; 
 echo $this->_tpl_vars['waStrings']['forms_req_fields']; 
 echo '</td></tr><tr><td colspan="3">&nbsp;</td></tr>'; 
 endif; 
 echo '<tr style="background-color: #eee;"><td>&nbsp;</td><td colspan="2">&nbsp;</td></tr><tr style="background-color: #eee;"><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr1_opt1']; 
 echo ':</td><td colspan="2">'; 
 if ($this->_tpl_vars['action'] == 'new'): 
 echo '<select name="hostData[DBSETTINGS][SQLSERVER]" class="control" onChange="this.form.submit()">'; 
 echo smarty_function_html_options(array('values' => $this->_tpl_vars['serverNames'],'selected' => $this->_tpl_vars['hostData']['DBSETTINGS']['SQLSERVER'],'output' => $this->_tpl_vars['serverNames']), $this);
 echo '</select><input type="hidden" value="'; 
 echo $this->_tpl_vars['prevServerName']; 
 echo '" name="prevServerName">'; 
 else: 
 echo '<strong>'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['SQLSERVER']; 
 echo '</strong><input type="hidden" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['SQLSERVER']; 
 echo '" name="hostData[DBSETTINGS][SQLSERVER]">'; 
 endif; 
 echo '<input type=hidden name=hasAdminRights value='; 
 echo $this->_tpl_vars['hasAdminRights']; 
 echo '></td></tr><tr style="background-color: #eee;"><td colspan="3">&nbsp;</td></tr>'; 
 if ($this->_tpl_vars['action'] == 'new'): 
 echo '<tr style="background-color: #eee;"><td align="left" valign=top>'; 
 if ($this->_tpl_vars['hasAdminRights']): 
 echo ''; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[DB_CREATE_OPTIONS][CREATE_OPTION]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo ''; 
 endif; 
 echo ''; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_sel1']; 
 echo ':</td><td colspan="2"><!-- Show this option only if server has administrative rights -->'; 
 if ($this->_tpl_vars['hasAdminRights']): 
 echo '<input type="radio" name="hostData[DB_CREATE_OPTIONS][CREATE_OPTION]" value="new"'; 
 echo smarty_function_switchedOutput(array('str1' => 'checked','str2' => "",'val' => $this->_tpl_vars['hostData']['DB_CREATE_OPTIONS']['CREATE_OPTION'],'true_val' => 'new'), $this);
 echo '>'; 
 else: 
 echo '<!-- Show notification otherwise -->'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_war1']; 
 echo ''; 
 endif; 
 echo '</td></tr style="background-color: #eee;">'; 
 if ($this->_tpl_vars['hasAdminRights']): 
 echo '<tr><td align="left">&nbsp;</td><td class="comment"><p class=comment>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_desc1']; 
 echo '</p><p class=comment>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_desc2']; 
 echo ':</p></td></tr><tr><td colspan="2" class="comment">&nbsp;</td></tr><tr><td>&nbsp;</td><td><!-- Database user name and password for create new option --><table cellpadding=0 cellspacing=2 border=0><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[DB_CREATE_OPTIONS][DATABASE_USER_NEW]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_username']; 
 echo ':&nbsp;*&nbsp;</td><td><input name="hostData[DB_CREATE_OPTIONS][DATABASE_USER_NEW]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DB_CREATE_OPTIONS']['DATABASE_USER_NEW']; 
 echo '" style="width: 150px"></td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[DB_CREATE_OPTIONS][PASSWORD_NEW]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_pwd']; 
 echo ':&nbsp;*&nbsp;</td><td><input name="hostData[DB_CREATE_OPTIONS][PASSWORD_NEW]" type="text" class="control" style="width: 150px" value="'; 
 echo $this->_tpl_vars['hostData']['DB_CREATE_OPTIONS']['PASSWORD_NEW']; 
 echo '"></td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[DB_CREATE_OPTIONS][DATABASE_NEW]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_dbname']; 
 echo ':&nbsp;*&nbsp;</td><td><input name="hostData[DB_CREATE_OPTIONS][DATABASE_NEW]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DB_CREATE_OPTIONS']['DATABASE_NEW']; 
 echo '" style="width: 150px"></td></tr><tr><td class=comment>&nbsp;</td><td class=comment>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_desc3']; 
 echo '</td></tr></table></td></tr>'; 
 endif; 
 echo '<tr style="background-color: #eee;"><td colspan="3" class="comment">&nbsp;</td></tr><tr style="background-color: #eee;"><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[DB_CREATE_OPTIONS][CREATE_OPTION]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_sel2']; 
 echo ':</td><td colspan="2"><input type="radio" name="hostData[DB_CREATE_OPTIONS][CREATE_OPTION]" value="use" '; 
 echo smarty_function_switchedOutput(array('str1' => 'checked','str2' => "",'val' => $this->_tpl_vars['hostData']['DB_CREATE_OPTIONS']['CREATE_OPTION'],'true_val' => 'use'), $this);
 echo '></td></tr><tr><td>&nbsp;</td><td colspan="2" class="comment"><p class="comment">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_desc4']; 
 echo '</p><p class="comment">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_desc5']; 
 echo ':</p></td></tr><tr><td colspan="2" class="comment">&nbsp;</td></tr><tr><td>&nbsp;</td><td><!-- Database user name and password for use existing option --><table cellpadding=0 cellspacing=2 border=0><tr><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_username']; 
 echo ':&nbsp;</td><td><input name="hostData[DB_CREATE_OPTIONS][DATABASE_USER_EXISTING]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DB_CREATE_OPTIONS']['DATABASE_USER_EXISTING']; 
 echo '" ></td></tr><tr><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_pwd']; 
 echo ':&nbsp;</td><td><input name="hostData[DB_CREATE_OPTIONS][PASSWORD_EXISTING]" type="text" class="control"  value="'; 
 echo $this->_tpl_vars['hostData']['DB_CREATE_OPTIONS']['PASSWORD_EXISTING']; 
 echo '"></td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[DB_CREATE_OPTIONS][DATABASE_EXISTING]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_dbname']; 
 echo ':&nbsp;*&nbsp;</td><td><input name="hostData[DB_CREATE_OPTIONS][DATABASE_EXISTING]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DB_CREATE_OPTIONS']['DATABASE_EXISTING']; 
 echo '" ></td></tr></table></td></tr><tr><td>&nbsp;</td><td colspan="2">&nbsp;</td></tr>'; 
 else: 
 echo '<!-- Modify database profile mode --><tr style="background-color: #eee;"><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_username']; 
 echo ':</td><td colspan="2"><input name="hostData[DBSETTINGS][DB_USER]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['DB_USER']; 
 echo '" style="width: 50%"></tr><tr style="background-color: #eee;"><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_pwd']; 
 echo ':</td><td colspan="2"><input name="hostData[DBSETTINGS][DB_PASSWORD]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['DB_PASSWORD']; 
 echo '" style="width: 50%"></tr><tr style="background-color: #eee;"><td>&nbsp;</td><td colspan="2">&nbsp;</td></tr><tr style="background-color: #eee;"><td>&nbsp;</td><td colspan=2 class=comment>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_desc6']; 
 echo '</td></tr><tr style="background-color: #eee;"><td>&nbsp;</td><td colspan="2">&nbsp;</td></tr><tr style="background-color: #eee;"><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr2_dbname']; 
 echo ':</td><td colspan="2"><input type="text" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['DB_NAME']; 
 echo '" class="control" name="hostData[DBSETTINGS][DB_NAME]"></td></tr><!-- NEW OPTION -->'; 
 if (false && ! $this->_tpl_vars['hostData']['DBSETTINGS']['FIRSTLOGIN'] && ! $this->_tpl_vars['hostData']['DBSETTINGS']['CREATE_DATE'] && ( $this->_tpl_vars['hostData']['DBSETTINGS']['DB_CREATE_OPTION'] == 'new' || $this->_tpl_vars['hasAdminRights'] )): 
 echo '<tr style="background-color: #eee;"><td>&nbsp;</td><td><input id="use_new" type="radio" name="hostData[DBSETTINGS][DB_CREATE_OPTION]" value="new"'; 
 echo smarty_function_switchedOutput(array('str1' => 'checked','str2' => "",'val' => $this->_tpl_vars['hostData']['DBSETTINGS']['DB_CREATE_OPTION'],'true_val' => 'new'), $this);
 echo '></td><td><label for="use_new">'; 
 echo ((is_array($_tmp='dbmgm_gr2_sel1')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</label></td></tr><tr style="background-color: #eee;"><td>&nbsp;</td><td><input id="use_exist" type="radio" name="hostData[DBSETTINGS][DB_CREATE_OPTION]" value="use" '; 
 echo smarty_function_switchedOutput(array('str1' => 'checked','str2' => "",'val' => $this->_tpl_vars['hostData']['DBSETTINGS']['DB_CREATE_OPTION'],'true_val' => 'use'), $this);
 echo '></td><td><label for="use_exist">'; 
 echo ((is_array($_tmp='dbmgm_gr2_sel2')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</label></td></tr>'; 
 endif; 
 echo '<tr style="background-color: #eee;"><td>&nbsp;</td><td colspan="2">&nbsp;</td></tr>'; 
 endif; 
 echo '<tr><td colspan="3"><div class="formSection" >'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr4_admin']; 
 echo '</div></td></tr><tr><td colspan="3">&nbsp;</td></tr>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<tr><td colspan="3" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr4_desc5']; 
 echo '</td></tr>'; 
 endif; 
 echo '<tr><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr4_lang']; 
 echo ':</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<select name="hostData[ADMINISTRATOR][LANGUAGE]" class="control">'; 
 echo smarty_function_html_options(array('values' => $this->_tpl_vars['language_ids'],'selected' => $this->_tpl_vars['hostData']['ADMINISTRATOR']['LANGUAGE'],'output' => $this->_tpl_vars['language_names']), $this);
 echo '</select>'; 
 else: 
 echo ''; 
 $this->assign('adminLang', $this->_tpl_vars['hostData']['ADMINISTRATOR']['LANGUAGE']); 
 echo ''; 
 echo $this->_tpl_vars['language_names_indexed'][$this->_tpl_vars['adminLang']]; 
 echo ''; 
 endif; 
 echo '</td></tr>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[ADMINISTRATOR][PASSWORD1]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo ((is_array($_tmp=$this->_tpl_vars['waStrings']['49'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ':&nbsp;*</td><td colspan="2"><div style="padding: 5px 0 5px 0;"><strong>ADMINISTRATOR</strong></div></td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[ADMINISTRATOR][PASSWORD1]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr4_pwd']; 
 echo ':&nbsp;*</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '><input name="hostData[ADMINISTRATOR][PASSWORD1]" type="text" class="control" size="18" maxlength="50" value="'; 
 echo $this->_tpl_vars['hostData']['ADMINISTRATOR']['PASSWORD1']; 
 echo '" style="font-weight: bold;"></td></tr><tr><td>&nbsp;</td><td colspan="2" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr4_desc6']; 
 echo '</td>'; 
 endif; 
 echo '<tr><td colspan="3">&nbsp;</td></tr><tr><td colspan="3"><div class="formSection" >'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr5_name']; 
 echo '</div></td></tr><tr><td colspan="3">&nbsp;</td></tr>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<tr><td colspan="3" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr5_desc1']; 
 echo '</td></tr><tr><td>&nbsp;</td><td colspan="2"></td></tr>'; 
 endif; 
 echo '<tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[FIRSTLOGIN][LOGINNAME]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr5_login']; 
 echo ':&nbsp;*</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[FIRSTLOGIN][LOGINNAME]" type="text" class="control" style="font-weight: bold;" value="'; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['LOGINNAME'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); 
 echo '" maxlength="20">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['LOGINNAME'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[FIRSTLOGIN][PASSWORD1]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr5_pwd']; 
 echo ':&nbsp;*</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '><input name="hostData[FIRSTLOGIN][PASSWORD1]" type="text" class="control" style="font-weight: bold;" maxlength="50" value="'; 
 echo $this->_tpl_vars['hostData']['FIRSTLOGIN']['PASSWORD1']; 
 echo '"></td></tr><tr><td colspan="2">&nbsp;</td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[FIRSTLOGIN][EMAIL]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr5_email']; 
 echo ':&nbsp;*</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[FIRSTLOGIN][EMAIL]" type="text" class="control" value="'; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['EMAIL'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); 
 echo '" maxlength="50">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['EMAIL'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[FIRSTLOGIN][FIRSTNAME]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr5_firstname']; 
 echo ':&nbsp;*</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[FIRSTLOGIN][FIRSTNAME]" type="text" class="control" value="'; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['FIRSTNAME'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); 
 echo '" maxlength="20">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['FIRSTNAME'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[FIRSTLOGIN][LASTNAME]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr5_lastname']; 
 echo ':&nbsp;*</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[FIRSTLOGIN][LASTNAME]" type="text" class="control" value="'; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['LASTNAME'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); 
 echo '" maxlength="24">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['LASTNAME'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[FIRSTLOGIN][COMPANYNAME]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr5_company']; 
 echo ':&nbsp;*</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[FIRSTLOGIN][COMPANYNAME]" type="text" class="control" value="'; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['COMPANYNAME'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); 
 echo '" maxlength="30">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['FIRSTLOGIN']['COMPANYNAME'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr><tr><td></td><td colspan="2" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr5_desc2']; 
 echo '</td>'; 
 endif; 
 echo '<tr><td colspan=3>&nbsp;</td></tr><tr><td colspan=3><div class="formSection" >'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr6_name']; 
 echo '</div></td></tr><tr><td colspan="3">&nbsp;</td></tr><tr><td colspan=3 class="comment"></td></tr><tr><td>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input type="checkbox" value="1" name="smsCheckbox" '; 
 echo smarty_function_switchedOutput(array('str1' => 'checked','str2' => "",'val' => $this->_tpl_vars['smsEnabled'],'true_val' => 1), $this);
 echo ' id="smsCheckbox"> <label for="smsCheckbox">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr6_opt1']; 
 echo '</label>'; 
 endif; 
 echo '</td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'smsModule','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr6_opt2']; 
 echo ':</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo ''; 
 if ($this->_tpl_vars['smsCount'] != 0): 
 echo '<select name="smsModule" class="control"><option label="<select>" value="">&lt;select&gt;</option>'; 
 $_from = $this->_tpl_vars['smsModules']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['mId'] => $this->_tpl_vars['mDefault']):

 echo '<option label="'; 
 echo $this->_tpl_vars['mId']; 
 echo '" value="'; 
 echo $this->_tpl_vars['mId']; 
 echo '" '; 
 if ($this->_tpl_vars['mDefault'] == 1): 
 echo 'selected="selected"'; 
 endif; 
 echo '>'; 
 echo $this->_tpl_vars['mId']; 
 echo '</option>'; 
 endforeach; endif; unset($_from); 
 echo '</select>'; 
 else: 
 echo '<b>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr6_war1']; 
 echo '</b> <input type="hidden" name="smsModule" value="">'; 
 endif; 
 echo ''; 
 else: 
 echo ''; 
 $_from = $this->_tpl_vars['smsModules']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['mId'] => $this->_tpl_vars['mDefault']):

 echo ''; 
 if ($this->_tpl_vars['mDefault'] == 1): 
 echo ''; 
 echo $this->_tpl_vars['mId']; 
 echo ''; 
 endif; 
 echo ''; 
 endforeach; endif; unset($_from); 
 echo ''; 
 endif; 
 echo '</td></tr><tr><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr6_optlim']; 
 echo ':</td><td colspan="2" class="comment" >'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[DBSETTINGS][SMS_RECIPIENTS_LIMIT]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['SMS_RECIPIENTS_LIMIT']; 
 echo '" size="10">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['DBSETTINGS']['SMS_RECIPIENTS_LIMIT'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[BALANCE][sms][VALUE]" type="hidden" value="'; 
 echo $this->_tpl_vars['hostData']['BALANCE']['sms']['VALUE']; 
 echo '" size="10">'; 
 endif; 
 echo '</td></tr><tr><td>&nbsp;</td><td colspan="2" class="comment" >'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr6_desc1']; 
 echo '</td></tr>'; 
 echo '<tr><td colspan=3>&nbsp;</td></tr><tr><td colspan=3><div class="formSection" >'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr7_name']; 
 echo '</div></td></tr><tr><td colspan="3">&nbsp;</td></tr>'; 
 if ($this->_tpl_vars['status'] != 'DELETED' && $this->_tpl_vars['action'] == 'new'): 
 echo '<tr><td colspan=3 class="comment">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr7_desc1']; 
 echo '</td></tr>'; 
 endif; 
 echo '<tr><td colspan=3> <table border="0" cellspacing="2" cellpadding="0">'; 
 $_from = $this->_tpl_vars['app_data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['app_id'] => $this->_tpl_vars['var']):

 echo ''; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<tr>'; 
 if ($this->_tpl_vars['status'] != 'DELETED' && ( $this->_tpl_vars['action'] == 'new' || ! $this->_tpl_vars['var']['CHECKED'] || $this->_tpl_vars['multiDBKEY'] )): 
 echo '<td><input type="checkbox" value="1" id="app_'; 
 echo $this->_tpl_vars['app_id']; 
 echo '"onClick="appSelected(this, new Array( '; 
 echo $this->_tpl_vars['var']['PARENTS_JS']; 
 echo ' ), new Array( '; 
 echo $this->_tpl_vars['var']['DEPENDENT_JS']; 
 echo ' ) )"	name="app_list['; 
 echo $this->_tpl_vars['app_id']; 
 echo ']" '; 
 echo smarty_function_switchedOutput(array('str1' => 'checked','str2' => "",'val' => $this->_tpl_vars['var']['CHECKED'],'true_val' => 1), $this);
 echo '></td>'; 
 elseif ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<td><input type="hidden" value="1" name="app_list['; 
 echo $this->_tpl_vars['app_id']; 
 echo ']"><img src="../../../common/html/classic/images/checked.gif"></td>'; 
 endif; 
 echo '<td><label for="app_'; 
 echo $this->_tpl_vars['app_id']; 
 echo '">'; 
 echo $this->_tpl_vars['var']['APPLICATION']['LOCAL_NAME']; 
 echo '</label></td></tr>'; 
 endif; 
 echo ''; 
 endforeach; endif; unset($_from); 
 echo '</table></td></tr><tr><td colspan=3>&nbsp;</td></tr><!--  --><tr><td colspan="3"><div class="formSection">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_name']; 
 echo '</div></td></tr><tr><td colspan="3">&nbsp;</td></tr>'; 
 if ($this->_tpl_vars['action'] == 'new'): 
 echo '<tr><td width="78"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[DBSETTINGS][DB_KEY]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_dbkey']; 
 echo ':</td><td width="242" colspan="2"><input name="hostData[DBSETTINGS][DB_KEY]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['DB_KEY']; 
 echo '" size="10" maxlength="12"></td></tr><tr><td>&nbsp;</td><td colspan="2" class="comment"><p class="comment">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_alias']; 
 echo '</p><p class="comment"><b>NOTE</b>: '; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_desc1']; 
 echo '</p></td></tr>'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['multiDBKEY']): 
 echo '<tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[DBSETTINGS][EXPIRE_DATE]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_expdate']; 
 echo ': </td><td colspan="2" valign="middle" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[DBSETTINGS][EXPIRE_DATE]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['EXPIRE_DATE']; 
 echo '" size="10">&nbsp;<span class="comment">(MM/DD/YYYY)</span>'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['DBSETTINGS']['EXPIRE_DATE'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr><tr><td>&nbsp;</td><td colspan="2" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_desc2']; 
 echo '</td></tr>'; 
 if ($this->_tpl_vars['action'] == 'edit' && $this->_tpl_vars['multiDBKEY']): 
 echo '<tr><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_ro']; 
 echo ':</td><td colspan="2">'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[DBSETTINGS][READONLY]" type="checkbox" value="1" '; 
 echo smarty_function_switchedOutput(array('val' => $this->_tpl_vars['hostData']['DBSETTINGS']['READONLY'],'true_val' => 1,'str1' => 'checked'), $this);
 echo '>'; 
 elseif ($this->_tpl_vars['hostData']['DBSETTINGS']['READONLY']): 
 echo '<img src="../../../common/html/classic/images/checked.gif">'; 
 endif; 
 echo '</td></tr>'; 
 endif; 
 echo ''; 
 else: 
 echo '<input name="hostData[DBSETTINGS][READONLY]" type="hidden" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['READONLY']; 
 echo '"><input name="hostData[DBSETTINGS][EXPIRE_DATE]" type="hidden" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['EXPIRE_DATE']; 
 echo '">'; 
 endif; 
 echo ''; 
 if (false): 
 echo '<tr><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_userlimit']; 
 echo ':</td><td colspan="2" class="comment" >'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[DBSETTINGS][MAX_USER_COUNT]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['MAX_USER_COUNT']; 
 echo '" size="10">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['DBSETTINGS']['MAX_USER_COUNT'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<tr><td>&nbsp;</td><td colspan="2" class="comment" >'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_usertotallimit']; 
 echo '</td></tr>'; 
 endif; 
 echo '<tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => "hostData[DBSETTINGS][DBSIZE_LIMIT]",'text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_dblimit']; 
 echo ':</td><td colspan="2" '; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo 'class="readonlyControl"'; 
 endif; 
 echo '>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[DBSETTINGS][DBSIZE_LIMIT]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['DBSIZE_LIMIT']; 
 echo '" size="10">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['DBSETTINGS']['DBSIZE_LIMIT'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<tr><td>&nbsp;</td><td colspan="2" class="comment" >'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_desc3']; 
 echo '</td></tr><tr><td colspan=3>&nbsp;</td></tr>'; 
 endif; 
 echo ''; 
 else: 
 echo '<tr><td colspan=3>&nbsp;<input name="hostData[DBSETTINGS][DBSIZE_LIMIT]" type="hidden" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['DBSIZE_LIMIT']; 
 echo '"><input name="hostData[DBSETTINGS][MAX_USER_COUNT]" type="hidden" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['MAX_USER_COUNT']; 
 echo '">'; 
 endif; 
 echo '</td></tr>'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<tr><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_dformat']; 
 echo ':</td><td colspan="2" class="comment" ><select name="hostData[DBSETTINGS][DATE_FORMAT]" class="control">'; 
 echo smarty_function_html_options(array('values' => $this->_tpl_vars['dateFormat_ids'],'selected' => $this->_tpl_vars['hostData']['DBSETTINGS']['DATE_FORMAT'],'output' => $this->_tpl_vars['dateFormat_names']), $this);
 echo '</select></td></tr><tr><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_reclimit']; 
 echo ':</td><td colspan="2" class="comment" >'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[DBSETTINGS][RECIPIENTS_LIMIT]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['RECIPIENTS_LIMIT']; 
 echo '" size="10">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['DBSETTINGS']['RECIPIENTS_LIMIT'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr><tr><td>&nbsp;</td><td colspan="2" class="comment" >'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_reclimbatch']; 
 echo '</td></tr><tr><td>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_defencoding']; 
 echo ': </td><td colspan="2" class="comment" >'; 
 if ($this->_tpl_vars['status'] != 'DELETED'): 
 echo '<input name="hostData[DBSETTINGS][DEFAULT_ENCODING]" type="text" class="control" value="'; 
 echo $this->_tpl_vars['hostData']['DBSETTINGS']['DEFAULT_ENCODING']; 
 echo '" size="10">'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp=$this->_tpl_vars['hostData']['DBSETTINGS']['DEFAULT_ENCODING'])) ? $this->_run_mod_handler('cat', true, $_tmp, "&nbsp;") : smarty_modifier_cat($_tmp, "&nbsp;")); 
 echo ''; 
 endif; 
 echo '</td></tr><tr><td>&nbsp;</td><td colspan="2" class="comment" >'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_gr3_desc4']; 
 echo '</td></tr>'; 
 endif; 
 echo '<tr><td colspan="3">&nbsp;</td></tr><!--   --><tr><td colspan=2>'; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo '<input type="submit" name="cancelbtn" value="'; 
 echo $this->_tpl_vars['waStrings']['btn_ok']; 
 echo '">'; 
 else: 
 echo '<input type="submit" name="savebtn" value="'; 
 echo $this->_tpl_vars['buttonCaption']; 
 echo '">'; 
 endif; 
 echo '</td><td align="right">'; 
 if ($this->_tpl_vars['hostData']['DBSETTINGS']['STATUS'] != 'DELETED' && $this->_tpl_vars['action'] != 'new'): 
 echo '<input type="submit" name="deletebtn" value="'; 
 echo $this->_tpl_vars['waStrings']['btn_del_db']; 
 echo '" onClick="return confirm( \''; 
 echo $this->_tpl_vars['waStrings']['dbmgm_query_delete']; 
 echo '\' )">'; 
 endif; 
 echo '</td></tr></table>'; 
 else: 
 echo '<p>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_lbl_succes1']; 
 echo '<b>'; 
 echo ((is_array($_tmp=((is_array($_tmp=' ')) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['DB_KEY']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['DB_KEY'])))) ? $this->_run_mod_handler('cat', true, $_tmp, ' ') : smarty_modifier_cat($_tmp, ' ')); 
 echo '</b>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_lbl_succes2']; 
 echo '</p><p>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_lbl_link']; 
 echo ': <a href="#" name=loginURL target="_blank"><script type="text/javascript">makeLinkURL( 4, \''; 
 echo $this->_tpl_vars['loginURL']; 
 echo '\', \'loginURL\', true );</script></a></p><p><input type="submit" name="cancelbtn" value="'; 
 echo $this->_tpl_vars['waStrings']['btn_ok']; 
 echo '"></p>'; 
 endif; 
 echo '<p>'; 
 else: 
 echo ''; 
 if (! $this->_tpl_vars['noServerFound']): 
 echo '<input type="submit" name="cancelbtn" value="'; 
 echo $this->_tpl_vars['waStrings']['btn_ok']; 
 echo '">'; 
 else: 
 echo ''; 
 if ($this->_tpl_vars['action'] == 'edit'): 
 echo '<p>'; 
 echo $this->_tpl_vars['noServerMessage']; 
 echo '</p><p style="width: 500px">'; 
 echo ((is_array($_tmp=$this->_tpl_vars['recoverMessage'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); 
 echo '</p><table cellspacing=0 cellpadding=0 border="1" class="settings-table"><tr><td><input type="submit" name="cancelbtn" value="'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_nav_ret']; 
 echo '"></td><td align=right>'; 
 if ($this->_tpl_vars['hostData']['DBSETTINGS']['STATUS'] != 'DELETED' && $this->_tpl_vars['action'] != 'new'): 
 echo '<input type="submit" name="removeprofilebtn" value="'; 
 echo $this->_tpl_vars['waStrings']['btn_del_db']; 
 echo '" onClick="return confirm( \''; 
 echo $this->_tpl_vars['waStrings']['dbmgm_query_fdelete']; 
 echo '\' )">'; 
 endif; 
 echo '</td></tr></table>'; 
 else: 
 echo '<p>'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_lbl_war']; 
 echo '</p><input type="submit" name="cancelbtn" value="'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_nav_ret']; 
 echo '">'; 
 endif; 
 echo '<input type=hidden name=noServerFound value=1><input type=hidden name=noServerMessage value="'; 
 echo $this->_tpl_vars['noServerMessage']; 
 echo '">'; 
 endif; 
 echo ''; 
 endif; 
 echo '<input name="sorting" type="hidden" value="'; 
 echo $this->_tpl_vars['sorting']; 
 echo '"> <input name="edited" type="hidden" value="1"><input name="action" type="hidden" value="'; 
 echo $this->_tpl_vars['action']; 
 echo '">'; 
 if ($this->_tpl_vars['action'] == 'edit'): 
 echo '<input name="DB_KEY" type="hidden" value="'; 
 echo $this->_tpl_vars['DB_KEY']; 
 echo '">'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['status'] == 'DELETED'): 
 echo '<input name="deleted" type="hidden" value="1">'; 
 endif; 
 echo '</p><input name="hostData[DBSETTINGS][IP_ADDRESS]" type="hidden" value=""></form></div></div><div class="i-col30">'; 
 if ($this->_tpl_vars['action'] == 'edit' && $this->_tpl_vars['logRecords']): 
 echo '<table border="0" cellpadding="0" cellspacing="0"><tr><td class="pageHeader" colspan="3">'; 
 echo $this->_tpl_vars['waStrings']['dbmgm_lbl_history']; 
 echo '</td></tr>'; 
 $_from = $this->_tpl_vars['logRecords']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['logRecord']):

 echo '<tr style="padding-bottom: 5px"><td class="accountHistoryCell">'; 
 if ($this->_tpl_vars['logRecord']['ROW_URL']): 
 echo '<a href="'; 
 echo $this->_tpl_vars['logRecord']['ROW_URL']; 
 echo '">'; 
 echo $this->_tpl_vars['logRecord']['DATETIME']; 
 echo '</a>'; 
 else: 
 echo ''; 
 echo $this->_tpl_vars['logRecord']['DATETIME']; 
 echo ''; 
 endif; 
 echo '</td><td class="accountHistoryCell">'; 
 if ($this->_tpl_vars['logRecord']['ROW_URL']): 
 echo '<a href="'; 
 echo $this->_tpl_vars['logRecord']['ROW_URL']; 
 echo '">'; 
 echo $this->_tpl_vars['logRecord']['TYPE']; 
 echo '</a>'; 
 else: 
 echo ''; 
 echo $this->_tpl_vars['logRecord']['TYPE']; 
 echo ''; 
 endif; 
 echo '</td><td class="accountHistoryCell">'; 
 if ($this->_tpl_vars['logRecord']['ROW_URL']): 
 echo '<a href="'; 
 echo $this->_tpl_vars['logRecord']['ROW_URL']; 
 echo '">'; 
 echo $this->_tpl_vars['logRecord']['IP']; 
 echo '</a>'; 
 else: 
 echo ''; 
 echo $this->_tpl_vars['logRecord']['IP']; 
 echo ''; 
 endif; 
 echo '</td></tr>'; 
 endforeach; endif; unset($_from); 
 echo '</table>'; 
 endif; 
 echo '</div></div>'; ?>

<script language="JavaScript" type="text/javascript">
function onLoad(){
if(focusControl('<?php echo $this->_tpl_vars['invalidField']; ?>
')) return;
<?php if ($this->_tpl_vars['action'] == 'new'): ?>
if(focusControl('hostData[DBSETTINGS][SQLSERVER]'))return;
<?php endif; ?>
focusControl('hostData[DBSETTINGS][DB_USER]');
};

onLoad();
</script>
<!-- /dbprofile.html -->