<? if isset( $HTML_AREA_FIELD ) && $HTML_AREA_FIELD != "" ?>

<script type="text/javascript">

	function calcRelativeURL( page )
	{
		var URL = new String( document.URL );

		lastSlash = URL.lastIndexOf( "/" );
		URL = URL.substring( 0, lastSlash )+"/"+page;

		return URL;
	}

	_editor_url = "../../../common/html/classic/xhina/";
	_editor_lang = "en";

	function e_findObj(n, d)
	{ //v4.01
		var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
		d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
		if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
		for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
		if(!x && d.getElementById) x=d.getElementById(n); return x;
	}
</script>

<script type="text/javascript" src="../../../common/html/classic/xhina/htmlarea.js"></script>

<? if $HTML_AREA_CONFIG == "full" ?>
<!-- load the plugins -->
<script type="text/javascript">

	HTMLArea.I18N = {
		lang: "en"
	};
	HTMLArea.loadPlugin("TableOperations");
	HTMLArea.loadPlugin("ListType");
	HTMLArea.loadPlugin("PageLink");

</script>
<? /if ?>

<script type="text/javascript">
var editor = null;

function resize_editor() {
	// resize editor to fix window
	var newHeight;

	if (document.all)
	{
		editor._textArea.style.width = "100%";
		editor._iframe.style.width = "100%";
	} else {
		editor._textArea.style.width = "99.8%";
		editor._iframe.style.width = "99.8%";
	}
}

function initEditor()
{
	var config = new HTMLArea.Config();

<? if $HTML_AREA_CONFIG == "full" ?>
	config.toolbar = [
		['popupeditor'],
		['formatblock', 'space', 'fontname', 'space',
		'fontsize', 'space',
		'bold', 'italic', 'underline', "strikethrough", "separator", "forecolor", "hilitecolor", "separator",
		"subscript", "superscript", "separator",
		"inserttable","separator"
		],
		[ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator",
		"insertorderedlist", "insertunorderedlist", "outdent", "indent", "separator",
		"inserthorizontalrule",  "createlink", "insertimage", "separator", "htmlmode", "killword" ]
		];
		config.statusBar = false;
<? else ?>
	config.toolbar = [
		['fontname', 'space',
		'fontsize', 'space',
		'bold', 'italic', 'underline', "strikethrough", "separator", "forecolor", "hilitecolor"
		],
		[ "justifyleft", "justifycenter", "justifyright", "justifyfull", "separator",
		"insertorderedlist", "insertunorderedlist", "outdent", "indent", "separator",
		"inserthorizontalrule", "separator", "htmlmode"  ]
		];
		config.statusBar = false;
<? /if ?>

	config.stripBaseHref = true;
	config.stripSelfNamedAnchors = true;

//	config.baseHref = "http://webasyst/";

	editor = new HTMLArea("<? $HTML_AREA_FIELD ?>", config );

<? if $HTML_AREA_CONFIG == "full" ?>
	editor.registerPlugin(TableOperations);
	editor.registerPlugin(ListType);
	editor.registerPlugin(PageLink);
<? /if ?>

	editor.generate();

	setTimeout(function() {
			window.onresize = resize_editor;
		}, 50 ); // give it some time to meet the new frame
}

function insertHTML(html)
{
	if (html)
	{
		editor.insertHTML(html);
	}
}

function onSubmitEditor()
{
	editor._textArea.value = editor.outwardHtml( editor.getHTML() );
	return true;
}

</script>

<script type="text/javascript">
HTMLArea.I18N = {

	// the following should be the filename without .js extension
	// it will be used for automatically load plugin language.

	lang: "en",

	tooltips: {
		bold:			"<? $kernelStrings.we_tooltips_bold?>",
		italic:		"<? $kernelStrings.we_tooltips_italic?>",
		underline:		"<? $kernelStrings.we_tooltips_underline?>",
		strikethrough:	"<? $kernelStrings.we_tooltips_strikethrough?>",
		subscript:	"<? $kernelStrings.we_tooltips_sub?>",
		superscript:	"<? $kernelStrings.we_tooltips_super?>",
		justifyleft:		"<? $kernelStrings.we_tooltips_justifyleft?>",
		justifycenter:	"<? $kernelStrings.we_tooltips_justifycenter?>",
		justifyright:	"<? $kernelStrings.we_tooltips_justifyright?>",
		justifyfull:		"<? $kernelStrings.we_tooltips_justifyfull?>",
		orderedlist:	"<? $kernelStrings.we_tooltips_orderedlist?>",
		unorderedlist:	"<? $kernelStrings.we_tooltips_unorderedlist?>",
		outdent:		"<? $kernelStrings.we_tooltips_outdent?>",
		indent:		"<? $kernelStrings.we_tooltips_indent?>",
		forecolor:		"<? $kernelStrings.we_tooltips_forecolor?>",
		hilitecolor:	"<? $kernelStrings.we_tooltips_hilitecolor?>",
		horizontalrule:	"<? $kernelStrings.we_tooltips_horizontalrule?>",
		createlink:	"<? $kernelStrings.we_tooltips_createlink?>",
		insertimage:	"<? $kernelStrings.we_tooltips_insertimage?>",
		inserttable:	"<? $kernelStrings.we_tooltips_inserttable?>",
		htmlmode:	"<? $kernelStrings.we_tooltips_htmlmode?>",
		popupeditor:	"<? $kernelStrings.we_tooltips_popupeditor?>",
		about:		"<? $kernelStrings.we_tooltips_about?>",
		showhelp:	"<? $kernelStrings.we_tooltips_showhelp?>",
		textindicator:	"<? $kernelStrings.we_tooltips_textindicator?>",
		undo:		"<? $kernelStrings.we_tooltips_undo?>",
		redo:		"<? $kernelStrings.we_tooltips_redo?>",
		cut:			"<? $kernelStrings.we_tooltips_cut?>",
		copy:		"<? $kernelStrings.we_tooltips_copy?>",
		paste:		"<? $kernelStrings.we_tooltips_paste?>",
		lefttoright:		"<? $kernelStrings.we_tooltips_lefttoright?>",
		righttoleft:		"<? $kernelStrings.we_tooltips_righttoleft?>"
	},

	buttons: {
		"ok":			"<? $kernelStrings.we_button_ok?>",
		"cancel":		"<? $kernelStrings.we_button_cancel?>"
	},

	msg: {
		"Path":			"<? $kernelStrings.we_msg_path?>",
		"TEXT_MODE":	"<? $kernelStrings.we_msg_textmode?>",

		"IE-sucks-full-screen" :

		"The full screen mode is known to cause problems with Internet Explorer, " +
		"due to browser bugs that we weren't able to workaround.  You might experience garbage " +
		"display, lack of editor functions and/or random browser crashes.  If your system is Windows 9x " +
		"it's very likely that you'll get a 'General Protection Fault' and need to reboot.\n\n" +
		"You have been warned.  Please press OK if you still want to try the full screen editor."
	},

	dialogs: {
		"Cancel":	"<? $kernelStrings.we_dlg_cancel?>",
		"Insert/Modify Link":	"<? $kernelStrings.we_dlg_link?>",
		"New window (_blank)":	"<? $kernelStrings.we_dlg_nwindow?>",
		"None (use implicit)":	"<? $kernelStrings.we_dlg_none?>",
		"OK":	"<? $kernelStrings.we_dlg_ok?>",
		"Other":	"<? $kernelStrings.we_dlg_other?>",
		"Same frame (_self)":	"<? $kernelStrings.we_dlg_sameframe?>",
		"Target:":	"<? $kernelStrings.we_dlg_target?>",
		"Title (tooltip):":	"<? $kernelStrings.we_dlg_title?>",
		"Top frame (_top)":	"<? $kernelStrings.we_dlg_topframe?>",
		"URL:":	"<? $kernelStrings.we_dlg_url?>",
		"You must enter the URL where this link points to":	"<? $kernelStrings.we_dlg_editor?>"
	}
};
</script>

<? /if ?>
