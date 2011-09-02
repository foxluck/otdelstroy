function getDocumentSize()
{
	if (document.clientHeight != null)
		return {height: document.clientHeight, width: document.clientHeight};
	
	if ( typeof(document.documentElement.clientHeight) != 'undefined' && document.documentElement.clientHeight > 0 )
		return {height: document.documentElement.clientHeight, width: document.documentElement.clientWidth};

	if ( typeof(document.body.clientHeight) != 'undefined' )
		return {height: document.body.clientHeight, width: document.body.clientWidth};
	

	return {height: 0, width: 0};
}

function addEmptyImg(elem) {
	var img = createElem("img");
	img.src = "../../common/html/res/images/s.gif";
	img.style.width = "1px";
	img.style.height = "1px";	
	elem.appendChild(img);
}