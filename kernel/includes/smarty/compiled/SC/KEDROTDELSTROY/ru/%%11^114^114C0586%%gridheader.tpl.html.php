<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:19
         compiled from backend/gridheader.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'backend/gridheader.tpl.html', 7, false),array('modifier', 'default', 'backend/gridheader.tpl.html', 9, false),)), $this); ?>
<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['GridHeaders']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['show'] = true;
$this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = $this->_sections['j']['loop'];
    if ($this->_sections['j']['total'] == 0)
        $this->_sections['j']['show'] = false;
} else
    $this->_sections['j']['total'] = 0;
if ($this->_sections['j']['show']):

            for ($this->_sections['j']['index'] = $this->_sections['j']['start'], $this->_sections['j']['iteration'] = 1;
                 $this->_sections['j']['iteration'] <= $this->_sections['j']['total'];
                 $this->_sections['j']['index'] += $this->_sections['j']['step'], $this->_sections['j']['iteration']++):
$this->_sections['j']['rownum'] = $this->_sections['j']['iteration'];
$this->_sections['j']['index_prev'] = $this->_sections['j']['index'] - $this->_sections['j']['step'];
$this->_sections['j']['index_next'] = $this->_sections['j']['index'] + $this->_sections['j']['step'];
$this->_sections['j']['first']      = ($this->_sections['j']['iteration'] == 1);
$this->_sections['j']['last']       = ($this->_sections['j']['iteration'] == $this->_sections['j']['total']);
?>
<td nowrap <?php if ($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['align']): ?>align="<?php echo $this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['align']; ?>
"<?php endif; ?>>
	<?php if ($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['descsort']['enabled'] == 1): ?><img src="images_common/asc_img.gif" alt="asc"  /><?php endif; ?>
	<?php if ($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['ascsort']['enabled'] == 1): ?><img src="images_common/desc_img.gif" alt="desc"  /><?php endif; ?>
	<?php if ($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['ascsort']['getvars'] != '' && $this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['descsort']['getvars'] != ''): ?>
		<a class="gridheader" 
		href="<?php if ($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['ascsort']['enabled'] == '1'): 
 echo ((is_array($_tmp=($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['descsort']['getvars']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); 
 elseif ($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['descsort']['enabled'] == '1'): 
 echo ((is_array($_tmp=($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['ascsort']['getvars']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); 
 elseif ($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['defsort']['getvars']): 
 echo ((is_array($_tmp=($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['defsort']['getvars']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); 
 else: 
 echo ((is_array($_tmp=($this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['ascsort']['getvars']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); 
 endif; ?>"><?php echo $this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['header_name']; ?>
</a>
	<?php else: ?>
	<?php echo ((is_array($_tmp=@$this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['header_name'])) ? $this->_run_mod_handler('default', true, $_tmp, "&nbsp;") : smarty_modifier_default($_tmp, "&nbsp;")); ?>

	<?php endif; ?>
    <?php echo $this->_tpl_vars['GridHeaders'][$this->_sections['j']['index']]['add_str']; ?>

</td>
<?php endfor; endif; ?>