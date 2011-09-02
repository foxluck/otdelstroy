function resizeBodyFrame() {
	var documentSize = getDocumentSize();
	var currentPos = getMenuPos();
	
	var bodyFrame = document.getElementById("body-frame");
	var menuBlock = document.getElementById("menu-block");
	var logoBlock = document.getElementById("logo-block");
	var topLine = document.getElementById("top-line");
	
	var heightOffset = topLine.offsetHeight;
	//if (logoBlock)
		//heightOffset += logoBlock.offsetHeight;
		
	//var isIE = window.navigator.appName == "Microsoft Internet Explorer";
	
	var frameMissWidth = getStyle(bodyFrame, "margin-left", true) + getStyle(bodyFrame, "margin-right", true);
	var frameMissHeight = getStyle(bodyFrame, "margin-bottom", true) + getStyle(bodyFrame, "margin-top", true);	
	
	switch (currentPos) {
		case "topmenu": case "bottommenu":
			menuBlock.style.height = "auto";
			bodyFrame.style.height = (documentSize.height - menuBlock.offsetHeight - heightOffset - frameMissHeight) + "px";
			bodyFrame.style.width = (documentSize.width - frameMissWidth) + "px";
			break;		
		case "leftmenu":
		case "rightmenu":
			var leftHeight = documentSize.height - heightOffset;
			menuBlock.style.height = leftHeight + "px";
			bodyFrame.style.height = (leftHeight - frameMissHeight) + "px";
			bodyFrame.style.width = (documentSize.width - menuBlock.offsetWidth  - frameMissWidth) + "px";
			break;		
	}
	
	var bodyTopRightBlock = document.getElementById("body-top-right-block");
	bodyTopRightBlock.style.top = (bodyFrame.offsetTop - getStyle(bodyFrame, "margin-top", true) )+"px";
	bodyTopRightBlock.style.right = (documentSize.width - bodyFrame.offsetWidth-bodyFrame.offsetLeft) + "px";
}

function resizeLogo () {
		//var menuPos = getMenuPos();
	var logo = document.getElementById("logo");
	if (!logo)
		return;
	logo.style.height = "auto";
	//if (logo.offsetHeight > 75)
		//logo.style.height = 75 + "px";
	/*var menuBlock = document.getElementById("menu-block");
	switch (menuPos) {
		case "topmenu": case "bottommenu":
			logo.style.height = "0px";
			logo.style.height = (menuBlock.offsetHeight -4)+ "px";
			logo.style.width = "auto";
			break;
		case "leftmenu": case "rightmenu":
			logo.style.width = "0px";
			logo.style.width = (menuBlock.offsetWidth)+ "px";
			logo.style.height = "auto";
			break;	
	}*/
}


function setFullscreen(value) {
	document.fullscreen = value;	
	setCookie("fullscreen", value);
	changeBodyClass();
}

function getFullscreen() {
	if (!document.fullscreen)
		document.fullscreen = getCookie("fullscreen");
	if (!document.fullscreen)
		document.fullscreen = false;
	return document.fullscreen;
}

function changeBodyClass() {
	var menuPos = getMenuPos();
	var menuType = getMenuType();
	var fullscreen = getFullscreen();
	var fullscreenClass = (fullscreen == "on") ? "minimized" : "normal";
	
	document.body.className = menuPos + " " + menuType + " " + fullscreenClass;	
	
	
	setTimeout("resizeBodyFrame()", 30);
	//setTimeout("resizeLogo()", 50);
}

var selectedLink;
var selectedIcon;
function openLink(link,appId, href) {
	var appData = (appId && document.appsData && document.appsData[appId]) ? document.appsData[appId] : null;
	
	if (appId && !appData && !href) {
		location.href = location.href.replace(/app?=[A-Z0-9]{2,3}/g, "");
	}
	
	var menuBlock = document.getElementById("menu-block");
	
	if (selectedLink)
		selectedLink.className = selectedLink.className.replace("selected", "unselect");
	if (link) {
		link.className = link.className.replace("unselect", "selected");
	}
	if (selectedIcon)
		selectedIcon.src = selectedIcon.src.replace("_selected", "");
	
	selectedIcon = document.getElementById("app_icon_" + appId);
	if (selectedIcon)
		selectedIcon.src = selectedIcon.src.replace(".gif", "_selected.gif");
	
	menuBlock.className = (appId) ? "menu-block for-" + appId : "menu-block";
	
	if (appData) {
		document.title = appData.name;
	}
	
	showHideLoading(true);
	document.getElementById("body-frame").src = (href) ? href : appData.url;
	selectedLink = link;
	return false;
}

function linkLoaded() {
	document.getElementById('body-frame').contentWindow.document.body.onclick = hideViewSelector;
	showHideLoading(false);
	hideViewSelector();
}

function showHideLoading(show) {
	document.getElementById("loading-block").style.visibility = (show) ? "visible" : "hidden";
}

function windowResized() {
	resizeBodyFrame();
}

function highlight (appId, block) {
	if(block.className.indexOf("highlight") == -1)
		block.className += " highlight";
}

function highlightOff (appId, block) {
	if(block.className.indexOf("highlight") != -1)
		block.className = block.className.replace("highlight", "");
}

function getMenuPos() {
	if (!document.menuPos)
		document.menuPos = getCookie("menu_pos");
	if (!document.menuPos)
		document.menuPos = "topmenu";
	return document.menuPos;
}

function getMenuType() {
	if (!document.menuType)
		document.menuType = getCookie("menu_type");
	if (!document.menuType)
		document.menuType = "iconslabels";
	return document.menuType;
}

function changeMenuPos (newValue) {
	document.menuPos = newValue;
	setCookie("menu_pos", newValue, 1000);
	changeBodyClass();
}

function changeMenuType (newValue) {
	document.menuType = newValue;
	setCookie("menu_type", newValue, 1000);
	changeBodyClass();
}

function initScreen() {
	changeBodyClass();
	var menuPos = getMenuPos();
	document.getElementById("radio_" + menuPos).checked = true;
	var menuType = getMenuType();
	document.getElementById("radio_" + menuType).checked = true;
	
	addHandler(document,'click',onDocumentClick,false);
	addHandler(document.getElementById('body-frame').contentWindow,'click',hideViewSelector,false);
	
	setTimeout("hideViewSelector()", 30);
}

function hideViewSelector() {
	document.getElementById("view-selector").style.visibility ="hidden";
}



function showViewSelector() {
	document.processShowSelector = true;
	var selector = document.getElementById("view-selector");
	selector.className = "visible";
	selector.style.visibility = (selector.style.visibility == "visible") ? "hidden" : "visible";
}

function onDocumentClick(e){
	if (document.processShowSelector) {
		document.processShowSelector = false;
		return;
	}
	e=e||event;
  var target=e.target||e.srcElement;
  var selector = document.getElementById("view-selector");
  if(selector){
    var parent=target;
    while(parent.parentNode&&parent!=selector)
    	parent=parent.parentNode;
    if(!parent || parent != selector)
      selector.style.visibility = "hidden";
  }
}


function refreshWrapper(url) {
	var loc = document.location.href;
	loc = loc.replace(/\?.*/, "");
	document.location.href = loc + "?url=" + encodeURI(url) ;
}

$(document).ready(function () {
	$("#change-password").click(function () {
		$("#div-change-password").show();
		$("#div-change-password .error").hide();
		$("#div-change-password input[type=password]").val('');
		$("#div-change-password .password1").select();
		$("#div-change-password .save").attr("disabled", "disabled");
		$(this).hide();
	});
	$("#div-change-password .password2").focus(function () {
		$("#div-change-password .save").removeAttr("disabled");
	});
	$("#div-change-password .save").click(function () {
		var password = $("#div-change-password .password1").val();
		if (password == $("#div-change-password .password2").val()) {
			$.post("UG/?mod=users&act=settings&ajax=1", {"info[U_PASSWORD]":password}, function (response) {
				$("#div-change-password").hide();
				$("#change-password").show();					
			}, "json");
		} else {
			$("#div-change-password .error").html(passwords_error).show();
		} 
	});
	$("#div-change-password .cancel").click(function () {
		$("#div-change-password").hide();
		$("#change-password").show();
	});		
});
