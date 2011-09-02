//
// Customizable list with selectable items
//

function DragList()
{
        this.items = new Array();

        this.instanceName = null;

        this.lastId = null;

        eval(this.obj + "=this");

        this.unselectedColor = null;
        this.selectedColor = null;
        this.selectedBorderColor = null;

        this.attachEventEx = function(target, event, func)
        {
                if (target.attachEvent)
                        target.attachEvent("on" + event, func);
                else if (target.addEventListener)
                        target.addEventListener(event, func, false);
                else
                        target["on" + event] = func;
        }


        this._createClickHandler = function( obj, item )
        {
    	   return function( ev )
        	   {
		var o = obj;
		o.click( ev, item );
            }
        }

        this._createDblClickHandler = function( obj, item )
        {
            return function( ev )
            {
                 var o = obj;
                 o.dblclick( ev, item );
            }
        }

        this.addItem = function(item, itemIndex, clickHandler, dblClickHandler, objIdentifier)
        {
                var sitem = new Object();

                if ( !item )
			return; 

                sitem.id = item;
                sitem.itemIndex = itemIndex;
                sitem.objIdentifier = objIdentifier;
                sitem.clickHandler = clickHandler;
                sitem.dblClickHandler = dblClickHandler;
                sitem.selected = false;

                var obj = document.getElementById(item);

                if (obj) {
		       			sitem.click = this._createClickHandler( this, item );
                        this.attachEventEx(obj, "click", sitem.click );

                        sitem.dblclick = this._createDblClickHandler( this, item );
                        this.attachEventEx(obj, "dblclick", sitem.dblclick );
                }
                this.items.push(sitem);
        }

        this.selectFirst = function()
        {
                if (this.items.length)
                        this.click( this.items[0].id );
        }

        this.selectById = function( id )
        {
                if (id.length)
                        this.click(id);
                else
                        this.selectFirst();
        }


        this._getItemIndex = function(id)
        {
	    for (var k = 0; k < this.items.length; k++ )
               if ( this.items[k].id == id )
                 return k;

            return null;
        }

        this.unselectAll = function( )
        {
            var itemObj;

            for (var k = 0; k < this.items.length; k++ )
            {
	      if ( this.items[k].selected )
               {

                 itemObj = document.getElementById(this.items[k].id);
                 this._unselectObject( itemObj );
                 this.items[k].selected = false;
               }
            }

            return null;
        }

        this.unselectItem = function( id )
        {
            var k = this._getItemIndex( id );

            if ( k == null )
              return null;

            if ( this.items[k].selected )
            {
               itemObj = document.getElementById(this.items[k].id);
               this._unselectObject( itemObj );
               this.items[k].selected = false;
               return k;
            }
            else
                return k;

            return null;
        }

        this._unselectObject = function( obj )
        {
        	/*
            obj.style.backgroundColor = this.unselectedColor;
            obj.style.borderStyle = "none";
            obj.style.borderStyle = "solid";
            obj.style.borderColor = this.unselectedColor;
            obj.style.backgroundColor = this.unselectedColor;
            obj.style.borderWidth = "2px";
            */
        }

        this.selectAll = function( )
        {
            var itemObj;

            for (var k = 0; k < this.items.length; k++ )
            {
               if ( !this.items[k].selected )
               {

                  itemObj = document.getElementById(this.items[k].id);
                  this._selectObject( itemObj );
                  this.items[k].selected = true;
               }
            }

            return null;
        }

        this.selectItem = function( id )
        {
            var k = this._getItemIndex( id );

            if ( k == null )
              return null;

            if ( !this.items[k].selected )
            {
               itemObj = document.getElementById(this.items[k].id);
               this._selectObject( itemObj );
               this.items[k].selected = true;
               return k;
            }
            else
                return k;

            return null;
        }

        this._selectObject = function( obj )
        {/*
               obj.style.borderStyle = "solid";
               obj.style.borderColor = this.selectedBorderColor;
               obj.style.backgroundColor = this.selectedColor;
               obj.style.borderWidth = "2px";
               */
        }

        this.selectByObj = function( obj )
        {
            var itemObj;

            this.unselectAll();

            for (var k = 0; k < this.items.length; k++ )
            {
                  if ( obj.id != this.items[k].id )
                    continue;

                  this._selectObject( obj );
                  this.items[k].selected = true;

                  return k;
            }

            return null;
        }


        this.click = function(ev,id)
        {
                var ev = ev || window.event;
                var ctrl = ev.altKey || false;

                if ( !ctrl )
                {
		    this.unselectAll();
               	    var index = this.selectItem( id );
                }
                else
                {
	            var index = this._getItemIndex( id );

	            if ( index == null )
              		return true;

                     var itemObj = document.getElementById(id);

           	    if ( !this.items[index].selected )
	            {
	              this._selectObject( itemObj );
         	      this.items[index].selected = true;
	              return null;
 	            }
                     else
                     {
                       this._unselectObject( itemObj );
                       this.items[index].selected = false;
                       return null;
                     }
                }

                if ( index == null )
                  return;

                if ( !ctrl && this.items[index].clickHandler && this.lastId != index)
                {
                        eval(this.items[index].clickHandler);
                        this.lastId = index;
                }

        }

        this.dblclick = function(ev,id)
        {
                this.unselectAll();
                var index = this.selectItem( id );

                if ( index == null )
                  return;

                if ( this.items[index].dblClickHandler )
                {
                        eval(this.items[index].dblClickHandler);
                        this.lastId = index;
                }
        }

        this.getCurrentData = function()
        {
	   var retarr = new Array();

            for (var k = 0; k < this.items.length; k++ )
               if ( this.items[k].selected )
                  retarr.push(this.items[k].objIdentifier);

            return retarr;
        }

        this.getCurrentData = function()
        {
            var retarr = new Array();

            for (var k = 0; k < this.items.length; k++ )
               if ( this.items[k].selected )
                  retarr.push(this.items[k].objIdentifier);

            return retarr;
        }

        this.getCurrentSortOrder = function()
        {
            var retarr = new Array();

            for (var k = 0; k < this.items.length; k++ )
                  retarr.push(this.items[k].objIdentifier);

            return retarr;
        }

        this.getCurrentSortOrderString = function()
        {
            var retStr = "";
            var flag = false;

            for (var k = 0; k < this.items.length; k++ )
            {
		if ( flag )
                    retStr = retStr+",";
		retStr = retStr+this.items[k].objIdentifier;
		flag=true;
            }

            return retStr;
        }

        this.placeAfter = function( root, id )
        {
            var newPos = -1;
            var oldPos = this._getItemIndex( id );

            if ( oldPos == null )
               return null;

            if ( root != null )
            {
	      newPos = this._getItemIndex( root );

	      if ( newPos == null )
                  return null;

	      if ( newPos > this.items.length-1 )
               	 newPos = this.items.length-1;
            }

            if ( oldPos == newPos || ( oldPos == 0 && newPos == -1 ) )
            	return null;

            var newItems = new Array();

            if ( newPos == -1 )
		newItems.push(this.items[oldPos]);

            for (var k = 0; k < this.items.length; k++ )
	    {
               if ( k == oldPos )
                  continue;

               newItems.push(this.items[k]);

               if ( k == newPos )
               {
                  newItems.push(this.items[oldPos]);
                  continue;
               }

            }

            this.items = newItems;
            return true;
        }

}