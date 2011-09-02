
var hideDelay = 300;

var menucount = 0;
var ua = navigator.userAgent;
var opera = /opera [56789]|opera\/[56789]/i.test(ua);
var moz = !opera && /gecko/i.test(ua);

var submenus = {};

var hideTimer = null;

function getElLeft(el) {
  l = 0;
  if (el == null) return l;
  while (el != null) {
    l += el.offsetLeft;
    el = el.offsetParent;
  }
  return l;
}

function SubMenu(caller, id) {
  this.name = "WebSubMenu_" + menucount++;
  this.obj = this.name;
  eval(this.obj + "=this");
  this.caller = caller;
  this.id = id;
  this.visible = false;
}

SubMenu.prototype.show = function() {
  clearTimeout(hideTimer);

  var oCanvas = document.getElementsByTagName((document.compatMode && document.compatMode == "CSS1Compat") ? "HTML" : "BODY")[0];

  m = document.getElementById(this.id);
  if (!m) return;
  if (this.caller != null) {
    m.style.position = "absolute";
    m.style.display = "inline";
    l = getElLeft(this.caller);
    if (moz) {
      l += "px";
    }
    m.style.left = l;
    if (moz) {
      m.style.top = (oCanvas.scrollTop + 0) + "px";
    } else {
      m.style.top = oCanvas.scrollTop;
    }
  }

  this.caller.className = "menubar-selected";
  m.style.visibility = "visible";
  this.visible = true;
  ShowHideCombox(false);
}

SubMenu.prototype._hide = function() {
  hideTimer = window.setTimeout(this.obj + ".__hide()", hideDelay);
}

SubMenu.prototype.__hide = function() {
  if (this.caller.getAttribute("selected") != "true") {
    this.caller.className = null;
  }
  m = document.getElementById(this.id);
  if (!m) return;
  m.style.visibility = "hidden";
  this.visible = false;
  ShowHideCombox(true);
}

SubMenu.prototype.hideMenu = function(e) {
  if (!moz) {
    el = e.toElement;
  } else {
    el = e.target;
  }

  m = document.getElementById(this.id);
  if (!m) return;
 
  x = (!moz)?m.style.pixelLeft:m.offsetLeft;
  y = (!moz)?m.style.pixelTop:m.offsetTop;
 
  px = (!moz)?e.x:e.pageX;
  py = (!moz)?e.y:e.pageY;
 
  var scrollTop = document.body.scrollTop;
  if (document.all) {
    var Canvas = document.getElementsByTagName((document.compatMode && document.compatMode == "CSS1Compat") ? "HTML" : "BODY")[0];
    scrollTop = Canvas.scrollTop;
  }

  if (moz) {
    px -= document.body.scrollLeft;
    py -= scrollTop;
  }
 
  x -= document.body.scrollLeft;
  y -= scrollTop;
 
  if (this.pointin(px, py, x, y, m.offsetWidth, m.offsetHeight)) return;
 
  this._hide();
}

SubMenu.prototype.pointin = function(px, py, x, y, w, h) {
  return ((px >= x) && (py >= y) &&
      (px < (x + w)) && (py < (y + h)));
}

function ShowMenu(caller, id) {
  HideAll();
  if (submenus[id] != null) {
    submenus[id].caller = caller;
  } else {
    mitem = new SubMenu(caller, id);
    submenus[id] = mitem;
  }
  submenus[id].show();
}

function SelectMenu(submenu) {
  if (!parent) return;
  if (!parent.frames[0].MenuBar) return;
  if (!parent.frames[0].MenuBar.selectMenu) return;
  parent.frames[0].MenuBar.selectMenu(submenu);
}

function HideMenu(target, id) {
  if (submenus[id] != null) {
    submenus[id].hide(target);
  }
}

function HideSubMenu(e, id) {
  if (submenus[id] != null) {
    submenus[id].hideMenu(e);
  }
}

function HideAll() {
  for (var i in submenus) {
    submenus[i].__hide(null);
  }
}

function ShowHideCombox(value) {
  if (moz || opera) return; 
  c = document.getElementsByTagName("select");
  for (i = 0; i < c.length; i++) {
    if (c[i] == null) continue;
    c[i].style.visibility = (!value)?"hidden":"visible";
  }
}

function resetHiding() {
  clearTimeout(hideTimer);
}

if (!moz) {
  window.attachEvent("onunload", HideAll);
} else {
  window.addEventListener("unload", HideAll, false);
}
