<?php /* Smarty version 2.6.26, created on 2011-08-31 14:43:51
         compiled from index.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlsafe', 'index.html', 46, false),)), $this); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
         "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Welcome</title>
	<link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['templates']; ?>
css/index.css" />
	<?php $this->assign('theme', $this->_tpl_vars['viewsettings']['theme']); ?>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->_tpl_vars['url']['common']; ?>
html/cssbased/themes/<?php echo $this->_tpl_vars['theme']; ?>
/colors.css" />
    <script type="text/javascript" src="<?php echo $this->_tpl_vars['url']['common']; ?>
js/jquery.js"></script>
	<script type="text/javascript" src="<?php echo $this->_tpl_vars['url']['templates']; ?>
js/common.new.js"></script>
	<script type="text/javascript" src="<?php echo $this->_tpl_vars['url']['templates']; ?>
js/index.js"></script>
</head>
<body onload="resizeBodyFrame()" onresize="windowResized()" class="leftmenu">
	<div id="top-line">
		<div id="user-block">
			<a class="userlink" onClick="showViewSelector()" href="javascript:void(0)"><b><?php echo $this->_tpl_vars['user_name']; ?>
</b></a> |
			<?php if ($this->_tpl_vars['controlPanelScreen']): ?>
				<a href="javascript:void(0)" onClick='return openLink(null, "<?php echo $this->_tpl_vars['controlPanelScreen']->AppId; ?>
")'>Account</a> |
			<?php endif; ?>
			<a href="javascript:void(0)" onClick='return openLink(null, "help")'>Help</a> |
			<a href="<?php echo $this->_tpl_vars['url']['published']; ?>
AA/html/scripts/logout.php">Logout</a>
			<?php if ($this->_tpl_vars['accountIsUnconfirmed'] && $this->_tpl_vars['controlPanelScreen']): ?>
				<div class="message">
					ACCOUNT NOT CONFIRMED
					<a href="javascript:void(0)" onClick='return openLink(null, "<?php echo $this->_tpl_vars['controlPanelScreen']->AppId; ?>
", "<?php echo $this->_tpl_vars['url']['published']; ?>
AA/html/scripts/confirm_info.php");'>What's this?</a>
				</div>
			<?php endif; ?>
			<?php if ($this->_tpl_vars['needBillingAlert']): ?>
				<div class="message">
					<?php echo $this->_tpl_vars['needBillingAlert']['message']; ?>

					<?php if ($this->_tpl_vars['controlPanelScreen']): ?>
						<a href="javascript:void(0)" onClick='return openLink(null, "<?php echo $this->_tpl_vars['controlPanelScreen']->AppId; ?>
", "<?php echo $this->_tpl_vars['url']['published']; ?>
AA/html/scripts/change_plan.php?exceed=period");'>Extend account</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		
		
		<div id="logo-block">
			<table>
				<tr valign="middle">
				<?php if ($this->_tpl_vars['viewsettings']['showLogo']): ?>
                    <td><img onLoad="resizeLogo()" id="logo" src="<?php echo $this->_tpl_vars['url']['common']; ?>
html/scripts/getlogo.php?lt=<?php echo $this->_tpl_vars['logoTime']; ?>
" /></td>
				<?php endif; ?>
				<?php if ($this->_tpl_vars['viewsettings']['showCompanyName']): ?>
					<td><span class="label"><?php echo ((is_array($_tmp=$this->_tpl_vars['companyName'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</span></td>
				<?php endif; ?>									
				</tr>
			</table>					
		</div>
		
	</div>
	
	<div id="menu-block" >
		<ul id="apps">
		<?php $_from = $this->_tpl_vars['screens']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['AppId'] => $this->_tpl_vars['screen']):
?>
			<?php if ($this->_tpl_vars['screen']->forMenu ( )): ?>
				<li id="app_<?php echo $this->_tpl_vars['AppId']; ?>
" class="app-block unselect" onMouseOver='highlight("<?php echo $this->_tpl_vars['AppId']; ?>
", this)' onMouseOut='highlightOff("<?php echo $this->_tpl_vars['AppId']; ?>
", this)'>
					<a class="icon-link" title="<?php echo $this->_tpl_vars['screen']->Name; ?>
" href="?app=<?php echo $this->_tpl_vars['AppId']; ?>
"><img id="app_icon_<?php echo $this->_tpl_vars['AppId']; ?>
" src="<?php echo $this->_tpl_vars['screen']->getIconUrl(); ?>
" alt="<?php echo $this->_tpl_vars['screen']->Name; ?>
" /></a>
					<a class="app-label" href="?app=<?php echo $this->_tpl_vars['AppId']; ?>
"><?php echo $this->_tpl_vars['screen']->Name; ?>
</a>
				</li>
			<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
		</ul>
	</div>
	
	<div id="view-selector" style="position: absolute;">
		<div class="inner clearfix">
			<div class="clearfix" style="border-bottom: 1px solid #BBB; padding-bottom: 5px">
				<img align="left" id="userpic" src="<?php echo $this->_tpl_vars['url']['common']; ?>
html/scripts/getuserpic.php?uid=<?php echo $this->_tpl_vars['user_id']; ?>
" />
				<span style="margin: 0px; font-size: 13pt; font-weight: bold"><?php echo $this->_tpl_vars['user_name']; ?>
</span>
				<?php if ($this->_tpl_vars['myAccountScreen']): ?>
					<br />
					<a onclick='openLink(null, "<?php echo $this->_tpl_vars['myAccountScreen']->AppId; ?>
"); hideViewSelector()' href="javascript:void(0)"><?php echo $this->_tpl_vars['myAccountScreen']->Name; ?>
</a>
				<?php endif; ?>
				<br />
				<a id="change-password" href="#">Change password</a>
				<div id="div-change-password" style="float:right; width: 252px; display:none">
					Enter a new password: <input class="password1" type="password" /><br />
					Confirm new password: <input class="password2" type="password" /><br />
					<div class="error" style="color:red;display:none"></div>
					<input type="button" class="save" value="Save" />
					<input type="button" class="cancel" value="Cancel" />
				</div>
			</div>
			
			<div style="margin-top: 5px">
				<div style="float: left; width: 50%">
					<span class="label">Menu position: </span>
				
					<table class="menupos">
						<tr>
							<td colspan="3"><input onclick="changeMenuPos(this.value)" id="radio_topmenu" value="topmenu" name="menupos" type="radio"><label for="radio_topmenu">Top</label></td>
						</tr>
						<tr>
							<td width="30"><input onclick="changeMenuPos(this.value)" id="radio_leftmenu" value="leftmenu" name="menupos" type="radio"><label for="radio_leftmenu"><BR>Left</label></td>
							<td width="50">&nbsp;</td>
							<td width="30"><input onclick="changeMenuPos(this.value)" id="radio_rightmenu" value="rightmenu" name="menupos" type="radio"><label for="radio_rightmenu"><BR>Right</label></td>
						</tr>
						<tr>
							<td colspan="3">
								<input onclick="changeMenuPos(this.value)" id="radio_bottommenu" value="bottommenu" name="menupos" type="radio"><label for="radio_bottommenu">Bottom</label>
							</td>
						</tr>
					</table>
				</div>		
				<div style="float: left; width: 49%;">
					<div style="margin-left: 20px">
						<span class="label">Show: </span>
						<ul class="clearfix menutype">
							<li><input onClick="changeMenuType(this.value)" name="menutype" id="radio_iconslabels" value="iconslabels" type="radio"><label for="radio_iconslabels">Icons and Names</label></li>
							<li><input onClick="changeMenuType(this.value)" name="menutype" id="radio_onlyicons" value="onlyicons" type="radio"><label for="radio_onlyicons">Icons only</label></li>
							<li><input onClick="changeMenuType(this.value)" name="menutype" id="radio_onlylabels" value="onlylabels" type="radio"><label for="radio_onlylabels">Names only</label></li>
						</ul>
					</div>
				</div>
			</div>
			<div style="float: right">
				<a href="javascript:void(0)" onClick="hideViewSelector()">Close</a>
			</div>
		</div>
	</div>
	
	<div id="body-top-right-block">
		<div id="fullscreen-block">
			<a href="javascript:void(0)" onClick="setFullscreen('on')" class="on"><img src="<?php echo $this->_tpl_vars['url']['templates']; ?>
img/fullscreen.gif" />Full screen</a>
			<a href="javascript:void(0)" onClick='setFullscreen("off")' class="off"><img src="<?php echo $this->_tpl_vars['url']['templates']; ?>
img/fullscreen.gif" />Exit full screen</a>
		</div>
		<div id="loading-block">Loading...</div>
	</div>
	<iframe onLoad="linkLoaded()" id="body-frame" scrolling="no" style="width: 100%; z-index: 0" frameborder="0"></iframe>
	<script type="text/javascript">
    document.appsData = {
        "blank": {url: "AA/html/scripts/blank.php", name: "Welcome"},
        "help": {url: "common/html/scripts/help.php", name: "Help"}
        <?php $_from = $this->_tpl_vars['screens']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['screens'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['screens']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['AppId'] => $this->_tpl_vars['screen']):
        $this->_foreach['screens']['iteration']++;
?>
        ,"<?php echo $this->_tpl_vars['AppId']; ?>
": {url: "<?php echo $this->_tpl_vars['screen']->getUrl(); ?>
", name: "<?php echo $this->_tpl_vars['screen']->Name; ?>
"}
        <?php endforeach; endif; unset($_from); ?>
    };
	initScreen();
	<?php if ($this->_tpl_vars['currentPage']['app']): ?>
		openLink(document.getElementById("app_<?php echo $this->_tpl_vars['currentPage']['app']; ?>
"), "<?php echo $this->_tpl_vars['currentPage']['app']; ?>
"<?php if ($this->_tpl_vars['currentPage']['url']): ?>, "<?php echo $this->_tpl_vars['currentPage']['url']; ?>
"<?php endif; ?> );
	<?php else: ?>
		openLink(null, null, "<?php echo $this->_tpl_vars['currentPage']['url']; ?>
");			
	<?php endif; ?>
		var passwords_error = "Passwords do not match.";
	</script>
</body>
</html>