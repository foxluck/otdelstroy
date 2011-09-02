
var ua = navigator.userAgent;
var opera = /opera [56789]|opera\/[56789]/i.test(ua);
var moz = !opera && /gecko/i.test(ua);

function openWBSHelp( app_id, help_url, db_key, language ) {
  if (arguments.length > 0) {
    url = arguments[0];
  }

  if (app_id != "NOHELP" || app_id=="")
	  help_page = "../../../"+app_id+"/help/"+language+"/"+app_id.toLowerCase()+".htm";
  else
	  help_page = "../../../common/help/"+language+"/whatiswbs.htm";

  url = help_url+language+"/index.php?page="+help_page+"&DB_KEY="+db_key;

  var xSize = screen.width*0.8;
  var ySize = screen.height*0.7;
  var yOffset = (screen.height - ySize) / 2;
  var xOffset = (screen.width - xSize) / 2;
/*
  var xOffset = (screen.width - xSize);
  if (!moz && !opera) {
    xOffset -= 10;
  } else if (moz) {
    xOffset -= 6;
  } else if (opera) {
    xOffset -= 8;
  }
*/
  var qs = window.open(url, "WBSHelp", "width="+xSize+",height="+ySize+",screenX="+xOffset+",screenY="+yOffset+",top="+yOffset+",left="+xOffset+",titlebar=no, resizable=yes");
  qs.opener = self;
  qs.focus();
}

function openHelp( url )
{
	var xSize = screen.width*0.8;
	var ySize = screen.height*0.7;
	var yOffset = (screen.height - ySize) / 2;
	var xOffset = (screen.width - xSize) / 2;

	var qs = window.open(url, "WebAsystHelp", "width="+xSize+",height="+ySize+",screenX="+xOffset+",screenY="+yOffset+",top="+yOffset+",left="+xOffset+",titlebar=no, resizable=yes");
	qs.opener = self;
	qs.focus();
}

function openWBSHelpURL(url) {
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
  var qs = window.open(url, "WBSHelp", "width="+xSize+",height="+ySize+",screenX="+xOffset+",screenY="+yOffset+",top="+yOffset+",left="+xOffset+",titlebar=no, resizable=yes");
  qs.opener = self;
  qs.focus();
}