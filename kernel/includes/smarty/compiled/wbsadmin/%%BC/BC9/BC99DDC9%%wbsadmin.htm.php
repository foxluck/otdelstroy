<?php /* Smarty version 2.6.26, created on 2011-09-02 13:20:51
         compiled from wbsadmin.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'conditionalOutput', 'wbsadmin.htm', 12, false),array('modifier', 'translate', 'wbsadmin.htm', 33, false),)), $this); ?>
<!-- wbsadmin.html -->
<?php if ($this->_tpl_vars['passwordRequered']): 
 $this->assign('invalidFieldMarkup', " style='color: #FF0000'"); ?>
<div id="message-block" class="error_block">
<h2><?php echo $this->_tpl_vars['waStrings']['wbs_auth_title']; ?>
</h2>
<p><?php echo $this->_tpl_vars['waStrings']['wbs_auth_description']; ?>
</p>
<p<?php echo $this->_tpl_vars['invalidFieldMarkup']; ?>
><?php echo $this->_tpl_vars['errorStr']; ?>
</p>
<form method="POST" action="">
<table>
<tr>
<td align="right" <?php echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'LOGIN','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);?>
><?php echo $this->_tpl_vars['waStrings']['49']; ?>
:&nbsp;</td><td><input type="text" name="user[LOGIN]" id="userLOGIN" class="control" value="<?php echo $this->_tpl_vars['user']['LOGIN']; ?>
"></td>
</tr>
<tr>
<td align="right" <?php echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'PASSWORD1','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);?>
><?php echo $this->_tpl_vars['waStrings']['50']; ?>
:&nbsp;</td><td><input type="password" name="user[PASSWORD1]" id="userPASSWORD1" class="control"></td>
</tr>
<tr>
<td align="right" <?php echo smarty_function_conditionalOutput(array('invalidField' => $this->_tpl_vars['invalidField'],'field' => 'PASSWORD2','text' => $this->_tpl_vars['invalidFieldMarkup']), $this);?>
><?php echo $this->_tpl_vars['waStrings']['wbs_auth_pass_confirm']; ?>
:&nbsp;</td><td><input type="password" name="user[PASSWORD2]" id="userPASSWORD2" class="control"></td>
</tr>
<tr>
<td>&nbsp;</td>
<td><input type="submit" name="setpassword"></td>
</tr>
</table>
</form>
</div>
<?php endif; ?>
<div class="i-col-container">
		<div class="i-col70">
		<div class="i-leftfloat double-size">
				<div <?php if ($this->_tpl_vars['activeSection'] == 'update'): ?>class="active" <?php endif; ?>style="background-image:url('../classic/images/update.gif');">
				<h2>
					<a href="<?php echo @PAGE_WA_UPDATE; ?>
" title="<?php echo ((is_array($_tmp='section_update')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"><?php echo ((is_array($_tmp='section_update')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
				</h2>
				<?php if ($this->_tpl_vars['updateDescription']): 
 echo $this->_tpl_vars['updateDescription']; 
 endif; ?>
				<p><?php echo ((is_array($_tmp='upd_m_wa_ver')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
&nbsp;<b><?php echo $this->_tpl_vars['systemInfo']['localVersion']; ?>
</b>&nbsp;<?php echo ((is_array($_tmp='upd_m_wa_date')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
&nbsp;<?php echo $this->_tpl_vars['systemInfo']['installDate']; ?>
</p>
				<p><?php echo ((is_array($_tmp='section_update_description')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
&nbsp;</p>
				</div>
			</div>
			<!--i-leftfloat-->
			<div class="i-leftfloat">
				<div  <?php if ($this->_tpl_vars['activeSection'] == 'setup'): ?>class="active" <?php endif; ?>style="background-image:url('../classic/images/setup.gif');">
    			<h2><a href="<?php echo @PAGE_SECTION_SETUP; ?>
"><?php echo ((is_array($_tmp='section_setup')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a></h2>
    			<p><?php echo ((is_array($_tmp='section_setup_description')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</p>
    			<?php if ($this->_tpl_vars['setupDescription']): 
 echo $this->_tpl_vars['setupDescription']; 
 endif; ?>
    			</div>
				
				<?php if ($this->_tpl_vars['availableMigration'] && ! $this->_tpl_vars['noDatabase']): ?>
    			<div <?php if ($this->_tpl_vars['activeSection'] == 'migrate'): ?>class="active" <?php endif; ?>style="background-image:url('../classic/images/migrate.gif');">
				<h2><a href="<?php echo @PAGE_WA_MIGRATE; ?>
" ><?php echo ((is_array($_tmp='migrate_header')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a></h2>
				<p><?php echo ((is_array($_tmp='migrate_desc')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</p>
				</div>
				<?php endif; ?>
    		</div>
    		<div class="i-leftfloat">
    			<div <?php if ($this->_tpl_vars['activeSection'] == 'diagnostic'): ?>class="active" <?php endif; ?>style="background-image:url('../classic/images/diagnostic.gif');">
				<h2><a href="<?php echo @PAGE_SECTION_DIAGNOSTIC; ?>
" title="<?php echo ((is_array($_tmp='section_diagnostic')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"><?php echo ((is_array($_tmp='section_diagnostic')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a></h2>
				<p><?php echo ((is_array($_tmp='section_diagnostic_description')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</p>
				<!--<p><?php echo $this->_tpl_vars['systemConfiguration']['SERVER']; ?>
</p>
				<p><?php echo $this->_tpl_vars['systemConfiguration']['MySQL']; ?>
</p>-->
				</div>
    			
    		</div>
    		
			<!--/i-leftfloat-->
			</div>	
			<div class="i-col30">
			<div class="i-colorbord i-p-padd">
				<h2><?php echo ((is_array($_tmp='upd_m_installed_app')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h2>
				<?php $_from = $this->_tpl_vars['app_data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['app_id'] => $this->_tpl_vars['var']):
?>
						<p><img class="app" src="../classic/images/<?php echo $this->_tpl_vars['app_id']; ?>
35.gif" alt="" title=""><?php echo $this->_tpl_vars['var']['APPLICATION']['LOCAL_NAME']; ?>

							<span class="i-subscr-link">
								<?php if ($this->_tpl_vars['var']['updateAvailable'] == true && $this->_tpl_vars['systemInfo']['webVersion']): ?>
									<?php echo $this->_tpl_vars['waStrings']['upd_m_updapp_av']; ?>
 <a href="updatewa.php?action=changelog&app_id=<?php echo $this->_tpl_vars['app_id']; ?>
" style="font-weight: bold;"><?php echo $this->_tpl_vars['waStrings']['upd_m_inf_upd_desc']; ?>

									</a>
								<?php elseif ($this->_tpl_vars['systemInfo']['webVersion']): ?>
									<?php echo $this->_tpl_vars['waStrings']['upd_m_upd_no']; ?>

								<?php endif; ?>
							</span> 
						</p>
				<?php endforeach; endif; unset($_from); ?>
				<!-- 
				<p class="i-size13"><a href="updatewa.php"><?php echo $this->_tpl_vars['waStrings']['upd_m_update_link']; ?>
</a></p>
				 -->

				<p style="padding-bottom: 20px;"><a href="updatewa.php?action=changelog&amp;full_change_log=1"><?php echo ((is_array($_tmp='upd_m_view_full_changelog')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a></p>


				<?php if ($this->_tpl_vars['availableApplications']): ?>				
					<div <?php if ($this->_tpl_vars['activeSection'] == 'buymore'): ?>class="active" style="padding: 10px;"<?php else: ?>style="border-top: 1px solid #ddd; padding-top: 15px; border-bottom: 1px solid #ddd;padding-bottom: 15px;"<?php endif; ?> ><?php echo $this->_tpl_vars['availableApplications']; ?>
</div>
				<?php endif; ?>
				
				<p style="margin-top: 20px; font-size: 100%;">
				&rarr; <a href="../../../../login/" style="font-weight: bold;"><?php echo $this->_tpl_vars['waStrings']['install_link_db']; ?>
</a></p>
				
				<p><?php echo $this->_tpl_vars['waStrings']['support_title']; ?>
:<br>
				&mdash; <a href="<?php echo $this->_tpl_vars['waStrings']['support_website_link']; ?>
"><?php echo $this->_tpl_vars['waStrings']['support_website']; ?>
</a><br>
				&mdash; <a href="<?php echo $this->_tpl_vars['waStrings']['support_helpcenter_link']; ?>
"><?php echo $this->_tpl_vars['waStrings']['support_helpcenter']; ?>
</a><br>
				&mdash; <a href="<?php echo $this->_tpl_vars['waStrings']['support_forum_link']; ?>
"><?php echo $this->_tpl_vars['waStrings']['support_forum']; ?>
</a>
				</p>				
			</div>
		</div>
		
	</div>



	<?php if ($this->_tpl_vars['winclientAvailable']): ?>
        <p><strong><a href="#" name=winURL><script type="text/javascript" language="JavaScript">makeLinkURL( 4, 'common/win/webasystwinsetup.exe', 'winURL', false )</script><?php echo $this->_tpl_vars['waStrings']['wawc_link']; ?>
</a></strong></p>
	<?php endif; ?>
<!-- /wbsadmin.html -->