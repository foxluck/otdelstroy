function UpdateTabbarSize()
{
	var TabBarContent = $("TabBarContent");
	var Scroller = $( 'ContentScroller');
	var Wrapper = $( 'ContentWrapper');
	var HeaderHeight = GetVisibleElementHeight( 'TabBarHeader' );
	var FooterHeight = GetVisibleElementHeight( 'TabBarFooter' );
	var ToolbarHeight = GetVisibleElementHeight( 'Toolbar' );

	var TabBar = $("TabBar");

	var Top = FindNodeY(TabBarContent, Wrapper);
	var ContentHeight = (window.navigator.appName != "Microsoft Internet Explorer") ? SkinProvider.GetContentScrollerHeight() : 0;
	if ( ContentHeight == 0 )
		ContentHeight = Scroller.offsetHeight;

	var TabBarPadding = 20;

	TabBarContent.style.height = ContentHeight - Top - TabBarPadding + ToolbarHeight - FooterHeight + 'px';
	//TabBarContent.style.width = TabBarContent.style.width - 25 + 'px';
}

if ( window.navigator.appName == "Netscape" )
	window.addEventListener( "resize", UpdateTabbarSize, false);
else
	window.attachEvent( "onresize", UpdateTabbarSize );

Event.observe(window, 'load', function(event){ UpdateTabbarSize() });