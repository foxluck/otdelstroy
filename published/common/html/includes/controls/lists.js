//
// Customizable list with selectable items
//

function CustomSelectableList()
{
	this.name = 'cslist';
	this.items = new Array();
	this.itemIdentifiers = new Array();
	this.clickHandlers = new Array();
	this.dblClickHandlers = new Array();
	this.instanceName = null;
	this.obj = "CSList";
	this.lastId = null;

	this.selectedObj = null;
	
	if (this.obj)
		eval(this.obj + "=this");

	this.unselectedColor = null;
	this.selectedColor = null;

	this.attachEventEx = function(target, event, func) 
	{
		if (target.attachEvent)
			target.attachEvent("on" + event, func);
		else if (target.addEventListener)
			target.addEventListener(event, func, false);
		else
			target["on" + event] = func;
	}

	this.addItem = function(item, itemIndex, clickHandler, doubleClickHandler, objIdentifier) 
	{
		this.items.push(item);
		this.clickHandlers.push(clickHandler);
		this.dblClickHandlers.push(doubleClickHandler);
		this.itemIdentifiers.push(objIdentifier);

		var obj = document.getElementById(item)
		if (obj) {
			this.attachEventEx(obj, "click", new Function(this.obj + ".click('" + item + "');"));
			this.attachEventEx(obj, "dblclick", new Function(this.obj + ".dblclick('" + item + "');"));
		}
	}

	this.selectFirst = function()
	{
		if (this.items.length)
			this.click(this.items[0]);
	}

	this.selectById = function( id )
	{
		if (id.length)
			this.click(id);
		else
			this.selectFirst();
	}

	this.selectByObj = function( obj )
	{
		if ( this.selectedObj != null )
			this.selectedObj.style.backgroundColor = this.unselectedColor;

		if (obj)
			obj.style.backgroundColor = this.selectedColor;

		this.selectedObj = obj;
	}

	this._getItemIndex = function(id)
	{
		return id.substr( this.instanceName.length+String("item").length );
	}

	this.click = function(id)
	{
		var itemObj = document.getElementById(id);
		this.selectByObj( itemObj );

		var index = this._getItemIndex(id);

		if (this.clickHandlers[index] && this.lastId != index) {
			eval(this.clickHandlers[index]);
		}

		this.lastId = index;
	}

	this.dblclick = function(id)
	{
		var itemObj = document.getElementById(id);

		var index = this._getItemIndex(id);
		if (this.dblClickHandlers[index]) {
			eval(this.dblClickHandlers[index]);
		}
	}

	this.getCurrentData = function()
	{
		if (this.lastId == null)
			return null;

		return this.itemIdentifiers[this.lastId];
	}
}