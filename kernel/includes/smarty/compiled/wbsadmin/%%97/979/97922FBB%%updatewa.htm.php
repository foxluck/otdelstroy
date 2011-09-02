<?php /* Smarty version 2.6.26, created on 2011-08-31 14:43:38
         compiled from updatewa.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', 'updatewa.htm', 62, false),array('modifier', 'linewrap', 'updatewa.htm', 131, false),)), $this); ?>
<!-- updatewa.html -->
<?php if ($this->_tpl_vars['inProgress']): ?>
   	<script type="text/javascript">
   	<!-- 
   	progressManager.init('<?php echo $this->_tpl_vars['language']; ?>
');
   	//-->
   	</script>
<?php endif; ?>
<?php echo '<h1 class="page-title">'; 
 if ($this->_tpl_vars['changeLog']): 
 echo '<a href="updatewa.php" style="text-decoration: underline;">'; 
 echo $this->_tpl_vars['waStrings']['upd_m_page_title']; 
 echo '</a> &raquo; '; 
 endif; 
 echo ''; 
 echo $this->_tpl_vars['pageHeader']; 
 echo '</h1><table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td width="60%" valign="top" style="padding-top: 10px;"><!-- Status Header -->'; 
 if ($this->_tpl_vars['status']): 
 echo '<div id="statusTitle" style="font-size:150%;font-weight: bold;background:#F3F3D7;padding:20px; margin-right: 20px;">'; 
 echo $this->_tpl_vars['status']; 
 echo '</div>'; 
 endif; 
 echo '<!-- List of updates -->'; 
 if ($this->_tpl_vars['applicationList'] && ! $this->_tpl_vars['changeLog']): 
 echo '<div style="padding: 20px;">'; 
 if (! $this->_tpl_vars['metadaView']): 
 echo '<strong>'; 
 echo $this->_tpl_vars['waStrings']['upd_m_upd_ver']; 
 echo '&nbsp;'; 
 echo $this->_tpl_vars['newestVersion']; 
 echo '</strong><br>'; 
 echo $this->_tpl_vars['waStrings']['upd_m_wa_ver']; 
 echo '&nbsp;'; 
 echo $this->_tpl_vars['currentVersion']; 
 echo '<br><br>'; 
 echo $this->_tpl_vars['waStrings']['upd_m_inf_list']; 
 echo '&nbsp;<a href="updatewa.php?action=changelog">'; 
 echo $this->_tpl_vars['waStrings']['upd_m_inf_upd_link']; 
 echo '</a>&nbsp;'; 
 echo $this->_tpl_vars['waStrings']['upd_m_inf_upd_inst']; 
 echo '<br>'; 
 else: 
 echo ''; 
 echo $this->_tpl_vars['waStrings']['upd_m_inf_upd']; 
 echo '&nbsp;'; 
 echo $this->_tpl_vars['currentVersion']; 
 echo '&nbsp;&rarr;&nbsp;'; 
 echo $this->_tpl_vars['downloadVersion']; 
 echo '<br><br>'; 
 echo $this->_tpl_vars['waStrings']['upd_m_inf_list']; 
 echo '&nbsp;<a href="JavaScript:showDetailsWindow(\'?action=details\')">'; 
 echo $this->_tpl_vars['waStrings']['upd_m_inf_meta_link']; 
 echo '</a>&nbsp;'; 
 echo $this->_tpl_vars['waStrings']['upd_m_inf_meta_inst']; 
 echo '<br>'; 
 endif; 
 echo '</div><table style="margin:20px;" border="0">'; 
 $_from = $this->_tpl_vars['applicationList']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['appList'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['appList']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['app_id'] => $this->_tpl_vars['application']):
        $this->_foreach['appList']['iteration']++;

 echo '<tr><td>'; 
 if ($this->_tpl_vars['app_id'] != 'KERNEL'): 
 echo '<img src="../classic/images/'; 
 echo $this->_tpl_vars['app_id']; 
 echo '35.gif">'; 
 else: 
 echo '&nbsp;'; 
 endif; 
 echo '</td><td style="padding-left: 10px;">'; 
 echo $this->_tpl_vars['application']; 
 echo '</td></tr>'; 
 endforeach; endif; unset($_from); 
 echo '</table>'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['statusDescription']): 
 echo '<div id="statusDescription" style="padding:20px;'; 
 if ($this->_tpl_vars['inProgress']): 
 echo 'background-image:url(\'../classic/images/progress35.gif\');background-repeat:no-repeat;background-position:left center;padding-left:50px;'; 
 else: 
 echo 'background:#fff;'; 
 endif; 
 echo '">'; 
 echo $this->_tpl_vars['statusDescription']; 
 echo '</div>'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['inProgress']): 
 echo ''; 
 if (( $this->_tpl_vars['progressValue'] === null )): 
 echo ''; 
 $this->assign('progressVisibility', ' style="display:none;"'); 
 echo ''; 
 endif; 
 echo '<div class="progressBar"'; 
 echo $this->_tpl_vars['progressVisibility']; 
 echo '><span><em id="progressBarStripe" style="left:'; 
 echo smarty_function_math(array('equation' => "2*a",'a' => $this->_tpl_vars['progressValue']), $this);
 echo 'px"></em></span></div><span class="progressValue"'; 
 echo $this->_tpl_vars['progressVisibility']; 
 echo '>'; 
 echo $this->_tpl_vars['progressValue']; 
 echo '%</span>'; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['updateAvailable']): 
 echo '<p style="padding: 20px 0 0 0; border-top: 1px solid #ccc; margin-right: 20px; margin-left: 20px;">'; 
 echo $this->_tpl_vars['waStrings']['upd_m_notice']; 
 echo '</p>'; 
 if ($this->_tpl_vars['updateAllowed']): 
 echo '<table style="margin: 20px; background-color: #fff;" border="0"><tr><td valign="top"><input type="checkbox" value="erase" name="agreement_1" id="agree_1" onClick="allowUpdate();"></td><td><label for="agree_1">'; 
 echo $this->_tpl_vars['waStrings']['upd_m_erase_agreement']; 
 echo '</label></td></tr><tr><td valign="top"><input type="checkbox" value="send" name="agreement_2" id="agree_2" onClick="allowUpdate();"></td><td><label for="agree_2">'; 
 echo $this->_tpl_vars['waStrings']['upd_m_license_agreement']; 
 echo '</label></td></tr></table>'; 
 endif; 
 echo ''; 
 endif; 
 echo ''; 
 if ($this->_tpl_vars['button']): 
 echo '<!-- controls --><div id="buttons" style="padding: 20px; background-color: #fff;">'; 
 echo $this->_tpl_vars['button']; 
 echo '</div><div style="padding: 20px; background-color: #fff;"><img src="../classic/images/progress35.gif" alt=\'\' style="display:none;" id="buttons_img"/></div>'; 
 endif; 
 echo '</td>'; 
 if ($this->_tpl_vars['updateAvailable']): 
 echo '<td width="40%" valign="top"><div style="font-size: 90%; color: #777; border-left: 1px dotted #ccc; padding: 20px; margin-top: 10px;"><h3 style="padding-left: 0px;">'; 
 echo $this->_tpl_vars['waStrings']['upd_m_inf_description_howitworks']; 
 echo '</h3>'; 
 echo $this->_tpl_vars['waStrings']['upd_m_inf_description']; 
 echo '</div></td>'; 
 endif; 
 echo '</tr><tr><td colspan="2"><!-- changelog -->'; 
 if ($this->_tpl_vars['changeLog']): 
 echo '<div style="color: #777; padding-top: 10px; font-weight: bold; font-size: 120%;">'; 
 echo $this->_tpl_vars['changeLogDescription']; 
 echo '<br></div><table width="100%" cellpadding="10" cellspacing="0" border="0">'; 
 $_from = $this->_tpl_vars['changeLog']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['param1'] => $this->_tpl_vars['changes']):

 echo '<tr><td colspan="2" class="formSection" style="color: #999; font-size: 110%;">'; 
 if ($this->_tpl_vars['applicationList'][$this->_tpl_vars['param1']]): 
 echo ''; 
 echo $this->_tpl_vars['applicationList'][$this->_tpl_vars['param1']]; 
 echo ''; 
 else: 
 echo ''; 
 echo $this->_tpl_vars['param1']; 
 echo ''; 
 endif; 
 echo '</td></tr>'; 
 $_from = $this->_tpl_vars['changes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['param2'] => $this->_tpl_vars['change']):

 echo '<tr><td style="vertical-align:top;"><div style="background-color: #eee; padding: 10px;">'; 
 if ($this->_tpl_vars['applicationList'][$this->_tpl_vars['param2']]): 
 echo ''; 
 echo $this->_tpl_vars['applicationList'][$this->_tpl_vars['param2']]; 
 echo ''; 
 else: 
 echo ''; 
 echo $this->_tpl_vars['param2']; 
 echo ''; 
 endif; 
 echo '</div></td><td align="left"><div style="overflow:auto;">'; 
 echo ((is_array($_tmp=$this->_tpl_vars['change'])) ? $this->_run_mod_handler('linewrap', true, $_tmp, "<br>", 100) : smarty_modifier_linewrap($_tmp, "<br>", 100)); 
 echo '</div></td></tr>'; 
 endforeach; endif; unset($_from); 
 echo '<tr><td colspan="2">&nbsp;</td></tr>'; 
 endforeach; endif; unset($_from); 
 echo '</table>'; 
 endif; 
 echo '<!-- report -->'; 
 if ($this->_tpl_vars['reportHeader']): 
 echo '<h3>'; 
 echo $this->_tpl_vars['reportHeader']; 
 echo '</h3><div><p><i>'; 
 echo $this->_tpl_vars['report']; 
 echo '</i></p></div>'; 
 endif; 
 echo '</td></tr></table><!-- here -->'; ?>

<?php if ($this->_tpl_vars['restartRequired'] && false): ?>
<script type="text/javascript">
<!-- 
document.body.onload = runAction('install&restart=1');
//->
</script>
<?php endif; ?>  
<!-- /updatewa.html -->