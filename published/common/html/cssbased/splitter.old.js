/* Splitter functions */

var SplitterInfo = new Object();

RegisterOnLoad( InitSplitter );

function SelectSplitterHandle(e)
{
	// Prevent text selecting
	//
	if ( typeof(e) != 'undefined' )
		e.preventDefault();

	if (SplitterInfo.IsNetscape) {
		SplitterInfo.SplitterObj.addEventListener("onmousemove", ResizeSpliiterPanel,true);
		SplitterInfo.SplitterObj.addEventListener("onmouseup", ReleaseSplitterHandle,true);
	}

	SplitterInfo.SplitterObj.onmousemove = ResizeSpliiterPanel;
	SplitterInfo.SplitterObj.onmouseup = ReleaseSplitterHandle;

	SplitterInfo.Dragging = true;
}

function ReleaseSplitterHandle()
{
	if (!SplitterInfo.Dragging)
		return;

	if ( SplitterInfo.RightPanelWidth != null )
		if ( parseInt(SplitterInfo.RightPanelContent.style.width) != SplitterInfo.RightPanelWidth )
			SplitterInfo.RightPanelContent.style.width = SplitterInfo.RightPanelWidth + 'px';

	WriteSplitterCookie(SplitterInfo.LeftPanelWidth);

	SplitterInfo.Dragging = false;
}

function ResizeSpliiterPanel(e)
{
	if ( !SplitterInfo.Dragging )
		return;

	var splitterWidth = SkinProvider.CustomSplitterGetSplitterWidth();
	var splitterOffset = SkinProvider.CustomSplitterGetSplitterOffset();

	var clientX = (SplitterInfo.IsNetscape ? e.pageX : window.event.clientX)- 2 - splitterOffset;
	var leftPanelWidth = clientX - parseInt(SplitterInfo.SplitterObj.offsetLeft);
	var rightPanelWidth = splitterWidth - leftPanelWidth - 10;

	if ( leftPanelWidth < 35 || rightPanelWidth < 35 )
		return;

	SplitterInfo.LeftPanelContainer.style.width = leftPanelWidth + 'px';
	SplitterInfo.LeftPanelContent.style.width = leftPanelWidth + 'px';
	SplitterInfo.RightPanelWidth = rightPanelWidth;
	SplitterInfo.LeftPanelWidth = leftPanelWidth;

	if (typeof(window.CustomSplitterWidthHandler) != 'undefined')
		window.CustomSplitterWidthHandler(leftPanelWidth, rightPanelWidth);
}

function GetSplitterHeight()
{
	var Height = SkinProvider.GetContentScrollerHeight();

	if ( Height == 0 )
		Height = parseInt(SplitterInfo.ContentScrollerObj.offsetHeight);

	var SplitterHeader = $('SplitterHeader');
	if ( SplitterHeader )
		Height -= SplitterHeader.offsetHeight;

	return Height;
}

function UpdateSplitterPanels()
{
	alert("BEFORE");
	// Update splitter panels height
	//
	var splitterHeight = GetSplitterHeight();

	if (SplitterInfo.IsIe)
	{
		/*@cc_on
			@if (@_jscript_version == 5.6)
				splitterHeight -= 3;
			@else
				splitterHeight -= 2;
			@end
		@*/
	} else
		splitterHeight -= 1;
		
	if (typeof(window.CustomSplitterHeightHandler) != 'undefined')
		window.CustomSplitterHeightHandler(splitterHeight);
	else
	{
		if ( SplitterInfo.LeftPanelVisible )
		{
			SplitterInfo.LeftPanelContent.style.height = splitterHeight + 'px';
			SplitterInfo.SplitterHandleDiv.style.height = splitterHeight + 'px';
		}

		SplitterInfo.RightPanelContent.style.height = splitterHeight + 'px';
	}

	// Update right panel width
	//
	var leftPanelWidth = 0;

	if ( SplitterInfo.LeftPanelVisible )
	{
		leftPanelWidth = SplitterInfo.LeftPanelContainer.offsetWidth;
		SplitterInfo.LeftPanelContent.style.width = leftPanelWidth - 2 + 'px';

		if (leftPanelWidth < 35)
		{
			leftPanelWidth = 35;
			SplitterInfo.LeftPanelContainer.style.width = '35px';
		}
	}

	var splitterWidth = SkinProvider.CustomSplitterGetSplitterWidth();
	var rightPanelWidth = splitterWidth - leftPanelWidth;
	if (SplitterInfo.LeftPanelVisible)
	{
		rightPanelWidth -= 6;
	}

	SplitterInfo.RightPanelContent.style.width = rightPanelWidth + 'px';
}

function InitSplitter()
{
	var SplitterName = 'PageSplitter';
	
	var SplitterObj = $(SplitterName);
	

	if ( !SplitterObj )
		return;

	// Find splitter elements
	//
	SplitterInfo.Dragging = false;
	SplitterInfo.SplitterObj = SplitterObj;
	SplitterInfo.RightPanelWidth = null;

	SplitterInfo.LeftPanelContainer = FindChildObjByClass( SplitterObj, "TD", "SplitterLeftPanelContainer" );
	SplitterInfo.LeftPanelVisible = SplitterInfo.LeftPanelContainer != null;
	if ( SplitterInfo.LeftPanelVisible )
	{
		SplitterInfo.Handle = FindChildObjByClass( SplitterObj, "TD", "SplitterHandle" );
		SplitterInfo.LeftPanelContent = FindChildObjById( SplitterObj, "DIV", "SplitterLeftPanelContent" );
		SplitterInfo.SplitterHandleDiv = FindChildObjByClass( SplitterInfo.Handle, "DIV", "" );
	}

	SplitterInfo.RightPanelContainer = FindChildObjByClass( SplitterObj, "TD", "SplitterRightPanelContainer" );
	SplitterInfo.RightPanelContent = FindChildObjById( SplitterObj, "DIV", "SplitterRightPanelContent" );
	SplitterInfo.ContentScrollerObj = $('ContentScroller');

	SplitterInfo.IsNetscape = window.navigator.appName == "Netscape";
	SplitterInfo.IsIe = window.navigator.appName == "Microsoft Internet Explorer";

	if ( SplitterInfo.Handle != null ) {
		// Attach the event handler to the splitter handle
		//
		SplitterInfo.Handle.onmousedown = SelectSplitterHandle;

		// Prevent IE text selecting
		//
		document.body.ondrag = function () { return !SplitterInfo.Dragging; };
		document.body.onselectstart = function () { return !SplitterInfo.Dragging; };
	}

	// Set spliitter panel widths
	//
	UpdateSplitterPanels();

	// Attacht event handlers to the window
	//
	Event.observe(window, 'resize', UpdateSplitterPanels );
/*	if (SplitterInfo.IsNetscape) {
		window.addEventListener( "resize", UpdateSplitterPanels, false);
	} else
		window.attachEvent( "onresize", UpdateSplitterPanels ); */
}

function WriteSplitterCookie(value)
{
	var date = new Date();
	var name = "splitterView"+document.splitterName;
	date.setTime( date.getTime()+31536000000 );
	var expires = "; expires="+date.toGMTString();

	document.cookie = name+"="+value+expires+"; path=/";
}

function ReadSplitterCookie()
{
	var name = "splitterView"+document.splitterName;

	var nameEQ = name + "=";
	var ca = document.cookie.split(';');

	for( var i=0; i < ca.length;i++)
	{
		var c = ca[i];

		while (c.charAt(0)==' ')
				c = c.substring(1,c.length);

		if (c.indexOf(nameEQ) == 0)
				return c.substring(nameEQ.length,c.length);
	}

	return null;
}

function SplitterScrollPanel( FolderId )
{
	var obj = $(FolderId);
	if (!obj)
		return;

	var panel = $('SplitterLeftPanelContent');
	if (!panel || panel.style.display == 'hidden')
		return;

	panel.scrollTop = obj.offsetTop;
}