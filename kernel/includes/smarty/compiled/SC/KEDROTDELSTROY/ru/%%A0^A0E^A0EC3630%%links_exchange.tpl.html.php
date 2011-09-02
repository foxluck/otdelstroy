<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:03
         compiled from links_exchange.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query', 'links_exchange.tpl.html', 6, false),array('modifier', 'transcape', 'links_exchange.tpl.html', 78, false),)), $this); ?>
<h1><?php echo 'Обмен ссылками'; ?>
</h1>
<table width="100%"  class="oncolorbg">
	<tr>
		<td>
		[ <a href="<?php echo ((is_array($_tmp="?did=".($this->_tpl_vars['CurrentDivision']['id']))) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo 'Все разделы'; ?>
</a> ]
		</td>
		<td align="right">
		[ <a href="#add_link"><?php echo 'Добавить ссылку'; ?>
</a> ]
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<h2><?php echo 'Разделы'; ?>
</h2>
		</td>
	</tr>
	<tr>
		<td valign="top" style="line-height:1.5;">
			 <?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['max'] = (int)$this->_tpl_vars['le_categories_pr'];
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['le_categories']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
if ($this->_sections['i']['max'] < 0)
    $this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = min(ceil(($this->_sections['i']['step'] > 0 ? $this->_sections['i']['loop'] - $this->_sections['i']['start'] : $this->_sections['i']['start']+1)/abs($this->_sections['i']['step'])), $this->_sections['i']['max']);
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
				<?php if ($this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cID'] != $this->_tpl_vars['le_CategoryID']): ?><a href="<?php echo ((is_array($_tmp="msg=&page=1&le_categoryID=".($this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cID']))) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo $this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cName']; ?>
</a>
				<?php else: ?>
					<?php $this->assign('le_CategoryName', $this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cName']); ?>
					<?php echo $this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cName']; ?>

				<?php endif; ?>
				<br />
			 <?php endfor; else: ?>
			 <?php echo 'Нет данных'; ?>
<br />
			 
			 <?php endif; ?>
		</td>
		<td valign="top" style="line-height:1.5;">
		 <?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['start'] = (int)$this->_tpl_vars['le_categories_pr'];
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['le_categories']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
if ($this->_sections['i']['start'] < 0)
    $this->_sections['i']['start'] = max($this->_sections['i']['step'] > 0 ? 0 : -1, $this->_sections['i']['loop'] + $this->_sections['i']['start']);
else
    $this->_sections['i']['start'] = min($this->_sections['i']['start'], $this->_sections['i']['step'] > 0 ? $this->_sections['i']['loop'] : $this->_sections['i']['loop']-1);
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = min(ceil(($this->_sections['i']['step'] > 0 ? $this->_sections['i']['loop'] - $this->_sections['i']['start'] : $this->_sections['i']['start']+1)/abs($this->_sections['i']['step'])), $this->_sections['i']['max']);
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
			<?php if ($this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cID'] != $this->_tpl_vars['le_CategoryID']): ?><a href="<?php echo ((is_array($_tmp="msg=&page=1&le_categoryID=".($this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cID']))) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo $this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cName']; ?>
</a>
			<?php else: ?>
				<?php $this->assign('le_CategoryName', $this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cName']); ?>
				<?php echo $this->_tpl_vars['le_categories'][$this->_sections['i']['index']]['le_cName']; ?>

			<?php endif; ?>
			<br />
		 <?php endfor; endif; ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
			<h2><?php echo 'Ссылки'; ?>
</h2>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="line-height:1.5;">
		<?php $_from = $this->_tpl_vars['le_links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_le_link']):
?>
			<a href="<?php echo $this->_tpl_vars['_le_link']['le_lURL']; ?>
"><?php echo $this->_tpl_vars['_le_link']['le_lText']; ?>
</a><br />
		<?php endforeach; endif; unset($_from); ?>
		<?php if ($this->_tpl_vars['last_page'] > 1): ?>
		<br />
			<?php if ($this->_tpl_vars['curr_page'] > 1): ?>
				&nbsp; <a class="no_underline" href ="<?php echo ((is_array($_tmp="page=".($this->_tpl_vars['curr_page']-1)."&show_all=")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
">&lt;&lt; <?php echo 'пред'; ?>
</a>
			<?php endif; ?>
			<?php $_from = $this->_tpl_vars['le_lister_range']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_page']):
?>
				&nbsp; <?php if ($this->_tpl_vars['_page'] != $this->_tpl_vars['curr_page'] || $this->_tpl_vars['showAllLinks']): ?><a class="no_underline" href="<?php echo ((is_array($_tmp="page=".($this->_tpl_vars['_page'])."&show_all=")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo $this->_tpl_vars['_page']; ?>
</a> <?php else: 
 echo $this->_tpl_vars['_page']; 
 endif; ?>
			<?php endforeach; endif; unset($_from); ?>
			<?php if ($this->_tpl_vars['curr_page'] < $this->_tpl_vars['last_page']): ?>
				&nbsp; <a class="no_underline" href ="<?php echo ((is_array($_tmp="page=".($this->_tpl_vars['curr_page']+1)."&show_all=")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo 'след'; ?>
 &gt;&gt;</a>
			<?php endif; ?>
			&nbsp; |&nbsp; <?php if ($this->_tpl_vars['showAllLinks']): 
 echo 'показать все'; 
 else: ?><a class="no_underline" href ="<?php echo ((is_array($_tmp="show_all=yes")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo 'показать все'; ?>
</a><?php endif; ?>
		<?php endif; ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td colspan="2">
		<div class="divider_grey"></div>
		<a name="add_link"></a>
		<h2><?php echo 'Добавить ссылку'; ?>
</h2>
		<?php if ($this->_tpl_vars['error']): ?><div class="error_msg_f"><?php echo ((is_array($_tmp=$this->_tpl_vars['error'])) ? $this->_run_mod_handler('transcape', true, $_tmp) : smarty_modifier_transcape($_tmp)); ?>
</div><?php endif; ?>
		<?php if ($this->_tpl_vars['error_ok']): ?><div class="ok_msg_f"><?php echo $this->_tpl_vars['error_ok']; ?>
</div><?php endif; ?>
		<form action="<?php echo $this->_tpl_vars['REQUEST_URI']; ?>
" method="POST"><div class="form_wrapper">
		<input name="fACTION" value="ADD_LINK" type="hidden" >
		<input name="fREDIRECT" value="<?php echo $this->_tpl_vars['REQUEST_URI']; ?>
" type="hidden" >
		<p><?php echo 'Раздел'; ?>
:
		<br />
			<select name="LINK[le_lCategoryID]">
			<option value="0"><?php echo 'Не определено'; ?>
</option>
			<?php $_from = $this->_tpl_vars['le_categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_category']):
?>
				<option value="<?php echo $this->_tpl_vars['_category']['le_cID']; ?>
" 
					<?php if ($this->_tpl_vars['le_CategoryID'] == $this->_tpl_vars['_category']['le_cID']): ?> selected="selected"
					<?php elseif ($this->_tpl_vars['pst_LINK']['le_lCategoryID'] == $this->_tpl_vars['_category']['le_cID']): ?> selected="selected"
					<?php endif; ?>
					><?php echo $this->_tpl_vars['_category']['le_cName']; ?>
</option>
			<?php endforeach; endif; unset($_from); ?>
			</select>
		</p>
		<p><?php echo 'URL'; ?>
:
		<br />
		<input name="LINK[le_lURL]" value="<?php if ($this->_tpl_vars['pst_LINK']['le_lURL']): 
 echo $this->_tpl_vars['pst_LINK']['le_lURL']; 
 else: ?>http://<?php endif; ?>" size="60" type="text" >
		</p>
        <p>
		<?php echo 'Текст'; ?>
:
		<br />
		<input name="LINK[le_lText]" value="<?php echo $this->_tpl_vars['pst_LINK']['le_lText']; ?>
" size="60" type="text" ></p>
		<?php if (@CONF_ENABLE_CONFIRMATION_CODE): ?>
		<p><?php echo 'Введите число, изображенное на рисунке'; ?>
:
		<br /> <input name="fConfirmationCode" type="text" ></p>
		<img src="<?php echo $this->_tpl_vars['conf_image']; ?>
" alt="code" />
		<br />
		<?php endif; ?>
		<p><input value="<?php echo 'Добавить'; ?>
" type="submit" ></p>
		</div></form>
		</td>
	</tr>
</table>