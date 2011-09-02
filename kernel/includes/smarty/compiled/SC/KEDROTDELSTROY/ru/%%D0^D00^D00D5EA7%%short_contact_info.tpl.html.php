<?php /* Smarty version 2.6.26, created on 2011-08-31 17:21:03
         compiled from short_contact_info.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'short_contact_info.tpl.html', 3, false),array('modifier', 'set_query_html', 'short_contact_info.tpl.html', 8, false),)), $this); ?>
<tr>
	<td valign="top">
		<span class="cust_name"><?php echo ((is_array($_tmp=$this->_tpl_vars['first_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['last_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</span>
		<div><?php echo ((is_array($_tmp=$this->_tpl_vars['Email'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</div>
	</td>
	
	<td valign="top" align="right">
		<a href='<?php echo ((is_array($_tmp="?ukey=contact_info")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'Посмотреть/редактировать контактную информацию'; ?>
</a>
	</td>
</tr>