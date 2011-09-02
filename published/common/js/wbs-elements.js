WbsObservable = newClass(WBS.util.Observable, {});

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
  		this.subFrame.parentNode.removeChild(this.subFrame);
  		this.subFrame = null;
  	}
  },
  
  getOldUrl: function(url) {
  	return "../html/" + url;
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

WbsPopwindow = newClass (WbsObservable, {
	
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
		$(elem).addClass(this.getWindowCls());
		$(elem).css({'z-index': 500});
		
		if (config.cls)
			$(elem).addClass(config.cls);
		
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
		
		e = e || window.event;
		
		if(e.stopPropagation) e.stopPropagation();
		else e.cancelBubble = true;
			
		var target = e.target||e.srcElement;

		var targetPos = getAbsolutePos(target);
		
		var left = targetPos.x;
		var top = targetPos.y;
		
	
		
		//elem.style.top = cursorPos.y;
		var elem = this.elem;
		elem.style.top = top + target.offsetHeight + "px";
		elem.style.left = left + "px";
/**		
		if (this.config.scroll) 
		$(this.config.scroll).scroll(function () {
			var targetPos = getAbsolutePos(target);
			var left = targetPos.x;
			var top = targetPos.y;
			
			if (top + target.offsetHeight + $(elem).height() <= $(document).height()) {
				$(elem).show();
				elem.style.top = top + target.offsetHeight + "px";
				elem.style.left = left + "px";
			} else {
				$(elem).hide();
			}
		});		
*/
		document.body.appendChild(elem);
		elem.style.visibility = "visible";
		
		document.wbsPopmenuCurrent = this;
		
		if (left + elem.offsetWidth > $(document).width()) {
			elem.style.left = ($(document).width() - elem.offsetWidth-20) + "px";
		}
		if (top + elem.offsetHeight + $(elem).height() > $(document).height()) {
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
		this.isPd = config.isPd;
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
		$(this.items[id]).addClass("hidden");
	},
	
	showItem: function (id) {
		$(this.items[id]).removeClass("hidden");
	},
	
	createItem: function(item) {
		var li = document.createElement("li");
		if (item.cls)
			$(li).addClass(item.cls);
		
		if (item.iconCls) {
			$(li).addClass("with-image").addClass(item.iconCls);
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
			
			if (!item.hidden) {
				if (this.isPd)
					$(li).append('<img src="../common/html/res/images/s.gif" width="1" height="1" />');
				else
					$(li).append('<img src="../common/html/res/images/s.gif" width="1" height="1" />');
			}
				
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
				$(this).addClass("highlight");
		}
		li.onmouseout = function() {
			if (!this.disabled)
				$(this).removeClass("highlight");
		}
		
		if(item.hidden) {
			$(li).addClass("hidden");
		}
		if(item.disabled) {
			$(li).addClass("disabled");
			li.disabled = true;
		}
		li.setText = function(text) {this.innerHTML = text;}
		return li;
	}
});


WbsButton = newClass (null, {
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
	
	render: function() {
		var contentElem = this.getContentElem();
		
		var btnElem = createDiv(this.cls);
		this.btnElem = btnElem;
		
		var advElem = createDiv("wbs-btn-adv");
		var mainElem = createElem("a", "wbs-btn-content");
		mainElem.setAttribute("href", "javascript:void(0)");
		
		if (this.config.iconUrl) {
			mainElem.style.backgroundImage = "URL(" + this.config.iconUrl + ")";
			$(mainElem).addClass("wbs-btn-withicon");
		}
		
		if (this.config.advIconUrl) {
			advElem.style.backgroundImage = "URL(" + this.config.advIconUrl + ")";
			$(advElem).addClass("wbs-btn-left-icon");
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
		if (!contentElem )
			throw "No content element for WbsButton: " + this.config.el;
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
			$(this.btnElem).addClass("wbs-btn-disabled");
		else
			$(this.btnElem).removeClass("wbs-btn-disabled");
	},
		
	disable: function() {
		this.setDisabled(true);
	}, 
		
	enable: function() {
		this.setDisabled(false);
	}
});

WbsLinkButton = newClass(WbsButton, {
	constructor: function (config) {
		this.cls = "wbs-link-btn";
		this.superclass().constructor.call(this,config);	
	},
	
	build: function(mainElem, advElem) {
		this.btnElem.appendChild(mainElem);
	}	
});

WbsMenuButton = newClass(WbsButton, {
	constructor: function (config) {
		this.cls = "wbs-menu-btn";
		this.superclass().constructor.call(this,config);	
		
		if (this.config.getMenu)
			addHandler(this.btnElem, "click", function(e) {
				var getMenu = (this.config.scope) ? this.config.getMenu.bind(this.config.scope) : this.config.getMenu;
					
				var menu = getMenu(e);
				if (menu) {
					menu.show(e);
				}
			}
			, this);
	},
	
	build: function(mainElem, advElem) {
		this.btnElem.appendChild(mainElem);
		this.btnElem.appendChild(advElem);
	}
});


WbsFlexContainer = newClass (null, {
	constructor: function (config) {
		this.config = config;
		this.contentElem = config.contentElem;
		this.elem = config.elem;
		if (!this.contentElem)
			throw "No exists content elem for WbsFlexContainer";
		this.elem.addClass("wbs-flexcontainer");
		this.childElems = new Array ();
		
		this.buildPage();
		
		var fn = this.resize.bind(this);

		$(document).ready(fn);
		$(window).resize(fn);
		
	},
	
	addChildElem: function(childElem) {
		this.childElems[this.childElems.length] = childElem;
	},
	
	buildPage: function () {
		if (this.config.headerElem) {
			this.config.headerElem.addClass("wbs-flexcontainer-header");
			this.addChildElem(this.config.headerElem);
		}		
		
		this.contentElem.addClass("wbs-flexcontainer-content");
		this.addChildElem(this.contentElem);
		
		if (this.config.footerElem) {
			this.contentElem.addClass("wbs-flexcontainer-footer");
			this.addChildElem(this.config.footerElem);
		}
	},
	
	resize: function () {
		var minusHeight = 0;
		for (var i = 0; i < this.childElems.length; i++) {
			var childElem = this.childElems[i];
			if (childElem == this.contentElem)
				continue;
			minusHeight += childElem.outerHeight();
		}
		this.elem.height($(window).height() - minusHeight);
		this.contentElem.height(this.elem.height());
	}
});

WbsViewmodeSelector = newClass(WbsObservable, {
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
		return ("../../common/templates/img/viewmode_" + mode + addPart + ".gif");
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
		if (this.table)
			this.table.setViewMode(mode);
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