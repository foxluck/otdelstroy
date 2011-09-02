<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_rate_form.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_rate_form.html', 21, false),)), $this); ?>
<?php if (@CONF_VOTING_FOR_PRODUCTS == 'True'): ?>

<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile'): ?>
<table cellpadding="0" cellspacing="0"><tr><td>
<?php if (! $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): ?>
<div>
<?php if ($this->_tpl_vars['product_info']['customer_votes'] > 0 || $this->_tpl_vars['CPT_CONSTRUCTOR_MODE']): ?> 	<div class="current-rating1"><div class="current-rating1-back">&nbsp;</div><div class="current-rating1-front" style="width: <?php echo $this->_tpl_vars['product_info']['customers_rating']*20; ?>
px;">&nbsp;</div></div>
	<div style="width: 100px;text-align: center;">
		<?php echo 'Голосов'; ?>
: <?php echo $this->_tpl_vars['product_info']['customer_votes']; ?>

	</div>
<?php endif; ?>

<?php if (! $this->_tpl_vars['printable_version'] && ! $this->_tpl_vars['vote_completed']): ?>
<script language="JavaScript" type="text/javascript">
<!--
function vote(score)
{
	
    /*
	var base="<?php echo ((is_array($_tmp="")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
";
	document.location.href=base+'&vote=yes&mark='+score;
    */
    var url = window.location.href;
    url += '&vote=yes&mark='+score;
    window.location = url;
	return false;
}
-->
</script>
	<ul class="unit-rating" style="width:100px;">
		<li class="current-rating" style="width:100px;">&nbsp;</li>
		<li><a rel="nofollow" href='javascript:void(0)' onclick='javascript:vote("1");' title='<?php echo 'Очень плохо'; ?>
' class="r1-unit rater"><?php echo 'Очень плохо'; ?>
</a></li>
		<li><a rel="nofollow" href='javascript:void(0)' onclick='javascript:vote("2");' title='<?php echo 'Плохо'; ?>
' class="r2-unit rater"><?php echo 'Плохо'; ?>
</a></li>
		<li><a rel="nofollow" href='javascript:void(0)' onclick='javascript:vote("3");' title='<?php echo 'Средне'; ?>
' class="r3-unit rater"><?php echo 'Средне'; ?>
</a></li>
		<li><a rel="nofollow" href='javascript:void(0)' onclick='javascript:vote("4");' title='<?php echo 'Хорошо'; ?>
' class="r4-unit rater"><?php echo 'Хорошо'; ?>
</a></li>
		<li><a rel="nofollow" href='javascript:void(0)' onclick='javascript:vote("5");' title='<?php echo 'Отлично'; ?>
' class="r5-unit rater"><?php echo 'Отлично'; ?>
</a></li>
	</ul>
	<div style="width: 100px;text-align: center;"><?php echo 'Оценить'; ?>
</div>
<?php endif; ?>
</div>

<?php else: ?>
	<div class="current-rating1"></div>
<?php endif; ?>
</td></tr></table>

<?php endif; ?>

<?php endif; ?>