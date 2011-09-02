/**
 * if after submited on the enter key happened to refresh of a page, useful apply this function
 * @param event evt keys handler
 * @param string for_exec code for executable
**/
function form_action(evt, for_exec)
	{
	evt = (evt) ? evt : event;
	if (13 == evt.keyCode)
		{
		eval(for_exec);
		return false;
		}
	}
	
function AttachEvent( Element, Event, Handler )
{
	if ( Element.addEventListener ) {
		Element.addEventListener( Event, Handler, true );
	} else
		if ( Element.attachEvent )
			Element.attachEvent( Event, Handler );
}

function FindChildObjByClass( Parent, TagName, ClassName )
{
	var i = 0;
	for ( i = 0; i < Parent.childNodes.length; i++ ) {
		var Node = Parent.childNodes[i];

		if ( Node.nodeName == TagName && ( ClassName == null || (ClassName != null && Node.className == ClassName) ) )
			return Node;

		var Result = FindChildObjByClass( Node, TagName, ClassName );
		if ( Result != null )
			return Result;
	}

	return null;
}

function FindChildObjById( Parent, TagName, Id )
{
	var i = 0;
	for ( i = 0; i < Parent.childNodes.length; i++ ) {
		var Node = Parent.childNodes[i];

		if ( Node.nodeName == TagName && ( Id == null || Node.id == Id ) )
			return Node;

		var Result = FindChildObjById( Node, TagName, Id );
		if ( Result != null )
			return Result;
	}

	return null;
}

function FindInputControl( Parent )
{
	var i = 0;
	for ( i = 0; i < Parent.childNodes.length; i++ ) {
		var Node = Parent.childNodes[i];

		if ( Node.nodeType != 1 )
			continue;

		if ( Node.nodeName == "INPUT" && (Node.type == 'text' || Node.type == 'password') )
			return Node;

		var Result = FindInputControl(Node);
		if ( Result != null )
			return Result;
	}

	return null;
}

function FindNodeY( Node, ContextNode )
{
	var Offset = Node.offsetTop;
	var CurParent = Node.offsetParent;
	
	while (CurParent != null && CurParent != ContextNode)
	{
		Offset += CurParent.offsetTop;
		CurParent = CurParent.offsetParent;
	}

	return Offset;
}

function FindNodeX( Node, ContextNode )
{
	var Offset = Node.offsetLeft;
	var CurParent = Node.offsetParent;
	
	while (CurParent != null && CurParent != ContextNode)
	{
		Offset += CurParent.offsetLeft;
		CurParent = CurParent.offsetParent;
	}

	return Offset;
}

function GetVisibleElementHeight( ElementId )
{
	var Element = $(ElementId);

	if (Element && Element.style.display != "none")
		return Element.offsetHeight;

	return 0;
}

function GetVisibleElementWidth( ElementId )
{
	var Element = $(ElementId);

	if (Element && Element.style.display != "none")
		return Element.offsetWidth;

	return 0;
}

function GetDocumentWidth()
{
	if ( typeof(document.documentElement.clientWidth) != 'undefined' && document.documentElement.clientWidth > 0 )
		return document.documentElement.clientWidth;

	if ( typeof(document.body.clientWidth) != 'undefined' )
		return document.body.clientWidth;

	return 0;
}

function GetDocumentHeight()
{
	if (document.clientHeight != null)
		return document.clientHeight;
	
	if ( typeof(document.documentElement.clientHeight) != 'undefined' && document.documentElement.clientHeight > 0 )
		return document.documentElement.clientHeight;

	if ( typeof(document.body.clientHeight) != 'undefined' )
		return document.body.clientHeight;

	return 0;
}

/*
 * Tab form classes
 */

function TabItem( TabElement, Parent, Index, id )
{
	this.TabElement = TabElement;
	this.CustomOnClickHandler = null;
	this.Anchor = FindChildObjByClass( this.TabElement, 'A', null );
	this.Parent = Parent;
	this.Index = Index;
	this.Id = id;

	var me = this;

	this.IsActive = this.TabElement.className.indexOf( 'Active' ) >= 0;

	if ( typeof(this.Anchor.onclick) != 'undefined' )
		this.CustomOnClickHandler = this.Anchor.onclick;

	this.ProcessClick = function(e)
	{
		if ( me.Anchor.blur )
			me.Anchor.blur();

		if (me.IsActive)
			return;

		if ( me.CustomOnClickHandler != null )
		{
			var OnClickResult = me.CustomOnClickHandler();
			if ( typeof(OnClickResult) != 'undefined' && !OnClickResult )
				return;
		}

		me.Parent.SwitchTab(me);
	}

	this.Activate = function()
	{
		if ( this.IsActive )
			return;

		me.TabElement.className = me.TabElement.className + ' Active';
		this.IsActive = true;
	}

	this.Deactivate = function()
	{
		me.TabElement.className = me.TabElement.className.replace( 'Active', '' );
		this.IsActive = false;
	}

	this.Anchor.onclick = this.ProcessClick;
}

function TabbedForm( ControlId, Vertical )
{
	this.Element = $(ControlId);
	this.Tabs = new Array();
	this.Pages = new Array();
	this.ControlId = ControlId;

	if ( !Vertical )
	{
		this.List = FindChildObjByClass( this.Element, 'DL', null );

		var tabIndex = 0;
		for ( var i = 0; i < this.List.childNodes.length; i++ ) {
			var Node = this.List.childNodes[i];

			if ( Node.nodeName == 'DD' )
				this.Tabs.push(new TabItem(Node, this, tabIndex++));

			if ( Node.nodeName == 'DT' )
				this.Pages.push(Node);
		}
	} else
	{
		this.List = $(ControlId+"Tabs");
		var tabIndex = 0;
		for ( var i = 0; i < this.List.childNodes.length; i++ ) {
			var Node = this.List.childNodes[i];

			if ( Node.nodeName == 'DD' )
				this.Tabs.push(new TabItem(Node, this, tabIndex++, Node.id));
		}

		this.List = $(ControlId+"Pages");
		for ( var i = 0; i < this.List.childNodes.length; i++ ) {
			var Node = this.List.childNodes[i];

			if ( Node.nodeName == 'DT' )
				this.Pages.push(Node);
		}
	}

	this.SwitchTab = function( Tab )
	{
		for ( var i = 0; i < this.Tabs.length; i++ ) {
			var CurTab = this.Tabs[i];
			if ( CurTab != Tab ) {
				CurTab.Deactivate();
				this.Pages[CurTab.Index].style.display = "none";
			}
		}

		for ( var i = 0; i < this.Tabs.length; i++ ) {
			var CurTab = this.Tabs[i];
			if ( CurTab == Tab ) {
				CurTab.Activate();
				this.Pages[CurTab.Index].style.display = "block";
				this.FocusTabControl(this.Pages[CurTab.Index]);
			}
		}
	}

	this.SwitchTo = function( TabId )
	{
		for ( var i = 0; i < this.Tabs.length; i++ )
		{
			var Tab = this.Tabs[i];
			if ( Tab.Id == this.ControlId + TabId )
			{
				this.SwitchTab( Tab );
				return;
			}
		}
	}

	this.FocusTabControl = function( TabPage )
	{
		var TabInputControl = FindInputControl( TabPage );
		if ( TabInputControl )
			TabInputControl.focus();
	}
}

/* 
 * Menu classes
 */

MainMenuSide = Class.create();

MainMenuSide.prototype = {
	initialize: function()
	{
	},

	toggleMenu: function(ItemElement)
	{
		if (ItemElement.parentNode.className.indexOf("MenuExpanded") != -1)
		{
			Element.classNames(ItemElement.parentNode).remove("MenuExpanded");
			this.SetCookie(ItemElement, 0);
		}
		else
		{
			Element.classNames(ItemElement.parentNode).add("MenuExpanded");
			this.SetCookie(ItemElement, 1);
		}
	},

	SetCookie: function(ItemElement, value)
	{
		var menuId = ItemElement.parentNode.parentNode.id;
		var date = new Date();
		var name = "sideMenu"+menuId;
		date.setTime( date.getTime()+31536000000 );
		var expires = "; expires="+date.toGMTString();
		document.cookie = name+"="+value+expires+"; path=/";
	},

	onMenuClick: function(Element)
	{
		this.toggleMenu(Element);
	}
}

MainMenuTop = Class.create();

MainMenuTop.prototype = {
	initialize: function()
	{
	},

	onMenuClick: function(ItemElement)
	{
	}
}

/*
 * SkinProvider class
 */

var SkinProviderBase = Class.create();
SkinProviderBase.prototype = 
{
	initialize: Prototype.emptyFunction,
	UpdateLayoutsize: Prototype.emptyFunction,

	CustomSplitterGetSplitterWidth: function()
	{
		return document.body.clientWidth;
	},

	CustomSplitterGetSplitterOffset: function()
	{
		return 0;
	},

	GetToolbarItemPadding: function()
	{
		return 0;
	},

	GetContentScrollerHeight: function()
	{
		var Scroller = $('ContentScroller');
		if (Scroller)
			return Scroller.offsetHeight;
		
		return 0;
	},

	InitLayout: function()
	{
		this.UpdateLayoutsize();
	},

	onMainMenuAppClick: function()
	{
	}
}

var SkinProvider = null;
var OnLoadFunctions = new Array();

function RegisterOnLoad( callback )
{
	OnLoadFunctions.push( callback );
}

/**
* CSS manage functions
*/
function hasClass(ele,cls) {
  return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
}
function addClass(ele,cls) {
  if (!this.hasClass(ele,cls)) ele.className += " "+cls;
}
function removeClass(ele,cls) {
	if (hasClass(ele,cls)) {
    	var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
		ele.className=ele.className.replace(reg,' ');
	}
}

function is_object( mixed_var ){
    if(mixed_var instanceof Array) {
	return false;
    } else {
	return (mixed_var !== null) && (typeof( mixed_var ) == 'object');
    }
}
