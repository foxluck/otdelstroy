/* Copyright (c) 2005 Tim Taylor Consulting (see LICENSE.txt) */

ToolMan._dragsortTableFactory = {
	makeSortable : function(item) {
		
		var handles = [];
		var divs = item.getElementsByTagName('td')
		for (var i = 0, n = divs.length; i < n; i++) {
			if(divs[i].className.search(/handle/)!=-1){
				handles.push(divs[i]);
			}
		}
		
		var group = ToolMan.drag().createSimpleGroup(item, handles[0])
		for(var i=handles.length-1; i>0; i--){
		
			group.setHandle(handles[i]);
		}

		group.register('dragstart', this._onDragStart)
		group.register('dragstart', this.onDragStart)
		group.register('dragmove', this._onDragMove)
		group.register('dragend', this._onDragEnd)
		group.register('dragend', this.onDragEnd)

		return group
	},

	/** 
	 * Iterates over a list's items, making them sortable, applying
	 * optional functions to each item.
	 *
	 * example: makeListSortable(myList, myFunc1, myFunc2, ... , myFuncN)
	 */
	makeListSortable : function(list) {
		var helpers = ToolMan.helpers()
		var coordinates = ToolMan.coordinates()
		var items = list.getElementsByTagName("tbody")

		helpers.map(items, function(item) {
			
			var dragGroup = dragsort.makeSortable(item)
			dragGroup.setThreshold(4)
			var min, max
			dragGroup.addTransform(function(coordinate, dragEvent) {
				return coordinate.constrainTo(min, max)
			})
			dragGroup.register('dragstart', function() {
				var items = list.getElementsByTagName("tbody")
				min = max = coordinates.topLeftOffset(items[0])
				for (var i = 1, n = items.length; i < n; i++) {
					var offset = coordinates.topLeftOffset(items[i])
					min = min.min(offset)
					max = max.max(offset)
				}
			})
		})
		for (var i = 1, n = arguments.length; i < n; i++)
			helpers.map(items, arguments[i])
	},

	_onDragStart : function(dragEvent) {
	},

	_onDragMove : function(dragEvent) {
		var helpers = ToolMan.helpers()
		var coordinates = ToolMan.coordinates()

		var item = dragEvent.group.element
		var xmouse = dragEvent.transformedMouseOffset
		var moveTo = null

		var previous = helpers.previousItem(item, item.nodeName)
		while (previous != null) {
			var bottomRight = coordinates.bottomRightOffset(previous)
			if (xmouse.y+3 <= bottomRight.y && xmouse.x+3 <= bottomRight.x) {
				moveTo = previous
			}
			previous = helpers.previousItem(previous, item.nodeName)
		}
		if (moveTo != null) {
			helpers.moveBefore(item, moveTo)
			return
		}
		
		var next = helpers.nextItem(item, item.nodeName)
		
		while (next != null) {
			var topLeft = coordinates.topLeftOffset(next)
			if (topLeft.y+3 <= xmouse.y && topLeft.x+3 <= xmouse.x) {
				moveTo = next
			}
			next = helpers.nextItem(next, item.nodeName)
		}
		if (moveTo != null) {
//			helpers.moveBefore(item, helpers.nextItem(moveTo, item.nodeName))
			helpers.moveAfter(moveTo, item)
			return
		}
	},

	_onDragEnd : function(dragEvent) {
		ToolMan.coordinates().create(0, 0).reposition(dragEvent.group.element)
	},
	
	onDragStart : function(dragEvent){
	},
	
	onDragEnd : function(){
	}
	
}
