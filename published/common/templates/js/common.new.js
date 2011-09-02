WbsCommon = function() {
}

WbsCommon.setPublishedUrl = function (url){
	WbsCommon.publishedUrl = url;
}

WbsCommon.getPublishedUrl = function (addUrl) {
	if (!addUrl)
		return WbsCommon.clearUrl(WbsCommon.publishedUrl);
	return WbsCommon.clearUrl(WbsCommon.publishedUrl + "/" + addUrl);
}

WbsCommon.clearUrl= function(url) {
	url = url.replace(/\/\//g, "/");
	return url;
}

WbsCommon.showLoading = function (el) {
	return false;
	/*var img = createElem("img");
	img.style.width = 16;
	img.style.height = 16;
	img.src = WbsCommon.getPublishedUrl("common/html/res/images/loading.gif");
	img.style.position = "absolute";
	img.style.backgroundColor = "white";
	
	el.insertBefore(img, el.firstChild);*/
}

WbsCommon.showError = function (errorData) {
	alert(errorData.errorStr);
	if (errorData.errorCode && errorData.errorCode == "SESSION_TIMEOUT" && errorData.redirectUrl)
			window.top.location.href = errorData.redirectUrl;
	if (errorData.newLocation && errorData.sessionExpired) { // for old code
			window.top.location.href = errorData.newLocation.replace("../", ""); // only ONE replace need			
	}
}



function setCookie(cookieName,cookieValue,nDays) {
	var today = new Date();
	var expire = new Date();
	if (nDays==null || nDays==0) nDays=1;
	expire.setTime(today.getTime() + 3600000*24*nDays);
	document.cookie = cookieName+"="+escape(cookieValue)
	+ ";expires="+expire.toGMTString();
}

function getCookie(name) {
	var dc = document.cookie;
	var prefix = name + "=";
	var begin = dc.indexOf("; " + prefix);
	if (begin == -1) {
    begin = dc.indexOf(prefix);
    if (begin != 0) return null;
	} else {
	    begin += 2;
	}
	var end = document.cookie.indexOf(";", begin);
	if (end == -1) {
    end = dc.length;
	}
	return unescape(dc.substring(begin + prefix.length, end));
}

function extend(Child, Parent) {
    var F = function() { }
    F.prototype = Parent.prototype
    Child.prototype = new F()
    Child.prototype.constructor = Child
    Child.superclass = Parent.prototype    
}

function newClass(parent, prop) {
  // Dynamically create class constructor.
  var clazz = function() {
    // Stupid JS need exactly one "operator new" calling for parent
    // constructor just after class definition.
    if (clazz.preparing) return delete(clazz.preparing);
    // Call custom constructor.
    if (clazz.constr) {
      this.constructor = clazz; // we need it!
      clazz.constr.apply(this, arguments);
    }
  }
  clazz.prototype = {}; // no prototype by default
  if (parent) {
    parent.preparing = true;
    clazz.prototype = new parent;
    clazz.prototype.constructor = parent;
    clazz.constr = parent; // BY DEFAULT - parent constructor
  }
  if (prop) {
    var cname = "constructor";
    for (var k in prop) {
      if (k != cname) clazz.prototype[k] = prop[k];
    }
    if (prop[cname] && prop[cname] != Object)
      clazz.constr = prop[cname];
  }
  clazz.prototype.superclass = function() {return this.constructor.prototype};
  return clazz;
}


document.onLoadFunctions = new Array();
function registerOnLoad( callback )
{
	document.onLoadFunctions.push( callback );
}

function runOnLoad () {
	for ( i = 0; i < document.onLoadFunctions.length; i++ ) {
		var callback = document.onLoadFunctions[i];
		callback();
	}
}
window.onload = function() {
	runOnLoad();
}

function getDocumentSize()
{
	if (document.clientHeight != null)
		return {height: document.clientHeight, width: document.clientHeight};
	
	if ( typeof(document.documentElement.clientHeight) != 'undefined' && document.documentElement.clientHeight > 0 )
		return {height: document.documentElement.clientHeight, width: document.documentElement.clientWidth};

	if ( typeof(document.body.clientHeight) != 'undefined' )
		return {height: document.body.clientHeight, width: document.body.clientWidth};
	

	return {height: 0, width: 0};
}


function createDiv(className) {
	return createElem("div", className);
}

function createElem(tag, className, attributes) {
	if (Ext.isIE && tag == "input" && attributes && (attributes.type == "radio" || attributes.type == "checkbox") && attributes.name) { // IE bug with radio and checkbox elements
			var elem = document.createElement("<input type='" + this.type + "' name='" + this.name + "'>");
	} else
		var elem = document.createElement(tag);
	
	
	if (Ext.isIE) {
		elem.show = window.showElem;
		elem.hide = window.hideElem;
	}
	if (className)
		elem.className = className;
	
	if (attributes) {
		for (var attName in attributes)
			elem.setAttribute(attName, attributes[attName]);
	}
	
	return elem;	
}


function createTextSpan(value, cls) {
	var span = createElem("span", cls);
	span.appendChild(document.createTextNode(value));
	return span;
}

function createLink(label, className, href) {
	if (href == null)
		href = "javascript:void(0)";
	var link = createElem("a");
	link.href = href;
	link.innerHTML = label;	
	return link;
}

function clearNode(node) {
	var nodesToRemove = new Array ();
	while (node.childNodes.length > 0) {
		node.removeChild(node.firstChild);
	}
	/*for (var childId in node.childNodes) {
		nodesToRemove[nodesToRemove.length] = childId;
	}
	
	for (var i = 0; i < nodesToRemove.length; i++) {
		var childNode = node.childNodes[nodesToRemove[i]];
		if (childNode.nodeType == 1) {
			node.removeChild(childNode);
		}
	}*/
}

function getStyle(elem, sStyle, pxClear) {
	var x = elem;
	var y;
	if (x.currentStyle) {
  	y = x.currentStyle[sStyle.camelize()];
	} else {
		try {
   		y = document.defaultView.getComputedStyle(x,null).getPropertyValue(sStyle);
   	}
   		catch(e) { }
 	}
 	if (y == "auto")
 		y = "0";
 		
 	if (pxClear) {
 		y = (y) ? y.replace("px", "") * 1 : 0;
 	}
 	return y;
}

String.prototype.camelize = function () {
  var oStringList = this.split('-');
  if (oStringList.length == 1) return oStringList[0];

  var camelizedString = this.indexOf('-') == 0
    ? oStringList[0].charAt(0).toUpperCase() + oStringList[0].substring(1)
    : oStringList[0];

  for (var i = 1, len = oStringList.length; i < len; i++) {
    var s = oStringList[i];
    camelizedString += s.charAt(0).toUpperCase() + s.substring(1);
  }

  return camelizedString;
}

String.prototype.truncate = function (length) {
	if ((this.length - 3) < length)
		return this;
	return this.substring(0,length) + "...";
}


String.prototype.htmlSpecialChars = function() {
	if (this == null || this.length == 0) {
		  return this;
	}
    return this.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;").replace(/\n/g, "<br />");
}

// simple
String.prototype.sprintf = function (param1, param2, param3) {
	var args = Array.prototype.slice.call(arguments);
  args.unshift(this);
  return sprintf.apply(this,args);	
}


function getFilesizeStr (fileSize) {
	fileSize = parseInt(fileSize);
	if ( !fileSize )
		return "0.00 KB";
		
	var res = "";
	if ( fileSize < 1024 )
		res = fileSize + " bytes";
	else if ( fileSize < 1024*1024 )
		res = Math.round(100*(Math.ceil(fileSize)/1024))/100 + " KB";
	else
		res = Math.round(100*Math.ceil(fileSize)/(1024*1024))/100 + " MB";
	return res;
}

function addClass(elem, className) {
	if (elem.className == null)
		elem.className = "";
	var classes = elem.className.split(" ");
	for (var i = 0; i < classes.length; i++) {
		if (classes[i] == className)
			return;
	}	
	classes[classes.length] = className;
	elem.className = classes.join(" ");
}

function removeClass (elem, className) {
	var classes = elem.className.split(" ");
	var newClasses = new Array ();
	for (var i = 0; i < classes.length; i++) {
		if (classes[i] == className)
			continue;
		newClasses[newClasses.length] = classes[i];
	}	
	elem.className = newClasses.join(" ");
}

var Exception = function(message) {
	this.message = message;
}


function addHandler(element,event,action,scope, param){
	if (scope)
		action = action.bind(scope);
	
  if(document.addEventListener)element.addEventListener(event,action,param);
  else if(document.attachEvent)element.attachEvent('on'+event,action);
  else element['on'+event]=action;
}


Function.prototype.bind = function(object) {
  var __method = this;
  return function() {
  	return __method.apply(object, arguments);
	}
}

if (typeof(jQuery) == 'undefined') {
	Object.prototype.extend = function(object) {
		  for (var prop in object)
		  	this[prop] = object[prop];
		  return this;
	}
}

if (window.Element) {
	Element.prototype.show = function() {
		this.style.display = "";
	}

	Element.prototype.hide = function() {
		this.style.display = "none";
	}
} else {
	window.showElem = function() {
		this.style.display = "";
	}
	window.hideElem = function() {
		this.style.display = "none";
	}
}


function addEmptyImg(elem) {
	var img = createElem("img");
	img.src = WbsCommon.getPublishedUrl("common/html/res/images/s.gif");
	img.style.width = "1px";
	img.style.height = "1px";	
	elem.appendChild(img);
}


Html = {
	getRadioGroupValue: function (radioName, parentNode)
	{
		if (!parentNode)
			parentNode = document;
		
		var inputs = parentNode.getElementsByTagName("input");
		
		for (var i=0; i < inputs.length; i++)
	    if (inputs[i].name == radioName && inputs[i].checked) return inputs[i].value;

	  return null;
	},
		
	setRadioGroupValue: function (radioName, value, parentNode)
	{
		if (!parentNode)
			parentNode = document;
		var inputs = parentNode.getElementsByTagName("input");
		
		for (var i=0; i < inputs.length; i++)
	    if (inputs[i].name == radioName) 
	  		inputs[i].checked = (inputs[i].value == value);
	  return null;
	}	
};








function sprintf () {
var a, f = arguments[0], fi= '', i = 1, m = [''], o = '', p;
while (f = f.substring (m[0].length)) {
m = /^([^\%]*)(?:(\x25)((\x25)|(?:(\d+)\$)?(\+)?(0|'([^$]))?(-|\^)?(\d+)?(?:\.(\d+))?([bcdefosuxX])))?/.exec(f);
if (a = m[12]) {
if (arguments.length < (i = m[5] || i))
throw("sprintf '" + m[0] + "' : No argument " + i);
a = arguments[i++];
s = (/[def]/.test(m[12]) && m[6] && a > 0) ? '+':'';
switch (m[12]) {
case 'b': a = a.toString(2); break;
case 'c': a = String.fromCharCode(a); break;
case 'd': a = parseInt(a); break;
case 'e': a = m[11] ? a.toExponential(m[11]) : a.toExponential(); break;
case 'f': a = m[11] ? parseFloat(a).toFixed(m[11]) : parseFloat(a); break;
case 'o': a = a.toString(8); break;
case 's': a = ((a = String(a)) && m[11] ? a.substring(0, m[11]) : a); break;
case 'u': a = Math.abs(a); break;
case 'x': a = a.toString(16); break;
case 'X': a = a.toString(16).toUpperCase(); break;
}
if (m[10] && (m[10] > a.length)) {
fir=fil=str_repeat(m[7] ? m[8] || '0' : ' ', m[10]-a.length);
if (m[9] == '^') {
fil = fil.substr(0, fil.length / 2);
fir = fir.substr(fil.length);
} else
m[9] == '-' ? (fil = '') : (fir = '');
a = fil + a + fir;
}
}
o += m[1] + ((m[3] ? m[4] || (s + a) : m[2]) || '');
}
return o;
}
