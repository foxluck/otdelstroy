function prddet_clickThumbnail(){
	var prev_thumb = getElementByClass('current_prd_thumbnail', getLayer('current_prd_thumbnail'));
	if(prev_thumb)prev_thumb.className = 'prd_thumbnail';
	this.className = 'current_prd_thumbnail';
	var enlargedPict = getLayer('current_picture');
	if(enlargedPict){
		enlargedPict.href = this.getAttribute('img_enlarged');
		enlargedPict.setAttribute('allow_enlarge', !this.getAttribute('img_enlarged')?'0':'1');
		getLayer('img-enlarge-link').style.display = this.getAttribute('img_enlarged')?'block':'none';
		enlargedPict.setAttribute('img_width', this.getAttribute('img_width'));
		enlargedPict.setAttribute('img_height', this.getAttribute('img_height'));
		getLayer('img-current_picture').src = this.getAttribute('img_picture');
	}
}

Behaviour.register({
	'a.prd_thumbnail': function(e){
		e.onclick = prddet_clickThumbnail;
	},
	'a.current_prd_thumbnail': function(e){
		e.onclick = prddet_clickThumbnail;
	}
});

var enlargedPict = getLayer('current_picture');
if(enlargedPict)enlargedPict.onclick = function(){
	if(this.getAttribute('allow_enlarge')!='0')open_window(this.href, this.getAttribute('img_width'), this.getAttribute('img_height'));
	return false;
}