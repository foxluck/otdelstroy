function vcl_insertVar( varName, targetID )
{
	var obj = document.getElementById(targetID);
	if (!obj)
		return;
	if (document.selection) {
		obj.focus();
		sel = document.selection.createRange();
		sel.text = varName;
	} else 
		if (obj.selectionStart || obj.selectionStart == 0) {
			var startPos = obj.selectionStart;
			var endPos = obj.selectionEnd;
			obj.value = obj.value.substring(0, startPos) + varName + obj.value.substring(endPos, obj.value.length);
		}
}