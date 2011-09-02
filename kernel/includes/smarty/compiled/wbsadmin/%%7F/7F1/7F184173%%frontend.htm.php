<?php /* Smarty version 2.6.26, created on 2011-09-02 13:22:31
         compiled from frontend.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'frontend.htm', 124, false),)), $this); ?>
<!-- frontend.html -->
<?php $this->assign('invalidFieldMarkup', ' style="color: #FF0000"'); 
 echo '<form action="'; 
 echo $this->_tpl_vars['formLink']; 
 echo '" method="post" enctype="multipart/form-data" name="form"><h2 class="page-title">'; 
 echo $this->_tpl_vars['waStrings']['fes_page_name']; 
 echo '</h2><br>'; 
 if ($this->_tpl_vars['errorStr']): 
 echo '<table cellspacing="0" cellpadding="0" border="0"><tr><td'; 
 echo $this->_tpl_vars['invalidFieldMarkup']; 
 echo '>'; 
 echo $this->_tpl_vars['errorStr']; 
 echo '</td></tr><tr><td>&nbsp;</td></tr></table>'; 
 endif; 
 echo ''; 
 if (! $this->_tpl_vars['fatalError']): 
 echo '<table border="0" cellpadding="5" cellspacing="0" class="settings-table"><tr><td colspan="2" style="background-color:#eeeeee;padding:20px;"><div id="message-block" >'; 
 echo $this->_tpl_vars['waStrings']['fes_page_main_page']; 
 echo ' <a href="'; 
 echo $this->_tpl_vars['indexUrl']; 
 echo '">'; 
 echo $this->_tpl_vars['indexUrl']; 
 echo '</a> '; 
 echo $this->_tpl_vars['waStrings']['fes_page_select_desc']; 
 echo '<br><h2 style="padding-left: 0px; padding-top: 10px;">'; 
 echo $this->_tpl_vars['mainPageInfo']; 
 echo '</h2></div></td></tr><tr><td colspan="2"><div class="formSection" style="color: #555;">'; 
 echo $this->_tpl_vars['waStrings']['fes_gen_desc2']; 
 echo '</div></td></tr><tr><td colspan="2" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['wbs_frontend_core_url_description']; 
 echo '</td></tr>'; 
 $_from = $this->_tpl_vars['commondata']['SERVICES']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['slLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['slLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['ID'] => $this->_tpl_vars['sl']):
        $this->_foreach['slLoop']['iteration']++;

 echo '<tr><td style="width: 20px;"><input type="radio" name="commondata[CURRENT_SERVICE_ID]" class="FormControl nodst" id="service'; 
 echo $this->_tpl_vars['sl']['ID']; 
 echo '" value=\''; 
 echo $this->_tpl_vars['sl']['ID']; 
 echo '\' '; 
 if ($this->_tpl_vars['commondata']['CURRENT_SERVICE_ID'] == $this->_tpl_vars['sl']['ID']): 
 echo ' checked '; 
 endif; 
 echo '></td><td style="width: 99%;"  class="nobr"><label for="service'; 
 echo $this->_tpl_vars['sl']['ID']; 
 echo '">'; 
 echo $this->_tpl_vars['sl']['NAME']; 
 echo '</label>'; 
 if ($this->_tpl_vars['sl']['LINK']): 
 echo '&nbsp;&nbsp;&nbsp;<a href="'; 
 echo $this->_tpl_vars['indexUrl']; 
 echo ''; 
 echo $this->_tpl_vars['sl']['LINK']; 
 echo '">'; 
 echo $this->_tpl_vars['indexUrl']; 
 echo ''; 
 echo $this->_tpl_vars['sl']['LINK']; 
 echo '</a>'; 
 endif; 
 echo '</td></tr>'; 
 endforeach; endif; unset($_from); 
 echo '<tr><td colspan="2">&nbsp;</td></tr>'; 
 if ($this->_tpl_vars['SCinstalled'] || $this->_tpl_vars['PDinstalled']): 
 echo ''; 
 $_from = $this->_tpl_vars['commondata']['DBKEYS']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['dbLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['dbLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['ID'] => $this->_tpl_vars['dbk']):
        $this->_foreach['dbLoop']['iteration']++;

 echo ''; 
 if (($this->_foreach['dbLoop']['iteration'] <= 1) && ! ($this->_foreach['dbLoop']['iteration'] == $this->_foreach['dbLoop']['total'])): 
 echo '<tr><td>&nbsp;</td><td>'; 
 echo $this->_tpl_vars['waStrings']['fes_gen_opt1']; 
 echo ':<br><select name="commondata[CURRENT_DBKEY]" class="FormControl" id="service" style="width: 300px">'; 
 endif; 
 echo ''; 
 if (! ( ($this->_foreach['dbLoop']['iteration'] <= 1) && ($this->_foreach['dbLoop']['iteration'] == $this->_foreach['dbLoop']['total']) )): 
 echo '<option value=\''; 
 echo $this->_tpl_vars['dbk']; 
 echo '\' class="nodst" '; 
 if ($this->_tpl_vars['commondata']['CURRENT_DBKEY'] == $this->_tpl_vars['dbk']): 
 echo ' selected '; 
 endif; 
 echo '>'; 
 echo $this->_tpl_vars['dbk']; 
 echo '</option>'; 
 else: 
 echo '<tr><td>&nbsp;</td><td><input name="commondata[CURRENT_DBKEY]" type="hidden" value="'; 
 echo $this->_tpl_vars['dbk']; 
 echo '"></td></tr>'; 
 endif; 
 echo ''; 
 if (! ($this->_foreach['dbLoop']['iteration'] <= 1) && ($this->_foreach['dbLoop']['iteration'] == $this->_foreach['dbLoop']['total'])): 
 echo '</select></td></tr><tr><td>&nbsp;</td><td width="300" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['fes_gen_desc1']; 
 echo '</td><tr><td colspan="2">&nbsp;</td></tr>'; 
 endif; 
 echo ''; 
 endforeach; endif; unset($_from); 
 echo ''; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['SCinstalled'] || $this->_tpl_vars['PDinstalled']): 
 echo '<tr><td colspan="2"><div class="formSection" style="color: #555;">'; 
 echo $this->_tpl_vars['waStrings']['wbs_frontend_rewrite_section']; 
 echo '</div></td></tr><tr><td colspan="2" class="comment">'; 
 echo $this->_tpl_vars['waStrings']['wbs_frontend_rewrite_description']; 
 echo '</td></tr><tr><td valign="top"><input type="radio" name="commondata[MOD_REWRITE]" value="1" '; 
 if ($this->_tpl_vars['mod_rewrite_disabled']): 
 echo ' disabled '; 
 else: 
 echo ''; 
 if ($this->_tpl_vars['commondata']['MOD_REWRITE']): 
 echo 'checked'; 
 endif; 
 echo ''; 
 endif; 
 echo ' id="friendly_urls_on"></td><td><label for="friendly_urls_on">'; 
 echo $this->_tpl_vars['waStrings']['wbs_frontend_rewrite_friendly']; 
 echo '</label></td></tr><tr><td>&nbsp;</td> <td>'; 
 if ($this->_tpl_vars['SCinstalled']): 
 echo '<font color="Blue">'; 
 echo $this->_tpl_vars['indexUrl']; 
 echo ''; 
 if ($this->_tpl_vars['commondata']['CURRENT_SERVICE_ID'] != 'SC'): 
 echo 'shop/'; 
 endif; 
 echo 'product/name/</font>'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['SCinstalled'] && $this->_tpl_vars['PDinstalled']): 
 echo '<br>'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['PDinstalled']): 
 echo '<font color="Blue">'; 
 echo $this->_tpl_vars['indexUrl']; 
 echo ''; 
 if ($this->_tpl_vars['commondata']['CURRENT_SERVICE_ID'] != 'PD'): 
 echo 'photos/'; 
 endif; 
 echo 'album/album_name/</font>'; 
 endif; 
 echo '</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td valign="top"><input type="radio" name="commondata[MOD_REWRITE]" value="0" '; 
 if (( ! $this->_tpl_vars['commondata']['MOD_REWRITE'] ) || $this->_tpl_vars['mod_rewrite_disabled']): 
 echo 'checked'; 
 endif; 
 echo ' id="friendly_urls_off"></td><td><label for="friendly_urls_off">'; 
 echo $this->_tpl_vars['waStrings']['wbs_frontend_rewrite_old']; 
 echo '</label></td></tr><tr><td>&nbsp;</td><td>'; 
 if ($this->_tpl_vars['SCinstalled']): 
 echo '<font color="Blue">'; 
 echo $this->_tpl_vars['indexUrl']; 
 echo ''; 
 if ($this->_tpl_vars['commondata']['CURRENT_SERVICE_ID'] != 'SC'): 
 echo 'shop/'; 
 endif; 
 echo 'index.php?productID=id</font>'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['SCinstalled'] && $this->_tpl_vars['PDinstalled']): 
 echo '<br>'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['PDinstalled']): 
 echo '<font color="Blue">'; 
 echo $this->_tpl_vars['indexUrl']; 
 echo ''; 
 if ($this->_tpl_vars['commondata']['CURRENT_SERVICE_ID'] != 'PD'): 
 echo 'photos/'; 
 endif; 
 echo 'index.php?album=album_name</font>'; 
 endif; 
 echo '</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr>'; 
 endif; 
 echo '<tr><td colspan="2">'; 
 if (! ( $this->_tpl_vars['SCinstalled'] || $this->_tpl_vars['PDinstalled'] )): 
 echo '<input name="commondata[CURRENT_DBKEY]" type="hidden" value="'; 
 echo $this->_tpl_vars['commondata']['DBKEY']; 
 echo '">'; 
 endif; 
 echo '<div class="formSection" style="color: #555;">'; 
 echo ((is_array($_tmp='wbs_frontend_powered_by_section')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</div></td></tr><tr><td valign="top"><input type="checkbox" name="commondata[DISABLE_POWERED_BY]" value="1" '; 
 if ($this->_tpl_vars['commondata']['DISABLE_POWERED_BY']): 
 echo 'checked'; 
 endif; 
 echo ' id="powered_by_input"></td><td><label for="powered_by_input">'; 
 echo ((is_array($_tmp='wbs_frontend_rpowered_by_checkbox')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '</label></td></tr><tr><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td colspan="2"> <input type="submit" name="savebtn" value="'; 
 echo $this->_tpl_vars['waStrings']['btn_save']; 
 echo '"></td></tr></table>'; 
 endif; 
 echo '</form>'; ?>

<!-- /frontend.html -->