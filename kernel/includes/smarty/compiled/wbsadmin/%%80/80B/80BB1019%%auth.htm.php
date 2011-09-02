<?php /* Smarty version 2.6.26, created on 2011-09-02 10:16:46
         compiled from auth.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'auth.htm', 11, false),array('modifier', 'escape', 'auth.htm', 14, false),)), $this); ?>
<form method="POST">
<table border="0" class="settings-table" style="width: 400px; margin-left: 0px;">

	
	<tr>
		<td colspan="3" align="center">&nbsp;<?php if ($this->_tpl_vars['fatalError']): ?><font color="Red"><?php echo $this->_tpl_vars['errorStr']; ?>
</font><?php endif; ?></td>
	</tr>
	
	<tr>
		<td align="right" width="25%">
			<?php echo ((is_array($_tmp=49)) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:&nbsp;
		</td>
		<td colspan="2">
			<input type="text" name="user[LOGIN]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']['LOGIN'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="big-control" style="width: 95%;">
		</td>
	</tr>
	<tr>
		<td align="right" style="padding-top: 10px;">
			<?php echo ((is_array($_tmp=50)) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:&nbsp;
		</td>
		<td colspan="2" style="padding-top: 10px;">
			<input type="password" name="user[PASSWORD]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']['PASSWORD'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" class="big-control" style="width: 95%;">
		</td>
	</tr>
	<tr>
		<td colspan="3">
			&nbsp;
			<input type="hidden" name="user[CODE1]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']['CODE1'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
			<input type="hidden" name="user[CODE2]" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['user']['CODE2'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
		</td>
	</tr>
	<tr>
		<td>
		<td>
<span id="password_reminder_link" class="nobr">
				<a href='JavaScript:return true;' onclick="{var obj;obj=document.getElementById('password_reminder');if(obj)obj.style.display='block';obj=document.getElementById('password_reminder_link');if(obj)obj.style.display='none';}"><?php echo ((is_array($_tmp='auth_remind_title')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
			</span>

		</td>
		<td align="right" width="25%">
			<input type="submit" value="<?php echo ((is_array($_tmp=65)) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
">
		</td>
	</tr>


	<tr>
		<td colspan="3" style="padding-top: 20px;">
						<span id="password_reminder" style="display:none;margin-left:20px;"  class="comment">
				<?php echo ((is_array($_tmp='auth_remind')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

			</span>
		</td>
	</tr>
</table>
</form>