<?php /* Smarty version 2.6.26, created on 2011-08-31 17:21:03
         compiled from short_order_history.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'short_order_history.tpl.html', 5, false),array('modifier', 'translate', 'short_order_history.tpl.html', 6, false),array('modifier', 'replace', 'short_order_history.tpl.html', 6, false),)), $this); ?>
<tr>
	<td valign="top">
		<strong><?php echo 'История заказов'; ?>
</strong>
		<p class="paddingblock">
		<?php $this->assign('_orders_list_url', ((is_array($_tmp="?ukey=order_history")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
		<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp='usr_orders_num')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '%ORDERS_NUM%', $this->_tpl_vars['orders_num']) : smarty_modifier_replace($_tmp, '%ORDERS_NUM%', $this->_tpl_vars['orders_num'])))) ? $this->_run_mod_handler('replace', true, $_tmp, '%ORDERS_LIST_URL%', $this->_tpl_vars['_orders_list_url']) : smarty_modifier_replace($_tmp, '%ORDERS_LIST_URL%', $this->_tpl_vars['_orders_list_url'])); ?>

		</p>
	</td>
	
	<td valign="top">
	</td>
</tr>