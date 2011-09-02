<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:19
         compiled from backend/nav2level.tpl.html */ ?>
<table cellpadding="0" cellspacing="0" width="98%" style="height: 98%; width: 98%;">
	<tr>
		<td id="content">
		<?php if ($this->_tpl_vars['safemode']): ?>
			<p class="faq"><?php echo $this->_tpl_vars['safemode']; ?>
</p>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['admin_sub_dpt'] != ''): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "backend/".($this->_tpl_vars['admin_sub_dpt']), 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php else: ?>
		<?php if ($this->_tpl_vars['sub_template'] != ''): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['sub_template'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['compiled_html'] != ''): ?>
			<?php echo $this->_tpl_vars['compiled_html']; ?>

		<?php endif; ?>
		<?php endif; ?>
		</td>
	</tr>
</table>