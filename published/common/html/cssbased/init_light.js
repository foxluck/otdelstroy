function InitLayout()
{
	for ( i = 0; i < OnLoadFunctions.length; i++ ) {
		var callback = OnLoadFunctions[i];
		callback();
	}
}

Event.observe(window, 'load', function(event){ InitLayout() });