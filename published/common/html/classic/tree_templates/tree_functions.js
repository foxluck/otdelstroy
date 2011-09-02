function tree_MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=tree_MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function listColumnControls( right )
{
	var result = new Array();

	thisForm = document.forms[0];

	for ( i = 0; i < thisForm.elements.length; i++ )
		if (thisForm.elements[i].name.substr(0, 12) == "userRightsCB") {
			len = thisForm.elements[i].name.length;

			if (thisForm.elements[i].name.substr(len-3) == ("["+right+"]") ) {
				result.push( thisForm.elements[i] );
			}
		}

	return result;
}

function listColumnMultiAppControls( right, app )
{
	var result = new Array();

	thisForm = document.forms[0];

	var appLen = app.length;

	for ( i = 0; i < thisForm.elements.length; i++ )
		if (thisForm.elements[i].name.substr(0, 12) == "userRightsCB") {
			len = thisForm.elements[i].name.length;

			appPart = thisForm.elements[i].name.substr(0, 14+appLen);

			if ( appPart == ("userRightsCB["+app+"]") )
				if (thisForm.elements[i].name.substr(len-3) == ("["+right+"]") )
					result.push( thisForm.elements[i] );
		}

	return result;
}

function setColumnStatus( objects, status )
{
	if ( objects )
	{
		len = objects.length;

		for ( i = 0; i < len; i++ )
			objects[i].checked = status;
	}
}

function setMultiRights( element, sn, right )
{
	checked = element.checked;

	thisObjects = listColumnControls( right );
	setColumnStatus( thisObjects, checked );

	if ( right == 0 ) {
		obj1 = listColumnControls( 1 );
		obj2 = listColumnControls( 2 );

		if ( !checked ) {
			setColumnStatus( obj1, false );
			setColumnStatus( obj2, false );
		}
	}
	if ( right == 1 ) {
		obj1 = listColumnControls( 0 );
		obj2 = listColumnControls( 2 );

		if ( checked )
			setColumnStatus( obj1, true );
		if ( !checked )
			setColumnStatus( obj2, false );
	}
	if ( right == 2 ) {
		obj1 = listColumnControls( 0 );
		obj2 = listColumnControls( 1 );

		if ( checked ) {
			setColumnStatus( obj1, true );
			setColumnStatus( obj2, true );
		}
	}

	return false;
}

function setMultiAppRights( element, sn, app, right )
{
	checked = element.checked;

	thisObjects = listColumnMultiAppControls( right, app );
	setColumnStatus( thisObjects, checked );

	if ( right == 0 ) {
		obj1 = listColumnMultiAppControls( 1, app );
		obj2 = listColumnMultiAppControls( 2, app );

		if ( !checked ) {
			setColumnStatus( obj1, false );
			setColumnStatus( obj2, false );
		}
	}
	if ( right == 1 ) {
		obj1 = listColumnMultiAppControls( 0, app );
		obj2 = listColumnMultiAppControls( 2, app );

		if ( checked )
			setColumnStatus( obj1, true );
		if ( !checked )
			setColumnStatus( obj2, false );
	}
	if ( right == 2 ) {
		obj1 = listColumnMultiAppControls( 0, app );
		obj2 = listColumnMultiAppControls( 1, app );

		if ( checked ) {
			setColumnStatus( obj1, true );
			setColumnStatus( obj2, true );
		}
	}

	return false;
}

function updateFolderCb( element, folderID, right )
{
	if ( right == 0 ) {
		obj1 = tree_MM_findObj( "userRightsCB["+folderID+"][1]" );
		obj2 = tree_MM_findObj( "userRightsCB["+folderID+"][2]" );

		if ( !element.checked ) {
			obj1.checked = false;

			if (obj2)
				obj2.checked = false;
		}
	}
	if ( right == 1 ) {
		obj1 = tree_MM_findObj( "userRightsCB["+folderID+"][0]" );
		obj2 = tree_MM_findObj( "userRightsCB["+folderID+"][2]" );

		if ( element.checked )
			obj1.checked = true;
		if ( !element.checked )
			if (obj2)
				obj2.checked = false;
	}
	if ( right == 2 ) {
		obj1 = tree_MM_findObj( "userRightsCB["+folderID+"][0]" );
		obj2 = tree_MM_findObj( "userRightsCB["+folderID+"][1]" );

		if ( element.checked ) {
			obj1.checked = true;
			if (obj2)
				obj2.checked = true;
		}
	}
}

function updateMultiAppFolderCb( element, folderID, app_id, right )
{
	if ( right == 0 ) {
		obj1 = document.getElementById( "userRightsCB["+app_id+"]["+folderID+"][1]" );
		obj2 = document.getElementById( "userRightsCB["+app_id+"]["+folderID+"][2]" );

		if ( !element.checked ) {
			obj1.checked = false;
			if (obj2)
				obj2.checked = false;
		}
	}
	if ( right == 1 ) {
		obj1 = document.getElementById( "userRightsCB["+app_id+"]["+folderID+"][0]" );
		obj2 = document.getElementById( "userRightsCB["+app_id+"]["+folderID+"][2]" );

		if ( element.checked )
			obj1.checked = true;
		if ( !element.checked )
			if (obj2)
				obj2.checked = false;
	}
	if ( right == 2 ) {
		obj1 = document.getElementById( "userRightsCB["+app_id+"]["+folderID+"][0]" );
		obj2 = document.getElementById( "userRightsCB["+app_id+"]["+folderID+"][1]" );

		if ( element.checked ) {
			obj1.checked = true;
			if (obj2)
				obj2.checked = true;
		}
	}
}

function setChecked_FolderCb (checked, name, userId, folderPath, right) {
	var obj1 = document.getElementById( name+"["+userId+"]["+folderPath+"][" + right + "]" );
	obj1.checked = checked;
	new_updateMultiAppFolderCb (obj1, name, userId, folderPath, right);
}


function new_updateMultiAppFolderCb( element, name, userId, folderPath, right )
{
	if ( right == 1 ) {
		obj1 = document.getElementById( name+"["+userId+"]["+folderPath+"][2]" );
		obj2 = document.getElementById( name+"["+userId+"]["+folderPath+"][4]" );

		if ( !element.checked ) {
			obj1.checked = false;
			if (obj2)
				obj2.checked = false;
		}
	}
	if ( right == 2 ) {
		obj1 = document.getElementById( name+"["+userId+"]["+folderPath+"][1]" );
		obj2 = document.getElementById( name+"["+userId+"]["+folderPath+"][4]" );

		if ( element.checked )
			obj1.checked = true;
		if ( !element.checked )
			if (obj2)
				obj2.checked = false;
	}
	if ( right == 4 ) {
		obj1 = document.getElementById( name+"["+userId+"]["+folderPath+"][1]" );
		obj2 = document.getElementById( name+"["+userId+"]["+folderPath+"][2]" );

		if ( element.checked ) {
			obj1.checked = true;
			if (obj2)
				obj2.checked = true;
		}
	}
}


function treeSelectAll( obj )
{
	checked = obj.checked;

	thisForm = document.forms[0];

	for ( i = 0; i < thisForm.elements.length; i++ )
		if (thisForm.elements[i].type == 'checkbox')
			thisForm.elements[i].checked = checked;

	return false;
}

function treeCheckSelection( text )
{
	thisForm = document.forms[0];

	checkedFound = false;

	for ( i = 0; i < thisForm.elements.length; i++ )
		if (thisForm.elements[i].type == 'checkbox')
			if ( thisForm.elements[i].name != "selectAllDocsCB" && thisForm.elements[i].checked ) {
				checkedFound = true;
				break;
			}

	if ( !checkedFound) {
		if (text)
			alert( text );
		return false;
	}

	return true;
}

function treeConfirmFolderDeletion( text )
{
	return confirm( text );
}


//
// Cookies
//

function createCookie(name,value,days)
{
	if (days)
	{
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name)
{
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

//
// Resizable tree/document view functions
//

var treeLeftPanelHeaderH = 0;
var treeRightPanelHeaderObj = null;
var treeRightPanelWidthDelta = 0;
var treeViewName = null;

function setElementsHeight()
{
	try
	{
		topPanel = document.getElementById("restdv_banner");

		docHeight = parseInt(document.body.clientHeight);

		if (window.navigator.appName != "Microsoft Internet Explorer")
			newDocHeight = docHeight - parseInt(topPanel.offsetHeight)-4;
		else
			newDocHeight = docHeight - parseInt(topPanel.offsetHeight);

		tree = document.getElementById("treediv");
		if (tree)
			tree.style.height = newDocHeight;

		footerHeight = 0;

		leftFooter = document.getElementById("leftPanelFooter");
		if (leftFooter) {
			is_ie = ( /msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent) );
			footerHeight = leftFooter.offsetHeight;
			if (is_ie)
				footerHeight += 5;
		}

		headerHeight = 0;

		leftHeader = document.getElementById("leftPanelHeader");
		if (leftHeader) {
			headerHeight = leftHeader.offsetHeight;
		}

		leftscroll = document.getElementById("leftscrolldiv");
		if (leftscroll)
			leftscroll.style.height = newDocHeight - this.treeLeftPanelHeaderH - footerHeight - headerHeight;

		rightscroll = document.getElementById("rightscrolldiv");
		if (rightscroll)
		{
			treeRightPanelHeaderH = 0;
			if ( this.treeRightPanelHeaderObj != null )
			{
				obj = document.getElementById(this.treeRightPanelHeaderObj);
				if (obj) {
					treeRightPanelHeaderH = obj.offsetHeight;
				}
			}
			rightscroll.style.height = newDocHeight - treeRightPanelHeaderH;
		}

		content = document.getElementById("contentdiv");
		content.style.height = newDocHeight;

		e = document.getElementById("splitter");
		if (e)
			e.style.height = newDocHeight;

		cnt = document.getElementById("contentdiv");
		td = document.getElementById("treetd");
		spl = document.getElementById("splitter");

		if (spl) {
			cnt.style.width = parseInt(document.body.clientWidth) - td.offsetWidth - spl.offsetWidth - 2;
		} else
			cnt.style.width = parseInt(document.body.clientWidth) - 2;
	}
	catch (e)
	{
	}
}

function initTreeDocumentView()
{
	e = document.getElementById("splitter");
	if ( e ) {
		//e.ondblclick = openCloseTocPanel;
		e.onmousedown = selectTocButton;
		e.onmouseup = releaseTocPane;

		/*img = document.getElementById("splitterImg");
		img.onclick = openCloseTocPanel;*/

	}

	setElementsHeight();

	if (window.attachEvent)
		window.attachEvent("onresize", setElementsHeight);

	if (window.addEventListener)
		window.addEventListener("resize", setElementsHeight, false);

	panel = document.getElementById("treediv");

	cntdv = document.getElementById("contentdiv");
	cnt = document.getElementById("contenttd");
	spl = document.getElementById("splitter");
	paneltd = document.getElementById("treetd");

	is_ie = ( /msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent) );
	if (!is_ie) {
		width = parseInt(document.body.clientWidth)  -  ( ( spl != null ) ? spl.offsetWidth : 0 )  - ( ( paneltd != null ) ? paneltd.offsetWidth  : 0  );
		cntdv.style.width = width;
		cnt.style.width = width;
	} else {
		cnt.style.width = "100%";
		cntdv.style.width = "100%";
	}
}

function openCloseTocPanel()
{
	try
	{
		document.onmousemove = null;

		tr = document.getElementById("treediv");
		td = document.getElementById("treetd");
		btn = document.images["splitterImg"];
		spl = document.getElementById("splitter");

		if (tr.style.display == "none") {
			tr.style.display = "block";
			btn.src = "../../images/expando_ltr2.gif";
			td.style.width = "200px";
		}
		else {
			tr.style.display = "none";
			btn.src = "../../images/expando_ltr.gif";
			td.style.width = spl.offsetWidth;
		}

		cnt = document.getElementById("contentdiv");
		cnt.style.width = parseInt(document.body.clientWidth) - td.offsetWidth - spl.offsetWidth;
	}
	catch(e)
	{
	}

	return;
}

attachEventEx = function(target, event, func)
{
	if (target.attachEvent)
		target.attachEvent("on" + event, func);
	else if (target.addEventListener)
		target.addEventListener(event, func, true );
	else
		target["on" + event] = func;
}

function selectTocButton()
{
	e = document.getElementById("splitter");
	e.className = "restdv_splitterBtn restdv_selected";

	if (window.navigator.appName == "Netscape") {
		document.addEventListener("onmousemove", resizeToc, true);
		arguments[0].preventDefault();
	}

	attachEventEx( document, "mousemove", resizeToc );
         attachEventEx( document, "mouseup", releaseTocPane );

	document.dragging = true;

	return;
}

function resizeToc(e)
{
	if (!document.dragging )
         	return false;

	try
	{
		var oEvent;
		var newX;

		if (window.navigator.appName == "Netscape") {
			oEvent = e;
			newX = oEvent.pageX;
		} else {
			oEvent = window.event;
			if (oEvent.clientX - 4 < 0)
				newX = 0;
			else
				newX = oEvent.clientX - 4;
		}

		panel = document.getElementById("treediv");
		paneltd = document.getElementById("treetd");
		spl = document.getElementById("splitter");
		cnt = document.getElementById("contenttd");

		dir = document.dir;
		if ((dir == null) || (dir == ""))
			dir = "ltr";

		if (Math.abs(parseInt(panel.style.width) - newX) < 5 && !document.dragging)
			return;

		document.dragging = true;

		if (dir == "ltr") {
			spl.style.left = newX;
			panel.style.width = newX;
			paneltd.style.width = newX;
			cnt.style.left = newX;
			cnt.style.width = "100%";
		} else
			if (dir == "rtl") {
				spl.style.left = parseInt(document.body.clientWidth) - newX;
				panel.style.width =  parseInt(document.body.clientWidth) - newX;
			}

		cntdv = document.getElementById("contentdiv");
		try
		{
			cntdv.style.width = parseInt(document.body.clientWidth) - paneltd.offsetWidth - spl.offsetWidth;
		}
		catch (e)
		{
		}
	}
	catch (e)
	{
	}

	return false;
}

function releaseTocPane() {
//	document.onmousemove = null;
//	document.onmouseup = null;
	document.dragging = false;

	e = document.getElementById("splitter");
	e.className = "restdv_splitterBtn";

	treedv = document.getElementById("treediv");

	createCookie("splitterView"+document.treeViewName, treedv.style.width, 365);
1
	return;
}

function toggleItems() {
	e = document.getElementById("items");
	e.style.display = (e.style.display == "block") ? "none" : "block";
}


// Class for containing application rights checkbox IDs
//
function appRightsContrainer()
{
	this.readCheckboxes = new Array();
	this.writeCheckboxes = new Array();
	this.folderCheckboxes = new Array();

	this.addCheckbox = function( cbType, cbId )
	{
		switch (cbType)
		{
			case 0 : this.readCheckboxes.push(cbId); break;
			case 1 : this.writeCheckboxes.push(cbId); break;
			case 2 : this.folderCheckboxes.push(cbId); break;
		}
	}

	this.updateCheckboxStates = function( cbType, value )
	{
		var cbArray = null;

		switch (cbType)
		{
			case 0 : cbArray = this.readCheckboxes; break;
			case 1 : cbArray = this.writeCheckboxes; break;
			case 2 : cbArray = this.folderCheckboxes; break;
		}

		if (cbArray)
		{
			cnt = cbArray.length;
			i = 0;

			for ( i = 0; i < cnt; i++ )
			{
				var obj = document.getElementById(cbArray[i]);
				if (obj)
					if ( !(value && obj.checked) || !(!value && !obj.checked) )
						obj.checked = value;
			}
		}
	}

	this.setAppRights = function( checkbox, cbType )
	{
		var checked = checkbox.checked;

		this.updateCheckboxStates( cbType, checked );

		switch (cbType)
		{
			case 0 :
				if ( !checked )
				{
					this.updateCheckboxStates( 1, 0 );
					this.updateCheckboxStates( 2, 0 );
				}
				break;
			case 1 :
				if ( checked )
					this.updateCheckboxStates( 0, 1 );
				else
					this.updateCheckboxStates( 2, 0 );
			case 2 :
				if ( checked )
				{
					this.updateCheckboxStates( 0, 1 );
					this.updateCheckboxStates( 1, 1 );
				}
				break;
		}
	}
}


// Class for containing application rights checkbox IDs
//
function new_appRightsContrainer()
{
	this.readCheckboxes = new Array();
	this.writeCheckboxes = new Array();
	this.folderCheckboxes = new Array();

	this.addCheckbox = function( cbType, cbId )
	{
		switch (cbType)
		{
			case 1 : this.readCheckboxes.push(cbId); break;
			case 2 : this.writeCheckboxes.push(cbId); break;
			case 4 : this.folderCheckboxes.push(cbId); break;
		}
	}

	this.updateCheckboxStates = function( cbType, value )
	{
		var cbArray = null;

		switch (cbType)
		{
			case 1 : cbArray = this.readCheckboxes; break;
			case 2 : cbArray = this.writeCheckboxes; break;
			case 4 : cbArray = this.folderCheckboxes; break;
		}

		if (cbArray)
		{
			cnt = cbArray.length;
			i = 0;

			for ( i = 0; i < cnt; i++ )
			{
				var obj = document.getElementById(cbArray[i]);
				if (obj)
					if ( !(value && obj.checked) || !(!value && !obj.checked) )
						obj.checked = value;
			}
		}
	}

	this.setAppRights = function( checkbox, cbType )
	{
		var checked = checkbox.checked;

		this.updateCheckboxStates( cbType, checked );

		switch (cbType)
		{
			case 1 :
				if ( !checked )
				{
					this.updateCheckboxStates( 2, 0 );
					this.updateCheckboxStates( 4, 0 );
				}
				break;
			case 2 :
				if ( checked )
					this.updateCheckboxStates( 1, 1 );
				else
					this.updateCheckboxStates( 4, 0 );
			case 4 :
				if ( checked )
				{
					this.updateCheckboxStates( 1, 1 );
					this.updateCheckboxStates( 2, 1 );
				}
				break;
		}
	}

}


// Class for containing application rights checkbox IDs
//
function appRightsContrainer()
{
	this.readCheckboxes = new Array();
	this.writeCheckboxes = new Array();
	this.folderCheckboxes = new Array();

	this.addCheckbox = function( cbType, cbId )
	{
		switch (cbType)
		{
			case 0 : this.readCheckboxes.push(cbId); break;
			case 1 : this.writeCheckboxes.push(cbId); break;
			case 2 : this.folderCheckboxes.push(cbId); break;
		}
	}

	this.updateCheckboxStates = function( cbType, value )
	{
		var cbArray = null;

		switch (cbType)
		{
			case 0 : cbArray = this.readCheckboxes; break;
			case 1 : cbArray = this.writeCheckboxes; break;
			case 2 : cbArray = this.folderCheckboxes; break;
		}

		if (cbArray)
		{
			cnt = cbArray.length;
			i = 0;

			for ( i = 0; i < cnt; i++ )
			{
				var obj = document.getElementById(cbArray[i]);
				if (obj)
					if ( !(value && obj.checked) || !(!value && !obj.checked) )
						obj.checked = value;
			}
		}
	}

	this.setAppRights = function( checkbox, cbType )
	{
		var checked = checkbox.checked;

		this.updateCheckboxStates( cbType, checked );

		switch (cbType)
		{
			case 0 :
				if ( !checked )
				{
					this.updateCheckboxStates( 1, 0 );
					this.updateCheckboxStates( 2, 0 );
				}
				break;
			case 1 :
				if ( checked )
					this.updateCheckboxStates( 0, 1 );
				else
					this.updateCheckboxStates( 2, 0 );
			case 2 :
				if ( checked )
				{
					this.updateCheckboxStates( 0, 1 );
					this.updateCheckboxStates( 1, 1 );
				}
				break;
		}
	}
}

// Class for containing application rights checkbox IDs
//
function toggleCBContainer( )
{
	this.checkboxes = new Array();

	this.addCheckbox = function( cbId )
	{
		this.checkboxes.push( cbId );
	}

	this.toggleStates = function( cbId )
	{
		var main = document.getElementById(cbId);
		var cbArray = this.checkboxes

		if (main && cbArray)
		{
			cnt = cbArray.length;
			i = 0;

			for ( i = 0; i < cnt; i++ )
			{
				var obj = document.getElementById(cbArray[i]);
				if (obj)
					if ( main.checked )
						obj.disabled = 1;
					else
						obj.disabled = 0;
			}
		}
	}
}