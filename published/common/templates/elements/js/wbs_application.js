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
		
		var left = cursorPos.x;
		var top = cursorPos.y;
		
		//elem.style.top = cursorPos.y;
		var elem = this.elem;
		elem.style.top = top + 4;
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
			
			if (!item.hidden)
				addEmptyImg(li);
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