<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
         "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>[`Welcome`]</title>
	<link rel="stylesheet" type="text/css" href="{{$url.templates}}css/index.css" />
	{{assign var=theme value=$viewsettings.theme}}
    <link rel="stylesheet" type="text/css" href="{{$url.common}}html/cssbased/themes/{{$theme}}/colors.css" />
    <script type="text/javascript" src="{{$url.common}}js/jquery.js"></script>
	<script type="text/javascript" src="{{$url.templates}}js/common.new.js"></script>
	<script type="text/javascript" src="{{$url.templates}}js/index.js"></script>
</head>
<body onload="resizeBodyFrame()" onresize="windowResized()" class="leftmenu">
	<div id="top-line">
		<div id="user-block">
			<a class="userlink" onClick="showViewSelector()" href="javascript:void(0)"><b>{{$user_name}}</b></a> |
			{{if $controlPanelScreen}}
				<a href="javascript:void(0)" onClick='return openLink(null, "{{$controlPanelScreen->AppId}}")'>[`Account`]</a> |
			{{/if}}
			
			<a href="{{$url.published}}AA/html/scripts/logout.php">[`Exit`]</a>
			{{if $accountIsUnconfirmed && $controlPanelScreen}}
				<div class="message">
					[`ACCOUNT NOT CONFIRMED`]
					<a href="javascript:void(0)" onClick='return openLink(null, "{{$controlPanelScreen->AppId}}", "{{$url.published}}AA/html/scripts/confirm_info.php");'>[`What's this?`]</a>
				</div>
			{{/if}}
			{{if $needBillingAlert}}
				<div class="message">
					{{$needBillingAlert.message}}
					{{if $controlPanelScreen}}
						<a href="javascript:void(0)" onClick='return openLink(null, "{{$controlPanelScreen->AppId}}", "{{$url.published}}AA/html/scripts/change_plan.php?exceed=period");'>[`Extend account`]</a>
					{{/if}}
				</div>
			{{/if}}
		</div>
		
		
		<div id="logo-block">
			<table>
				<tr valign="middle">
				{{if $viewsettings.showLogo}}
                    <td><img onLoad="resizeLogo()" id="logo" src="{{$url.common}}html/scripts/getlogo.php?lt={{$logoTime}}" /></td>
				{{/if}}
				{{if $viewsettings.showCompanyName}}
					<td><span class="label"></span></td>
				{{/if}}									
				</tr>
			</table>					
		</div>
		
	</div>
	
	<div id="menu-block" >
		<ul id="apps">
		{{foreach from=$screens item=screen key=AppId}}
			{{if $screen->forMenu ()}}
				<li id="app_{{$AppId}}" class="app-block unselect" onMouseOver='highlight("{{$AppId}}", this)' onMouseOut='highlightOff("{{$AppId}}", this)'>
					<a class="icon-link" title="{{$screen->Name}}" href="?app={{$AppId}}"><img id="app_icon_{{$AppId}}" src="{{$screen->getIconUrl()}}" alt="{{$screen->Name}}" /></a>
					<a class="app-label" href="?app={{$AppId}}">{{$screen->Name}}</a>
				</li>
			{{/if}}
		{{/foreach}}
		</ul>
	</div>
	
	<div id="view-selector" style="position: absolute;">
		<div class="inner clearfix">
			<div class="clearfix" style="border-bottom: 1px solid #BBB; padding-bottom: 5px">
				<img align="left" id="userpic" src="{{$url.common}}html/scripts/getuserpic.php?uid={{$user_id}}" />
				<span style="margin: 0px; font-size: 13pt; font-weight: bold">{{$user_name}}</span>
				{{if $myAccountScreen}}
					<br />
					<a onclick='openLink(null, "{{$myAccountScreen->AppId}}"); hideViewSelector()' href="javascript:void(0)">{{$myAccountScreen->Name}}</a>
				{{/if}}
				<br />
				<a id="change-password" href="#">[`Change password`]</a>
				<div id="div-change-password" style="float:right; width: 252px; display:none">
					[`Enter a new password`]: <input class="password1" type="password" /><br />
					[`Confirm new password`]: <input class="password2" type="password" /><br />
					<div class="error" style="color:red;display:none"></div>
					<input type="button" class="save" value="[`Save`]" />
					<input type="button" class="cancel" value="[`Cancel`]" />
				</div>
			</div>
			
			<div style="margin-top: 5px">
				<div style="float: left; width: 50%">
					<span class="label">[`Menu position`]: </span>
				
					<table class="menupos">
						<tr>
							<td colspan="3"><input onclick="changeMenuPos(this.value)" id="radio_topmenu" value="topmenu" name="menupos" type="radio"><label for="radio_topmenu">[`Top`]</label></td>
						</tr>
						<tr>
							<td width="30"><input onclick="changeMenuPos(this.value)" id="radio_leftmenu" value="leftmenu" name="menupos" type="radio"><label for="radio_leftmenu"><BR>[`Left`]</label></td>
							<td width="50">&nbsp;</td>
							<td width="30"><input onclick="changeMenuPos(this.value)" id="radio_rightmenu" value="rightmenu" name="menupos" type="radio"><label for="radio_rightmenu"><BR>[`Right`]</label></td>
						</tr>
						<tr>
							<td colspan="3">
								<input onclick="changeMenuPos(this.value)" id="radio_bottommenu" value="bottommenu" name="menupos" type="radio"><label for="radio_bottommenu">[`Bottom`]</label>
							</td>
						</tr>
					</table>
				</div>		
				<div style="float: left; width: 49%;">
					<div style="margin-left: 20px">
						<span class="label">[`Show`]: </span>
						<ul class="clearfix menutype">
							<li><input onClick="changeMenuType(this.value)" name="menutype" id="radio_iconslabels" value="iconslabels" type="radio"><label for="radio_iconslabels">[`Icons and Names`]</label></li>
							<li><input onClick="changeMenuType(this.value)" name="menutype" id="radio_onlyicons" value="onlyicons" type="radio"><label for="radio_onlyicons">[`Icons only`]</label></li>
							<li><input onClick="changeMenuType(this.value)" name="menutype" id="radio_onlylabels" value="onlylabels" type="radio"><label for="radio_onlylabels">[`Names only`]</label></li>
						</ul>
					</div>
				</div>
			</div>
			<div style="float: right">
				<a href="javascript:void(0)" onClick="hideViewSelector()">[`Close`]</a>
			</div>
		</div>
	</div>
	
	<div id="body-top-right-block">
		<div id="fullscreen-block">
			<a href="javascript:void(0)" onClick="setFullscreen('on')" class="on"><img src="{{$url.templates}}img/fullscreen.gif" />[`Full screen`]</a>
			<a href="javascript:void(0)" onClick='setFullscreen("off")' class="off"><img src="{{$url.templates}}img/fullscreen.gif" />[`Exit full screen`]</a>
		</div>
		<div id="loading-block">[`Loading`]...</div>
	</div>
	<iframe onLoad="linkLoaded()" id="body-frame" scrolling="no" style="width: 100%; z-index: 0" frameborder="0">���⤫���</iframe>
	<script type="text/javascript">
    document.appsData = {
        "blank": {url: "AA/html/scripts/blank.php", name: "[`Welcome`]"},
        "": {url: ""}
        {{foreach name=screens from=$screens item=screen key=AppId}}
        ,"{{$AppId}}": {url: "{{$screen->getUrl()}}", name: "{{$screen->Name}}"}
        {{/foreach}}
    };
	initScreen();
	{{if $currentPage.app}}
		openLink(document.getElementById("app_{{$currentPage.app}}"), "{{$currentPage.app}}"{{if $currentPage.url}}, "{{$currentPage.url}}"{{/if}} );
	{{else if $currentPage.url}}
		openLink(null, null, "{{$currentPage.url}}");			
	{{/if}}
		var passwords_error = "[`Passwords do not match.`]";
	</script>
</body>
</html>