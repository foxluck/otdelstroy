<?php /* Smarty version 2.6.26, created on 2011-08-31 17:21:03
         compiled from user_account.html */ ?>
<h1><?php echo 'Мой счет'; ?>
</h1>

<table cellpadding="15" cellspacing="0" width="100%">
<?php unset($this->_sections['op']);
$this->_sections['op']['name'] = 'op';
$this->_sections['op']['loop'] = is_array($_loop=$this->_tpl_vars['ChildShortHTMLs']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['op']['show'] = true;
$this->_sections['op']['max'] = $this->_sections['op']['loop'];
$this->_sections['op']['step'] = 1;
$this->_sections['op']['start'] = $this->_sections['op']['step'] > 0 ? 0 : $this->_sections['op']['loop']-1;
if ($this->_sections['op']['show']) {
    $this->_sections['op']['total'] = $this->_sections['op']['loop'];
    if ($this->_sections['op']['total'] == 0)
        $this->_sections['op']['show'] = false;
} else
    $this->_sections['op']['total'] = 0;
if ($this->_sections['op']['show']):

            for ($this->_sections['op']['index'] = $this->_sections['op']['start'], $this->_sections['op']['iteration'] = 1;
                 $this->_sections['op']['iteration'] <= $this->_sections['op']['total'];
                 $this->_sections['op']['index'] += $this->_sections['op']['step'], $this->_sections['op']['iteration']++):
$this->_sections['op']['rownum'] = $this->_sections['op']['iteration'];
$this->_sections['op']['index_prev'] = $this->_sections['op']['index'] - $this->_sections['op']['step'];
$this->_sections['op']['index_next'] = $this->_sections['op']['index'] + $this->_sections['op']['step'];
$this->_sections['op']['first']      = ($this->_sections['op']['iteration'] == 1);
$this->_sections['op']['last']       = ($this->_sections['op']['iteration'] == $this->_sections['op']['total']);
?>
<?php if ($this->_tpl_vars['ChildShortHTMLs'][$this->_sections['op']['index']]['tpl'] != ''): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['ChildShortHTMLs'][$this->_sections['op']['index']]['tpl'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<?php endif; ?>
<?php endfor; endif; ?>
</table>