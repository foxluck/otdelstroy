<?php /* Smarty version 2.6.26, created on 2011-09-01 08:17:21
         compiled from backend/conf_setting.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'backend/conf_setting.tpl.html', 3, false),array('modifier', 'set_query_html', 'backend/conf_setting.tpl.html', 7, false),array('function', 'cycle', 'backend/conf_setting.tpl.html', 17, false),)), $this); ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/niftycube.js"></script>

<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>
<div>
<ul id="edmod">
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['setting_groups']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li class="tab<?php if ($this->_tpl_vars['settings_groupID'] == $this->_tpl_vars['setting_groups'][$this->_sections['i']['index']]['settings_groupID']): ?> current<?php endif; ?>"><a href='<?php echo ((is_array($_tmp="&settings_groupID=".($this->_tpl_vars['setting_groups'][$this->_sections['i']['index']]['settings_groupID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['setting_groups'][$this->_sections['i']['index']]['settings_group_name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a></li>
	<?php endfor; endif; ?>
</ul>
</div>
<br />
<form action='<?php echo ((is_array($_tmp="&settings_groupID=".($this->_tpl_vars['settings_groupID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' method="post" enctype="multipart/form-data">

	<table class="grid" width="100%">
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['settings']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<tr class="<?php echo smarty_function_cycle(array('values' => 'gridline,gridline1'), $this);?>
"> 
		<td align="right" valign="top">
			<strong><?php echo ((is_array($_tmp=$this->_tpl_vars['settings'][$this->_sections['i']['index']]['settings_title'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</strong>:
			<br><?php echo ((is_array($_tmp=$this->_tpl_vars['settings'][$this->_sections['i']['index']]['settings_description'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</td>
		<td align="left" valign="top"><?php echo $this->_tpl_vars['controls'][$this->_sections['i']['index']]; ?>
</td>
	</tr>
	<?php endfor; endif; ?>
		
	<tr class="gridsfooter">
		<td></td>
		<td width="50%"><input type="submit" name="save" value="<?php echo 'Сохранить'; ?>
" /></td>
	</tr>
	</table>
	
</form>

<script type="text/javascript">
Nifty("li.tab","top same-height");
</script>