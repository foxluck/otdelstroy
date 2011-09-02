function MM_showHideLayers() { //v9.0
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  var lastV = "none";
  for (i=0; i<(args.length-2); i+=3) {
	  with (document) {
	  	if (getElementById && ((obj=getElementById(args[i]))!=null)) { 
	  		v=args[i+2];
	    	if (obj.style) { 
	    		obj=obj.style; 
	    		v=(v=='show')?'block':(v=='hide')?'none':v; 
	    	}
	    	obj.display=v; 
	    	lastV = v;
	    }
	  }
  }
  
  if (lastV == "none") {
  	LayoutManager.SetComboBoxesVisibility (true);
  } else {
  	LayoutManager.SetComboBoxesVisibility (false);
  }
}