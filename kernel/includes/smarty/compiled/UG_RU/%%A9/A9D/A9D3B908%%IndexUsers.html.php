<?php /* Smarty version 2.6.26, created on 2011-09-01 08:18:11
         compiled from IndexUsers.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'csscombine', 'IndexUsers.html', 9, false),array('block', 'jscombine', 'IndexUsers.html', 15, false),)), $this); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Users</title>
<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
templates/css/common.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
templates/elements/mainscreen_complex.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/resources/css/resizable.css" />
<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/resources/css/tree.css" />
<?php $this->_tag_stack[] = array('csscombine', array('file' => ($this->_tpl_vars['url']['css'])."contacts-index.css")); $_block_repeat=true;smarty_block_csscombine($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo $this->_tpl_vars['url']['css']; ?>
reset.css
	<?php echo $this->_tpl_vars['url']['css']; ?>
users-common.css
	<?php echo $this->_tpl_vars['url']['css']; ?>
users.css
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_csscombine($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['url']['common']; ?>
js/jquery.js"></script>
<?php $this->_tag_stack[] = array('jscombine', array('file' => ($this->_tpl_vars['url']['js'])."users-index.js")); $_block_repeat=true;smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
	<?php echo $this->_tpl_vars['url']['common']; ?>
js/jquery.wbs.popup.js
	<?php echo $this->_tpl_vars['url']['common']; ?>
templates/js/common.new.js
	<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/ext-small.js
	<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/build/widgets/Resizable-min.js
	<?php echo $this->_tpl_vars['url']['js']; ?>
complex.js
	<?php echo $this->_tpl_vars['url']['js']; ?>
ug.js
	<?php echo $this->_tpl_vars['url']['js']; ?>
users.js
	<?php echo $this->_tpl_vars['url']['js']; ?>
users-common.js
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
<script type="text/javascript">
	WbsCommon.setPublishedUrl("<?php echo $this->_tpl_vars['url']['published']; ?>
");
	Ext.BLANK_IMAGE_URL = '<?php echo $this->_tpl_vars['url']['common']; ?>
html/res/ext/resources/images/default/s.gif';
	
	document.groupNodes = <?php echo $this->_tpl_vars['groups']; ?>
;
	document.contactTypes = <?php echo $this->_tpl_vars['contact_types']; ?>
;
	document.dbfields = <?php echo $this->_tpl_vars['dbfields']; ?>
;
	document.listfields = <?php echo $this->_tpl_vars['list_fields']; ?>
;
	document.photoField = <?php echo $this->_tpl_vars['photoField']; ?>
;
	
	document.fields = new Array();

	function updateOnline() {
		jQuery.get("index.php?mod=users&act=online", {} , function (response) {
			if (response.status == 'OK') {
				if (jQuery("#online").hasClass('selected') && jQuery("#online-num").html() != response.data) {
					document.app.selectGroup('online');
				}
				jQuery("#online-num").html(response.data);
			}
		}, "json");
		setTimeout(updateOnline, 30000);
	}
	
	jQuery(document).ready(function() {
		jQuery("#main-screen").height(jQuery(window).height() - jQuery("#header").height());
		document.app = new UGApplication({	
			right: <?php echo $this->_tpl_vars['right_js']; ?>
,
			currentGroupId: "<?php echo $this->_tpl_vars['group_id']; ?>
",
			itemsOnPage: <?php if ($this->_tpl_vars['viewSettings']['itemsOnPage']): 
 echo $this->_tpl_vars['viewSettings']['itemsOnPage']; 
 else: ?>30<?php endif; ?>,
			viewmodeApplyTo: "<?php if ($this->_tpl_vars['viewSettings']['viewmodeApplyTo']): 
 echo $this->_tpl_vars['viewSettings']['viewmodeApplyTo']; 
 endif; ?>"							
			<?php if ($this->_tpl_vars['page']): ?>, page: <?php echo $this->_tpl_vars['page']; 
 endif; ?> 
		});
		setTimeout(updateOnline, 30000);
		init('groups');
		document.getElementById("main-screen").style.visibility = "visible";
		jQuery(window).resize();	
		jQuery("#list_info div.info-message").fadeOut(5000, function () {jQuery(this).remove()});	
	});
	jQuery(window).resize(function () {
		var h = jQuery(window).height() - jQuery("#header").height();
		jQuery("#main-screen").height(jQuery(window).height() - jQuery("#header").height());
	});
</script>
</head>
<body>
<div id='main-screen'>
	<div class='screen-left-block'>
		<div class='nav-bar' id='nav-bar'>
			<div id='groups' class='acc-block'>
				<div class="topfolders title"><ul>
                    <li class="f-item"><a id="add-user" href="#"><img src="<?php echo $this->_tpl_vars['url']['img']; ?>
i-adduser.png" width="32" height="32"/>Add a new user</a></li>
						<li><a class="select-group" id="all" href="#"><img src="<?php echo $this->_tpl_vars['url']['img']; ?>
i-allusers.png" width="32" height="32"/>All users</a></li>
						<li><a class="select-group" id="online" href="#"><img src="<?php echo $this->_tpl_vars['url']['img']; ?>
i-onlineusers.png" width="32" height="32"/>Now online <nobr>(<span id="online-num"><?php echo $this->_tpl_vars['online']; ?>
</span>)</nobr></a></li>
						<li><a class="select-group" id="invited" href="#"><img src="<?php echo $this->_tpl_vars['url']['img']; ?>
i-invitedusers.png" width="32" height="32"/>Invited</a></li>
						<li><a class="select-group" id="disabled" href="#"><img src="<?php echo $this->_tpl_vars['url']['img']; ?>
i-disableusers.png" width="32" height="32"/>Temporarily disabled</a></li></ul>
					</div>			
				<div class='content'>

                    <div class='create-new-block' >
					
					<div class="sub user-groups">
						<div class="h">Groups</div>
						<a class="create-group" style="font-size:80%" href="#">Add</a>
					</div>
					</div>
					<div id="groups-list">
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class='screen-main-block' style='margin-left: 0; height: 100%; overflow-y: auto; '>
		<div class='screen-content-block' id='screen-content-block' style='overflow-y: hidden; overflow-x: hidden; overflow: hidden'>
			<div id='main-container'>
				<div id='main-header'>
					<div id='control-panel' class='wbs-control-panel'>
						<div id='nav-bar-expander' class='nav-bar-expander' style='margin-left: 10px'></div>			
						<div id='group-title-container'></div>			
						<div class='contacts-info'>
							<div class="wbs-link-btn">
								
							</div>									
							<div id='view-settings-block'>
								<input type="button" id="users-actions-btn" value="Actions" />
								<div style='text-align:right; float: left; padding-right: 3px; padding-top: 3px; width: 50px; overflow:hidden'>View:</div> 
								<div id='viewmode-selector-wrapper'></div>
								<div id='viewmode-print' class='viewmode-print'><img src="../common/templates/img/printer.gif" title='Print preview'></div>
							</div>
							<div id="list_info"><?php if ($this->_tpl_vars['message']): ?><div class="info-message onload"><?php echo $this->_tpl_vars['message']; ?>
</div><?php endif; ?></div>
						</div>
						<div class="hidden wbs-dlg-content-inner" id='dlg-content'>
						</div>
					</div>
				</div>
				<div id='main-content'></div>
			</div>
		</div>
	</div>
</div>
<div class="wbs-dlg" id="popup" style="display: none;">
	<div id="progress" style="display: none;">
		<img src="<?php echo $this->_tpl_vars['url']['app']; ?>
img/ajax-loader.gif" />
	</div>
</div>

</body>
</html>