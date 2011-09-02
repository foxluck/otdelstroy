
var ua = navigator.userAgent;
var opera = /opera [56789]|opera\/[56789]/i.test(ua);
var moz = !opera && /gecko/i.test(ua);

function openWBSHelp(url) {
  if (arguments.length > 0) {
    url = arguments[0];
  }
  var xSize = 600;
  var ySize = 600;
  var yOffset = 0;
  var xOffset = (screen.width - xSize);
  if (!moz && !opera) {
    xOffset -= 10;
  } else if (moz) {
    xOffset -= 6;
  } else if (opera) {
    xOffset -= 8;
  }
  var qs = window.open(url, "WBSQuickStart", "width="+xSize+",height="+ySize+",screenX="+xOffset+",screenY="+yOffset+",top="+yOffset+",left="+xOffset+",titlebar=no, resizable=yes");
  qs.opener = self;
  qs.focus();
}