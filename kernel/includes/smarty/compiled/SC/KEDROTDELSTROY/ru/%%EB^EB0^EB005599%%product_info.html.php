<?php /* Smarty version 2.6.26, created on 2011-08-31 14:55:32
         compiled from product_info.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'component', 'product_info.html', 3, false),)), $this); ?>
<table style="width: 100%; padding: 0px;">
<tr>
<td><!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'product_name','overridestyle' => ''), $this);?>
<!-- cpt_container_end --></td>
</tr>
<tr>
<td id="prddeatailed_container">
<?php echo smarty_function_component(array('cpt_id' => 'product_images'), $this);?>

<!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'product_params_selectable','overridestyle' => ''), $this);
 echo smarty_function_component(array('cpt_id' => 'product_params_fixed','overridestyle' => ''), $this);
 echo smarty_function_component(array('cpt_id' => 'product_rate_form','overridestyle' => ''), $this);
 echo smarty_function_component(array('cpt_id' => 'product_price','overridestyle' => ''), $this);
 echo smarty_function_component(array('cpt_id' => 'product_add2cart_button','request_product_count' => 'request_product_count','overridestyle' => ':qa8pav'), $this);?>
<!-- cpt_container_end -->
</td>
</tr>
<tr><td>
<!-- AddThis Button BEGIN -->
<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
<div class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="button" data-yashareQuickServices="yaru,vkontakte,facebook,twitter,odnoklassniki,moimir,lj,moikrug"></div> 
<!-- AddThis Button END -->
</td></tr>
<tr><td><hr><?php echo smarty_function_component(array('cpt_id' => 'product_description','overridestyle' => ''), $this);?>
</td></tr>

<tr>
<td><!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'product_discuss_link','overridestyle' => ''), $this);
 echo smarty_function_component(array('cpt_id' => 'product_related_products','overridestyle' => ''), $this);
 echo smarty_function_component(array('cpt_id' => 'product_details_request','overridestyle' => ''), $this);?>
<!-- cpt_container_end --></td>
</tr>


</table>