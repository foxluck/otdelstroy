SkinProvider = new SkinProviderClass();

function InitLayout()
{
	SkinProviderClass.prototype.InitLayout()
	Event.observe(window, 'resize', function(event){SkinProviderClass.prototype.UpdateLayoutSize()});

	for ( i = 0; i < OnLoadFunctions.length; i++ ) {
		var callback = OnLoadFunctions[i];
		callback();
	}

	LayoutManager.FixToolbarItemsWidth();
	
	var screenMode = LayoutManager.ReadScreenModeCookie();
	if (screenMode != null && !inplaceScreen && !directAccess)
		LayoutManager.SetScreenMode (screenMode);
	
	var img = new Image ();
	var img2 = new Image ();
	var img3 = new Image ();
	var img4 = new Image ();
	var img31 = new Image ();
	var img32 = new Image ();

	//img.src = "../../../common/html/res/ext/resources/images/default/basic-dialog/hd-sprite.gif";
	//img2.src = "../../../common/html/res/ext/resources/images/default/basic-dialog/btn-sprite.gif";
	//img3.src = "../../../common/html/res/ext/resources/images/default/gradient-bg.gif";
	
	img.src = "../../../common/html/res/ext/resources/images/slate/window/top-bottom.png";
	img2.src = "../../../common/html/res/ext/resources/images/slate/window/left-right.png";
	img3.src = "../../../common/html/res/ext/resources/images/slate/button/btn-sprite.gif";
	img31.src = "../../../common/html/res/ext/resources/images/slate/window/right-corners.png";
	img32.src = "../../../common/html/res/ext/resources/images/slate/window/left-corners.png";
	
	img4.src = "../../../common/html/cssbased/themes/loading.gif"; 
}

Event.observe(window, 'load', function(event){ InitLayout() });