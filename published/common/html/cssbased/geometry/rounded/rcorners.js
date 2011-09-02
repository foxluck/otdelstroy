function startRCorners () {
	//RegisterOnLoad (function(){Nifty('a.Button','transparent');});
	
	RegisterOnLoad (function(){Nifty('dd.Tab','transparent top');});
	RegisterOnLoad (function(){Nifty('table.VerticaslTabForm dd.Tab','transparent left');});
	RegisterOnLoad (function(){Nifty('div.MenuBlock','transparent');});	
	//RegisterOnLoad (function(){Nifty('div.SplitterPanelHeader','transparent top');});	
	RegisterOnLoad (function(){Nifty('span.MessageBlock','transparent');});	
	
	document.rcorners = true;
	
	if (Ext.isSafari) {
		DoRCorners ();
	}
	
}

function DoRCorners () {
	Nifty('a.Button','transparent');
}