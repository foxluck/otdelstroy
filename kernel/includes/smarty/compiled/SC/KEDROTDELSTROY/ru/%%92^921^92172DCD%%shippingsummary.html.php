<?php /* Smarty version 2.6.26, created on 2011-09-01 05:31:35
         compiled from /home/kedr/domains/otdelstroy31.ru/public_html/published/SC/html/scripts/templates/forms/shippingsummary.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', '/home/kedr/domains/otdelstroy31.ru/public_html/published/SC/html/scripts/templates/forms/shippingsummary.html', 18, false),array('modifier', 'escape', '/home/kedr/domains/otdelstroy31.ru/public_html/published/SC/html/scripts/templates/forms/shippingsummary.html', 38, false),)), $this); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo @PRINTFORMS_SHIPPING_SUMMARY_NAME; ?>
</title>
<style type="text/css"><?php echo '
@media screen {input,.noprint {display: inline;height: auto;} .printable{display: none;}}
@media print {input,.noprint {display: none;} textarea {border:0;} .printable{display: inline;}}
body, td { font-family: \'Trebuchet MS\', Arial, Helvetica, sans-serif;}
</style>'; ?>

<?php if (! $this->_tpl_vars['strict']): ?>
<script type="text/javascript">
var lang_strings = <?php echo '{'; ?>

	'edit_link':'<?php echo 'Корректировка перед печатью'; ?>
',
	'field_title':'<?php echo 'Двойной клик для редактирования'; ?>
',
	'save_link':'<?php echo 'OK'; ?>
'
<?php echo '}'; ?>

var page_url = '<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
';
</script>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/inline_edit_printform.js"></script>

<?php echo '
<script type="text/javascript">
var search_complete = false;
var map = null;
var geocoder = null;
'; ?>

</script>
<?php if (@CONF_GOOGLE_MAPS_API_KEY): ?>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true&amp;key=<?php echo @CONF_GOOGLE_MAPS_API_KEY; ?>
" type="text/javascript"></script>
<script src="http://www.google.com/uds/api?file=uds.js&amp;v=1.0&amp;key=<?php echo @CONF_GOOGLE_MAPS_API_KEY; ?>
" type="text/javascript"></script>
<script src="http://www.google.com/uds/solutions/localsearch/gmlocalsearch.js" type="text/javascript"></script>
<style type="text/css">
  @import url("http://www.google.com/uds/css/gsearch.css");
  @import url("http://www.google.com/uds/solutions/localsearch/gmlocalsearch.css");
</style>
<script type="text/javascript">
var add = '<?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_country'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_city'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
 <?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_address'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
';
<?php echo '
function search()
{
	var map_address = document.getElementById("map_address");
	if(map_address){
		initialize(map_address.value);
	}
}
function initialize(address) {
	if(!address)address = add;
	if (GBrowserIsCompatible()){
		if(!geocoder){
			geocoder = new GClientGeocoder();
		}
		if(geocoder) {
			geocoder.getLatLng(address,
				function(point) {
					if (!point) {
						var map_canvas = document.getElementById("map_canvas");
						map_canvas.innerHTML = "<span class=\\"noprint\\" style=\\"color:red;\\">'; 
 echo 'Адрес не найден на карте. Уточните адрес и повторите поиск.'; 
 echo '<\\/span><textarea id=\\"map_address\\" cols=\\"50\\" rows=\\"6\\" class=\\"noprint\\">"+address + "<\\/textarea><input type=\\"button\\" value=\\"'; 
 echo 'Повторить поиск'; 
 echo '\\" onclick=\\"search();return false;\\">";
					} else {
						if(!map){
							map = new GMap2(document.getElementById("map_canvas"));
							map.addControl(new GLargeMapControl());
							map.addControl(new GMapTypeControl());
						}
						map.setCenter(point, 13);
						var marker = new GMarker(point);
						map.addOverlay(marker);
						marker.openInfoWindowHtml(address);
					}
				}
			);
		}
	}
}
</script>'; ?>

<?php endif; ?>
<?php endif; ?>
</head>
<body<?php if (! $this->_tpl_vars['strict']): ?> onload="Printform.init('inline_edit');<?php if (@CONF_GOOGLE_MAPS_API_KEY): ?>setTimeout('initialize();',500);" onunload="GUnload()"<?php else: ?>"<?php endif; 
 endif; ?>>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "print_button.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<table cellpadding="10" cellspacing="0" width="100%" border="0">
<tr>
	<td width="60%" style="border-bottom: 1px solid #ccc;">
		<?php if (@CONF_PRINTFORM_COMPANY_LOGO): ?>
		<h1><img src="<?php echo @URL_IMAGES; ?>
/<?php echo @CONF_PRINTFORM_COMPANY_LOGO; ?>
"/></h1>
		<?php else: ?>
		<h1><img src="<?php echo @URL_IMAGES; ?>
/companyname.gif"/></h1>
		<?php endif; ?>
	</td>
	<td width="40%" style="border-bottom: 1px solid #ccc;">
		<?php echo 'Комментарий'; ?>
:
		<textarea style="width: 100%; height: 100px; font-size: 100%; font-weight: bold;"><?php echo $this->_tpl_vars['order']['customers_comment']; ?>
</textarea>
	</td>
</tr>
<tr>
	<td>
		<h2><?php echo 'Номер заказа'; ?>
 <span class="inline_edit"><?php echo $this->_tpl_vars['order']['orderID_view']; ?>
</span></h2>
		<p>
		<strong style="font-size: 120%;" class="inline_edit"><?php echo $this->_tpl_vars['order']['shipping_name']; ?>
</strong></p>
		<table>
		<?php $_from = $this->_tpl_vars['customer']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['field'] => $this->_tpl_vars['value']):
?>
			<tr><td><?php echo $this->_tpl_vars['field']; ?>
</td><td>:</td><td class="inline_edit"><?php echo $this->_tpl_vars['value']; ?>
</td></tr>
		<?php endforeach; endif; unset($_from); ?>
		</table>
		<p><?php echo 'Адрес доставки заказа'; ?>
:</p>
		<?php if ($this->_tpl_vars['order']['shipping_address']): ?><span class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_address'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</span><br /><?php endif; ?>
		<?php if ($this->_tpl_vars['order']['shipping_city']): ?><span class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_city'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</span><?php endif; ?>
		<?php if ($this->_tpl_vars['order']['shipping_state']): ?><span class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_state'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</span><?php endif; ?>
		<?php if ($this->_tpl_vars['order']['shipping_zip']): ?><span class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_zip'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</span><br /><?php endif; ?>
		<?php if ($this->_tpl_vars['order']['shipping_country']): ?><span class="inline_edit"><?php echo ((is_array($_tmp=$this->_tpl_vars['order']['shipping_country'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</span><?php endif; ?>
		
		<div id="map_canvas"  style="width: 400px; height: 400px;"></div>
	</td>
	<td valign="top">
		<h2 class="inline_edit"><?php echo $this->_tpl_vars['order']['order_amountToShow']; ?>
</h2>
		<p><?php echo 'Оплата'; ?>
: <strong class="inline_edit"><?php echo $this->_tpl_vars['order']['payment_type']; ?>
</strong></p>
		<p><?php echo 'Доставка'; ?>
: <strong class="inline_edit"><?php echo $this->_tpl_vars['order']['shipping_type']; ?>
</strong></p>
		<p><?php echo 'Заказанные продукты'; ?>
:</p>
		<p style="padding-left: 20px;">
		<?php $_from = $this->_tpl_vars['order_content']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['order_item']):
?>
			<?php echo $this->_tpl_vars['order_item']['name']; ?>
 (&times;<?php echo $this->_tpl_vars['order_item']['Quantity']; ?>
)<br />
		<?php endforeach; endif; unset($_from); ?>
		</p>
	</td>
</tr>
</table>

</body>
</html>