function confirmDelete(text,url)
{
	temp = window.confirm(text);
	if (temp) //delete
	{
		window.location=url;
	}
}

function open_window(link,w,h)
{
	var win = "width="+w+",height="+h+",menubar=no,location=no,resizable=yes,scrollbars=yes";
	wishWin = window.open(link,'wishWin',win);
}
function position_this_window()
{
	var x = (screen.availWidth - 795) / 2;
	window.resizeTo(795, screen.availHeight - 50);
	window.moveTo(Math.floor(x),25);
}