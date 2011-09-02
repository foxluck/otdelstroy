(function($) {
	
	$.fn.wbspopup = function(settings) {
		settings = $.extend({
			width		: 500,
            height		: 400,
            backgroundColor: '#000000',
            opacity		: 0.5,
            iframe		: false,
            close		: ''
		},settings);
		
		var onOpen = settings.onOpen || function() {};
        var onOpenComplite = settings.onOpenComplite || function() {};
        var onCancel = settings.onCancel || function() {};
        var onSend = settings.onSend || function() {};
        var onSuccess = settings.onSuccess || function() {};
        var onClose = settings.onClose || function() {};
        
        var $target = this;	
        
        $(window).keydown(function(e){		 
	    	e = e || window.event;
	    	if (e.keyCode == 27) {
				$target.wbspopupClose();
	    	}
		});
		
		$target.wbspopupClose = function (param){			
			if (!param || param.close == true) {
				_close($(this));
				onClose.apply(this, [param]);
			}
			else {
				onClose.apply(this);
			}
		};
		
		function _close($popup) {
			$popup.children('iframe').remove();
			$('#popup_iframe').remove();
			$('#wbs-popup-bg').remove();
			$popup.hide();
		};
		
		window.closePopup = function(param){
			if (param)
				this.wbspopupClose(param);
			else
				this.wbspopupClose();
		}.bind($target);
        
		var $background = $("<div id='wbs-popup-bg'></div>");
    	$('body').append($background);
    	$background.css({
    		opacity: settings.opacity,
    		'z-index': 500,
    		position: 'absolute',
    		top: 0,
    		left: 0,
			height: '100%',
			width: '100%',
			backgroundColor: settings.backgroundColor
    	});

    	if (settings.height == 'auto' || !$.browser.msie) {
    		var top = '50%';
    	} else {
    		var top = ($(window).height() - settings.height) / (2 * $(window).height());
    		top = 100 * top + '%';
    	}
    	$target.css({
        		marginLeft: '-' + parseInt((settings.width / 2),10) + 'px', 
        		width: settings.width + 'px',
        		position: 'absolute',
        		display: 'block',
        		height: settings.height,
        		width : settings.width,
        		left: '50%',
        		top: top,
        		'z-index': 502        		
        });
        if ( !jQuery.browser.msie && settings.height != 'auto') { // take away IE6
			$target.css({
				marginTop: '-' + parseInt((settings.height / 2), 10) + 'px'
			});
		}
        	
		if (settings.url) {
			if ( settings.iframe ) {
				$target.children('.content').empty();
				var $iframe = $('<iframe id="popup_iframe" src="'+settings.url+'" style="width:100%; height:100%;"/>')
					.appendTo($target.find('.content').show());
					
				var isSubmit = false;
				$iframe.load(function(){
					$('#progress').hide();
					$iframe.contents().find('form').submit(function(){
						$target.children('.content').hide();
						$('#progress').show();
						isSubmit = true;
						return true;
					});
					if (isSubmit) {					
						var response = $iframe.contents().find('body').html();
						if ( response.length > 0 ) 
						try {
							var response = eval("(" + response + ")");
						} catch (e) {}
						onSuccess.apply(this, [response]);
						if (response && response.close_popup && response.close_popup == 1) {
							setTimeout(function(){
								$target.wbspopupClose();
							}, 10);
						}
						else {
							$target.find('.content').show();
						}
						isSubmit = false;
					}
					else {
						onOpenComplite.apply(this);
					}
				});
			}
			else {
				
			}
		}
		else {
			
		}
		
		/**
		 / THIRD FUNCTION
		 * getPageSize() by quirksmode.com
		 *
		 * @return Array Return an array with page width, height and window width, height
		 */
		function ___getPageSize() {
			var xScroll, yScroll;
			if (window.innerHeight && window.scrollMaxY) {	
				xScroll = window.innerWidth + window.scrollMaxX;
				yScroll = window.innerHeight + window.scrollMaxY;
			} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
				xScroll = document.body.scrollWidth;
				yScroll = document.body.scrollHeight;
			} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
				xScroll = document.body.offsetWidth;
				yScroll = document.body.offsetHeight;
			}
			var windowWidth, windowHeight;
			if (self.innerHeight) {	// all except Explorer
				if(document.documentElement.clientWidth){
					windowWidth = document.documentElement.clientWidth; 
				} else {
					windowWidth = self.innerWidth;
				}
				windowHeight = self.innerHeight;
			} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
				windowWidth = document.documentElement.clientWidth;
				windowHeight = document.documentElement.clientHeight;
			} else if (document.body) { // other Explorers
				windowWidth = document.body.clientWidth;
				windowHeight = document.body.clientHeight;
			}	
			// for small pages with total height less then height of the viewport
			if(yScroll < windowHeight){
				pageHeight = windowHeight;
			} else { 
				pageHeight = yScroll;
			}
			// for small pages with total width less then width of the viewport
			if(xScroll < windowWidth){	
				pageWidth = xScroll;		
			} else {
				pageWidth = windowWidth;
			}
			arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight);
			return arrayPageSize;
		};
		/**
		 / THIRD FUNCTION
		 * getPageScroll() by quirksmode.com
		 *
		 * @return Array Return an array with x,y page scroll values.
		 */
		function _getPageScroll() {
			var xScroll, yScroll;
			if (self.pageYOffset) {
				yScroll = self.pageYOffset;
				xScroll = self.pageXOffset;
			} else if (document.documentElement && document.documentElement.scrollTop) {	 // Explorer 6 Strict
				yScroll = document.documentElement.scrollTop;
				xScroll = document.documentElement.scrollLeft;
			} else if (document.body) {// all other Explorers
				yScroll = document.body.scrollTop;
				xScroll = document.body.scrollLeft;	
			}
			arrayPageScroll = new Array(xScroll,yScroll);
			return arrayPageScroll;
		};
	};
	
})(jQuery);