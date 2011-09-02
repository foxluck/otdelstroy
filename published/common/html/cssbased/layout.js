function WebAsyst_LayoutManager()
{
	this.IsIE = window.navigator.appName == "Microsoft Internet Explorer";
	this.ToolbarItems = new Array();
	this.ScreenMode = null;

	var me = this;

	this.navigationStatus = { toolbar: null };
	this.navigationItems = new Array( 'Header', 'PageTitlePanel', 'FooterContainer', 'MainMenu', 'Toolbar', 'UserName' );

	this.SetComboBoxesVisibility = function (visible)
	{
		if ( !me.IsIE )
			return;

		c = document.getElementsByTagName("select");

		for (i = 0; i < c.length; i++) 
		{
			if (c[i] == null) continue;
			if (c[i].getAttribute("nohide") != null)
				continue;
			c[i].style.visibility = (!visible)? "hidden":"visible";
		}
	}

	this.ProcessShowMenu = function ()
	{
		me.SetComboBoxesVisibility(false);
	}

	this.ProcessHideMenu = function ()
	{
		me.SetComboBoxesVisibility(true);
	}

	this.FixToolbarItemsWidth = function (ElementId)
	{
		var padding = SkinProvider.GetToolbarItemPadding();

		for ( var i = 0; i < this.ToolbarItems.length; i++ ) {
			var element = document.getElementById(this.ToolbarItems[i]);
			element.style.width = element.offsetWidth - padding + 'px';
		}
	}

	this.AddToolbarItemId = function (ElementId)
	{
		this.ToolbarItems.push(ElementId);
	}

	this.HideNavigation = function()
	{
		for( var i = 0; i < this.navigationItems.length; i++ )
		{
			var elementObj = $(this.navigationItems[i]);
			if (elementObj)
			{
				this.navigationStatus.element = elementObj.style.visibility;
				elementObj.style.visibility = 'hidden';
			}
		}
	}

	this.ShowNavigation = function()
	{
		for( var i = 0; i < this.navigationItems.length; i++ )
		{
			var elementObj = $(this.navigationItems[i]);
			if (elementObj)
			{
				elementObj.style.visibility = this.navigationStatus.element;
			}
		}
	}
	
	
	this.SetScreenMode = function(mode) {
		this.screenMode = mode;
		
		SkinProvider.ShowScreenMode (this.screenMode);
		this.WriteScreenModeCookie(this.screenMode);		
		if ($('FullScreenOff') && $('FullScreenOn')) {
			$('FullScreenOff').style.display = (this.screenMode == "min") ? "block" : "none";
			$('FullScreenOn').style.display = (this.screenMode != "min") ? "block" : "none";
		}
		SkinProvider.UpdateLayoutSize ();
	
		if ($("PageSplitter") != null)
			InitSplitter();
		
		if (window.OnResizeHandler)
			window.OnResizeHandler ();
	}
	
	
	
	this.WriteScreenModeCookie = function(value)
	{
		var date = new Date();
		date.setTime( date.getTime()+31536000000 );
		var expires = "; expires="+date.toGMTString();

		document.cookie = "screenMode="+value+expires+"; path=/";
	}

	this.ReadScreenModeCookie = function()
	{
		var nameEQ = "screenMode=";
		var ca = document.cookie.split(';');

		for( var i=0; i < ca.length;i++)
		{
			var c = ca[i];

			while (c.charAt(0)==' ')
					c = c.substring(1,c.length);

			if (c.indexOf(nameEQ) == 0)
					return c.substring(nameEQ.length,c.length);
		}
		
		return "max";
	}
	
	
}

var LayoutManager = new WebAsyst_LayoutManager();