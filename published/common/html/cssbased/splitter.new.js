var mainLayout;
var innerLayout;
var northInitSize = 0;
var footerInitSize = 0;
Splitter = function(){
	return {
		init : function(){
		   	northInitSize = $("RightPanelHeader").offsetHeight;
		   	footerInitSize= $("ListFooterContainer").offsetHeight;
	
		   	mainLayout = new Ext.BorderLayout("PageSplitter", {
		        west: {split:true, initialSize: 200, titlebar: true, collapsible: true, minSize: 100, maxSize: 400},
		        center: {autoScroll: false}
		    });
		    mainLayout.beginUpdate();
		    
		    mainLayout.add('west', new Ext.ContentPanel('SplitterLeftPanel', {title: "Folders", closable:false}));
		    innerLayout = new Ext.BorderLayout('SplitterRightPanelWrapper', {north: {initialSize: northInitSize}, center: {autoScroll: true}, south: {initialSize: footerInitSize, background: false}});
		    //innerLayout.add('south', new Ext.ContentPanel('inner1', "More Information"));
		    innerLayout.add('center', new Ext.ContentPanel('SplitterContentPanel2', {autoScroll: true}));
		    if($("RightPanelHeader") != null)
		    	innerLayout.add('north', new Ext.ContentPanel('RightPanelHeader'));
		    if($("ListFooterContainer") != null)
		    	innerLayout.add('south', new Ext.ContentPanel('ListFooterContainer'));
		    
		    mainLayout.add('center', new Ext.NestedLayoutPanel(innerLayout, {}));
		    mainLayout.endUpdate();
		    
		    Splitter.updateSize();
			Event.observe(window, 'resize', Splitter.updateSize);
		},
			
		updateSize: function () {
			var splitterObj = $('SplitterWrapper');
			var contentScrollerObj = $('ContentScroller');
			splitterObj.style.height = contentScrollerObj.offsetHeight + "px";
			splitterObj.style.width = contentScrollerObj.offsetWidth + "px";
			mainLayout.layout();
			window.setTimeout("mainLayout.layout()", 100);
		},
			
		reloadRight: function () {
			mainLayout.beginUpdate();
			innerLayout = new Ext.BorderLayout('SplitterRightPanelWrapper', {north:{initialSize: northInitSize}, center: {autoScroll: true}, south: {initialSize: footerInitSize}});
			innerLayout.beginUpdate();
			innerLayout.add('center', new Ext.ContentPanel('SplitterContentPanel2'));
			innerLayout.add('north', new Ext.ContentPanel('RightPanelHeader'));
			if($("ListFooterContainer") != null)
		    	innerLayout.add('south', new Ext.ContentPanel('ListFooterContainer'));
			innerLayout.endUpdate();
			mainLayout.add('center', new Ext.NestedLayoutPanel(innerLayout));
			mainLayout.endUpdate();
		}
	};
}();
RegisterOnLoad(Splitter.init);