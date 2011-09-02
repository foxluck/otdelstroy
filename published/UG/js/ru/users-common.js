function moveSelect(from, to, btn_id, disable) {
	jQuery("#" + from + " option:selected").each(function () {
		jQuery(this).appendTo("#" + to).attr("selected", "");
	});
	if(disable) {
		if(!jQuery("#" + from).html()) {
			jQuery("#" + btn_id).attr('disabled', true);
		} else {
			jQuery("#" + btn_id).attr('disabled', false);
		}
	}
}

function showMultiSelect(data1, data2, btn_id, disable) {
	var str1 = '';
	for (var i = 0; i < data1[1].length; i++) {
		var f = data1[1][i];
		var style = (f[0] == 0) ? ' style="font-weight:bold"' : '';
		str1 += '<option value="' + f[0] + '"' + style + '>' + f[1] + '</option>';
	}
	jQuery("#" + data1[0]).append(str1).dblclick(function () {
		if(typeof(btn_id) == "undefined" || !jQuery("#" + btn_id).hasClass("disable")) {
			moveSelect(data1[0], data2[0], btn_id, disable);
		}
	});
	var str2 = '';
	for (i = 0; i < data2[1].length; i++) {
		f = data2[1][i];
		str2 += '<option value="' + f[0] + '">' + f[1] + '</option>';
	}
	jQuery("#" + data2[0]).append(str2).dblclick(function () {
		moveSelect(data2[0], data1[0], btn_id, disable);
	});	
}