<?php /* Smarty version 2.6.26, created on 2011-08-31 14:46:24
         compiled from home.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'component', 'home.html', 2, false),)), $this); ?>
<!-- cpt_container_start -->
<?php echo smarty_function_component(array('cpt_id' => 'product_lists','list_id' => 'specialoffers','block_height' => '','overridestyle' => ''), $this);?>

<?php echo smarty_function_component(array('cpt_id' => 'root_categories','categories_col_num' => '2','show_sub_category' => 'enable_sub_category','subcategories_numberlimit' => '','subcategories_delimiter' => ' , ','overridestyle' => '1:9a0dbx'), $this);?>

<!-- cpt_container_end -->