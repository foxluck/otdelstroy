<?php /* Smarty version 2.6.26, created on 2011-09-02 13:21:46
         compiled from commonsettings.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'conditionalOutput', 'commonsettings.htm', 33, false),array('function', 'switchedOutput', 'commonsettings.htm', 148, false),array('modifier', 'translate', 'commonsettings.htm', 47, false),)), $this); ?>
<!-- commonsettings.html -->
<?php $this->assign('invalidFieldMarkup', '  style="color: #FF0000;"'); 
 echo '<form action="'; 
 echo $this->_tpl_vars['formLink']; 
 echo '" method="post" enctype="multipart/form-data" name="form"><h2 class="page-title">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_page_title']; 
 echo '</h2><br>'; 
 if ($this->_tpl_vars['errorStr']): 
 echo '<table cellspacing="0" cellpadding="0" border="0"><tr><td'; 
 echo $this->_tpl_vars['invalidFieldMarkup']; 
 echo '>'; 
 echo $this->_tpl_vars['errorStr']; 
 echo '</td></tr></table>'; 
 endif; 
 echo ''; 
 if (! $this->_tpl_vars['fatalError']): 
 echo '<table border="0" cellpadding="5" cellspacing="0" class="settings-table"><tr><td colspan="2" class="form-required-field">'; 
 echo $this->_tpl_vars['waStrings']['forms_req_fields']; 
 echo '</td></tr><tr style="background-color: #eee;"><td colspan="2">&nbsp;</td></tr><tr style="background-color: #eee;"><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'COMPANY','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_gen_company']; 
 echo ':&nbsp;*&nbsp;</td><td><input name="commondata[COMPANY]" value="'; 
 echo $this->_tpl_vars['commondata']['COMPANY']; 
 echo '" type="text" class="big-control"></td></tr><tr style="background-color: #eee;"><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'LICENSE','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_gen_license']; 
 echo ':&nbsp;*&nbsp;</td><td><input name="commondata[LICENSE]" value="'; 
 echo $this->_tpl_vars['commondata']['LICENSE']; 
 echo '" type="text" class="big-control" ></td></tr><tr style="background-color: #eee;" class="comment"><td colspan="2">'; 
 if ($this->_tpl_vars['commondata']['LICENSE']): 
 echo '&nbsp;'; 
 else: 
 echo ''; 
 if ($_GET['register_now']): 
 echo '<span style="color:red;font-weight: bolder;">'; 
 echo ((is_array($_tmp='not_registered_instruction')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</span>'; 
 else: 
 echo ''; 
 echo ((is_array($_tmp='not_registered_description')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ''; 
 endif; 
 echo ''; 
 endif; 
 echo '</td></tr><tr><td colspan="2"><div class="formSection">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_sys_name']; 
 echo '</div></td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'DATA_PATH','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_sys_opt1']; 
 echo ':&nbsp;*&nbsp;</td><td><input name="commondata[DATA_PATH]" value="'; 
 echo $this->_tpl_vars['commondata']['DATA_PATH']; 
 echo '" type="text" class="control"></td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_sys_desc1']; 
 echo '<br>'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_sys_desc2']; 
 echo '<br><b>'; 
 echo $this->_tpl_vars['waStrings']['lbl_form_note']; 
 echo ' </b>: '; 
 echo $this->_tpl_vars['waStrings']['cmn_set_sys_note1']; 
 echo '</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'WBS_INSTALL_PATH','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['wbs_install_path']; 
 echo ':&nbsp;</td><td><input name="commondata[WBS_INSTALL_PATH]" value="'; 
 echo $this->_tpl_vars['commondata']['WBS_INSTALL_PATH']; 
 echo '" type="text" class="control"></td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['wbs_install_path_desc']; 
 echo '</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'MEMLIMIT','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_sys_opt2']; 
 echo ':&nbsp;</td><td width="300"><input name="commondata[MEMLIMIT]" value="'; 
 echo $this->_tpl_vars['commondata']['MEMLIMIT']; 
 echo '" type="text" class="control"></td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_sys_desc3']; 
 echo '<br><b>'; 
 echo $this->_tpl_vars['waStrings']['lbl_form_note']; 
 echo ' </b>: '; 
 echo $this->_tpl_vars['waStrings']['cmn_set_sys_note2']; 
 echo ' '; 
 echo $this->_tpl_vars['defavalmem']; 
 echo ' '; 
 echo $this->_tpl_vars['waStrings']['cmn_set_sys_note3']; 
 echo '</td></tr><tr><td colspan="2"><div class="formSection">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_name']; 
 echo '</div></td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'TIMEOUT','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_opt1']; 
 echo ':&nbsp;*&nbsp;</td><td><input name="commondata[TIMEOUT]" value="'; 
 echo $this->_tpl_vars['commondata']['TIMEOUT']; 
 echo '" type="text" class="control"></td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_desc1']; 
 echo '</td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'PORT','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_opt4']; 
 echo ':&nbsp;*&nbsp;</td><td width="300"><input name="commondata[PORT]" value="'; 
 echo $this->_tpl_vars['commondata']['PORT']; 
 echo '" type="text" class="control"></td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_desc4']; 
 echo '<br><b>'; 
 echo $this->_tpl_vars['waStrings']['lbl_form_note']; 
 echo ' </b>: '; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_note4']; 
 echo '</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td class="nobr">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_opt2']; 
 echo ':&nbsp;</td><td><input type="checkbox" name="commondata[EMAIL]" value="1" '; 
 echo smarty_function_switchedOutput(array('str1' => 'checked','str2' => "",'val' => $this->_tpl_vars['commondata']['EMAIL'],'true_val' => 1), $this);
 echo '></td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_desc2']; 
 echo '</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_opt3']; 
 echo ':&nbsp;</td><td><input name="commondata[ROBOTEMAIL]" value="'; 
 echo $this->_tpl_vars['commondata']['ROBOTEMAIL']; 
 echo '" type="text" class="control"></td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_dsc3']; 
 echo '<br><b>'; 
 echo $this->_tpl_vars['waStrings']['lbl_form_note']; 
 echo ' </b>: '; 
 echo $this->_tpl_vars['waStrings']['cmn_set_web_note3']; 
 echo '						</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><!-- SMTP --><tr><td colspan="2"><div class="formSection">'; 
 echo ((is_array($_tmp='wbs_smtp_settings')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</div></td></tr><tr><td colspan="2" class="comment">'; 
 echo ((is_array($_tmp='wbs_smtp_description')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'SMTP_HOST','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo ((is_array($_tmp='smtp_host')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ':&nbsp;</td><td width="300"><input name="commondata[SMTP_HOST]" value="'; 
 echo $this->_tpl_vars['commondata']['SMTP_HOST']; 
 echo '" type="text" class="control"></td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo ((is_array($_tmp='smtp_host_desc')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'SMTP_PORT','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo ((is_array($_tmp='smtp_port')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ':&nbsp;</td><td width="300"><input name="commondata[SMTP_PORT]" value="'; 
 echo $this->_tpl_vars['commondata']['SMTP_PORT']; 
 echo '" type="text" class="control"></td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'SMTP_USER','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo ((is_array($_tmp='smtp_user')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ':&nbsp;</td><td width="300"><input name="commondata[SMTP_USER]" value="'; 
 echo $this->_tpl_vars['commondata']['SMTP_USER']; 
 echo '" type="text" class="control"></td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'SMTP_PASSWORD','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo ((is_array($_tmp='smtp_password')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ':&nbsp;</td><td width="300"><input name="commondata[SMTP_PASSWORD]" value="'; 
 echo $this->_tpl_vars['commondata']['SMTP_PASSWORD']; 
 echo '" type="text" class="control"></td></tr>'; 
 echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr><!-- proxy --><tr><td colspan="2"><div class="formSection">'; 
 echo $this->_tpl_vars['waStrings']['wbs_proxy_settings']; 
 echo '</div></td></tr><tr><td colspan="2" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['wbs_proxy_description']; 
 echo '</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'PROXY_HOST','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['wbs_proxy_host']; 
 echo ':&nbsp;</td><td width="300"><input name="commondata[PROXY_HOST]" value="'; 
 echo $this->_tpl_vars['commondata']['PROXY_HOST']; 
 echo '" type="text" class="control"></td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'PROXY_PORT','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['wbs_proxy_port']; 
 echo ':&nbsp;</td><td width="300"><input name="commondata[PROXY_PORT]" value="'; 
 echo $this->_tpl_vars['commondata']['PROXY_PORT']; 
 echo '" type="text" class="control"></td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['wbs_proxy_user_desc']; 
 echo '</td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'PROXY_USER','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['wbs_proxy_user']; 
 echo ':&nbsp;</td><td width="300"><input name="commondata[PROXY_USER]" value="'; 
 echo $this->_tpl_vars['commondata']['PROXY_USER']; 
 echo '" type="text" class="control"></td></tr><tr><td class="nobr"'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'PROXY_PASS','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['wbs_proxy_pass']; 
 echo ':&nbsp;</td><td width="300"><input name="commondata[PROXY_PASS]" value="'; 
 echo $this->_tpl_vars['commondata']['PROXY_PASS']; 
 echo '" type="text" class="control"></td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><!-- time zone --><tr><td colspan="2"><div class="formSection">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_time_name']; 
 echo '</div></td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td class="nobr">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_time_opt1']; 
 echo ':&nbsp;</td><td><input type="checkbox" onchange="checkTZ()" name="commondata[SERVER_TZ]" value="1" '; 
 echo smarty_function_switchedOutput(array('str1' => 'checked','str2' => "",'val' => $this->_tpl_vars['commondata']['SERVER_TZ'],'true_val' => 1), $this);
 echo '  id="tzenable"></td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td'; 
 echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'T','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);
 echo '>'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_time_opt2']; 
 echo ':&nbsp;</td><td><select onchange="checkDST()"  name="commondata[SERVER_TIME_ZONE_ID]" class="FormControl" id="timezone">'; 
 $_from = $this->_tpl_vars['timeZones']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['tzLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['tzLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['ID'] => $this->_tpl_vars['tz']):
        $this->_foreach['tzLoop']['iteration']++;

 echo '<option value=\''; 
 echo $this->_tpl_vars['tz']['ID']; 
 echo '\' '; 
 if ($this->_tpl_vars['tz']['DST'] == true): 
 echo ' class="hasdst" '; 
 else: 
 echo ' class="nodst" '; 
 endif; 
 echo ' '; 
 if ($this->_tpl_vars['commondata']['SERVER_TIME_ZONE_ID'] == $this->_tpl_vars['tz']['ID']): 
 echo ' selected '; 
 endif; 
 echo '>'; 
 echo $this->_tpl_vars['tz']['NAME']; 
 echo '</option>'; 
 endforeach; endif; unset($_from); 
 echo '</select></td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td class="nobr">'; 
 echo $this->_tpl_vars['waStrings']['cmn_set_time_opt3']; 
 echo ':&nbsp;</td><td><input type="checkbox" name="commondata[SERVER_TIME_ZONE_DST]" value="1" '; 
 echo smarty_function_switchedOutput(array('str1' => 'checked','str2' => "",'val' => $this->_tpl_vars['commondata']['SERVER_TIME_ZONE_DST'],'true_val' => 1), $this);
 echo '  id="tzdst"></td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td colspan="2"><input name="edited" type="hidden" id="edited3" value="1">&nbsp;&nbsp;</td></tr><tr><td colspan="2"> <input type="submit" name="savebtn" value="'; 
 echo $this->_tpl_vars['waStrings']['btn_save']; 
 echo '"></td></tr></table>'; 
 endif; 
 echo '</form>'; ?>


<script type="text/javascript">
<!--
checkDST = function()
{
	if (  document.getElementById('timezone').item(document.getElementById('timezone').selectedIndex).className == "nodst" )
	{
		document.getElementById('tzdst').disabled = true;
	}
	else
	{
		document.getElementById('tzdst').disabled = false;
	}
}

checkTZ = function()
{
	var tzenable = document.getElementById('tzenable');

	if ( tzenable.checked == true )
	{
		document.getElementById('timezone').disabled = false;
		document.getElementById('tzdst').disabled = false;

		checkDST();
	}
	else
	{
		document.getElementById('timezone').disabled = true;
		document.getElementById('tzdst').disabled = true;
	}
}
onLoad = function()
{
	checkTZ();
	focusControl(<?php if ($this->_tpl_vars['invalidField']): ?>'commondata[<?php echo $this->_tpl_vars['invalidField']; ?>
]'<?php else: ?>'commondata[COMPANY]'<?php endif; ?>);
}
//alert(document.body.onload);
onLoad();
//-->
</script>
<!-- /commonsettings.html -->