// iMouseDown represents the current mouse button state: up or down
/*
lMouseState represents the previous mouse button state so that we can
check for button clicks and button releases:

if(iMouseDown && !lMouseState) // button just clicked!
if(!iMouseDown && lMouseState) // button just released!
*/
var mouseOffset = null;
var iMouseDown  = false;
var lMouseState = false;
var dragObject  = null;

var DragDrops   = [];
var DragDropsList = [];
var curTarget   = null;
var lastTarget  = null;
var dragHelper  = null;
var tempDiv     = null;
var rootParent  = null;
var rootSibling = null;

Number.prototype.NaN0=function(){return isNaN(this)?0:this;}

function attachEventEx(target, event, func)
{
        if (target.attachEvent)
                target.attachEvent("on" + event, func);
        else if (target.addEventListener)
                target.addEventListener(event, func, false );
        else
                target["on" + event] = func;
}

function CreateDragList( dragList )
{
        /*
        Create a new "Container Instance" so that items from one "Set" can not
        be dragged into items from another "Set"
        */
        var cDrag        = DragDrops.length;

        DragDrops[cDrag] = [];

        /*
        Each item passed to this function should be a "container".  Store each
        of these items in our current container
        */
        for(var i=1,len=arguments.length; i<len; i++){
			
			addToDragList(dragList, arguments[i]);
        }

        DragDropsList[cDrag] = dragList || null;

//        attachEventEx(document,"mousedown", mouseDown);
        document.onmousedown = mouseDown;

}

function addToDragList(dragList, containerElem){
	
	var cDrag = DragDrops.length-1;
    var cObj = containerElem;
	
    DragDrops[cDrag].push(cObj);
    cObj.setAttribute('DropObj', cDrag);

    /*
    Every top level item in these containers should be draggable.  Do this
    by setting the DragObj attribute on each item and then later checking
    this attribute in the mouseMove function
    */
    for(var j=0; j<cObj.childNodes.length; j++)
    {
            // Firefox puts in lots of #text nodes...skip these
            if(cObj.childNodes[j].nodeName=='#text')
                    continue;

            cObj.childNodes[j].setAttribute('DragObj', cDrag);

            if ( dragList != null )
            {
            	
                    node = cObj.childNodes[j];
                    dragList.addItem( node.id, node.getAttribute( "listItemIndex" ), node.getAttribute( "listClickHandler" ) || null, node.getAttribute( "listDoubleClickHandler" ) || null, node.getAttribute( "listObjIdentifier" ) );
            }
			makeShield(cObj.childNodes[j]);
    }
}

function mouseMove(ev)
{
	try {
        ev = ev || window.event;

        /*
	        We are setting target to whatever item the mouse is currently on
        	Firefox uses event.target here, MSIE uses event.srcElement
        */

        var target   = ev.target || ev.srcElement;
        var mousePos = mouseCoords(ev);

        // mouseOut event - fires if the item the mouse is on has changed
        if(lastTarget && (target!==lastTarget)){
                // reset the classname for the target element
                var origClass = lastTarget.getAttribute('origClass');
                if(origClass) lastTarget.className = origClass;
        }

        /*
        dragObj is the grouping our item is in (set from the createDragContainer function).
        if the item is not in a grouping we ignore it since it can't be dragged with this
        script.
        */
		var dragObj = target.getAttribute('DragObj');

        // if the user is just starting to drag the element
        if(iMouseDown && !lMouseState)
        {
	       if ( typeof(dragObj) == "string" && dragObj == "" )
			dragObj = null;

                if ( dragObj == null )
                {
                        var node = target.parentNode;
					if(!node.getAttribute)return;
                       dragObj = node.getAttribute('DragObj');
	               if ( typeof(dragObj)=="string" && dragObj == ""  )
	                   dragObj = null;

                        while( node != document )
                        {
	  		   dragObj = node.getAttribute('DragObj');

	                   if ( typeof(dragObj)=="string" && dragObj == ""  )
         	                   dragObj = null;

                            if ( dragObj != null )
                               break;

                            node = node.parentNode;
                        }

                        if ( dragObj != null )
                                target = node;

                }

                 // if the mouse was moved over an element that is draggable
                if( dragObj != null )
                {

                        var listObj = DragDropsList[dragObj];

                        // mouseDown target
                        curTarget = target;

                        if ( listObj && target )
	                  listObj.selectByObj( target );

                        // Record the mouse x and y offset for the element
                        rootParent    = curTarget.parentNode;
                        rootSibling   = curTarget.nextSibling;

                        mouseOffset   = getMouseOffset(target, ev);

                        // We remove anything that is in our dragHelper DIV so we can put a new item in it.
                        for(var i=0; i<dragHelper.childNodes.length; i++)
                                dragHelper.removeChild(dragHelper.childNodes[i]);

                        // Make a copy of the current item and put it in our drag helper.
                        var cloneCurTarget = curTarget.cloneNode(true);
                        dragHelper.appendChild(cloneCurTarget);
                        dragHelper.style.display = 'block';
                        dragHelper.style.zIndex = '1000';

                        makeShield(cloneCurTarget);

                        // set the class on our helper DIV if necessary
                        var dragClass = curTarget.getAttribute('dragClass');

                        if (dragClass)
                        {
                                dragHelper.firstChild.className = dragClass;
                        }

                        // disable dragging from our helper DIV (it's already being dragged)
                        dragHelper.firstChild.removeAttribute('DragObj');

                        /*
                        Record the current position of all drag/drop targets related
                        to the element.  We do this here so that we do not have to do
                        it on the general mouse move event which fires when the mouse
                        moves even 1 pixel.  If we don't do this here the script
                        would run much slower.
                        */
                        var dragConts = DragDrops[dragObj];

                        /*
                        first record the width/height of our drag item.  Then hide it since
                        it is going to (potentially) be moved out of its parent.
                        */
                        curTarget.setAttribute('startWidth',  parseInt(curTarget.offsetWidth));
                        curTarget.setAttribute('startHeight', parseInt(curTarget.offsetHeight));
                        curTarget.style.display  = 'none';
                        /*
                        Hide at all
                        */
                        document.body.appendChild(curTarget);

                        // loop through each possible drop container
                        for(var i=0; i<dragConts.length; i++)
                        {
                                with(dragConts[i])
                                {
                                        var pos = getPosition(dragConts[i]);

                                        /*
                                        save the width, height and position of each container.

                                        Even though we are saving the width and height of each
                                        container back to the container this is much faster because
                                        we are saving the number and do not have to run through
                                        any calculations again.  Also, offsetHeight and offsetWidth
                                        are both fairly slow.  You would never normally notice any
                                        performance hit from these two functions but our code is
                                        going to be running hundreds of times each second so every
                                        little bit helps!

                                        Note that the biggest performance gain here, by far, comes
                                        from not having to run through the getPosition function
                                        hundreds of times.
                                        */
                                        setAttribute('startWidth',  parseInt(offsetWidth));
                                        setAttribute('startHeight', parseInt(offsetHeight));
                                        setAttribute('startLeft',   pos.x);
                                        setAttribute('startTop',    pos.y);
                                }

                                // loop through each child element of each container
                                for(var j=0; j<dragConts[i].childNodes.length; j++){
                                        with(dragConts[i].childNodes[j]){
                                                if((nodeName=='#text') || (dragConts[i].childNodes[j]==curTarget)) continue;

                                                var pos = getPosition(dragConts[i].childNodes[j]);

                                                // save the width, height and position of each element
                                                setAttribute('startWidth',  parseInt(offsetWidth));
                                                setAttribute('startHeight', parseInt(offsetHeight));
                                                setAttribute('startLeft',   pos.x);
                                                setAttribute('startTop',    pos.y);
                                        }
                                }
                        }
                }
        }

        // If we get in here we are dragging something
        if( curTarget )
        {
                // move our helper div to wherever the mouse is (adjusted by mouseOffset)
                //                dragHelper.style.top  = mousePos.y - mouseOffset.y;
                //                dragHelper.style.left = mousePos.x - mouseOffset.x;

                dragHelper.style.top  = mousePos.y+"px";
                dragHelper.style.left = mousePos.x+"px";

                var dragConts  = DragDrops[curTarget.getAttribute('DragObj')];
                var activeCont = null;

                var xPos = mousePos.x;
                var yPos = mousePos.y;
//
//                var xPos = mousePos.x - mouseOffset.x + (parseInt(curTarget.getAttribute('startWidth')) /2);
//                var yPos = mousePos.y - mouseOffset.y + (parseInt(curTarget.getAttribute('startHeight'))/2);

                // check each drop container to see if our target object is "inside" the container

                for(var i=0; i<dragConts.length; i++)
                {
                        with(dragConts[i])
                        {
                                if (
                                        (parseInt(getAttribute('startLeft')) < xPos) &&
                                        (parseInt(getAttribute('startTop')) < yPos) &&
                                        ((parseInt(getAttribute('startLeft')) + parseInt(getAttribute('startWidth')))  > xPos) &&
                                        ((parseInt(getAttribute('startTop'))  + parseInt(getAttribute('startHeight'))) > yPos)
                                   )
                                {

                                                /*
                                                our target is inside of our container so save the container into
                                                the activeCont variable and then exit the loop since we no longer
                                                need to check the rest of the containers
                                                */
                                                activeCont = dragConts[i];

                                                // exit the for loop
                                                break;
                                }
                        }
                }

                // Our target object is in one of our containers.  Check to see where our div belongs
                if( activeCont )
                {
                		var dropHackObj = cpt_getDropHackObject(curTarget);
                        // beforeNode will hold the first node AFTER where our div belongs
                        var beforeNode = null;

                        // loop through each child node (skipping text nodes).
                        for(var i=activeCont.childNodes.length-1; i>=0; i--)
                        {
                                with(activeCont.childNodes[i])
                                {
                                        if(nodeName=='#text') continue;

                                                // if the current item is "After" the item being dragged
                                                if(
                                                        curTarget != activeCont.childNodes[i] &&
                                                        ((parseInt(getAttribute('startLeft')) + parseInt(getAttribute('startWidth')))  > xPos) &&
//                                                        ((parseInt(getAttribute('startTop'))  + parseInt(getAttribute('startHeight'))) > yPos)
                                                        ((parseInt(getAttribute('startTop'))  + parseInt(getAttribute('startHeight')/2)) > yPos)
                                                )
                                                {
                                                        beforeNode = activeCont.childNodes[i];
                                                }
                                }
                        }

                        // the item being dragged belongs before another item
                        if(beforeNode){
                                if(beforeNode!=curTarget.nextSibling){
//                                        activeCont.insertBefore(curTarget, beforeNode);
                                        activeCont.insertBefore(dropHackObj, beforeNode);
                                }

                        // the item being dragged belongs at the end of the current container
                        } else {
                                if((curTarget.nextSibling) || (curTarget.parentNode!=activeCont)){
//                                        activeCont.appendChild(curTarget);
                                        activeCont.appendChild(dropHackObj);
                                }
                        }

                                // the timeout is here because the container doesn't "immediately" resize
                                setTimeout(function(){
                                var contPos = getPosition(activeCont);
                                activeCont.setAttribute('startWidth',  parseInt(activeCont.offsetWidth));
                                activeCont.setAttribute('startHeight', parseInt(activeCont.offsetHeight));
                                activeCont.setAttribute('startLeft',   contPos.x);
                                activeCont.setAttribute('startTop',    contPos.y);}, 5);

//						makeShield(curTarget);
                        // make our drag item visible
//                        if(curTarget.style.display!='')
//                        {
 //                               curTarget.style.display  = '';

//                                curTarget.style.borderStyle = "dashed";
//                                curTarget.style.borderColor = "a0a0a0";
//                        }
                }
                else
                {
                        // our drag item is not in a container, so hide it.
                        if(curTarget.style.display!='none')
                        {
                                // curTarget.style.borderStyle = "solid";
                                // curTarget.style.borderColor = "white";
                        }
                }
        }

        // track the current mouse state so we can compare against it next time
        lMouseState = iMouseDown;

        // mouseMove target
        lastTarget  = target;

        // track the current mouse state so we can compare against it next time
        lMouseState = iMouseDown;

        // this helps prevent items on the page from being highlighted while dragging
        return false;
} catch ( e ) {;} finally {;}
}

function mouseUp(ev)
{
try{
        if( curTarget != null )
        {
                // hide our helper object - it is no longer needed
                dragHelper.style.display = 'none';

                var dragObj = curTarget.getAttribute('DragObj');
                if ( typeof(dragObj) == "string" && dragObj == "" )
                         dragObj = null;
        	       for(var i=0; i<dragHelper.childNodes.length; i++)
                   dragHelper.removeChild(dragHelper.childNodes[i]);

                if ( dragObj != null )
                {
                    var listObj = DragDropsList[dragObj];

                    listObj.selectByObj(curTarget);

                    var cnts = DragDrops[dragObj];

                    var prev = curTarget.previousSibling;
                    while( prev != null && prev.nodeName=='#text' )
			prev = prev.previousSibling;

                    if ( prev != null )
	              for ( var k in cnts )
         	      {
                           if ( prev.id == cnts[k].id ){
						   	
								/* Comment because IE generate error
								parent = null;
								*/
								break;
                           }
                       }

                    listObj.placeAfter( prev == null ? null : prev.id, curTarget.id );

                }
                else{
                	
//				curTarget.style.borderStyle = "solid";
//				curTarget.style.borderColor = "white";
                }

                // if the drag item is invisible put it back where it was before moving it
                if(curTarget.style.display == 'none')
                {
                        if(rootSibling)
                        {
                                rootParent.insertBefore(curTarget, rootSibling);
                        }
                        else
                        {
                                rootParent.appendChild(curTarget);
                        }
                }

                // make sure the drag item is visible
                curTarget.style.display = '';
                var objDropHack = cpt_getDropHackObject(null);
                objDropHack.parentNode.replaceChild(curTarget,objDropHack);
                makeShield(curTarget);
				onDropElement(curTarget, rootParent);
                checkTrash();
        }
        curTarget  = null;
        iMouseDown = false;

        for(var i=0; i<dragHelper.childNodes.length; i++)
           dragHelper.removeChild(dragHelper.childNodes[i]);
} catch ( e ) {;} finally {;}
}

function mouseDown(ev)
{
        iMouseDown = true;
/*
        if(lastTarget)
        {
                return false;
        }
        */
}

function mouseCoords(ev)
{
        if( ev.pageX || ev.pageY )
        {
                return {x:ev.pageX, y:ev.pageY};
        }

        return { x:ev.clientX + document.body.scrollLeft - document.body.clientLeft, y:ev.clientY + document.body.scrollTop  - document.body.clientTop };
}

function getPosition(e)
{
        var left = 0;
        var top  = 0;

        while (e.offsetParent)
        {
                left += e.offsetLeft + (e.currentStyle?(parseInt(e.currentStyle.borderLeftWidth)).NaN0():0);
                top  += e.offsetTop  + (e.currentStyle?(parseInt(e.currentStyle.borderTopWidth)).NaN0():0);
                e     = e.offsetParent;
        }

        left += e.offsetLeft + (e.currentStyle?(parseInt(e.currentStyle.borderLeftWidth)).NaN0():0);
        top  += e.offsetTop  + (e.currentStyle?(parseInt(e.currentStyle.borderTopWidth)).NaN0():0);

        return {x:left, y:top};
}

function getMouseOffset(target, ev)
{
        ev = ev || window.event;

        var docPos    = getPosition(target);
        var mousePos  = mouseCoords(ev);
        return {x:mousePos.x - docPos.x, y:mousePos.y - docPos.y};
}

function getElementsByClass(searchClass,node,tag) {
	
  var classElements = new Array();
  if ( node == null )
          node = document;
  if ( tag == null )
         tag = '*';
  var els = node.getElementsByTagName(tag);
  var elsLen = els.length;

  var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
  for (var i = 0, j = 0; i < elsLen; i++) {
         if ( pattern.test(els[i].className) ) {
              classElements[j] = els[i];
             j++;
        }
  }
  
  return classElements;
}

function removeShield(elem){
	
	var shields = getElementsByClass("cpt_shield", elem, "div");
	for(var i=0,len=shields.length;i<len;i++){
		elem.removeChild(shields[i]);
		shields[i] = null;
	}
}

function makeShield(elem){
//return;
	if(!elem)return;
	removeShield(elem);
	var p_offsetWidth = elem.offsetWidth;
	var p_offsetHeight = elem.offsetHeight;

	var shieldElem = createTag('div',elem);
	shieldElem.className = "cpt_shield";
	shieldElem.style.position = "relative";
	shieldElem.style.zIndex = "99";
	shieldElem.style.marginTop = "-"+p_offsetHeight+"px";
	shieldElem.style.width = p_offsetWidth;
	shieldElem.style.height = p_offsetHeight;
	
	shieldElem.onmouseover = function(ev){
		var obj = getLayer('dnd-dblckick-tooltip');
		var _ev = getEventObject(ev);
		var m_coords = mouseCoords(_ev.ev);
		
		obj.style.left = m_coords.x+10;
		obj.style.top = m_coords.y+10;
		obj.style.display = curTarget?'none':'block';
	};
	shieldElem.onmousemove = function(ev){
		var obj = getLayer('dnd-dblckick-tooltip');
		var _ev = getEventObject(ev);
		var m_coords = mouseCoords(_ev.ev);
		
		obj.style.left = m_coords.x+10;
		obj.style.top = m_coords.y+10;
		obj.style.display = curTarget?'none':'block';
	};
	shieldElem.onmouseout = function(){
		var obj = getLayer('dnd-dblckick-tooltip');
		obj.style.display = "none";
	};
}

function onDropElement(){
	
}

function cpt_getDropHackObject(curTarget){

   	var myItm = document.getElementById('cpt-dropHackObject');
//   	curTarget.style.display = 'none';
   	if(!myItm){
    	var myItm = document.createElement('div');
	   	myItm.id = 'cpt-dropHackObject';
   	}
   	myItm.className = 'cpt_wrapper drophackobject';
   	return myItm;
}

//attachEventEx(document,"mousedown", mouseDown);
//attachEventEx(document,"mousemove", mouseMove);
//attachEventEx(document,"mouseup", mouseUp);
