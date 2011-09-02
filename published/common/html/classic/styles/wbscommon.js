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

function processTextButton( buttonName, formName )
{
	for ( i = 0; i < document.forms.length; i++ )
		if ( document.forms[i].name == formName )
			for ( j = 0; j < document.forms[i].elements.length; j++ ) {
				if ( document.forms[i].elements[j].name == this.lastDummyName ) {
					document.forms[i].elements[j].value = 1;
					document.forms[i].elements[j].name = buttonName;
					document.forms[i].submit();

					this.lastDummyName = buttonName;

					return true;
				}
			}

	return false;
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
			if ( (form.elements[j].tagName == 'INPUT' && form.elements[j].type == 'text') || form.elements[j].tagName == 'TEXTAREA' ) {
				form.elements[j].focus();
				return;
			}
	}
	catch ( e )
	{
	}
}

function fillSelectedLists( lists )
{
	for ( var l = 0; l < lists.length; l++ ) {

		var list = lists[l];

		var Object = findObj(list);
		var selObj = Object.options;

		for (var i = 0; i < selObj.length; i++)
			selObj[i].selected = true;
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