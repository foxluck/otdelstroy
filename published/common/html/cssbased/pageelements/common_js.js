window.CustomSplitterHeightHandler = function (splitterHeight)
{

	if ( SplitterInfo.LeftPanelVisible )
	{
		var FoldersPanel = document.getElementById( 'FoldersHeadersPanel' );

		var Content = document.getElementById( 'SplitterLeftScrollableContent' );

		var TotalHeight = splitterHeight - FoldersPanel.offsetHeight;

		var OffsetHeight = Content.offsetHeight;

		SplitterInfo.LeftPanelContent.style.height = TotalHeight + 'px';
	}

	var RightPanelHeader = document.getElementById( 'RightPanelHeader' );
	var ListHeader = document.getElementById( 'ListHeaderContainer' );
	var ListFooter = document.getElementById( 'ListFooterContainer' );

	var ListHeaderHeight = 0;
	if ( ListHeader )
		ListHeaderHeight = ListHeader.offsetHeight;

	var ListFooterHeight = 0;
	if ( ListFooter )
	{
		ListFooterHeight = ListFooter.offsetHeight;
		ListFooter.style.visibility = 'visible';
	}

	rphHeight = (RightPanelHeader == null) ? 0 : RightPanelHeader.offsetHeight;
	SplitterInfo.RightPanelContent.style.height = splitterHeight - rphHeight - ListHeaderHeight - ListFooterHeight + 'px';
	
}


function checkMinRights( rights )
{
	thisForm = document.forms[0];

	for ( i = 0; i < thisForm.elements.length; i++ )
		if (thisForm.elements[i].type == 'checkbox')
			if ( thisForm.elements[i].name != "selectAllDocsCB" && thisForm.elements[i].checked ) {
				DL_ID = thisForm.elements[i].value;

				var rightsObj = tree_MM_findObj( "filerights["+DL_ID+"]" );
				if (!rightsObj)
					return false;

				if ( rightsObj.value < rights )
					return false;
			}

	return true;
}




function getFilesizeStr (fileSize) {
	fileSize = parseInt(fileSize);
	if ( !fileSize )
		return "0.00 KB";
		
	var res = "";
	if ( fileSize < 1024 )
		res = fileSize + " bytes";
	else if ( fileSize < 1024*1024 )
		res = Math.round(100*(Math.ceil(fileSize)/1024))/100 + " KB";
	else
		res = Math.round(100*Math.ceil(fileSize)/(1024*1024))/100 + " MB";
	return res;
}

function showPopupMessage()
{
	alert(CommonStrings.popupMessage);
}

function alertDelete()
{
	 alert( CommonStrings.app_treenoflddelrights_message );
	 return false;
}

function alertMove()
{
	alert( CommonStrings.app_treenomovetosubfldrights_message);
	return false;
}

function alertCopy()
{
	alert( CommonStrings.app_treenocopyfldrights_message);
	return false;
}

function alertAdd()
{
	alert(	CommonStrings.app_treenofldrights_message  );
	return false;
}

function alertAddRoot()
{
	alert( CommonStrings.app_treenorootrights_message);
	return false;
}

function alertModify()
{
	alert(CommonStrings.app_treeinvcurfldrights_message );
	return false;
}

function submitFolder( obj )
{
	selected = obj.selectedIndex;
	if ( obj.options[selected].value == -1 ) {
		return false;
	}

	obj.form.submit();
}

function tree_MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=tree_MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}