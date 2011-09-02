
/* Options (in any order): 
 
 start (number|string) 
    Index number of default tab. ex: $(...).idTabs(0) 
    String of id of default tab. ex: $(...).idTabs("tab1") 
    default: class "selected" or index 0 
    Passing null will force it to not select a default tab 
  
 click (function) 
    Function will be called when a tab is clicked. ex: $(...).idTabs(foo) 
    If the function returns true, idTabs will show/hide content (as usual). 
    If the function returns false, idTabs will not take any action. 
    The function is passed four variables: 
      The id of the element to be shown 
      an array of all id's that can be shown 
      the element containing the tabs 
      and the current settings 
 
 selected (string) 
    Class to use for selected. ex: $(...).idTabs(".current") 
    default: ".selected" 
*/ 

(function($){
	var settings = {
			start:'',
		    click:null, 
		    selected:".active_tabs" 
	};
	
	$.fn.idTabs = function(option){
		settings = $.extend(settings, option);
		
		if(settings.selected.charAt(0)=='.') settings.selected = settings.selected.substr(1); 
		
		return this.each(function(){
			if( settings.start.length > 0 ) {
				$('a[href='+settings.start+']').parent().addClass(settings.selected);
				$(this).children('div').not(settings.start).hide();
			}
			
			var $ul = $('ul', this);			
			$('a', $ul).click(function (){ $.tabsSelect(this); return false; });
		});
	};
	
	$.tabsSelect = function(tab){
		$tab = $(tab);		
		if ($tab.parent().is('.' + settings.selected) ) return false;
		
		$tab.parent().parent().children('li').removeClass(settings.selected);
		$tab.parent().addClass(settings.selected);
		
		$target = $( $tab.attr('href') );
		$target.nextAll('div').hide();
		$target.prevAll('div').hide();
		$target.show();
		
		return false;
	}; 
})(jQuery); 
