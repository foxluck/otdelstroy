function createRequestObject() {
	if (window.XMLHttpRequest) {
		try {
			return new XMLHttpRequest();
		} catch (e) {
		}
	} else if (window.ActiveXObject) {
		try {
			return new ActiveXObject('Msxml2.XMLHTTP');
		} catch (e) {
		}
		try {
			return new ActiveXObject('Microsoft.XMLHTTP');
		} catch (e) {
		}
	}
	return null;
}

function runAction(action, query) {
	if (query) {
		if (!confirm(query))
			return false;
	}
	var req = createRequestObject();
	req.open("GET", "?action=" + action, true);
	req.setRequestHeader("If-Modified-Since", "Sat, 1 Jan 2000 00:00:00 GMT");
	req.send(null);
	var obj = document.getElementById('buttons');
	if (obj)
		obj.style.display = 'none';
	obj = document.getElementById('buttons_img');
	if (obj)
		obj.style.display = 'block';
	setTimeout("window.location.reload()", 2000);

}
var xmlReq = null;
var progressManager = {
	'lookup' : true,
	'language' : 'eng',
	'prevState' : null,
	'hasRestartTime' : null,
	'init' : function(language) {
		if (language)
			progressManager.language = language;
		setTimeout(progressManager.getState, 800);
	},
	'hideProgress' : function() {
		var progressBar = getElementByClass('progressBar');
		var progressValue = getElementByClass('progressValue');
		if (progressValue)
			progressValue.style.display = 'none';
		if (progressBar)
			progressBar.style.display = 'none';
	},
	'showLabel' : function(label) {
		var progressValue = getElementByClass('progressValue');
		if (progressValue) {
			progressValue.style.display = 'block';
			progressValue.innerHTML = label;
		}
	},
	'showDescription' : function(description) {
		var descriptionElement = document.getElementById('statusDescription');
		if (descriptionElement) {
			descriptionElement.style.display = 'block';
			descriptionElement.innerHTML = description;
		}
	},
	'showTitle' : function(title) {
		var titleElement = document.getElementById('statusTitle');
		if (titleElement) {
			titleElement.style.display = 'block';
			titleElement.innerHTML = title;
		}
	},
	'showProgress' : function() {
		var progressBar = getElementByClass('progressBar');
		var progressValue = getElementByClass('progressValue');
		if (progressValue)
			progressValue.style.display = 'block';
		if (progressBar)
			progressBar.style.display = 'block';
	},
	'onProgress' : function(progress, description, title)// intVal
	{
		try {
			if (progress == null) {
				progressManager.hideProgress()
			} else {
				progressManager.showProgress(progress);
				var progressBarStripe = document
						.getElementById('progressBarStripe');
				var progressValue = getElementByClass('progressValue');
				var style = '' + (2 * progress) + 'px';
				var value = '' + progress + '% ';// +progressManager.prevState;
			}
			progressManager.showDescription(description);
			progressManager.showTitle(title);
			if (progressBarStripe)
				progressBarStripe.style.left = style;
			if (progressValue)
				progressValue.innerHTML = value;

		} catch (e) {
			alert(e.message);
		}
	},
	'onWait' : function()// UPDATE
	{
		progressManager.hideProgress();
	},
	'onRestart' : function(restartDescription, title)// RESTART
	{

		try {
			var curDate = new Date();
			var curTime = curDate.getTime();
			if ((progressManager.hasRestartTime == null)
					|| ((curTime - progressManager.hasRestartTime) > 10000)) {
				if (restartDescription)
					progressManager.showDescription(restartDescription);

				progressManager.hasRestartTime = curTime;
				var xmlReqRestart = createRequestObject();
				// xmlReq.onreadystatechange=progressManager.onGetState;
				xmlReqRestart.open("GET", "?action=install&restart=1", true);
				xmlReqRestart.setRequestHeader("If-Modified-Since",
						"Sat, 1 Jan 2000 00:00:00 GMT");
				xmlReqRestart.send(null);
			} else {
				// progressManager.showLabel('restart skipped'+curTime+'
				// '+progressManager.hasRestartTime+'
				// delta:'+(curTime-progressManager.hasRestartTime));
				return;
			}
		} catch (e) {
			alert(e.message);
		}
	},
	'onDownload' : function() {
		try {
			var curDate = new Date();
			var curTime = curDate.getTime();
			if ((progressManager.hasRestartTime == null)
					|| ((curTime - progressManager.hasRestartTime) > 10000)) {
				progressManager.hasRestartTime = curTime;
				var xmlReqRestart = createRequestObject();
				// xmlReq.onreadystatechange=progressManager.onGetState;
				xmlReqRestart.open("GET", "?action=full&force=1", true);
				xmlReqRestart.setRequestHeader("If-Modified-Since",
						"Sat, 1 Jan 2000 00:00:00 GMT");
				xmlReqRestart.send(null);
			} else {
				// progressManager.showLabel('restart skipped'+curTime+'
				// '+progressManager.hasRestartTime+'
				// delta:'+(curTime-progressManager.hasRestartTime));
				return;
			}
		} catch (e) {
			alert(e.message);
		}
	},
	'onRefresh' : function()// ANOTHER
	{
		progressManager.hideProgress();
		progressManager.lookup = false;
		// progressManager.showLabel('onRefresh');
		progressManager.reload();
	},
	'getState' : function(prevState) {
		try {
			xmlReq = createRequestObject();
			xmlReq.onreadystatechange = progressManager.onGetState;
			xmlReq.open("GET", "?ajax=1&lang=" + (progressManager.language),
					true);
			xmlReq.setRequestHeader("If-Modified-Since",
					"Sat, 1 Jan 2000 00:00:00 GMT");
			xmlReq.send(null);
		} catch (e) {
			alert(e.message);
		}
	},
	'onGetState' : function() {
		try {
			if (xmlReq.readyState == 4) {// 4 = "loaded"
				if (xmlReq.status == 200) {// 200 = OK
					// progressManager.showLabel(xmlReq.responseText);
					var responce = xmlReq.responseText
							.match(/^([^:]+):([^:]+):([^:]+):(.*)$/);
					// [1 CODE]:[2 PROGRESS]:[3 DESCRIPTION]:[4 TITLE]
					/*
					 * alert('code '+responce[1]+ '\nprogress '+responce[2]+
					 * '\ntitle '+responce[3]+ '\ndescription '+responce[4]);
					 */
					switch (responce[1]) {// ...our code here...
						case 'DONWLOAD_RESTART' :
							progressManager.onDownload();
							break;
						case 'RESTART' :
							progressManager.onRestart(responce[4], responce[3]);
							break;
						case 'REFRESH' :
							progressManager.onRefresh();
							break;
						case 'WAIT' :
							progressManager.onWait();
							progressManager.prevState = 'wait';
							break;
						case 'PROGRESS' :
							var progress = responce[2];
							if (isNaN(progress)) {
								progressManager.onProgress(null, responce[4],
										responce[3]);
							} else {
								progressManager.prevState = ((progress > 100)
										? 'download'
										: 'install');
								progressManager.onProgress(progress,
										responce[4], responce[3]);
							}
							break;
						default :
							progressManager.showLabel('unknown state: '
									+ responce[0]);
							break;
					}
					xmlReq = null;

					if (progressManager.lookup)
						setTimeout(progressManager.getState, 1200);
				} else {
					if (progressManager.lookup)
						setTimeout(progressManager.getState, 1200);
				}
			}
		} catch (e) {/* alert(e.message); */
			if (progressManager.lookup)
				setTimeout(progressManager.getState, 800);
		}
	},
	'reload' : function() {
		setTimeout("window.location.reload()", 400);
	}
}

function showDetailsWindow(link) {
	window
			.open(link, 'WA_detailsWindow',
					'alwaysRaised=yes,innerWidth=900,innerHeight=675,menubar=no,scrollbars=yes');

}
function allowUpdate() {
	// if(isCheck == true){
	var $agree1 = document.getElementById('agree_1');
	var $agree2 = document.getElementById('agree_2');
	var $button = document.getElementById('btn_install');
	if ($agree1 && $agree2 && $button) {
		$button.disabled = !($agree1.checked & $agree2.checked);
	}
	/*
	 * }else{ setTimeout("allowUpdate(true)",100); }
	 */
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
function disableGroups(positiveClass, invertClass, value) {
	var positiveElements = getElementsByClass(positiveClass);
	for (var i = 0; i < positiveElements.length; i++) {
		positiveElements[i].disabled = value;
	}
	var invertElements = getElementsByClass(invertClass);
	for (var i = 0; i < invertElements.length; i++) {
		invertElements[i].disabled = !value;
	}
}
function groupCheckBox(masterClass, itemClass) {
	var masterValue = true;
	var masterCheckbox = getElementByClass(masterClass);
	var itemCheckboxs = getElementsByClass(itemClass);
	for (var i = 0; i < itemCheckboxs.length; i++) {
		if (!itemCheckboxs[i].checked) {
			masterValue = false;
		}
	}
	if (masterCheckbox) {
		masterCheckbox.checked = masterValue;
	}
}
function setGroupCheckBox(masterClass, itemClass) {
	var masterCheckbox = getElementByClass(masterClass);
	if (!masterCheckbox)
		return false;
	var itemCheckboxs = getElementsByClass(itemClass);
	for (var i = 0; i < itemCheckboxs.length; i++) {
		itemCheckboxs[i].checked = masterCheckbox.checked;
	}
}

modalWindow = function() {
	this.titleElement = null;
	this.controlBar = null;
	this.contentElement = null;
	this.closeButton = null;
	this.fade = null;
	this.window = null;

	this.init = function() {
		alert('init');

	};
	this.show = function(mainElementId, titleElementId, contentElementId,
			controlbarElementId, closeButtonId) {
		try {
			// window
			this.window = document.getElementById(mainElementId);
			if (!this.window) {
				this.window = createTag('div');
			}
			this.window.className = 'modal-window';
			this.window.style.display = 'block';
			this.window.style.backgound = '#eee';

			// fade background
			this.fade = createTag('div', this.window);
			this.fade.className = 'modal-window modal-window-fade';
			var _this = this;
			this.fade.onclick = function() {
				_this.close();
			};
			this.window.parentNode.insertBefore(this.fade, this.window);

			// content
			this.contentElement = document.getElementById(contentElementId);
			this.contentElement.className = 'modal-window modal-window-content';
			this.contentElement.style.resize = 'none';
			// title
			this.titleElement = document.getElementById(titleElementId);
			this.titleElement.className = 'modal-window modal-window-title';

			// controlbar
			this.controlBar = document.getElementById(controlbarElementId);
			if (!this.controlBar) {
				this.controlBar = createTag('div', this.window);
			} else {
			}
			this.controlBar.className = 'modal-window-control';

			// close button
			this.closeButton = document.getElementById(closeButtonId);
			if (this.closeButton) {
				this.closeButton.onclick = function() {
					_this.close();
				};
			}
			// body
			document.body.style.overflow = 'hidden';

		} catch (e) {
			alert(e.message + '\n' + '\nmodalWindow.show');
		}

		this.resize();
		var _this = this;
		window.onresize = function() {
			_this.resize()
		};
		document.onkeypress = function(event) {
			_this.onkeypress(event);
		};

	};

	this.close = function() {
		window.onresize = null;
		window.onkeypress = null;
		document.body.style.width = 'auto';
		document.body.style.height = 'auto';
		document.body.style.overflow = 'auto';
		this.window.style.display = 'none';
		this.fade.style.display = 'none';
		return false;

		;
	};
	this.onkeypress = function(e) {
		var code = null;
		try {

			if (window.event)
				code = window.event.keyCode;
			else if (e.which)
				code = e.which;

		} catch (e) {
			// alert('err: '+e.message);
			// code = e.which;
		}
		// alert(code);

		if (code == 27) {
			this.close();
		}

	}
	this.fade = function(delta, timeout) {
	};
	this.resize = function() {
		try {
			var size = this.getSize();
			var bufer = window.onresize;
			var _iteration_limit;
			window.onresize = null;

			// content
			size.width = ((size.width > 90) ? size.width : 90);
			size.height = ((size.height > 160) ? size.height : 160);
			this.window.style.height = ((size.height > 90)
					? (size.height - 80)
					: 10)
					+ 'px';// +'px';
			this.window.style.width = ((size.width > 90)
					? (size.width - 40)
					: 50)
					+ 'px';
			this.contentElement.style.height = ((size.height > 170)
					? (size.height - 160)
					: 10)
					+ 'px';// +'px';
			this.contentElement.style.width = ((size.width > 95)
					? (size.width - 80)
					: 10)
					+ 'px';

			this.window.style.height = (this.contentElement.offsetHeight + 80)
					+ 'px';// +'px';
			this.window.style.width = (this.contentElement.offsetWidth + 40)
					+ 'px';
			this.titleElement.style.width = (this.contentElement.offsetWidth - 10)
					+ 'px';

			// controlbar

			// body

			document.body.style.width = size.width + 'px';
			document.body.style.height = size.height + 'px';
			this.fade.style.width = size.width + 'px';
			this.fade.style.height = size.height + 'px';
			window.onresize = bufer;

		} catch (e) {
			alert('error: ' + e.message + '\n\nthis.resize');
		}
	};
	this.getSize = function() {
		var myWidth = 0, myHeight = 0;
		if (typeof(window.innerWidth) == 'number') {
			// Non-IE
			myWidth = window.innerWidth;
			myHeight = window.innerHeight;
		} else if (document.documentElement
				&& (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
			// IE 6+ in 'standards compliant mode'
			myWidth = document.documentElement.clientWidth;
			myHeight = document.documentElement.clientHeight;
		} else if (document.body
				&& (document.body.clientWidth || document.body.clientHeight)) {
			// IE 4 compatible
			myWidth = document.body.clientWidth;
			myHeight = document.body.clientHeight;
		}
		return {
			'width' : myWidth,
			'height' : myHeight
		};
	}
};
function findObj(n, d) { // v4.01
	var p, i, x;
	if (!d)
		d = document;
	if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
		d = parent.frames[n.substring(p + 1)].document;
		n = n.substring(0, p);
	}
	if (!(x = d[n]) && d.all)
		x = d.all[n];
	for (i = 0; !x && i < d.forms.length; i++)
		x = d.forms[i][n];
	for (i = 0; !x && d.layers && i < d.layers.length; i++)
		x = findObj(n, d.layers[i].document);
	if (!x && d.getElementById)
		x = d.getElementById(n);
	return x;
}
function focusControl(objName) {
	if (objName) {
		var obj = findObj(objName);
		if (obj) {
			obj.focus();
			return true;
		}else{
			return false;
		}
	}
}

function invertDisplayForChildren(object) {
	children = object.childNodes
	try {
		for (i = 0; i < children.length; i++) {
			if (children.item(i).nodeType == 1) {
				children.item(i).style.display = (children.item(i).style.display == 'none')
						? 'block'
						: 'none';
				disableChildrenInputs(children.item(i),
						(children.item(i).style.display == 'none')
								? true
								: false);
			}
		}
	} catch (e) {
		alert(e.message);
	}
}

function disableChildrenInputs(object, disabled) {
	if (object.hasChildNodes()) {
		var children = object.childNodes;
		for (j = 0; j < children.length; j++) {
			if (children.item(j).nodeType == 1) {
				children.item(j).disabled = disabled;
			}
		}
	}
}

function changeVisibilityByClasses(hideClass, showClass) {
	var elements = getElementsByClass(hideClass);
	if (elements) {
		for (i = 0; i < elements.length; i++) {
			elements[i].style.display = 'none';
			disableChildrenInputs(elements[i], true);
		}
	}
	elements = getElementsByClass(showClass);
	if (elements) {
		for (i = 0; i < elements.length; i++) {
			elements[i].style.display = 'block';
			disableChildrenInputs(elements[i], false);
		}
	}
}

function appSelected(app_cb, parents, slaves) {
	if (app_cb.checked) {
		for (var i = 0; i < parents.length; i++)
			if (parents[i] != "") {
				for (var j = 0; j < document.cform.elements.length; j++)
					if (document.cform.elements[j].name == ("app_list["
							+ parents[i] + "]"))
						document.cform.elements[j].checked = true;
			}
	} else {
		for (var i = 0; i < slaves.length; i++)
			if (slaves[i] != "") {
				for (var j = 0; j < document.cform.elements.length; j++)
					if (document.cform.elements[j].name == ("app_list["
							+ slaves[i] + "]"))
						document.cform.elements[j].checked = false;
			}
	}
}

function onFocusSelect(name_selected, name_unselected) {
	rbtn_selected = document.getElementById(name_selected);
	rbtn_unselected = document.getElementById(name_unselected);
	if (!rbtn_selected.checked && !rbtn_unselected.checked)
		rbtn_selected.checked = true;
}

function onSelectFocus(name) {
	control = document.getElementById(name);
	if (control) {
		control.focus();
	}

}