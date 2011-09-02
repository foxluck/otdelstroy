function getElementComputedStyle(elem, prop) {
	if (typeof elem != "object") {
		elem = document.getElementById(elem);
	}

	// external stylesheet for Mozilla, Opera 7+ and Safari 1.3+
	if (document.defaultView && document.defaultView.getComputedStyle) {
		if (prop.match(/[A-Z]/)) {
			prop = prop.replace(/([A-Z])/g, "-$1").toLowerCase();
		}
		return document.defaultView.getComputedStyle(elem, "")
				.getPropertyValue(prop);
	}

	// external stylesheet for Explorer and Opera 9
	if (elem.currentStyle) {
		var i;
		while ((i = prop.indexOf("-")) != -1) {
			prop = prop.substr(0, i) + prop.substr(i + 1, 1).toUpperCase()
					+ prop.substr(i + 2);
		}
		return elem.currentStyle[prop];
	}

	return "";
}

function getWindowSize(wnd) {

	var windowWidth, windowHeight;

	if (wnd.innerHeight) { // all except Explorer
		if (wnd.document.documentElement.clientWidth) {
			windowWidth = wnd.document.documentElement.clientWidth;
		} else {
			wnd.windowWidth = wnd.innerWidth;
		}
		windowHeight = wnd.innerHeight;
	} else if (wnd.document.documentElement
			&& wnd.document.documentElement.clientHeight) { // Explorer 6 Strict
		// Mode
		windowWidth = wnd.document.documentElement.clientWidth;
		windowHeight = wnd.document.documentElement.clientHeight;
	} else if (wnd.document.body) { // other Explorers
		windowWidth = wnd.document.body.clientWidth;
		windowHeight = wnd.document.body.clientHeight;
	}

	return [windowWidth, windowHeight];
}

function number_format(a, b, c, d) {
	a = Math.round(a * Math.pow(10, b)) / Math.pow(10, b);
	e = a + '';
	f = e.split('.');
	if (!f[0]) {
		f[0] = '0';
	}
	if (!f[1]) {
		f[1] = '';
	}
	if (f[1].length < b) {
		g = f[1];
		for (i = f[1].length + 1; i <= b; i++) {
			g += '0';
		}
		f[1] = g;
	}
	if (d != '' && f[0].length > 3) {
		h = f[0];
		f[0] = '';
		for (j = 3; j < h.length; j += 3) {
			i = h.slice(h.length - j, h.length - j + 3);
			f[0] = d + i + f[0] + '';
		}
		j = h.substr(0, (h.length % 3 == 0) ? 3 : (h.length % 3));
		f[0] = j + f[0];
	}
	c = (b <= 0) ? '' : c;
	return f[0] + c + f[1];
}

function getLayer(layerName, pwindow) {
	if (!pwindow) {
		pwindow = window;
	}

	if (pwindow.document.getElementById) {
		return pwindow.document.getElementById(layerName);
	}
	if (pwindow.document.all)
		return pwindow.document.all[layerName];
	if (pwindow.document.layers)
		return pwindow.document.layers[layerName];
	return null;
}

function changeState(id_name, state) {

	var layer = getLayer(id_name);
	if (is_null(layer))
		return;

	if (is_null(state) ? layer.style.display != 'block' : state) {
		layer.style.display = 'block';
	} else {
		layer.style.display = 'none';
	}
}

function is_null(obj) {

	if (typeof(obj) == 'undefined')
		return true;
	if (obj == null)
		return true;
	return false;
}

function getAbsolutePos(el) {
	var SL = 0, ST = 0;
	var is_div = /^div$/i.test(el.tagName);
	if (is_div && el.scrollLeft)
		SL = el.scrollLeft;
	if (is_div && el.scrollTop)
		ST = el.scrollTop;
	var r = {
		x : el.offsetLeft - SL,
		y : el.offsetTop - ST
	};
	if (el.offsetParent) {
		var tmp = this.getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
}

function getElementsByClass(searchClass, node, tag) {

	var classElements = new Array();
	if (node == null)
		node = document;
	if (tag == null)
		tag = '*';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;

	var pattern = new RegExp("(^|\\s)" + searchClass + "(\\s|$)");
	for (var i = 0, j = 0; i < elsLen; i++) {
		if (pattern.test(els[i].className)) {
			classElements[j] = els[i];
			j++;
		}
	}

	return classElements;
}

function getElementByClass(searchClass, node, tag) {

	var elems = getElementsByClass(searchClass, node, tag);
	if (!elems.length)
		return null;

	return elems[0];
}

function createTag(tag, pel, wnd) {

	if (!wnd)
		wnd = window;
	if (!pel)
		pel = wnd.document.body;

	el = wnd.document.createElement(tag);
	pel.appendChild(el);
	return el;
}

function deleteTag(element, parent) {

	if (!parent)
		parent = element.parentNode;

	parent.removeChild(element);
}

function open_window(link, w, h) {

	var win = "width=" + w + ",height=" + h
			+ ",menubar=no,location=no,resizable=yes,scrollbars=yes";
	var newWin = window.open(link, 'newWin', win);
	if (newWin) {
		newWin.focus();
	}
	return newWin;
}

function select_getCurrValue(selectEntry) {

	for (var i = 0; i < selectEntry.options.length; i++) {

		if (selectEntry.options[i].selected) {
			return selectEntry.options[i].value;
		}
	}

	return null;
}

function select_getCurrOption(selectEntry) {

	for (var i = 0; i < selectEntry.options.length; i++) {

		if (selectEntry.options[i].selected) {
			return selectEntry.options[i];
		}
	}

	return null;
}

function select_selectOptionByValue(selectEntry, value) {

	for (var i = 0; i < selectEntry.options.length; i++) {

		selectEntry.options[i].selected = (value == selectEntry.options[i].value);
	}
}

function select_getOptionByValue(selectEntry, value) {

	for (var i = 0; i < selectEntry.options.length; i++) {

		if (selectEntry.options[i].value == value)
			return selectEntry.options[i];
	}
	return null;
}

function findSelectedIndex(ob) {

	for (i = 0; i < ob.length; i++) {
		if (ob[i].selected)
			return i;
	}
	return -1;
}

function select_addOption(oListbox, text, value, isDefaultSelected, isSelected) {
	var oOption = document.createElement("option");
	oOption.appendChild(document.createTextNode(text));
	oOption.setAttribute("value", value);

	if (isDefaultSelected)
		oOption.defaultSelected = true;
	else if (isSelected)
		oOption.selected = true;

	oListbox.appendChild(oOption);
}

function confirmDelete(id, ask, url) { // confirm order delete

	if (window.confirm(ask)) { // delete
		window.location = url + id;
	}
}

function formatPrice(price) {

	return defaultCurrency.getView(price);
}

function allowInsertAtCarret(field) {

	return document.selection
			|| (field.selectionStart || field.selectionStart == '0') || false;
}

function insertAtCarret(field, value) {

	// IE support
	if (document.selection) {
		field.focus();
		sel = document.selection.createRange();
		sel.text = value;
		// MOZILLA/NETSCAPE support
	} else if (field.selectionStart || field.selectionStart == '0') {
		var startPos = field.selectionStart;
		var endPos = field.selectionEnd;
		var scrollTop = field.scrollTop;
		field.value = field.value.substring(0, startPos) + value
				+ field.value.substring(endPos, field.value.length);

		field.focus();
		var cPos = startPos + (value.length);
		field.selectionStart = cPos;
		field.selectionEnd = cPos;
		field.scrollTop = scrollTop;
	} else {
		field.value += "\n" + value;
	}
	// reposition cursor if possible
	if (field.createTextRange) {
		field.caretPos = document.selection.createRange().duplicate();
	}
}

FadeSteps = ["ff", "ee", "dd", "cc", "bb", "aa", "99"];
FadeTimeout = 600;

function fadeBlock(targetId) {

	DoFade(FadeSteps.length - 1, targetId);
}

function DoFade(colorId, targetId) {

	if (colorId >= 0 && document.getElementById(targetId)) {
		document.getElementById(targetId).style.backgroundColor = "#ffff"
				+ FadeSteps[colorId];

		colorId--;

		// Wait a little bit and fade another shade
		setTimeout("DoFade(" + colorId + ",'" + targetId + "')", FadeTimeout);
	} else if (document.getElementById(targetId).getAttribute('obj_wnd')) {
		document.getElementById(targetId).getAttribute('obj_wnd').close();
	}
}

function __alert(message) {
	var _msg = new Message();
	_msg.__type = MSGTYPE_SUCCESS;
	_msg.__message = message;
	_msg.showMessage();
}

var MSGTYPE_SUCCESS = 1;
var MSGTYPE_ERROR = 2;

var Message = function() {

	this.__type = null;
	this.__code = null;
	this.__message = null;
	this.__params = null;

	this.init = function(data) {

		if (data.type)
			this.__type = data.type;
		if (data.code)
			this.__code = data.code;
		if (data.message)
			this.__message = data.message;
		if (data.params)
			this.__params = data.params;
	};

	this.isError = function() {

		return this.__type == MSGTYPE_ERROR;
	};

	this.isSuccess = function() {

		return this.__type == MSGTYPE_SUCCESS;
	};

	this.getMessage = function() {

		return this.__message;
	};

	this.getParams = function() {

		return this.__params;
	};

	this.showMessage = function() {

		var msgWnd = new wnd();
		msgWnd.move(100, 100);
		msgWnd.setWidth(400);
		var messageBlock = msgWnd.getContentObj();
		messageBlock.className = this.isSuccess()
				? "success_block"
				: "error_block";
		messageBlock.innerHTML = this.getMessage();
		messageBlock.id = 'wnd-msg-block';
		messageBlock.style.position = 'fixed';
		w_size = getWindowSize(window);
		w1_size = msgWnd.getSize();
		msgWnd.move((w_size[0] - w1_size[0]) / 2, (w_size[1] - w1_size[1]) / 4);
		msgWnd.show();
		msgWnd.fade();
	};
};

var wnd = function() {

	if (getLayer('wnd-init')) {
		this.__objDiv = getLayer('wnd-init');
		this.__objDiv.parentNode.removeChild(this.__objDiv);
	}
	this.__objDiv = createTag('div');
	this.__objDiv.id = 'wnd-init';
	this.__objDiv.className = 'wnd_init';
	this.__objDiv.setAttribute('obj_wnd', this);
	/*
	 * var objCloseDiv = createTag('div', this.__objDiv);
	 * objCloseDiv.style.textAlign = 'right'; objCloseButton =
	 * createTag('img',objCloseDiv); objCloseButton.src =
	 * window.url_img+'/w_close.gif'; objCloseButton.alt = translate.btn_close;
	 */
	var objWnd = this;
	/*
	 * objCloseButton.onclick = function (){
	 * 
	 * objWnd.close(); }
	 */
	this.__objContent = createTag('div', this.__objDiv);

	this.fade = function() {

		setTimeout("getLayer('" + this.__objDiv.id
						+ "').style.display = 'none'", 5000);
	};

	this.getSize = function() {
		return [this.__objDiv.offsetWidth, this.__objDiv.offsetHeight];
	};

	this.getContentObj = function() {
		return this.__objContent;
	};

	this.setWidth = function(w) {
		this.__objDiv.style.width = w + 'px';
	};

	this.setHeight = function(h) {
		this.__objDiv.style.height = h + 'px';
	};

	this.show = function() {
		this.__objDiv.style.visibility = 'visible';
	};

	this.hide = function() {
		this.__objDiv.style.visibility = 'hidden';
	};

	this.close = function() {
		this.hide();
		this.__objDiv.parentNode.removeChild(this.__objDiv);
	};

	this.move = function(x, y) {
		this.__objDiv.style.left = x + "px";
		this.__objDiv.style.top = y + "px";
	};
};

function getFormByElem(elem) {

	var max = 50;
	var p = elem.parentNode;
	while (p && p.tagName && !(/^form$/i.test(p.tagName)) && 0 < max--) {
		p = p.parentNode;
	}

	if (p && p.tagName && /^form$/i.test(p.tagName))
		return p;

	return null;
}

function getCookie(name) {
	var start = document.cookie.indexOf(name + '=');
	var len = start + name.length + 1;
	if ((!start) && (name != document.cookie.substring(0, name.length))) {
		return null;
	}
	if (start == -1)
		return null;
	var end = document.cookie.indexOf(';', len);
	if (end == -1)
		end = document.cookie.length;
	return unescape(document.cookie.substring(len, end));
}

function setCookie(name, value, expires, path, domain, secure) {
	var today = new Date();
	today.setTime(today.getTime());
	if (expires) {
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date(today.getTime() + (expires));
	document.cookie = name + '='
			+ escape(value)
			+ ((expires) ? ';expires=' + expires_date.toGMTString() : '')
			+
			// expires.toGMTString()
			((path) ? ';path=' + path : '')
			+ ((domain) ? ';domain=' + domain : '')
			+ ((secure) ? ';secure' : '');
}

function deleteCookie(name, path, domain) {
	if (getCookie(name))
		document.cookie = name + '=' + ((path) ? ';path=' + path : '')
				+ ((domain) ? ';domain=' + domain : '')
				+ ';expires=Thu, 01-Jan-1970 00:00:01 GMT';
}

function catchResult(e) {
	alert(e);
}

function openFadeIFrame(url, caption) {

	sswgt_CartManager.shop_url = (window.WAROOT_URL != null)
			? window.WAROOT_URL
			: ORIG_URL;
	sizes = getPageSize();
	sswgt_CartManager.show(url, sizes[0] * 0.7, sizes[1] * 0.7);
}

function resizeFadeIFrame(width, height) {
	sswgt_CartManager.resizeFrame(width, height);
}

function closeFadeIFrame() {
	sswgt_CartManager.hide();
}

function split_query(query) {

	var gets = query.split(/\&/);
	var q_get = {};
	var t = null;
	for (var i = gets.length - 1; i >= 0; i--) {

		t = gets[i].split(/\=/);
		if (t[0] != null) {
			q_get[t[0]] = t[1] ? t[1] : '';
		}
	}
	return q_get;
}

function set_query(query, url) {

	if (!url)
		url = document.location.href;

	var reg = /([^\?]*)\?([^\#]*)(|\#.*)$/;
	var results = url.match(reg);

	var path_part = results && results[1] ? results[1] : '';
	var get_part = results && results[2] ? results[2] : '';
	var anchor_part = results && results[3] ? results[3] : '';

	var mode = query.search(/^\?/) == -1 ? 'update' : 'new';

	query = query.replace(/^\?|^\&/, '');

	var q_get = split_query(query);
	var u_get = split_query(get_part);
	var new_query = '';
	if (mode == 'update') {// update

		for (var k in q_get) {

			u_get[k] = q_get[k] ? q_get[k] : null;
		}
	} else { // new query

		for (var k in q_get) {

			if (!q_get[k])
				q_get[k] = u_get[k] ? u_get[k] : null;
		}
		u_get = q_get;
	}

	for (var k in u_get) {
		if (k && (u_get[k] != null))
			new_query += '&' + k + '=' + u_get[k];
	}
	return '?' + new_query.replace(/^\&/, '') + anchor_part;
}

var AjaxCptHndls = {

	__handlers : {},

	register : function(event_name, cpt_id, handler) {

		if (is_null(this.__handlers[cpt_id]))
			this.__handlers[cpt_id] = {};

		this.__handlers[cpt_id][event_name] = handler;
	},

	call : function(event_name, cpt_id, data) {

		if (is_null(this.__handlers[cpt_id][event_name]))
			return;

		return this.__handlers[cpt_id][event_name](data);
	}
};

function getEventObject(ev) {

	var my_ev = {};
	ev = ev ? ev : window.event;
	if (ev.srcElement) {
		my_ev.target = ev.srcElement;
	} else {
		my_ev.target = ev.target;
	}

	my_ev.ev = ev;
	return my_ev;
}

function getPageSize() {

	var xScroll, yScroll;

	if (window.innerHeight && window.scrollMaxY) {
		xScroll = window.innerWidth + window.scrollMaxX;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight) { // all
		// but
		// Explorer
		// Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla
		// and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight;

	if (self.innerHeight) { // all except Explorer
		if (document.documentElement.clientWidth) {
			windowWidth = document.documentElement.clientWidth;
		} else {
			windowWidth = self.innerWidth;
		}
		windowHeight = self.innerHeight;
	} else if (document.documentElement
			&& document.documentElement.clientHeight) { // Explorer 6 Strict
		// Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	// for small pages with total height less then height of the viewport
	if (yScroll < windowHeight) {
		pageHeight = windowHeight;
	} else {
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if (xScroll < windowWidth) {
		pageWidth = xScroll;
	} else {
		pageWidth = windowWidth;
	}

	arrayPageSize = new Array(pageWidth, pageHeight, windowWidth, windowHeight);
	return arrayPageSize;
}

var beforeUnloadHandler_contentChanged = false;
var beforeUnloadHandler = function() {

	if (beforeUnloadHandler_contentChanged)
		return translate.msg_unsaved_changes;
	if (window.tinyMCE) {
		var anyDirty = false;
		for (var n in tinyMCE.instances) {
			var inst = tinyMCE.instances[n];
			if (!tinyMCE.isInstance(inst))
				continue;
			if (inst.isDirty())
				return translate.msg_unsaved_changes;
		}
	}
	return;
};

function getCookie(c_name) {
	if (document.cookie.length > 0) {
		c_start = document.cookie.indexOf(c_name + "=");
		if (c_start != -1) {
			c_start = c_start + c_name.length + 1;
			c_end = document.cookie.indexOf(";", c_start);
			if (c_end == -1)
				c_end = document.cookie.length;
			return unescape(document.cookie.substring(c_start, c_end));
		}
	}
	return null;
}

function setCookie(c_name, value, expiredays) {
	var exdate = new Date();
	exdate.setDate(exdate.getDate() + expiredays);
	document.cookie = c_name + "=" + escape(value)
			+ ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString());
}
window.onbeforeunload = beforeUnloadHandler;