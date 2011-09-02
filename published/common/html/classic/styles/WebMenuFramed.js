
var hideDelay = 100;

var menucount = 0;
var ua = navigator.userAgent;
var opera = /opera [56789]|opera\/[56789]/i.test(ua);
var moz = !opera && /gecko/i.test(ua);

function WebMenu() {
  this.topFrame = 0;
  this.contentFrame = 1;
  if (arguments.length > 0) {
    this.topFrame = arguments[0];
    if (arguments.length > 1) {
      this.contentFrame = arguments[1];
    }
  }
  this.name = "WebMenu_" + menucount++;
  this.obj = this.name;
  eval(this.obj + "=this");
}

WebMenu.prototype.show = function(caller, id) {
  if (!parent) return;
  if (!parent.frames[this.contentFrame].resetHiding) return;
  parent.frames[this.contentFrame].resetHiding();
  parent.frames[this.contentFrame].ShowMenu(caller, id);
}

WebMenu.prototype.hide = function(e, id) {
  if (!parent) return;
  if (!moz) {
    target = e.toElement;
  } else {
    target = e.target;
  }
  if (!parent.frames[this.contentFrame].HideMenu) return;
  parent.frames[this.contentFrame].HideMenu(target, id);
}

WebMenu.prototype.contains = function(el) {
  if (el != null) {
    if (!moz) {
      return document.body.contains(el);
    } else {
      found = false;
      for (i = 0; i < document.documentElement.childNodes; i++) {
        if (document.documentElement.childNodes[i] = target) {
          found = true;
          break;
        }
      }
      return found;
    }
  }
  return false;
}

WebMenu.prototype.blur = function(e) {
  if (!parent.frames[this.contentFrame].HideAll) return;
  if (!moz) {
    src = e.srcElement;
    target = e.toElement;
  } else {
    src = e.target;
    target = e.target;
  }
  if ((src.tagName.toLowerCase() == "table") && (src.className.toLowerCase() == "menubar")) {
    if (this.contains(target)) {
      if (parent != null) {
        parent.frames[this.contentFrame].HideAll();
      }
    }
  } else {
   if (parent != null) {
     parent.frames[this.contentFrame].HideAll();
   }
  }
}

WebMenu.prototype.selectMenu = function(submenu) {
  var links = document.getElementsByTagName("a");
  for (var i = 0; i < links.length; i++) {
    if (links[i].getAttribute("submenu") != null) {
      if (links[i].getAttribute("submenu") == submenu) {
        links[i].className = "menubar-selected";
        links[i].setAttribute("selected", "true");
      } else {
        links[i].className = null;
        links[i].setAttribute("selected", null);
      }
    }
  }
}

MenuBar = new WebMenu();
