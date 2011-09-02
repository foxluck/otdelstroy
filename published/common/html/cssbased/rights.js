function showHideRightsBlock (appName, input) {
	if (input.className == "GroupValue")
		return;
	var table = document.getElementById("TestId");
	var trs = table.getElementsByTagName("tr");
	var searched = false;
	for (j = 0; j < trs.length; j++) {
		tr = trs[j];
		if (tr.id == "AppTitle_" + appName) {
			searched = true;
			continue;
		}
		if (searched) {
			if (tr.id != null && tr.id.substring(0,8) == "AppTitle")
				return;
			tr.id = (tr.id == "hiddenTr") ? "blockTr" : "hiddenTr";
		}
	}
	j = 0;
}

function initHideRightsBlocks () {
	var table = document.getElementById("TestId");
	var trs = table.getElementsByTagName("tr");
	for (i = 0; i < trs.length; i++) {
		tr = trs[i];
		if (tr.id != null && tr.id.substring(0,9) == "AppTitle_")
		{
			inputs = tr.getElementsByTagName("input");
			if (inputs.length > 0 && (inputs[0].checked == false))
				showHideRightsBlock(tr.id.substring(9,11), inputs[0]);
		}
	}	
	i = 0;		
}

function checkRightsBlocks () {
	var table = document.getElementById("TestId");
	if (table == null)
		return;
	var trs = table.getElementsByTagName("tr");
	var currentAppSelected = -1;
	for (j = 0; j < trs.length; j++) {
		tr = trs[j];
		if (tr.id != null && tr.id.substring(0,8) == "AppTitle") {
			inputs = tr.getElementsByTagName("input");
			if (inputs.length < 1) {
				currentAppSelected = -1;
				continue;
			}
			currentAppSelected = (inputs[0].checked || inputs[0].className == "GroupValue") ? 1 : 0;
		} else {
			if (currentAppSelected != 0)
				continue;
			inputs = tr.getElementsByTagName("input");
			for (z = 0; z < inputs.length; z++) 
				inputs[z].checked = false;
		}
	}
	j = 0;
	z = 0;
}