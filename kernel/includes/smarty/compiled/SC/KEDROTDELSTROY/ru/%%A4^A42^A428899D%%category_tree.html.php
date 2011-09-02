<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:24
         compiled from category_tree.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'category_tree.html', 7, false),array('modifier', 'escape', 'category_tree.html', 11, false),array('modifier', 'default', 'category_tree.html', 11, false),)), $this); ?>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['categories_tree']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
?>  <?php if ($this->_tpl_vars['categories_tree'][$this->_sections['i']['index']]['categoryID'] != 1): ?>
<li class="<?php if ($this->_tpl_vars['categories_tree'][$this->_sections['i']['index']]['level'] > 1): ?>child<?php else: ?>parent<?php endif; 
 if ($this->_tpl_vars['categoryID'] == $this->_tpl_vars['categories_tree'][$this->_sections['i']['index']]['categoryID']): ?>_current<?php endif; ?>">
<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['categories_tree']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['max'] = (int)$this->_tpl_vars['categories_tree'][$this->_sections['i']['index']]['level']-1;
$this->_sections['j']['show'] = true;
if ($this->_sections['j']['max'] < 0)
    $this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = min(ceil(($this->_sections['j']['step'] > 0 ? $this->_sections['j']['loop'] - $this->_sections['j']['start'] : $this->_sections['j']['start']+1)/abs($this->_sections['j']['step'])), $this->_sections['j']['max']);
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
?><span class="tab">&nbsp;</span><?php endfor; endif; ?>
<?php if ($this->_tpl_vars['categories_tree'][$this->_sections['i']['index']]['slug']): ?>
<?php $this->assign('_category_url', ((is_array($_tmp="?categoryID=".($this->_tpl_vars['categories_tree'][$this->_sections['i']['index']]['categoryID'])."&category_slug=".($this->_tpl_vars['categories_tree'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
<?php else: ?>
<?php $this->assign('_category_url', ((is_array($_tmp="?categoryID=".($this->_tpl_vars['categories_tree'][$this->_sections['i']['index']]['categoryID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
<?php endif; ?>
<span class="bullet">&nbsp;</span><a href='<?php echo $this->_tpl_vars['_category_url']; ?>
'><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['categories_tree'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, "(no name)") : smarty_modifier_default($_tmp, "(no name)")); ?>
</a>
</li>
<?php endif; ?>  <?php endfor; endif; ?>
</ul>