
//var popupmenu_hideDelay = 800;
var popupmenu_hideDelay = 300;
//var popupmenu_hideDelay = 0;

var popupmenu_menucount = 0;
var ua = navigator.userAgent;
var opera = /opera [56789]|opera\/[56789]/i.test(ua);
var moz = !opera && /gecko/i.test(ua);

function attachEventEx(target, event, func) {
  if (!moz) {
    target.attachEvent("on" + event, func);
  }
  if (moz) {
    target.addEventListener(event, func, false);
  }
}

function detachEventEx(target, event, func) {
  if (!moz) {
    target.detachEvent("on" + event, func);
  }
  if (moz) {
    target.removeEventListener(event, func, false);
  }
}

function PopupWebMenu(className) {
  this.className = className;
  this.name = "WebMenu_" + popupmenu_menucount++;
  this.obj = this.name;
  eval(this.obj + "=this");
  this.menus = {};
  this.hideTimer = null;
  this.rightAlign = (arguments.length > 1)?arguments[2]:false;
  this.blockModel = (arguments.length > 1)?arguments[1]:false;
  this.boxModel = (this.className.toLowerCase() == "popupmenubox");
  this.clicked = false;
  this.clickTimer = null;
}

PopupWebMenu.prototype.show = function(caller, id)
{
  if (this.hideTimer != null)
    window.clearTimeout(this.hideTimer);
  this.h2 = new Function(this.obj + ".hide('" + this.id + "');");
  attachEventEx(document.body, "mouseup", this.h2);
  if (this.menus[id] != null) {
    this.menus[id].caller = caller;
    this.menus[id].show(( arguments.length > 2) ? [ arguments[2]||false, arguments[3]||false ] : false);
  } else {
    mitem = new PopupSubMenu(this, caller, id );
    this.menus[this.menus.length] = mitem;
    mitem.rightAlign = this.rightAlign;
    mitem.show(( arguments.length > 2) ? [ arguments[2]||false, arguments[3]||false ]:false);
  }
  window.status = '';
  return true;
}

PopupWebMenu.prototype.hide = function(id) {
  if (this.hideTimer != null)
    window.clearTimeout(this.hideTimer);
  this.hideTimer = window.setTimeout(this.obj + "._hide('" + id + "')", popupmenu_hideDelay);
}

PopupWebMenu.prototype.click = function(id) {
  this.clicked = true;
  this.clickTimer = setTimeout(this.obj + ".resetClick('" + id + "')", 50);
  this._hide(id);
}

PopupWebMenu.prototype.resetClick = function(id) {
  if (this.clicked) return;
  if (this.clickTimer)
    window.clearTimeout(this.clickTimer);
  this.clicked = false;
}

PopupWebMenu.prototype._hide = function(id) {
  this.h2 = new Function(this.obj + ".hide('" + this.id + "');");
  detachEventEx(document.body, "mouseup", this.h2);
  if (this.menus[id] != null)
    this.menus[id].hide();
}

PopupWebMenu.prototype.hideAll = function(except) {
  for (var j in this.menus) {
    if (this.menus[j] == except) continue;
    this.menus[j].hide();
  }
}

PopupWebMenu.prototype.resetHiding = function() {
  window.status = '';
  if (this.hideTimer != null)
     window.clearTimeout(this.hideTimer);
}

PopupWebMenu.prototype.getElLeft = function(el) {
  l = 0;
  if (el == null) return l;
  while (el != null) {
    l += el.offsetLeft;
    el = el.offsetParent;
  }
  return l;
}

PopupWebMenu.prototype.getElTop = function(el) {
  t = 0;
  if (el == null) return t;
  while (el != null) {
    t += el.offsetTop;
    el = el.offsetParent;
  }
  return t;
}

function PopupSubMenu(parent, caller, id) {
  this.parent = parent;
  this.caller = caller;
  this.id = id;
  this.idx = -1;
  this.name = this.id + "_SubMenu";
  this.obj = this.name;
  eval(this.obj + "=this");

  this.parent.menus[this.id] = this;

  this.visible = false;
  this.hideTimer = null;

  this.r = new Function(this.parent.obj + ".resetHiding(); return true");
  this.c = new Function(this.parent.obj + ".click('" + this.id + "');");

  this.e = document.getElementById(this.id);
  if (this.e.attachEvent) {
    this.e.attachEvent("onmouseover", this.r);
  }
  if (this.e.addEventListener) {
    this.e.addEventListener("mouseover", this.r, false);
  }

  for (var i in this.e.children) {
    if (this.e.children[i].attachEvent) {
      this.e.children[i].attachEvent("onmouseover", this.r);
      this.e.children[i].attachEvent("onclick", this.c);
    }
    if (this.e.children[i].addEventListener) {
      this.e.children[i].addEventListener("mouseover", this.r, false);
      this.e.children[i].addEventListener("click", this.c, false);
    }
  }
}

PopupSubMenu.prototype.show = function() {
  this.parent.hideAll(this);
  this.parent.resetHiding();
  this.visible = true;

  if (!moz) this.caller.focus();

  var arg = arguments[0];
  var ev = arg[0]||false;
  var parentDiv = arg[1]||false;

  if ( arguments.length >= 1 && ev )
  {
          if (!moz)
           {
	      this.x = this.parent.getElLeft(this.caller) - ( ( parentDiv != false ) ? this.parent.getElLeft(parentDiv) : 0 );
               this.y = this.parent.getElTop(this.caller) - ( ( parentDiv != false  ) ? this.parent.getElTop(parentDiv) : 0 );
           }
           else
           {
               this.x = this.parent.getElLeft(this.caller);
               this.y = ev.pageY;
           }
  }
  else
  {
           this.x = this.parent.getElLeft(this.caller);
           this.y = this.parent.getElTop(this.caller);
           width = this.caller.offsetWidth;

	  if (!moz) {
	    if (!this.parent.boxModel) {
	      this.y += this.caller.offsetHeight;
	    } else {
	      this.x += this.caller.offsetWidth;
	    }
	  } else {
	    if (!this.parent.boxModel) {
	      this.y += this.caller.offsetHeight;
	    } else {
	      this.x += this.caller.offsetWidth;
	    }
	  }
  }

  width = this.caller.offsetWidth;


  if (this.rightAlign)
    this.x += width - 150;

  this.e = document.getElementById(this.id);
  if (this.parent.blockModel) {
    this.e.style.position = "relative";
    this.e.style.display = "block";
    this.e.style.visibility = "visible";
  } else {
    this.e.style.position = "absolute";
    this.e.style.left = this.x;
    this.e.style.top = this.y;
    this.e.style.display = "inline";
    this.e.style.visibility = "visible";
  }

  ShowHideCombox(false);
}

PopupSubMenu.prototype.hide = function() {
  if (!moz) this.caller.blur();

  if (!this.visible) return;

  this.visible = false;
  this.e = document.getElementById(this.id);
  if (this.parent.blockModel) {
    this.e.style.display = "none";
  } else {
    this.e.style.visibility = "hidden";
  }
  ShowHideCombox(true);
}

function ShowHideCombox(value) {
  if (moz || opera) return;
  c = document.getElementsByTagName("select");
  for (i = 0; i < c.length; i++) {
    if (c[i] == null) continue;
    c[i].style.visibility = (!value)?"hidden":"visible";
  }
}


function showHideComment (fieldName) {
	if (document.getElementById(fieldName + "Comment").style.display != "block") {
		document.getElementById(fieldName + "Comment").style.display = "block";
		ShowHideCombox(false);
	} 	else { 
		document.getElementById(fieldName + "Comment").style.display = "none";
		ShowHideCombox(true);
	}
}