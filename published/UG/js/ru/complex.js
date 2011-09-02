WbsObservable = newClass(Ext.util.Observable, {});

WbsApplication = newClass(null, {
	contructor: function() {
	},
		
	startLoading: function() {
		if (window.parent && window.parent.showHideLoading)
  		window.parent.showHideLoading(true);
	},
		
	finishLoading: function() {
		if (window.parent && window.parent.showHideLoading)
  		window.parent.showHideLoading(false);
	},
	
	openSubframe: function(url, oldUrl) {
  	this.closeSubframe();
  	
  	if (oldUrl)
  		url = this.getOldUrl(url);
  	
  	var iframe = createElem("iframe", "subframe");
  	iframe.style.width = "100%";
  	iframe.style.height = "100%";
  	iframe.frameBorder = "no";
  	iframe.setAttribute("SCROLLING", "NO");
  	this.startLoading();
  	addHandler(iframe, "load", this.finishLoading, this);
  	//iframe.onload = function() {alert("HELLO"); }
  	
  	var contentBlock = document.getElementById("screen-content-block");
  	contentBlock.insertBefore (iframe, contentBlock.firstChild);
  	
  	if (Ext.isOpera) {
  		iframe.location.href = url;
  	} else {
  		iframe.src = url;
  	}
  	this.subFrame = iframe;
  },
  
  closeSubframe: function() {
  	if (this.subFrame) {
  			jQuery(this.subFrame).remove();
  	}  
	jQuery("#main-container").show();
  }  
});


function getAbsolutePos(el)
{
	var r = { x: el.offsetLeft, y: el.offsetTop };
	if (el.parentNode && el.parentNode.scrollTop)
		r.y -= el.parentNode.scrollTop;
	if (el.offsetParent)
		{
		var tmp = getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
		}
	return r;
}

function mousePageXY(e)
{
  var x = 0, y = 0;

  if (!e) e = window.event;

  if (e.pageX || e.pageY) {
    x = e.pageX;
    y = e.pageY;
  } else if (e.clientX || e.clientY) {
    x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
    y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
  }

  return {"x":x, "y":y};
}


var WbsPopwindow = newClass (WbsObservable, {
	
	constructor: function (config) {
		if (!config)
			config = {};
		this.config = config;
		
		if (!this.config.hideMode)
			this.config.hideMode = "close";
		
		if (!document.wbsPopmenuClickLinked) {
			addHandler(document,'click',wbsPopmenuOnDocumentClick,false);
			document.wbsPopmenuClickLinked = true;
		}
		
		var elem = document.createElement("div");
		elem.style.position = "absolute";
		addClass(elem, this.getWindowCls());
		
		if (config.cls)
			addClass(elem, config.cls);
		
		if (config.width)
			elem.style.width = config.width + "px"
		else if (!config.cls)
			elem.style.width = "auto";
		
		if (config.height)
			elem.style.height = config.height + "px"

		this.elem = elem;
	},
		
	getWindowCls: function() {
		return "wbs-popwindow";
	},
			
	render: function() {
	},
			
		
	show: function (e) {
		if(document.wbsPopmenuCurrent != null)
			document.wbsPopmenuCurrent.close();
		this.render();
		//document.wbsPopmenuShowProcess = true;
		
		if(e.stopPropagation) e.stopPropagation();
		else e.cancelBubble = true;
			
		var target=e.target||e.srcElement;
		var cursorPos = mousePageXY(e);
		//var cursorPos = getAbsolutePos(target);
		if (target)
			var targetPos = getAbsolutePos(target);
		
		var left = targetPos.x;
		var top = targetPos.y;
		
		
		//elem.style.top = cursorPos.y;
		var elem = this.elem;
		elem.style.top = top + target.offsetHeight + "px";
		elem.style.left = left + "px";
		
		document.body.appendChild(elem);
		elem.style.visibility = "visible";
		
		document.wbsPopmenuCurrent = this;
		
		if (left + elem.offsetWidth > getDocumentSize().width) {
			elem.style.left = (getDocumentSize().width - elem.offsetWidth-20) + "px";
		}
		if (top + elem.offsetHeight > getDocumentSize().height) {
			var newTop = top - elem.offsetHeight;
			if (newTop > 0)
				elem.style.top = newTop + "px";
		}
		
		
		if (this.onAfterShow)
			this.onAfterShow(e);
	},
		
	hide: function() {
		throw "Not implement hide method";		
	},
	
	close: function() {
		if (this.onClose)
			this.onClose();
		
		var elem = this.elem;
		elem.style.visibility = "hidden";
		document.wbsPopmenuCurrent = null;
		elem.parentNode.removeChild(elem);
		delete elem;
		//delete this;
	},
	
	getElem: function () {
		return this.elem;
	},
		
	getInnerElem: function() {
		if (this.innerElem)
			return this.innerElem;
		
		this.innerElem = createDiv("wbs-popwindow-inner");
		this.getElem().appendChild(this.innerElem);
		return this.innerElem;
	}
});

function getAbsolutePos(el)
{
	var r = { x: el.offsetLeft, y: el.offsetTop };
	if (el.parentNode && el.parentNode.scrollTop)
		r.y -= el.parentNode.scrollTop;
	if (el.offsetParent)
		{
		var tmp = getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
		}
	return r;
}

function mousePageXY(e)
{
  var x = 0, y = 0;

  if (!e) e = window.event;

  if (e.pageX || e.pageY) {
    x = e.pageX;
    y = e.pageY;
  } else if (e.clientX || e.clientY) {
    x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
    y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
  }

  return {"x":x, "y":y};
}



function wbsPopmenuOnDocumentClick(e){
	if (!document.wbsPopmenuCurrent)
		return;
	/*if (document.wbsPopmenuShowProcess) {
		document.wbsPopmenuShowProcess = false;
		return;
	}*/
	e=e||event;
  var target=e.target||e.srcElement;
  var menuElem = document.wbsPopmenuCurrent.getElem();
  if(menuElem){
    var parent=target;
    while(parent.parentNode&&parent!=menuElem)
    	parent=parent.parentNode;
    if(!parent || parent != menuElem)
      document.wbsPopmenuCurrent.close();
  }
}

WbsPopmenu = newClass(WbsPopwindow, {
	constructor: function (config) {
		this.items = new Array ();
		this.superclass().constructor.call(this, config);
	},
		
	render: function() {
		
		var ul = document.createElement("ul");
		for (var i = 0; i < this.config.items.length; i++) {
			var item = this.config.items[i];
			
			var li = this.createItem(item);
			if (item.id) 
				this.items[item.id] = li;
			else
				this.items[this.items.length] = li;
			
			ul.appendChild(li);
		}
		this.elem.appendChild(ul);
	},
		
	getWindowCls: function() {
		if (this.config.withImages)
			return "wbs-popmenu wbs-popmenu-images";
		else
			return "wbs-popmenu";
	},
	
	getItem: function(id) {
		return this.items[id];
	},
	
	setItems: function(items) {
		this.config.items = items;
	},
	
	refreshItem: function (li, item) {
		var newLi = this.createItem(item);
		if (item.id)
			this.items[item.id] = li;
		li.parentNode.replaceChild(newLi, li);
	},
	
	hideItem: function (id) {
		addClass(this.items[id], "hidden");
	},
	
	showItem: function (id) {
		removeClass(this.items[id], "hidden");
	},
	
	createItem: function(item) {
		var li = document.createElement("li");
		if (item.cls)
			addClass(li, item.cls);
		
		if (item.iconCls) {
			addClass(li, "with-image");
			addClass(li,item.iconCls);
		}
		
		if (item == "-") {
			li.className = "separator";
			li.innerHTML = "<div></div>";
		} else if (item.html) {
			li.innerHTML = item.html;
		} else {
			var anchor = document.createElement("a");
			anchor.href = "javascript:void(0)";
			var text = document.createTextNode(item.label);
			anchor.appendChild(text);
			li.appendChild(anchor);
		}

		if (item.onClick && !item.disabled) {
			li.onclick = function(item) { 
				return function() {
					var scope = item.scope ? item.scope : this;
					var val = item.onClick.bind(scope)();
					if (!item.onClickNoHide)
						this.close();
					return val;
				}; 			
			} (item).bind(this);
		}
		
		li.onmouseover = function() {
			if (!this.disabled)
				addClass(this,"highlight");
		}
		li.onmouseout = function() {
			if (!this.disabled)
				removeClass(this, "highlight");
		}
		
		if(item.hidden) {
			addClass(li, "hidden");
		}
		if(item.disabled) {
			addClass(li, "disabled");
			li.disabled = true;
		}
		li.setText = function(text) {this.innerHTML = text;}
		return li;
	}
});

var WbsButton = newClass (null, {
	constructor: function(config) {
		this.config = config;
		this.disabled = false;
		if (!this.cls)
			this.cls = "wbs-btn";
		this.render();
	},
	
	build: function(mainElem, advElem) {
		this.btnElem.appendChild(advElem);
		this.btnElem.appendChild(mainElem);
	},
		
	setDisplayed: function (value) {
		this.btnElem.style.display = (value != false) ? "" : "none";
	},
	
	setTitle: function(title) {
			this.btnElem.firstChild.innerHTML = title;
	},
	
	render: function() {
		var contentElem = this.getContentElem();
		
		var btnElem = createDiv(this.cls);
		this.btnElem = btnElem;
		
		var advElem = createDiv("wbs-btn-adv");
		this.advElem = advElem;
		var mainElem = createElem("a", "wbs-btn-content");
		mainElem.setAttribute("href", this.config.href ? this.config.href : "javascript:void(0)");
		
		if (this.config.iconUrl) {
			mainElem.style.backgroundImage = "URL(" + this.config.iconUrl + ")";
			addClass(mainElem, "wbs-btn-withicon");
		}
		
		if (this.config.advIconUrl) {
			advElem.style.backgroundImage = "URL(" + this.config.advIconUrl + ")";
			addClass(advElem, "wbs-btn-left-icon");
		}
		
		var label = this.config.label;
		if (!label && contentElem.value)
			label = contentElem.value;
		if (!label)
			label = "";
		mainElem.innerHTML = label;
		this.build(mainElem, advElem);
		
		if(this.config.onClick)
			addHandler(btnElem, "click", this.onClick, this);
		
		contentElem.parentNode.replaceChild(btnElem, contentElem);
	},
	
	getContentElem: function() {
		var contentElem = document.getElementById(this.config.el);
		return contentElem;		
	},
		
	getElem: function() {
		return this.btnElem;
	},
		
	onClick: function(e) {
		if (this.disabled)
			return false;
		if (this.config.onClick)
			this.config.onClick(e);
	},
		
	setDisabled: function(value) {
		this.disabled = value;
		if (value)
			addClass(this.btnElem, "wbs-btn-disabled");
		else
			removeClass(this.btnElem, "wbs-btn-disabled");
	},
		
	disable: function() {
		this.setDisabled(true);
	}, 
		
	enable: function() {
		this.setDisabled(false);
	}
});

var WbsLinkButton = newClass(WbsButton, {
	constructor: function (config) {
		this.cls = "wbs-link-btn";
		this.superclass().constructor.call(this,config);	
	},
	
	build: function(mainElem, advElem) {
		this.btnElem.appendChild(mainElem);
	}	
});

var WbsMenuButton = newClass(WbsButton, {
	constructor: function (config) {
		if (!document.getElementById(config.el)) {
			return false;
		}
		this.cls = "wbs-menu-btn";
		this.noAdvElem = config.advElem | false;
		this.superclass().constructor.call(this,config);	
		var event = config.eventType || 'click';
		if (this.config.getMenu)
			addHandler(this.btnElem, event, function(e) {
				if (this.config.url) {
					var t = e.target || e.srcElement;
					if (t.className != 'wbs-btn-adv') {
						document.app.openSubframe(this.config.url, 1);
						return false;
					}
				}
				
				var getMenu = (this.config.scope) ? this.config.getMenu.bind(this.config.scope) : this.config.getMenu;
				var menu = getMenu(e);
				if (menu) {
					menu.show(e);
				}
			}, this);
	},
	
	build: function(mainElem, advElem) {
		this.btnElem.appendChild(mainElem);
		if (!this.config.noAdvElem) { 
			this.btnElem.appendChild(advElem);
		}
	}
});


var WbsDataStore = newClass(WbsObservable, {
	constructor: function (config) {
		this.records = new Array ();
		this.recordClass = config.recordClass ? config.recordClass : WbsRecord;
		this.offset = config.offset | 0;
		this.config = config;
		this.params = {};
		if (this.config.reader) {
			this.reader = this.config.reader;
			this.reader.addListener("success", this.onRecordsLoaded, this);
		}	
		this.idProperty = this.config.idProperty;
		
		this.addEvents ({
			"dataChanged" : true,
			"recordChanged" : true,
			"startLoading" : true,
			"finishLoading" : true
		});
	},

	add: function (record) {
		record.addListener("modified", this.recordModified, this);
		this.records.push(record);
	},
	
	unshift: function (record) {
		this.records.unshift(record);
	},
		
	clear: function () {
		this.records = new Array ();
	},
		
	getLength: function () {
		return this.records.length;		
	},
		
	hasRecords: function () {
		return this.records.length > 0;
	},
		
	getRecordByIndex: function(index) {
		return this.records[index];
	},
		
	setSorting: function (column, direction) {
		this.reader.setSorting(column, direction);
	},
		
	setParams: function(params) {
		this.params = params;
	},
		
	setOffset: function (offset, limit) {
		this.offset = offset;
		this.reader.addBaseParams({offset: offset, limit: limit});
	},
		
	recordModified: function(record) {
		this.fireEvent("recordModified", record);
	},
		
	getOffset: function() {
		return this.offset;
	},
	
	getTotal: function() {
		return this.total;
	},
		
	getTotalAdvanced: function() {
		return this.total_advanced;
	},	
	
	onRecordsLoaded: function(responseData, records, data) {
		this.fireEvent("finishLoading");
		
		if (responseData.errorStr) {
			WbsCommon.showError(responseData);
			return false;
		}
		if (responseData)
			this.responseData = responseData;
		
		this.clear();	
		
		if (data.total)
			this.total = data.total;
		if (responseData.total_advanced) {
			this.total_advanced = responseData.total_advanced;
		} else {
			this.total_advanced = 0;
		}
		for (var i = 0; i < records.length; i++) {
			records[i].id = records[i][this.idProperty];
			this.add(new this.recordClass(records[i]));
		}
		this.fireEvent("dataChanged", this);
	},
		
	load: function(params) {
		this.fireEvent("startLoading");
		if (!params)
			params = {};
		jQuery.extend(params, this.params);
		this.reader.read(params);
	}
});

var WbsEditable = newClass(WbsObservable, {
	value: null, 
	mode: null,
		
	constructor: function (config) {
		this.config = config;
		this.mode = WbsEditable.VIEW_MODE;
		
		// read config
		this.elem = config.elem;
		
		this.superclass().constructor.call(this);
		
		this.addEvents({
			"changeMode": true
		});
	},
		
	setEditMode: function() {
		this.mode = WbsEditable.EDIT_MODE;
		this.render();
		this.editElem.select();
		this.fireEvent("changeMode");
	},
		
	setViewMode: function() {
		this.mode = WbsEditable.VIEW_MODE;
		this.render();
		this.fireEvent("changeMode");
	},
		
	setValue: function(value) {
		this.value = value;
		this.render();
	},
		
	render: function() {
		if (this.mode == WbsEditable.EDIT_MODE) {
			var editElem = this.createEditElem();
			
			clearNode(this.elem);
			
			editElem.setValue(this.value);
			this.elem.appendChild(editElem);
			this.editElem = editElem;
			
			var saveBtn = createElem("input",null, {type: "button", value:"Сохранить"});
			addHandler (saveBtn, "click", function() {this.save()}, this);
			
			var cancelBtn = createElem("input",null, {type: "button", value:"Отмена"});
			addHandler (cancelBtn, "click", function(e) {this.cancel();}, this);
			
			var linksBlock = createDiv("wbs-editable-links-block");
			linksBlock.appendChild(saveBtn);
			linksBlock.appendChild(cancelBtn);
			
			this.elem.appendChild(linksBlock);
		} else {
			clearNode(this.elem);
			var textValue = (!this.value || this.value.length == 0 && this.config.emptyText) ? this.config.emptyText : this.value;
			
			var valueSpan = createElem('div');
			if (!this.config.clickToEdit) {
				jQuery(this.elem).addClass('noedit');
			}
			valueSpan.innerHTML = (textValue) ? textValue.htmlSpecialChars() : "";		
			valueSpan.onclick = function(){if (this.config.clickToEdit) this.setEditMode()}.bind(this);
			
			this.elem.appendChild(valueSpan);
		}
	},
	
	setEditable: function (edit) {
		this.config.clickToEdit = edit;
		if (this.config.clickToEdit) {
			jQuery(this.elem).removeClass('noedit');
		} else {
			jQuery(this.elem).addClass('noedit');
		}
	},
		
	save: function() {
		if (!this.saveHandler)
			throw "No save handler for editable";
		
		var newValue = this.editElem.getValue();
		this.saveHandler(newValue, this.saveSuccess.bind(this), this.saveFailed.bind(this));
	},
		
	saveSuccess: function() {
		this.value = this.editElem.getValue();
		this.setViewMode();
	},
	saveFailed: function(err) {
		if (!$(this.elem).find(".error").length) {
			$(this.elem).find(".wbs-editable-links-block").prepend('<div class="error" style="color:red; font-weight: normal; font-size: 12px; float: none"></div>');
		}
		$(this.elem).find(".error").html(err).show();
	},
	
		
	cancel: function() {
		this.setViewMode();
	}
});
WbsEditable.VIEW_MODE = "view";
WbsEditable.EDIT_MODE = "edit";


var WbsEditableText = newClass(WbsEditable, {
	constructor: function(config) {
		this.superclass().constructor.call(this, config);
	},
		
	createEditElem: function() {
		var elem = createDiv("wbs-editable-text");
		var textarea = createElem("textarea");
		textarea.value = this.value;
		elem.appendChild(textarea);
		
		if(this.config.adjustSize && this.elem.offsetHeight > 40)
			textarea.style.height = this.elem.offsetHeight - 20 + "px";
		if(this.config.adjustSize)
			textarea.style.width = this.elem.offsetWidth + "px";
			
		
		elem.setValue = function(value) {textarea.value = value;}		
		elem.getValue = function() { return textarea.value; }
		elem.select = function() {textarea.select(); textarea.focus(); }
		
		return elem;
	}	
});

var WbsEditableLabel = newClass(WbsEditableText, {
	
	createEditElem: function() {
		var elem = createDiv("wbs-editable-label");
		var input = createElem("input");
		input.value = this.value;
		elem.appendChild(input);
		
		if(this.config.adjustSize)
			input.style.width = this.elem.offsetWidth + "px";
		
		elem.setValue = function(value) {input.value = value;}
		elem.getValue = function() { return input.value; }
		elem.select = function() {input.select(); input.focus(); }
		
		return elem;
	}
});

var WbsFlexContainer = newClass (WbsObservable, {
	constructor: function (config) {
		this.config = config;
		
		this.contentElem = config.contentElem;
		this.elem = config.elem;
		if (!this.contentElem)
			throw "No exists content elem for WbsFlexContainer";
		addClass(this.elem, "wbs-flexcontainer");
		this.childElems = new Array ();
		
		this.buildPage();
		WbsFlexContainer.addContainer(this);
		
		this.superclass().constructor.call(this);
		
		this.addEvents({
			"resize" : true
		});
	},
	
	addChildElem: function(childElem) {
		this.childElems[this.childElems.length] = childElem;
	},
	
	buildPage: function () {
		if (this.config.headerElem) {
			addClass(this.config.headerElem, "wbs-flexcontainer-header");
			this.addChildElem(this.config.headerElem);
		}		
		
		addClass(this.contentElem, "wbs-flexcontainer-content");
		this.addChildElem(this.contentElem);
		
		if (this.config.footerElem) {
			addClass(this.config.footerElem, "wbs-flexcontainer-footer");
			this.addChildElem(this.config.footerElem);
		}
	},
	
	resize: function () {
		var minusHeight = 0;
		for (var i = 0; i < this.childElems.length; i++) {
			var childElem = this.childElems[i];
			if (childElem == this.contentElem)
				continue;
			minusHeight += childElem.offsetHeight;
		}
		if (this.elem.offsetHeight > 21 && minusHeight == 0) minusHeight = 21;
		this.contentElem.style.height = (this.elem.offsetHeight - minusHeight) + "px";
		this.fireEvent("resize");
	},
		
	parentResized: function() {
		this.resize();
	}
});

WbsFlexContainer.containers = new Array ();
WbsFlexContainer.addContainer = function(container) {
	var parentFinded = false;
	for (var i = 0; i < WbsFlexContainer.containers.length; i++) {
		var otherContainer = WbsFlexContainer.containers[i];
		if (otherContainer == container)
			continue;
		if (nodeIsParentOf(container.elem, otherContainer.elem)) {
			parentFinded = true;
			otherContainer.addListener("resize", container.parentResized, container);
		}
	}
	if (!parentFinded) {
		addHandler(window,"resize",container.resize,container);
		addHandler(window,"load",container.resize,container);
	}		
	WbsFlexContainer.containers.push(container);
}
function nodeIsParentOf(node, parentNode) {
	var currentParent = node;
	while (currentParent && currentParent != window) {
		if (currentParent == parentNode)
			return true; 
		currentParent = currentParent.parentNode;
	}
	return false;
}

function WbsFooter (config) {
	this.config = config;
	this.contentElem = document.getElementById(config.contentElemId);
	this.wrapElem = this.contentElem.parentNode;
	
	var footer = this;
	this.resize = function () {
		footer.contentElem.style.height = (footer.wrapElem.offsetHeight - footer.footerElem.offsetHeight) + "px";
	}
	
	this.getElem = function() {
		return this.footerElem;
	}
	
	this.footerElem = document.createElement("div");
	this.footerElem.className = config.cls;
	this.footerElem.innerHTML = "&nbsp;";
	
	this.wrapElem.appendChild(this.footerElem);
	this.resize();
	
	addHandler(window,"resize",this.resize,false);
}


var WbsNavBar = newClass(WbsObservable, {
	constructor: function(config) {
		
		this.superclass().constructor.call(this);		
		this.addEvents({
			"resize" : 1,
			"horizontalResize" : 1,
			"blockActivated" : 1			
		});
		
		this.config = config;
		this.collapsed = false;
		this.contentElem = document.getElementById(config.contentElemId);
		this.wrapElem = this.contentElem.parentNode;
		this.activeBlock = null;
		this.width = 0;
		
		var resizer = new Ext.Resizable (this.wrapElem,  {handles: 'e', pinned: false,  disableTrackOver : true});
		this.resizer = resizer;
		resizer.on("resize", function(resizer, width) {
			this.wrapElem.style.height = "100%";
			if (this.width != width) {
				this.width = width;
				this.saveSize(width);
			}
			this.fireEvent("horizontalResize");
		}, this);
		
		var btnCollapse = createDiv("btn-collapse");
		this.contentElem.insertBefore(btnCollapse, this.contentElem.firstChild);
		
		addHandler(btnCollapse, "click", function() {
			this.collapse();
		}, this);
		
		var btnExpand = createDiv("btn-expand");
		this.contentElem.insertBefore(btnExpand, this.contentElem.firstChild);
		
		addHandler(btnExpand, "click", function() {
			this.expand();
		}, this);
		
		this.blocks = new Array ();	
		this.blocksIds = {};
		
		for (var i = 0; i < this.contentElem.childNodes.length; i++) {
			var node = this.contentElem.childNodes[i];
			if (node.className == "acc-block") {
				var block = new WbsNavBarBlock({
					navBar: this,				
					elem: node
				});
				this.blocks[this.blocks.length] = block;
				this.blocksIds[block.id] = block;
			}
		}
	
		//this.collapse.bind(this);
		
		this.closePanel = createDiv("close-panel");
		this.contentElem.insertBefore(this.closePanel, this.contentElem.firstChild);
		this.closePanel.onclick = this.collapse.bind(this);
		
		
		addHandler(window,"resize",this.resize,this);
		
		
		if (this.config.saveSize) {
			var savedSize = getCookie("navbar-" + this.config.id);
			if (!savedSize || savedSize == undefined) {
				savedSize = 200;
			}
			if (savedSize == -1)
				this.collapse();
			else if (savedSize)
				resizer.resizeTo(savedSize);
		}
	},
	
	collapse: function() {
		this.collapsed = true;
		this.resizer.resizeTo(20);
		addClass(this.contentElem, "collapsed");
		this.resizer.east.el.hide();
		/*if (this.expanderElem)
			addClass(this.expanderElem, "visible");*/
	},
	
	expand: function() {
		this.collapsed = false;
		removeClass(this.contentElem, "collapsed");
		this.resizer.resizeTo(200);
		this.resizer.east.el.show();
		this.resize();
	},
	
	resize: function () {
		var totalHeight = this.wrapElem.offsetHeight;
		var blocksTotalHeight = 0;
		for (var i = 0; i < this.blocks.length; i++) {
			var block= this.blocks[i];
			if (block == this.activeBlock)
				continue;
			blocksTotalHeight += block.getHeight();
		}
		var activeBlockHeight = totalHeight - blocksTotalHeight - 4;
		this.activeBlock.setHeight (activeBlockHeight);
		this.fireEvent("resize");
	},
	
	
	getElem: function() {
		return this.contentElem;
	},
		
	getBlock: function(id) {
		return this.blocksIds[id];
	},
	
	setActiveBlock: function (block, noFire) {
		if (typeof block == "string")
			block = this.blocksIds[block];
		if (this.activeBlock)
			this.activeBlock.setActive(false);
			
		this.activeBlock = block;
		this.activeBlock.setActive(true);
		if (noFire == undefined || !noFire) {
			this.fireEvent("blockActivated", block);
		}
		this.resize();		
	},
	
	saveSize: function(size) {
		if (!this.collapsed && size < 50) {
			this.collapse();
		}
		var value = (this.collapsed) ? -1 : size;
		if (this.config.id)
			setCookie("navbar-" + this.config.id, value);
	}
});



var WbsNavBarBlock = newClass(WbsObservable, {
	constructor: function (config) {
		this.navBar = config.navBar;
		this.elem = config.elem;
		this.id = config.elem.id;
		this.contentEl = null;
		this.titleEl = null;
		
		this.build();
	},
		
	getContentElem: function() {
		return this.contentEl;		
	},
	
	build: function() {
		for (var i = 0; i < this.elem.childNodes.length; i++) {
			var node = this.elem.childNodes[i];
			if ($(node).hasClass('title'))
				this.titleEl = node;
			if (node.className == "content")
				this.contentEl = node;
		}
	},
	
	setHeight: function(height) {
		if (height <= 0) {
			return;
		}
		this.elem.style.height = height;
		if (height != "auto" && this.contentEl) {
			var changeHeight = 0;
			if (this.titleEl) {
				changeHeight = $(this.titleEl).height();
			}
			this.contentEl.style.height = (height - changeHeight) + "px";
		}
		$(this.contentEl).find(".x-panel").height("auto");
	},
	
	getHeight: function() {
		return this.elem.offsetHeight;
	},
	
	activate: function() {
		this.navBar.setActiveBlock(this);		
	},
	
	deactivate: function() {
		
	},
	
	setActive: function(value) {
		if (value) 
			addClass(this.elem, "active");
		else {
			this.elem.style.height = "auto";
			removeClass(this.elem, "active");
		}
	}
});

var WbsPager = newClass(null,{
	constructor: function(config) {
		this.config = config;
		this.table = this.config.table;
		this.offset = config.offset | 0;
		
		this.itemsOnPage = 10;
		this.currentPage = 0;
		
		if (this.getStore())
			this.getStore().setOffset(this.offset, this.itemsOnPage);
	},
		
	setItemsOnPage: function(value) {
		if (!value)
			value = 10;
		this.itemsOnPage = value;
		this.resetPage();
	},
	
	render: function() {
		var renderElem = this.config.elem;
		renderElem.innerHTML = "&nbsp;";	
		if (!this.getStore())
			return;
		
		var listEl = createElem ("ul", "pages-list");
		var pagesCount = Math.ceil(this.getStore().getTotal() / this.itemsOnPage);
		labelEl = createElem("li", "pages-label");
		var advanced = this.getStore().getTotalAdvanced();
		labelEl.innerHTML = "<span class='records-label'>" + (this.config.nameElements ? this.config.nameElements : "Записей") + ": " + (advanced ? advanced + ' (Записей: ' + this.getStore().getTotal() + ')' : this.getStore().getTotal()) + "</span> ";
		if (pagesCount > 0)
			labelEl.innerHTML += "Страницы" + ": ";
		listEl.appendChild(labelEl);
		
		var maxShowPages = 10;
		var showPagesDelta = 4;
		var separatorOuted = false;
		
		for (var i = 0; i < pagesCount; i++) {
			var pageEl = createElem("li", "page-item");
			if (i == this.currentPage)
				addClass(pageEl, "selected");				
			pageEl.innerHTML = (i + 1);
			pageEl.pageNo = i;
			
			if (!(pagesCount < maxShowPages || Math.abs(i - this.currentPage) < showPagesDelta || i == 0 || i == pagesCount - 1)) {
				if (!separatorOuted) {
					separatorEl = createElem("li", "separator");
					separatorEl.innerHTML = "...";
					listEl.appendChild(separatorEl);
					separatorOuted = true;
				}
				continue;
			}
			separatorOuted = false;
			
			addHandler(pageEl, "click", function(pageEl, pageNo) {
				return function() {pageEl, this.onPageClick(pageEl, pageNo);}
			}(pageEl, i), this);
			
			listEl.appendChild(pageEl);
		}
		renderElem.insertBefore(listEl, renderElem.firstChild);
	},
	
	resetPage: function(pageNo) {
		pageNo = pageNo | 0;
		this.currentPage = pageNo;
		this.getStore().setOffset(pageNo * this.itemsOnPage, this.itemsOnPage);
	},
	
	getStore: function() {
		if (!this.table)
			return false;
		return this.table.getStore();
	},
	
	onPageClick: function(pageEl,pageNo) {
		this.currentPage = pageNo;
		WbsCommon.showLoading(pageEl);
		this.getStore().setOffset(pageNo * this.itemsOnPage, this.itemsOnPage);
		this.getStore().load();
	}
});

var WbsReader = newClass(WbsObservable, {
	constructor: function(config) {
		this.config = config;
		this.baseParams = config.baseParams;
		if (!this.baseParams)
			this.baseParams = {};
		
		this.addEvents({
			"success" : true,
			"changeParams": true,
			"startReading" : true,
			"finishReading" : true
		});
	},
		
	addBaseParams: function (params) {
		var baseParams = this.baseParams;
		if (!baseParams)
			baseParams = [];
		for (var i in params) {
			baseParams[i] = params[i];
		}
		this.baseParams = baseParams;
		this.fireEvent("changeParams");
	},
	
	setSorting: function(column, direction) {
		this.addBaseParams({sortColumn: column, sortDirection: direction});
		this.baseParams.sortColumn = column;
	}, 
	
	read: function (addParams) {
		var params = {};
		for (var key in this.baseParams)
			params[key] = this.baseParams[key];
		if (addParams) {
			for (var key in addParams)
				params[key] = addParams[key];
			
		}		
		
		this.fireEvent("startReading");
		Ext.Ajax.request ({
			url: this.config.url,
			params: params,
			success: function (response) {
				var data = {};
				var responseData = Ext.decode(response.responseText);
				if (responseData.errorStr) {
					var dlg = new UGShowErrorDlg({error: responseData.errorStr});
					dlg.show();
					if (responseData.errorCode && responseData.errorCode == "SESSION_TIMEOUT" && responseData.redirectUrl) {
						window.top.location.href = responseData.redirectUrl;
					}
					if (responseData.newLocation && responseData.sessionExpired) { // for old code
							window.top.location.href = responseData.newLocation.replace("../", ""); // only ONE replace need			
					}					
					//WbsCommon.showError(responseData);
					return false;
				}
				var records = (this.config.recordsProperty) ? responseData[this.config.recordsProperty] : responseData;
				if (this.config.totalProperty)
					data.total = responseData[this.config.totalProperty];
				this.fireEvent("success", responseData, records, data);
			},
			scope: this
		});		
	}
});

var WbsRecord = newClass(WbsObservable, {
	constructor: function(recordData) {
		this.superclass().constructor.call(this);
		
		this.addEvents({
			"modified" : true			
		});
		
		this.loadFromData(recordData);
		if (this.data.id)
			this.id = this.data.id;
	},
		
	loadFromData: function(data) {
		this.data = data;
		var fields = this.getFields();
		if (data.id)
			this.id = data.id;
		for (var i = 0; i < fields.length; i++) {
			var field = fields[i];
			var value = data[field.name];
			if (field.convert) {
				value = field.convert(value);
			}
			this[field.name] = value;
		}
	},
		
	getFields: function() {
		return[];
	},
		
	getId: function() {
		return this.id;
	}	
});

WbsRecordsList = newClass(null, {
	records: [],
	constructor: function(records) {
		this.records = (records) ? records : [];
	},
		
	add: function(record) {
		this.records.push(record);
	},
		
	getRecords: function() {
		return this.records;
	},
		
	getRecord: function(index) {
		return this.records[index];
	},
		
	getCount: function() {
		return this.records.length;
	},
	
	getIds: function() {
		var ids = new Array ();
		for (var i = 0; i < this.records.length; i++) {
			ids.push(this.records[i].getId());
		}
		return ids;
	}
});

WbsRecordset = newClass(null, {
	records: [],
	constructor: function(records) {
		this.records = (records) ? records : [];
	},
		
	add: function(record) {
		this.records.push(record);
	},
		
	getRecords: function() {
		return this.records;
	},
		
	getRecord: function(index) {
		return this.records[index];
	},
		
	getCount: function() {
		return this.records.length;
	},
		
	isEmpty: function() {
		return this.getCount() < 1;		
	},
	
	getIds: function() {
		var ids = new Array ();
		for (var i = 0; i < this.records.length; i++) {
			ids.push(this.records[i].getId());
		}
		return ids;
	},
		
	getMinRights: function() {
		var minRights = 7;
		for (var i = 0; i < this.records.length; i++) {
			if (this.records[i].Rights < minRights)
				minRights = this.records[i].Rights;
		}
		return minRights;
	},
		
	canWrite: function() {
		return WbsRightsMask.canWrite(this.getMinRights());
	}
});

WbsRightsMask = {
	canRead: function(rightsValue) {
		return rightsValue >= 1;
	},
		
	canWrite: function(rightsValue) {
		return rightsValue >= 3;
	},
	
	canFolder: function (rightsValue) {
		return rightsValue >= 7;
	},
		
	getRightsStr: function(rightsValue) {
		switch (parseInt(rightsValue)) {
			case 0:
				return "Нет прав";
			case 1:
				return "Чтение";
			case 3:
				return "Запись";
			case 7:
				return "Полные";
			default: 
				return "Неизвестные права: " + rightsValue;
		}
	}
}

var WbsTable = newClass(WbsObservable, {

	constructor: function (config) {
		this.config = config;
				
		if (config.store)
			this.setStore(config.store);
		
		this.id = config.id;
		if(!this.id)
			this.id = "commontable";
		
		this.configureSorting();
		
		this.sortItems = config.sortItems;
		this.view = null;
		this.views = new Array ();
		this.columns = config.columns;
		
		if (this.config.elem)
			this.elem = this.config.elem;
		else
			throw "No exists elem for table";
		
		this.tableElem = createDiv("wbs-table");
		this.elem.appendChild(this.tableElem);
		
		this.renderElem = createDiv("wbs-table-content");
		this.tableElem.appendChild(this.renderElem);
		
		if (this.config.pager) {
			this.pagerElem = createDiv("wbs-table-pager");
			this.getFooterElem().appendChild(this.pagerElem);
			this.pager = new WbsPager({elem: this.pagerElem, table: this, nameElements: config.nameElements, offset: config.offset});
		}
		
		this.itemsElem = createDiv("wbs-items-page");
		this.itemsElem.innerHTML = 'Показать <span class="records-page-count">30</span> записей на странице'; 
		this.getFooterElem().appendChild(this.itemsElem);
		
		this.rightsElem = createDiv("wbs-access-rights");
		this.getFooterElem().appendChild(this.rightsElem);		
		
		if (this.config.statusBar) {
			this.statusBarElem = createDiv("wbs-table-status-bar");
			this.getFooterElem().appendChild(this.statusBarElem);
		}
		
		if (this.config.autoHeight == false) {
			var container = createDiv("wbs-container");
			if (this.config.dock)
				this.tableElem.style.height = "100%";
			this.tableElem.appendChild(container);
			container.appendChild(this.renderElem);
			if(this.footerElem)
				container.appendChild(this.footerElem);
			container.resize = function(){};
			this.container = container;
		} else {
			this.container = new WbsFlexContainer({elem: this.tableElem, contentElem: this.renderElem,  footerElem: this.footerElem});
		}
		
		this.items = new Array ();
		this.checkboxes = new Array ();
		
		
		this.superclass().constructor.call(config);
		
		this.addEvents ({
			"afterRender": true,
			"afterLoad": true
		});
	},
		
	onLoad: function () {
	},
	
	getColumns: function() {
		return this.columns;
	},
		
	getFooterElem: function() {
		if (!this.footerElem) {
			this.footerElem = createDiv("wbs-table-footer");
			this.tableElem.appendChild(this.footerElem);
		}
		return this.footerElem;
	},
		
	configureSorting: function() {
		var sorting = null;
		if (this.config.sortingToCookie) {
			sortingStr = getCookie(this.id + '-sorting');
			if (sortingStr) {
				var parts = sortingStr.split(":");
				sorting = {column: parts[0], direction: parts[1]};
			}
		} 
		if (!sorting && this.config.defaultSorting) {
			sorting = this.config.defaultSorting;
		}
		if (!sorting)
			sorting = {column: null, direction: null};
		
		this.currentSorting = sorting;
		this.store.setSorting(sorting.column, sorting.direction);
	},
	
	setStore: function (store) {
		this.store = store;
		store.addListener("recordModified", function(record) {this.storeRecordModified(record)}, this);
		store.addListener("dataChanged", function() {this.renderData(); this.onLoad(); this.fireEvent("afterLoad"); }, this);
		store.addListener("startLoading", function() {this.showLoading();}, this);
		
	},
		
	storeRecordModified: function(record) {
		this.refreshRecordBlock(record.id);
	},
	
	getStore: function () {
		return this.store;
	},
		
	setView: function (view) {
		this.view = view;
	},
		
	showLoading: function() {
		if (this.view && !this.config.hideLoading)
			this.view.showLoading();
	},
	
	addItem: function (item) {
		this.items[this.items.length] = item;
	},
		
	focusRecord: function(recordId) {
		this.view.focusRecordBlock(recordId);
	},
	
	getRenderElem: function() {
		return this.renderElem;
	},
		
	getColumnById: function(name) {
		var column = null;
		for (var i = 0; i < this.columns.length; i++)
			if (this.columns[i].name == name) {
				column = this.columns[i];
				break;
			}
		return column;
	},
		
	setSortingColumn: function(columnId, direction, realSort) {
		var column = this.getColumnById(columnId);
		if (!column)
			return false;
		var realColumnName = (column.realSorting) ? column.realSorting : (realSort ? realSort : column.name);
		
		if (this.config.sortingToCookie)
			setCookie(this.id + '-sorting', realColumnName + ":" + direction);
		this.currentSorting = {column: realColumnName, direction: direction};
		if (this.pager) {
			this.pager.resetPage();
		}
		this.store.setSorting(realColumnName, direction);
		this.store.load();
	},
		
	render: function() {
		this.renderData();
		this.resize();
	},
		
	resize: function() {
		this.container.resize();
	},
		
	renderData: function() {
		this.checkboxes = new Array();
		if (this.view) {
			this.view.render();
		}
		if (this.pager)
			this.pager.render();
		
		this.fireEvent("afterRender");
	},
		
	reload: function() {
		this.store.load();
	},
		
	setViewMode: function (viewMode) {
		if (this.currentViewMode == viewMode)
			return;

		var view = null;
		if (this.views[viewMode] != null) {
			view = this.views[viewMode];
		} else {
			view = this.createView(viewMode);
			this.views[viewMode] = view;
		}
		
		this.currentViewMode = viewMode;
		this.setView(view);
		this.renderData();
	},
	
	reloadView: function () {
		var view = this.createView(this.currentViewMode);
		this.views[this.currentViewMode] = view;
		this.setView(view);
		this.reload();
		//this.renderData();
	},

	getCommonCheckbox: function() {
		var checkbox = createElem("input");
		checkbox.setAttribute("type", "checkbox");
		checkbox.table = this;
		checkbox.toggleAll = function(){
			var allChecked = true;
			for (var checkboxId in this.table.checkboxes) {
				var childCheckbox = this.table.checkboxes[checkboxId];
				if (!childCheckbox.checked)
					allChecked = false;
			}
			var needCheckAll = !allChecked;
			for (var checkboxId in this.table.checkboxes) {
				this.table.checkboxes[checkboxId].checked = needCheckAll;
				var check = this.table.checkboxes[checkboxId];
    			if (jQuery(check).is(":checked")) {
    				jQuery(check).parent().parent().addClass("selected");	    				
    			} else {
    				jQuery(check).parent().parent().removeClass("selected");
    			}
			}
			
			this.checked = needCheckAll;
		}
		checkbox.onclick = function() {this.toggleAll()};
		return checkbox;
	},

	updateItemBlock: function(block, record) {
		if (this.config.selection) {
			var selectorPlace = null;
			var sampObjects = block.getElementsByTagName("SAMP");
			for (var i = 0; i < sampObjects.length; i++)
	  		if (sampObjects[i].className=="selector")
	  			selectorPlace = sampObjects[i];
	  	if (selectorPlace) {
	  		var checkbox = document.createElement("input");
	  		checkbox.setAttribute("type", "checkbox");
	    	checkbox.className = "wbs-table-checkbox";
	    	checkbox.record = record;
	    	this.checkboxes.push(checkbox);
	    	selectorPlace.parentNode.replaceChild(checkbox, selectorPlace);
	    	jQuery(checkbox).change(function () {
	    		if (jQuery(this).parent().parent().hasClass("hover")) {
	    			if (jQuery(this).is(":checked")) {
	    				jQuery(this).parent().parent().addClass("selected");	    				
	    			} else {
	    				jQuery(this).parent().parent().removeClass("selected");
	    			}
	    		}
	    	});
	    	
	  	}
		}
	},

	refreshRecordBlock: function (recordId) {
		var record = null;
		for (var i= 0; i < this.store.records.length; i++) {
			if (this.store.records[i].id == recordId) {
				record = this.store.records[i];
				break;
			}
		}
		this.view.refreshRecordBlock (recordId, record);
	},

	openSortingMenu: function(e, column, a) {
		var table = this;
		var typesLabels = {
			"string" : ["A-Z", "Z-A"],
			"int" : ["1-999", "999-1"],
			"date" : ["1-31", "31-1"]
		};
		var labels = (column.type && typesLabels[column.type]) ? typesLabels[column.type] : typesLabels["string"];

		var menu = new SortingMenu({items: [
				{label: labels[0], onClick: function() {table.setSortingColumn(column.name, "asc")}, iconCls: "sorting-menu-asc"},
				{label: labels[1], onClick: function() {table.setSortingColumn(column.name, "desc")}, iconCls: "sorting-menu-desc"}
		]});
		menu.show(e);		
	},

	createSortLabel: function (column, parentEl, labelValueEl) {
		var sortEl = createDiv("sort-header");
		var table = this;
		sortEl.columnId = column.name;
		
		var label = createElem("label");
		if (labelValueEl)
			label.appendChild(labelValueEl);
		else
			label.innerHTML = column.label;
		/*
		if (column.name == 'C_NAME' && column.name == this.currentSorting.column) {
			label.innerHTML += ' (' + (this.currentSorting.direction == 'desc' ? 'Я-А' : 'А-Я') + ')';
		}
		*/
		if (label.innerHTML.length == 0)
			sortEl.className = "sort-header-empty";
		sortEl.appendChild(label);
		
		var sortingCallback = (column.sortingMenu) ?
			function(e) {table.labelClick = true ; table.openSortingMenu(e, column, table.sortItems);} :
			function(e) {if (table.labelClick) {table.labelClick = false; return; }; table.setSortingColumn(column.name, table.currentSorting.column == column.name ? (table.currentSorting.direction == "asc" ? "desc" : "asc") : "asc")};
		
		addHandler(sortEl, "click", sortingCallback);
		
		addEmptyImg(sortEl);
		
		var table = this;
		if (column.name == this.currentSorting.column) {
			addClass(sortEl, "current-" + this.currentSorting.direction);
		}
					
		parentEl.appendChild(sortEl);
		/*
		if (column.sortingMenu) {
			var sortname = "";
			for (var i = 0; i < this.sortItems.length; i++) {
				if (this.currentSorting.column == this.sortItems[i].dbname) {
					sortname = this.sortItems[i].label;
				}
			}
			var sortedby = jQuery('<div class="sortedby">sorted by: <span id="sortedby">' + sortname + '</span></div>');
			jQuery(parentEl).append(sortedby);
		}
		*/
		
	},

	getSortingColumns: function() {
		var result = new Array ();
		for (var i = 0; i < this.columns.length; i++) {
			if (this.columns[i] && this.columns[i].sorting)
				result.push(this.columns[i]);
		}
		return result;
	},
		
	getNoRecordsMessage: function() {
		return "<no records>";
	},
		
	createRecordsList: function() {
		return new WbsRecordsList();
	},
		
	getSelectedRecords: function() {
		var recordsList = this.createRecordsList();
		for (var checkboxId in this.checkboxes) {
			if (this.checkboxes[checkboxId].checked && this.checkboxes[checkboxId].record) {
				recordsList.add(this.checkboxes[checkboxId].record);
			}
		}
		return recordsList;
	}
});


SortingMenu = newClass (WbsPopmenu, {
	constructor: function(config) {
		config.withImages = true;
		this.superclass().constructor.call(this, config);
	}	
});

var WbsTree = newClass(null, {
	constructor: function (config) {
		this.config = config;
		this.treePanel = null;
		this.rootNode = null;
		this.nodes = new Array ();
		
		this.nodeMap = {
			id : 0,
			text: 1,
			children: 3
		}
	}, 

	init: function() {
		var cf = this.config;
		this.treePanel = new Ext.tree.TreePanel ({el: cf.elemId, lines: false, autoHeight: true, border: false, animate: false, rootVisible: cf.rootVisible});
		
		this.treePanel.addListener("click", this.onNodeClick, this);
		
		if (this.onBeforeNodeSelect)
			this.treePanel.getSelectionModel().on ("beforeselect", this.onBeforeNodeSelect);
		
		if (this.config.nodes) {
			this.loadNodes(this.config.nodes, true);
			this.render();			
		}
	},
		
	loadNodes: function (nodesData, isRootNode) {
		
		var dataRoot = this.addNode(nodesData);
		
		if (isRootNode) {
			this.treePanel.setRootNode(dataRoot);
			this.rootNode = dataRoot;
		} else {
			var rootNode = new Ext.tree.TreeNode ({text:"Root"});
			rootNode.appendChild(dataRoot);
			this.treePanel.setRootNode(rootNode);
			this.rootNode = rootNode;
		}
	},
	
	addNode: function(nodeData, addConfig) {
		var config = {id: nodeData[this.nodeMap.id], text: nodeData[this.nodeMap.text].htmlSpecialChars(), iconCls: this.config.iconCls};
		if (addConfig)
			jQuery.extend(config, addConfig);
		var extNode = new Ext.tree.TreeNode(config);
		extNode.Id = config.id;
		extNode.Name = config.text;
		this.nodes[nodeData[this.nodeMap.id]] = extNode;
		var childrenData = nodeData[this.nodeMap.children];
		if (childrenData) {
			for (var i = 0; i < childrenData.length; i++) {
				var childNodeData = childrenData[i];
				var extChildNode = this.addNode(childNodeData);
				extNode.appendChild(extChildNode);
			}
		}
		$('#' + this.config.elemId + ' .no-tree-items').remove();	
		return extNode;
	},
	
	setChildren: function (parent_id, children)
	{
		var parent = this.getNode(parent_id);
		for (var i = 0; i < children.length; i++) {
			var node = this.getNode(children[i][this.nodeMap.id]);
			if (node) {
				node.setText(children[i][this.nodeMap.text]);
			} else {
				var child = this.addNode(children[i]);
				parent.appendChild(child);
			}
		}
	},
	
	removeNode: function (node) {
		node.parentNode.removeChild(node);
		delete this.nodes[node.Id];
		node.ui.remove();
		this.checkNoItems();
	},
	
	
	checkNoItems: function () {
		if (this.treePanel.getRootNode() && !this.treePanel.getRootNode().firstChild) {
			var message = '';
			var c = this.config.elemId.replace(/-list/, '');
			if (c == 'folders') {
				message = 'нет папок';
			} else if (c == 'lists') {
				message = 'нет списков';
			} else if (c == 'widgets') {
				message = 'нет форм';
			} else if (c == 'groups') {
				message = 'нет групп';
			}
			if (message) {
				$('#' + this.config.elemId).append('<div class="no-tree-items">&lt;' + message + '&gt;</div>');
			}
		} else {
			$('#' + this.config.elemId + ' .no-tree-items').remove();
		}
	},
	
		
	render: function () {
		if (this.onBeforeRender)
			this.onBeforeRender();			
		this.treePanel.render();
		this.treePanel.getRootNode().expand();
		this.checkNoItems();
		if (this.onAfterRender)
			this.onAfterRender();			
	},
	
		
	selectNode: function(nodeId, hide) {	
		var node = this.nodes[nodeId];//this.treePanel.getNodeById(nodeId);
		if (!node || node == "undefined") {
			if (this.rootNode) {
				
				this.rootNode.select();
				this.onNodeClick(this.rootNode);
				return false;
			} else {
				return false;
			}
		}
		if (node.Id != this.rootNode.Id) {
			node.ensureVisible();
		}
		node.select();
		if (hide !== true) {
			this.onNodeClick(node);
		}
		node.expand();
		
		return true;
	},
	

	getNode: function(nodeId) {
		var node = this.treePanel.getNodeById(nodeId);
		return node;
	},
		
	getSelectedNode: function() {
		var node = this.treePanel.getSelectionModel().getSelectedNode();
		return node;
	},
	
	unSelect: function () {
		node = this.getSelectedNode();
		if (node) {
			this.treePanel.getSelectionModel().selNode = null;
			jQuery(node.ui.anchor).parent().removeClass('x-tree-selected');
		}
	}, 
	
	addClass: function (cls) {
		node = this.getSelectedNode();
		if (node) {
			jQuery(node.ui.anchor).prev().addClass(cls);
		}		
	},
	
	removeClass: function (cls) {
		node = this.getSelectedNode();
		if (node) {
			jQuery(node.ui.anchor).prev().removeClass(cls);
		}		
	}	
});

var WbsViewmodeSelector = newClass(WbsObservable, {
	constructor: function (table, config) {
		this.renderElem = config.elem;
		this.rendered = false;
		this.table = table;
		if (config.modes) this.modes = config.modes;
		else this.modes = new Array ("columns", "list", "detail", "tile");
		
		modeSelector = this;
		this.modesElems = {};
		this.onclick = null;
		
		
		this.superclass().constructor.call(this);
		
		this.addEvents ({
			"viewmodeChanged"	: true
		});
	},
	
	render: function() {
		this.container = document.createElement("div");
		this.container.className = "viewmode-selector";
		
		for (var i = 0; i < this.modes.length; i++) {
			var elem = this.createModeElem(this.modes[i]);
			this.container.appendChild(elem);
		}
		this.renderElem.appendChild(this.container);
		this.rendered = true;		
	},

	setSelectedElem: function(modeElem) {
		if (this.selectedModeElem != null) {
			removeClass(this.selectedModeElem, "selected");
			this.selectedModeElem.img.src = this.getModeImgSrc(this.selectedModeElem.mode, false);
		}
		if (!modeElem)
			return;
		addClass(modeElem, "selected");
		modeElem.img.src = this.getModeImgSrc(modeElem.mode, true);
		this.selectedModeElem = modeElem;
	},
	
	getModeImgSrc: function(mode, selected) {
		var addPart = (selected) ? "_on" : "";			
		return WbsCommon.getPublishedUrl("common/templates/img/viewmode_" + mode + addPart + ".gif");
	},
		
	isRendered: function() {
		return this.rendered;
	},
		
	selectMode: function(mode) {
		this.setMode(mode);
		this.fireEvent("viewmodeChanged", mode);
	},
		
	setMode: function(mode) {
		this.selectedMode = mode;
		if (this.isRendered())
			this.setSelectedElem(this.modesElems[mode]);
		if (this.table) {
			if (mode != undefined && mode != 'columns' && this.table.currentSorting.column == 'C_EMAILADDRESS') {
				this.table.setSortingColumn('C_NAME', 'asc');
			}
			this.table.setViewMode(mode);
		}
		if (this.onClick)
			this.onClick(this, mode);
	},
		
	setTable: function(table) {
		this.table = table;
		this.setMode(this.selectedMode);
	},
	
	createModeElem: function(mode) {
		var modeElem = createDiv("mode-elem");
		
		this.modesElems[mode] = modeElem;
		modeElem.mode = mode;
		modeElem.img = createElem("img");
		modeElem.img.src = this.getModeImgSrc(mode);
		modeElem.appendChild(modeElem.img);
		if (mode == this.selectedMode) {
			this.setSelectedElem(modeElem);
		}
		
		var table = this.table;
		modeElem.onclick = function () {
			modeSelector.selectMode(mode);
		};
		return modeElem;
	}
});

var WbsView = newClass(null, {
	constructor: function(table, config) {
		this.config = config;
		this.table = table;
	},

	outputRendered: function(outputObj) {
		var renderedElem = this.table.getRenderElem();

		if (this.loading) {
			outputObj = this.renderLoading();
			this.loading = false;
		} else if (!this.table.getStore() || !this.table.getStore().hasRecords()) {
			delete outputObj;
			if (document.app.mode == 'search' && document.app.search_type == 'advanced') {
				$("div.contacts-info").addClass('no-records');
			}
			var outputObj = this.renderNoRecords();
		} else {
			if (document.app.mode == 'search') {
				$("div.contacts-info").removeClass('no-records');
			}
		}
		
		if (renderedElem.listBody) {
			renderedElem.replaceChild(outputObj, renderedElem.listBody);
		} else {
			renderedElem.appendChild(outputObj);
		}
		renderedElem.listBody = outputObj;
		this.table.resize();
	},
	
	getClassName: function() {
		return this.modeName;
	},
	
	selectionEnabled: function() {
		return this.table.config.selection;
	},
		
	renderLoading: function() {
		var block = createDiv("wbs-loading");
		block.innerHTML = "Загрузка&nbsp; <div class='wbs-loading-icon'></div>";
		return block;
	},
	
	renderNoRecords: function () {
		var block = createDiv("no-records");
		block.appendChild(document.createTextNode(this.table.getNoRecordsMessage()));
		return block;
	},
		
	showLoading: function() {
		this.loading = true;
		this.outputRendered();
	},		
		
	getCellHeaderValueElem: function(column) {
		return document.createTextNode(column.label);
	}
});


var WbsColumnsView = newClass(WbsView, {
	constructor: function (table, config) {
		this.modeName = "columns";
		this.superclass().constructor.call(this, table, config);
	},
	
	render: function() {
		
		var tableElem = document.createElement("table");
		tableElem.className = this.getClassName();
		this.trElems = new Array ();
		
		if (this.config.header != false) {
			var headerElem = this.renderHeader(tableElem);
			tableElem.appendChild(headerElem);
		}
		
		var tbodyElem = document.createElement("tbody");
		tableElem.appendChild(tbodyElem);
		
		for (var i = 0; i < this.table.getStore().getLength(); i++) {
			var record = this.table.getStore().getRecordByIndex(i);
			record.index = i;
			var trElem = document.createElement("tr");
			trElem.className = "item " + ((i % 2) ? " odd" : " even");
			this.renderRow (trElem, record);
			this.trElems[record.id] = trElem;
			jQuery(trElem).hover(function () {
				jQuery(this).addClass("hover");
			}, function () {
				jQuery(this).removeClass("hover");
			});
			tbodyElem.appendChild(trElem);
		}
		this.outputRendered(tableElem);
	},
	
	refreshRecordBlock: function(recordId, record) {
		var block = this.getRecordBlock(recordId);
		clearNode(block);
		
		this.renderRow (block, record);
	},
		
	getRecordBlock: function(recordId) {
		return this.trElems[recordId];
	},
	
	getCellValue: function(column, record) {
		return record[column.name];  	
	},
	  
	getCellHeaderValueElem: function(column) {
		return document.createTextNode(column.label);
	},
	
	renderHeader: function (tableElem) {
		var theadElem = document.createElement("thead");
		var trElem = document.createElement("tr");
		
		var td = document.createElement("th");
		if (this.selectionEnabled()) {
			td.appendChild(this.table.getCommonCheckbox());
		}
		td.style.width = "10px";
		trElem.appendChild(td);
		
		var columns = this.table.getColumns();
		for (var i = 0; i < columns.length; i++) {
			var column = columns[i];
			var td = document.createElement("th");
			if (column.cls)
				td.className = column.cls;
			if (column.width) {
				td.style.width = (!isNaN(column.width)) ? column.width + "px" : column.width;
			}
			//addClass (td, "sort-column");
			var labelValueEl = this.getCellHeaderValueElem(column);
			if (column.sorting)
				this.table.createSortLabel(column, td, labelValueEl);
			else
				td.appendChild(labelValueEl);				
			//td.innerHTML = column.label;
			trElem.appendChild(td);
		}
		
		theadElem.appendChild(trElem);
		
		return theadElem;
	},
	
	renderRow: function(trElem, record) {
		
		var td = document.createElement("td");
		td.style.width = "10px";
		if (this.selectionEnabled()) {
			td.innerHTML = "<SAMP class='selector'></SAMP>";
		}
		trElem.appendChild(td);
		var columns = this.table.getColumns();
		for (var i = 0; i < columns.length; i++) {
			var column = columns[i];
			var td = document.createElement("td");
			if (column.width)
				td.style.width = (!isNaN(column.width)) ? column.width + "px" : column.width;
			if (column.cls)
				td.className = column.cls;
			if (column.type == "date")
				addClass(td, "date-cell");
			
			td.innerHTML = this.getCellValue(column, record);
			trElem.appendChild(td);
		}
		this.table.updateItemBlock(trElem, record);
	}
});



var WbsDivView = newClass(WbsView, {
	
	render: function () {
		
		var itemsListObj = document.createElement("div");
		itemsListObj.className = this.getClassName();
		this.itemElems = new Array ();
		
		if (this.config.header) {
			this.headerElem = createDiv("header");
			if (this.selectionEnabled()) {
				this.headerElem.appendChild(this.table.getCommonCheckbox());
			} else {
				jQuery(this.headerElem).append("&nbsp;");
			}
			this.createSortingPanel(this.headerElem);
			itemsListObj.appendChild(this.headerElem);
		}
		
		for (var i = 0; i < this.table.getStore().getLength(); i++) {
			var record = this.table.getStore().getRecordByIndex(i);
			
			var block = document.createElement("div");
			block.className = "item";
			
			this.buildRecordBlock (block, record);
			this.table.updateItemBlock(block, record);
			this.itemElems[record.id] = block;
			$(block).hover(function () {
				$(this).addClass("hover");
			}, function () {
				$(this).removeClass("hover");
			});			
			itemsListObj.appendChild(block);
		}
		this.outputRendered(itemsListObj);
	},
	
	createSortingPanel: function(headerElem) {
		var sortingPanel = createDiv("sorting-panel");
		sortingPanel.innerHTML = "<div class='label'>Сортировка по</label>";
		var sortingColumns = this.table.getSortingColumns();
		// sortingColumns.length
		for (var i = 0; i < 1; i++) {
			var column = sortingColumns[i];
			var sortColumn = createDiv("sort-column");
			//if (i == sortingColumns.length - 1)
			addClass(sortColumn, "last");
			this.table.createSortLabel(column, sortColumn);
			sortingPanel.appendChild(sortColumn);
		}
		headerElem.insertBefore(sortingPanel, headerElem.firstChild);
	},
	
	buildRecordBlock: function(block, record) {
		throw "Not implemented buildRecordBlock in the view";
	},
	
	refreshRecordBlock: function(recordId, record) {
		var block = this.getRecordBlock(recordId);
		block.innerHTML = "";
		this.buildRecordBlock (block, record);
		this.table.updateItemBlock(block, record);
	},
		
	getRecordBlock: function(recordId) {
		return this.itemElems[recordId];
	}
});

var WbsListView = newClass(WbsDivView, {
	constructor: function (table, config) {
		this.superclass().constructor.call(this, table, config); 
		this.modeName = "list";
	}
});

WbsTileView = newClass(WbsDivView, {
	constructor: function (table, config) {
		this.superclass().constructor.call(this, table, config); 
		this.modeName = "tile";
	}
});


var WbsDlg = newClass(WbsObservable, {
	constructor: function (config) {
		if (!config)
			config = this.getDefaultConfig();
			
		this.config = config;
		var wnd = this;
		this.buttons = new Array ();
		this.innerContentElem = null;
		
		this.superclass.constructor.call(this);
	},
	
	render: function() {
		var windowElem = document.createElement("div");
		windowElem.className = "wbs-dlg";
		if (this.config.cls)
			addClass(windowElem, this.config.cls);
		windowElem.style.display = "none";
		
		if (this.config.width) {
			windowElem.style.width = this.config.width + "px";
		}
		if (this.config.height)
			windowElem.style.height = this.config.height == 'auto' ? 'auto' : this.config.height + "px";
		
		this.createHeader(windowElem);
		this.createContent(windowElem);
		this.createFooter(windowElem);
			
		document.body.appendChild(windowElem);
		return windowElem;
	},
	
	setPosition: function() {
		var docSize = getDocumentSize();
		var windowElem = this.getWindowElem();
		windowElem.style.top = (docSize.height - windowElem.offsetHeight)/2 + "px";
		windowElem.style.left = (docSize.width - windowElem.offsetWidth)/2 + "px";
	},
	
	getWindowElem: function() {
		if (!this.windowElem) {
			this.windowElem = this.render();
		}
		return this.windowElem;
	},
	
	show: function() {
		var windowElem = this.getWindowElem();
		windowElem.style.display = "block";
		this.showFade();
		this.setSizes();
		this.setPosition();
		if (this.onAfterShow)
			this.onAfterShow();
	},
	hide: function() {
		var windowElem = this.getWindowElem();
		windowElem.style.display = "none";
		this.hideFade();
	},
		
	close: function() {
		this.hideFade();
		var windowElem = this.getWindowElem();		
		windowElem.parentNode.removeChild(windowElem);
		delete windowElem;
	},
		
	showLoading: function(label) {
		if (!label)
			label = "Loading";
		if (!this.loadingElem) {
			this.loadingElem = createDiv("wbs-absolute-full-size");
			this.loadingElem.innerHTML = "<span class='wbs-loading'>" + label + " <div class='wbs-loading-icon'>&nbsp;</div></span>";
			this.getContentElem().appendChild(this.loadingElem);
		}
	},
		
	hideLoading: function() {
		if (this.loadingElem) {
			this.loadingElem.parentNode.removeChild(this.loadingElem);
			this.loadingElem = null;
		}
	},
		
	
	getDefaultConfig: function() {
		return {};
	},
	
	createHeader: function (windowElem) {
		this.headerElem = createDiv("wbs-dlg-header");
		
		if (this.config.title) {
			var label = createDiv("label");
			label.innerHTML = this.config.title;
			this.headerElem.appendChild(label);
		}
		
		if(!this.config.hideCloseBtn)
			this.createCloseButton(this.headerElem);
		windowElem.appendChild(this.headerElem);
	},
	
	createContent: function(windowElem) {
		this.contentElem = createDiv("wbs-dlg-content");
		
		if (this.config.contentElemId) {
			var content = document.getElementById(this.config.contentElemId);
			this.contentElem.appendChild(content);
			content.style.display = "block";
		} else {
			this.innerContentElem = createDiv("wbs-dlg-content-inner");
			this.contentElem.appendChild(this.innerContentElem);
		}
		
		this.buildContent(this.getContentElem());
		
		windowElem.appendChild(this.contentElem);
	},
		
	buildContent: function(contentElem) {
	},
		
	buildFooter: function (footerElem) {
		if (this.config.buttons) {
			for (var i = 0; i < this.config.buttons.length; i++) {
				var button = this.config.buttons[i];
				var addClass = button.disabled ? " dsabled-btn" : "";
				var buttonElem = createElem("input", "wbs-dlg-button" + addClass);
				buttonElem.setAttribute("type", "button");
				buttonElem.value = button.label;
				if (button.onClick)
					buttonElem.onclick = button.onClick;
				if (button.scope)
					buttonElem.onclick = buttonElem.onclick.bind(button.scope);
				if (button.disabled)
					buttonElem.disabled = true;
				if (button.id) {
					buttonElem.id = button.id;
					this.buttons[button.id] = buttonElem;
				}
				footerElem.appendChild(buttonElem);
			}
		}	
	},
		
	getContentElem: function() {
		if (this.innerContentElem)
			return this.innerContentElem;
		else
			return this.contentElem;
	},
		
	createFooter: function (windowElem) {
		this.footerElem = createDiv("wbs-dlg-footer");
		this.buildFooter(this.footerElem);
		windowElem.appendChild(this.footerElem);
	},
	
	setSizes: function() {
		this.contentElem.style.height = (this.getHeight() - this.headerElem.offsetHeight - this.footerElem.offsetHeight) + "px";
	},
	
	getHeight: function() {
		return this.windowElem.offsetHeight;
	},
	
	getWidth: function() {
		return this.windowElem.offsetWidth;
	},
	
	createCloseButton: function (parentElem) {
		var closeBtn = createDiv("close-btn");
		closeBtn.innerHTML = '<img height="16" width="16" src="../common/templates/img/close.gif"/>';
		closeBtn.onclick = function(){
			if (this.config.closeMode == "close")
				this.close();
			else 
				this.hide();
		}.bind(this);
		parentElem.appendChild(closeBtn);
	},
	
	showFade: function() {
		if (this.fade == null) {
			this.fade = createDiv("black-overlay");
			document.body.appendChild(this.fade);
		}
		this.fade.style.display = "block";
	},
	hideFade: function() {
		if (this.fade)
			this.fade.style.display = "none";
	}
});