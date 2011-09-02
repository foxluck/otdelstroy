function openUniqueWindow( windowName, url )
{
	var existingWindow = null;

	if ( !parent.unique_windows )
	{
		parent.unique_windows = new Array();
	} else
		if ( parent.unique_windows[windowName] )
			existingWindow = parent.unique_windows[windowName];

	if ( existingWindow == null || existingWindow.closed )
	{
		existingWindow = window.open(url, windowName, "status=1,toolbar=no,menubar=no,location=no,resizable=yes");
		parent.unique_windows[windowName] = existingWindow;
		existingWindow.focus();
	} else
		existingWindow.focus();
}

function findObj(n, d) {
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function markArrayField( fieldName, arrayName )
{
	if ( !fieldName )
		return;

	if ( arrayName != "" )
		fullFieldName = arrayName+"["+ fieldName + "]";
	else
		fullFieldName = fieldName;

	var obj = findObj(fullFieldName);
	if (obj)
		obj.focus( );
}

var lastDummyName = "btndummy";
var buttonProcessed = false;

function processTextButton( buttonName )
{
	try
	{
		var form = document.forms[0];

		//if ( typeof(form) == 'undefined' )
		//	return false;

		for ( j = 0; j < form.elements.length; j++ ) {
			if ( form.elements[j].name == this.lastDummyName ) {
				form.elements[j].value = 1;
				form.elements[j].name = buttonName;
				form.submit();

				this.lastDummyName = buttonName;
				this.buttonProcessed = true;

				//return true;
			}
		}
	}
	catch ( e )
	{
	}

	//return false;
}

function processAjaxButton (buttonName) {
	var form = document.forms[0];
	var formParams = new Array ();
	for ( j = 0; j < form.elements.length; j++ ) {
		if ( form.elements[j].name == this.lastDummyName ) {
			form.elements[j].value = 1;
			form.elements[j].name = buttonName;
			
			this.lastDummyName = buttonName;
			this.buttonProcessed = true;
		}
		if (form.elements[j].type == "checkbox" && !form.elements[j].checked)
			continue;
		formParams[form.elements[j].name] = form.elements[j].value;
	}
	formParams['ajaxAccess'] = 1;

	AjaxLoader.doRequest(form.action, AjaxLoader.loadPageHandler, formParams, {showLoading: true});
}

function addToHref (link, param, value) {
	link.href += "&" + param + "=" + value;
	return true;
}


var fadeValue = 50;

var ua = navigator.userAgent;
var opera = /opera [56789]|opera\/[56789]/i.test(ua);
var ie = /msie/i.test(ua) && !/opera/i.test(ua);

function blockPage() {
	document.body.insertAdjacentHTML("afterBegin", "<div style='position: absolute; left: 0; top: 0; width: " + document.body.clientWidth + "; height: " + document.body.clientHeight + "; background-color: white; filter: alpha(opacity=" + fadeValue + "); z-index: 1000; cursor: wait'>&nbsp;</div>");
	var sels = document.getElementsByTagName("select");
	for (var i in sels) {
		if (typeof sels[i] == "object") {
			sels[i].disabled = true;
		}
	}
}

if (ie) {
	window.attachEvent("onunload", blockPage);
}

function calcRelativeURL( level, page )
{
	var URL = new String( document.URL );

	for ( i = 0; i < level; i++ ) {
		lastSlash = URL.lastIndexOf( "/" );
		URL = URL.substring( 0, lastSlash );
	}

	URL += "/" + page;

	return URL;
}

function makeLinkURL( level, page, link, outputLink )
{
	var URL = calcRelativeURL( level, page );
	if ( outputLink )
		document.write( URL );

	for ( i = 0; i < document.links.length; i++ ) {
		if ( document.links[i].name == link ) {
			document.links[i].href = URL;

			break;
		}
	}

}

function writeLinkURL( level, page )
{
	var URL = calcRelativeURL( level, page );
	document.write( URL );
}

function makeHiddenURL( level, page, varName )
{
	var URL = calcRelativeURL( level, page );
	var form = document.forms[0];

	for ( i = 0; i < form.elements.length; i++ ) {
		if ( form.elements[i].name == varName ) {
			form.elements[i].value = URL;
			break;
		}
	}

}

function switchFormTab( tabID, tabList, control )
{
	if ( !document.getElementById )
			return;

	if ( this.tabsLocked )
		return;

	for ( i = 0; i < tabList.length; i++ ) {

			var pageObj = document.getElementById( tabList[i] );
			if ( !pageObj )
					continue;

			var tabObj = document.getElementById( tabList[i]+"TAB" );
			if ( !tabObj )
					continue;

			if ( tabList[i] != tabID ) {
					pageObj.style.display = "none";
					tabObj.className = "tabbar_tab";
			}
	}

	var pageObj = document.getElementById( tabID );
	if ( !pageObj ) return;
	var tabObj = document.getElementById( tabID+"TAB" );
	if ( !tabObj ) return;
	pageObj.style.display = "block";
	tabObj.className = "tabbar_active_tab";

	this.activeTab = tabID;

	focusFormControl( control );
}

function switchHorFormTab( tabID, tabList, control )
{
	if ( !document.getElementById )
			return;

	if ( this.tabsLocked )
		return;

	for ( i = 0; i < tabList.length; i++ ) {

			var pageObj = document.getElementById( tabList[i] );
			if ( !pageObj )
					continue;

			var tabObj = document.getElementById( tabList[i]+"TAB" );
			if ( !tabObj )
					continue;

			if ( tabList[i] != tabID ) {
					pageObj.style.display = "none";
					tabObj.className = "tabbar_hor_tab";
			}
	}

	var pageObj = document.getElementById( tabID );
	if ( !pageObj ) return;
	var tabObj = document.getElementById( tabID+"TAB" );
	if ( !tabObj ) return;
	pageObj.style.display = "block";
	tabObj.className = "tabbar_hor_active_tab";

	focusFormControl( control );
}

function focusFormControl( objName )
{
	try
	{
		var obj = findObj(objName);
		if (obj) {
			obj.focus();
		}
	}
	catch ( e )
	{
	}
}

function focusFirstFormControl()
{
	try
	{
		if ( document.forms.length == 0 )
			return;

		var form = document.forms[0];

		for ( var j = 0; j < form.elements.length; j++ )
			if ( form.elements[j].type == 'textarea' || form.elements[j].type == 'text' || form.elements[j].type == 'password' ) {
				form.elements[j].focus();
				return;
			}
	}
	catch ( e )
	{
	}
}

function autoFocusFormControl( invalidField, fieldsPrefix )
{
	if ( invalidField && invalidField.length )
	{
		var invalidFieldName = invalidField;

		if ( fieldsPrefix && fieldsPrefix.length )
			invalidFieldName = fieldsPrefix+'['+invalidFieldName+']';

		focusFormControl(invalidFieldName);
	} else
		focusFirstFormControl();
}

function fillSelectedLists( lists )
{
	for ( var l = 0; l < lists.length; l++ ) {

		var list = lists[l];

		var Object = findObj(list);
		if (Object != null) {
    		var selObj = Object.options;
            
    		for (var i = 0; i < selObj.length; i++)
    			selObj[i].selected = true;
		}
	}
}

function getComboItemNum( obj )
{
	srcObj = findObj(obj);

	if ( !srcObj )
		return null;

	return srcObj.options.length;
}

function moveComboItems( src, dest )
{
	srcObj = findObj(src);
	destObj = findObj(dest);


	if ( dest != null )
		for ( i = 0; i < srcObj.options.length; i++ ) {
			selObj = srcObj.options[i];
			if ( selObj.selected )
			{
				curPos = destObj.length;
				destObj.options[curPos] = new Option(selObj.text, selObj.value);
			}
		}

	deletedNum = 0;
	first = 1;
	while ( deletedNum || first ) {
		deletedNum = 0;
		for ( i = 0; i < srcObj.options.length; i++ ) {
			selObj = srcObj.options[i];

			if ( selObj.selected ) {
				srcObj.options[selObj.index] = null;
				deletedNum ++;
			}
		}
		first = 0;
	}
}

function changeComboItemPosition( obj, direction )
{
	srcObj = findObj(obj);
	
	if (srcObj.selectedIndex>=0) {
	
		selected = srcObj.selectedIndex;
		len = srcObj.options.length;
	
		if ( (selected == 0 && direction == -1) || (selected == (len-1) && direction == 1)  )
			return;
		
		selObj = srcObj.options[selected];
		obj1 = srcObj.options[selected+direction];
		
		srcObj.options[selected+direction] = new Option(selObj.text, selObj.value);
		srcObj.options[selected] = obj1;
		
		srcObj.selectedIndex = selected+direction;
	}	
}

function checkTreeFolderAccessRights( accessType, groups )
{

}

function URLDecode( encoded )
{
	var HEXCHARS = "0123456789ABCDEFabcdef";
	var plaintext = "";
	var i = 0;
	while (i < encoded.length) {
		var ch = encoded.charAt(i);
		if (ch == "+") {
			plaintext += " ";
			i++;
	} else if (ch == "%") {
			if (i < (encoded.length-2)
					&& HEXCHARS.indexOf(encoded.charAt(i+1)) != -1
					&& HEXCHARS.indexOf(encoded.charAt(i+2)) != -1 ) {
				plaintext += unescape( encoded.substr(i,3) );
				i += 3;
			} else {
				plaintext += "%[ERROR]";
				i++;
			}
		} else {
			plaintext += ch;
			i++;
		}
	}

	return plaintext;
};

function updateAll() 
{ 
	if ( parent.frames[0] )
		parent.frames[0].location.reload();

	if ( parent.frames[2] )
		parent.frames[2].location.reload();

	location.href = "#action:reloadUI";
}

function setTitle( Title )
{
	parent.document.title = Title;
}








// Function for ajax working


function Webasyst_AjaxLoader() {
	
	this.loadingImg = new Image ();
	this.isLoading = false;
	
	this.init = function () {
		this.loadingImg.src = "../../../common/html/res/images/progress.gif";		
	}
	
	/**********
	* Load page with get method to ajax
	**********/
	this.loadPage = function (href, config) {
		if (href == "" || href == null)
			return false;
		
		href += "&ajaxAccess=1";
		var iobj = document.getElementById("inplaceScreen");
		if (iobj != null && iobj.value != "")
			href += "&inplaceScreen=" + iobj.value;
		
		var conn = new Ext.data.Connection();
		
		if (config == null)
			config = new Array ();
		config.showLoading = true;
		this.showLoading (config);
		
		conn.on('requestexception', this.ajaxRequestError);
		conn.request({method : 'GET', url : href, success: this.loadPageHandler}); 
		
		return true;
	}
	
	
	/**********
	* Handler for loadPage
	**********/
	this.loadPageHandler = function (response, options) {
		if (response.responseText == "__EXPIRED") {
			location.href = location.href;
			return;
		}
		
		var gettedParts = response.responseText.split("__-WBS_AJAX_DEL-__");
		var gettedBlocks = new Array ();
		var gettedBlocksKeys = new Array ();
		
		for (i = 0; i < gettedParts.length; i++) {
			var key = gettedParts[i].substr(0, gettedParts[i].indexOf (":"));
			gettedBlocks[key] = gettedParts[i].substr(key.length+1);
			gettedBlocksKeys.push(key);
		}

		var blocksMap = {toolbar: 'Toolbar', toolbarContent: 'ToolbarIn', leftPanel : 'LeftPanelContent', rightContent : 'SplitterRightPanelContainer', mainContent: 'SplitterRightScrollableContent'};
		for (i = 0; i < gettedBlocksKeys.length; i++) {
			if (blocksMap[gettedBlocksKeys[i]] != null) {
				document.getElementById(blocksMap[gettedBlocksKeys[i]]).innerHTML = gettedBlocks[gettedBlocksKeys[i]];
			}
		}
		
		AjaxLoader.isLoading = false;
		InitSplitter();
		Ext.MessageBox.hide();
		if (document.rcorners)
			DoRCorners ();
		if (document.afterPageLoad != null)
			document.afterPageLoad ();
		
		//LayoutManager.SetComboBoxesVisibility(true);		
	}
	
	this.showLoading = function (config) {
		if (config == null)
			return;
		
		var loadingImgHTML = "<img src='" + this.loadingImg.src + "'>";
		this.isLoading = true;
		
		if (config.inPlace) {
			if (config.inPlaceMsg && config.fromEl)
				config.fromEl.className = "waiting";
			else 
				Ext.MessageBox.show ({msg: "Loading...", title: "Please wait", animEl: config.fromEl});
		} else if (config.showLoading) {
			var loadingContainer = null;
			loadingContainer = document.getElementById("SplitterRightPanelContainer");
				
			if (loadingContainer != null)
				loadingContainer.innerHTML = "<div class='MessageBlock'>Loading..." + loadingImgHTML + "</div>";
		}
	}
	
	this.doRequest = function(url, onSuccess, params, config) {
		var iobj = document.getElementById("inplaceScreen");
		
		if (iobj != null && iobj.value != "")
			params.inplaceScreen = iobj.value;

		if (params.headers == null)
			params.headers = {'Content-type': 'application/json'};
		
		if (config != null && config.showLoading) {
			this.showLoading(config);
		}
		
		var conn = new Ext.data.Connection();
		//conn.on('requestcomplete', onSuccess);
		conn.on('requestexception', AjaxLoader.ajaxRequestError);
		
		if (config!= null && config.scope != null)	{
			conn.request({method : 'POST', url : url, params: params, scope : config.scope, success: onSuccess});
		}
		else {
			conn.request({method : 'POST', url : url, params: params, success: onSuccess});
		}
	}
	
	
	/*****
	* Ajax request error
	******/
	this.ajaxRequestError = function () {
		Ext.MessageBox.alert('Critical Error', 'Request Exception');
	},
		
	/*****
	* Ajax server error (returned from server)
	******/
	this.ajaxServerError = function (result) {
		if (result.newLocation) {
			location.href = result.newLocation;
			return;
		}
		alert(result.errorStr);
	}
}

var AjaxLoader = new Webasyst_AjaxLoader ();
AjaxLoader.init ();

/**
*
*  Javascript sprintf
*  http://www.webtoolkit.info/
*
*
**/

sprintfWrapper = {

    init : function () {

        if (typeof arguments == "undefined") { return null; }
        if (arguments.length < 1) { return null; }
        if (typeof arguments[0] != "string") { return null; }
        if (typeof RegExp == "undefined") { return null; }

        var string = arguments[0];
        var exp = new RegExp(/(%([%]|(\-)?(\+|\x20)?(0)?(\d+)?(\.(\d)?)?([bcdfosxX])))/g);
        var matches = new Array();
        var strings = new Array();
        var convCount = 0;
        var stringPosStart = 0;
        var stringPosEnd = 0;
        var matchPosEnd = 0;
        var newString = '';
        var match = null;

        while (match = exp.exec(string)) {
            if (match[9]) { convCount += 1; }

            stringPosStart = matchPosEnd;
            stringPosEnd = exp.lastIndex - match[0].length;
            strings[strings.length] = string.substring(stringPosStart, stringPosEnd);

            matchPosEnd = exp.lastIndex;
            matches[matches.length] = {
                match: match[0],
                left: match[3] ? true : false,
                sign: match[4] || '',
                pad: match[5] || ' ',
                min: match[6] || 0,
                precision: match[8],
                code: match[9] || '%',
                negative: parseInt(arguments[convCount]) < 0 ? true : false,
                argument: String(arguments[convCount])
            };
        }
        strings[strings.length] = string.substring(matchPosEnd);

        if (matches.length == 0) { return string; }
        if ((arguments.length - 1) < convCount) { return null; }

        var code = null;
        var match = null;
        var i = null;

        for (i=0; i<matches.length; i++) {

            if (matches[i].code == '%') { substitution = '%' }
            else if (matches[i].code == 'b') {
                matches[i].argument = String(Math.abs(parseInt(matches[i].argument)).toString(2));
                substitution = sprintfWrapper.convert(matches[i], true);
            }
            else if (matches[i].code == 'c') {
                matches[i].argument = String(String.fromCharCode(parseInt(Math.abs(parseInt(matches[i].argument)))));
                substitution = sprintfWrapper.convert(matches[i], true);
            }
            else if (matches[i].code == 'd') {
                matches[i].argument = String(Math.abs(parseInt(matches[i].argument)));
                substitution = sprintfWrapper.convert(matches[i]);
            }
            else if (matches[i].code == 'f') {
                matches[i].argument = String(Math.abs(parseFloat(matches[i].argument)).toFixed(matches[i].precision ? matches[i].precision : 6));
                substitution = sprintfWrapper.convert(matches[i]);
            }
            else if (matches[i].code == 'o') {
                matches[i].argument = String(Math.abs(parseInt(matches[i].argument)).toString(8));
                substitution = sprintfWrapper.convert(matches[i]);
            }
            else if (matches[i].code == 's') {
                matches[i].argument = matches[i].argument.substring(0, matches[i].precision ? matches[i].precision : matches[i].argument.length)
                substitution = sprintfWrapper.convert(matches[i], true);
            }
            else if (matches[i].code == 'x') {
                matches[i].argument = String(Math.abs(parseInt(matches[i].argument)).toString(16));
                substitution = sprintfWrapper.convert(matches[i]);
            }
            else if (matches[i].code == 'X') {
                matches[i].argument = String(Math.abs(parseInt(matches[i].argument)).toString(16));
                substitution = sprintfWrapper.convert(matches[i]).toUpperCase();
            }
            else {
                substitution = matches[i].match;
            }

            newString += strings[i];
            newString += substitution;

        }
        newString += strings[i];

        return newString;

    },

    convert : function(match, nosign){
        if (nosign) {
            match.sign = '';
        } else {
            match.sign = match.negative ? '-' : match.sign;
        }
        var l = match.min - match.argument.length + 1 - match.sign.length;
        var pad = new Array(l < 0 ? 0 : l).join(match.pad);
        if (!match.left) {
            if (match.pad == "0" || nosign) {
                return match.sign + pad + match.argument;
            } else {
                return pad + match.sign + match.argument;
            }
        } else {
            if (match.pad == "0" || nosign) {
                return match.sign + match.argument + pad.replace(/0/g, ' ');
            } else {
                return match.sign + match.argument + pad;
            }
        }
    }
}

sprintf = sprintfWrapper.init;
//document.writeln('Result: ' + sprintf("Decimal %+05d, Float %07.2f, String '%-10.4s', Hexadecimal %05X", 123, 123, 'abcdefg', 123123));