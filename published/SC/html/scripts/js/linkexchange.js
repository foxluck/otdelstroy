getLayer('show-add-category').onclick = function(){
	getLayer('add-category-block').style.display = "block";
	this.style.display = 'none';
	return false;
};
getLayer('cancel-add-category').onclick = function(){
	getLayer('add-category-block').style.display = "none";
	getLayer('show-add-category').style.display = '';
	return false;
};
if(getLayer('edit-category'))getLayer('edit-category').onclick = function(){
	getLayer('rename-category-block').style.display = 'block';
	getLayer('links-block').style.display = 'none';
}
getLayer('cancel-save-category').onclick = function(){
	getLayer('rename-category-block').style.display = 'none';
	getLayer('links-block').style.display = 'block';
}
if(getLayer('add-link'))getLayer('add-link').onclick = function(){
	getLayer('links-block').style.display = 'none';
	getLayer('form_new_le_link').style.display = 'block';
}
if(getLayer('cancel-add-link'))getLayer('cancel-add-link').onclick = function(){
	getLayer('form_new_le_link').style.display = 'none';
	getLayer('links-block').style.display = 'block';
}

Behaviour.register({
	'.do_action': function(e){
		e.onclick = function(){
			
			var check_boxes = getElementsByClass('select_links');
			if(!check_boxes.length){
				alert(translate.le_no_links_selected);
				return false;
			}
			var some_checked = false;
			for(var k_max=check_boxes.length-1; k_max>=0; k_max--){
				if(!check_boxes[k_max].checked)continue;
				some_checked = true;
				break;
			}
			if(!some_checked){
				alert(translate.le_no_links_selected);
				return false;
			}
			
			if(this.getAttribute('title') && !window.confirm(this.getAttribute('title')))return false;
				
			getLayer('form_change_links').elements['action'].value = this.getAttribute('rel');
			getLayer('form_change_links').submit();
			return false;
		}
	}
});