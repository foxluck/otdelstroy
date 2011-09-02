<?php /* Smarty version 2.6.26, created on 2011-08-31 17:21:03
         compiled from short_address_book.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'short_address_book.tpl.html', 13, false),array('modifier', 'replace', 'short_address_book.tpl.html', 13, false),array('modifier', 'set_query', 'short_address_book.tpl.html', 19, false),)), $this); ?>
<tr>
	<td valign="top">
		<strong><?php echo 'Адрес по умолчанию'; ?>
</strong>
		<div class="paddingblock">
		<p>
		<?php if ($this->_tpl_vars['addressStr']): ?>
			<?php echo $this->_tpl_vars['addressStr']; ?>

		<?php else: ?>
			<?php echo 'Не определено'; ?>

		<?php endif; ?>
		</p>
		<?php if ($this->_tpl_vars['addresses_num']): ?>
		<?php echo ((is_array($_tmp=((is_array($_tmp='usr_addresses_num')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '%ADRESSES_NUM%', $this->_tpl_vars['addresses_num']) : smarty_modifier_replace($_tmp, '%ADRESSES_NUM%', $this->_tpl_vars['addresses_num'])); ?>

		<?php endif; ?>
		</div>
	</td>
	
	<td valign="top" align="right">
		<a href="<?php echo ((is_array($_tmp="?ukey=address_book")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo 'Посмотреть/редактировать адресную книгу'; ?>
</a>
	</td>
</tr>