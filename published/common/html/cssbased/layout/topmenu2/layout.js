/* Skin provider class */

SkinProviderClass = Class.create();

Object.extend( SkinProviderClass.prototype, SkinProviderBase.prototype );

Object.extend( SkinProviderClass.prototype,
{
	Menu: null,

	CustomSplitterGetSplitterWidth: function()
	{
		return GetDocumentWidth() - GetVisibleElementWidth('MainMenu');
	},

	CustomSplitterGetSplitterOffset: function()
	{
		return GetVisibleElementWidth('MainMenu');
	},

	GetToolbarItemPadding: function()
	{
		return 5;
	},

	GetContentScrollerHeight: function()
	{
		var Height = GetDocumentHeight();

		if ( Height == 0 )
			return 0;

		Height -= GetVisibleElementHeight( 'Toolbar' );
		Height -= GetVisibleElementHeight( 'HeaderContainer' ); 
		Height -= GetVisibleElementHeight( 'PageTitlePanel' );
 
		if (Ext.isIE) {
			Height += 2;
			if ((LayoutManager.screenMode == "min") && !Ext.isIE7)
				Height += 18;
		}
		else
			Height += 1;
		
		
		return Height;
	},

	initialize: function()
	{
	},

	UpdateLayoutSize: function()
	{
		var newWidth = GetDocumentWidth() - GetVisibleElementWidth("MainMenu") - 1;

		var Toolbar = $('Toolbar');
		if ( Toolbar ) {
			if ( newWidth > 800 )
				Toolbar.style.width = newWidth + 'px';
			else
				Toolbar.style.width = '800px';
		}

		var ContentScroller = $( 'ContentScroller' );
		if (ContentScroller)
		{
			var Height = this.GetContentScrollerHeight();

			ContentScroller.style.width = newWidth + 'px';
			ContentScroller.style.height = Height + 'px';
		}

		var MainMenu = $( 'MainMenu' );
		if (MainMenu)
		{
			MainMenu.style.height = GetDocumentHeight() - GetVisibleElementHeight( 'HeaderContainer' ) - GetVisibleElementHeight( 'FooterContainer' ) + 'px';
		}
	},

	InitLayout: function()
	{
		this.Menu = new MainMenuSide();
		this.UpdateLayoutSize();
	},

	onMainMenuAppClick: function( Element )
	{
		this.Menu.onMenuClick(Element);
	},
	
	ShowScreenMode: function (mode) {
		if (mode == "min") {
			$("HeaderContainer").style.height = "0px";
			$("Header").style.display = "none";
			$("FooterContainer").style.display = "none";
			$("FooterContainer").style.height = "2px";
			$('TContentWrapper').style.top = "0px";
			$('LoginBlock').style.display = "none";			
		} else {
			$("HeaderContainer").style.height = "70px";
			$('TContentWrapper').style.top = "70px";
			$("Header").style.display = "block";
			$('LoginBlock').style.display = "block";
			$("FooterContainer").style.height = "55px";
			$("FooterContainer").style.display = "block";
		}
	}
}
);