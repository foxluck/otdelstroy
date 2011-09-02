window._dmManager = {
	"focus": null
};

function _DropMenu(_Settings){

	this.X = -555;
	this.Y = -555;

	if(!_Settings['id'])_Settings['id'] = _Settings['pid']+'_ddmenu';
	if(!_Settings['show'])_Settings['show'] = "onclick";
	if(!_Settings['PointStyle'])_Settings['PointStyle'] = "menu_point_li";
	if(!_Settings['PointOverStyle'])_Settings['PointOverStyle'] = "menu_point_over_li";
	if(!_Settings['StyleClass'])_Settings['StyleClass'] = "menu_ul";
	if(!_Settings['PointsDivider'])_Settings['PointsDivider'] = "menu_divider_li";
	if(!_Settings['PointsDividerOver'])_Settings['PointsDividerOver'] = "menu_divider_over_li";
	
	this.Id = _Settings['id']
	this.PId = _Settings['pid']
	this.PointStyle = _Settings['PointStyle']
	this.PointOverStyle = _Settings['PointOverStyle']
	this.Settings = _Settings
	this.Points = new Array()
	this.Delay = 1000;
	this.TimoutCounter = 0
	this.current_point_index = null
	
	if(getLayer(this.Id)){
		
		var oldMenu = getLayer(this.Id);
		deleteTag(oldMenu);
	}
	
	if(this.Settings['GroupID']){
		
		if(!window.DropMenuActiveInGroup){
			
			window.DropMenuActiveInGroup = {};
		}
	}
	
	this.ParentObj = getLayer(_Settings['pid'])

	this.ParentObj.DropMenuID = this;
	this.ParentObj.style.cursor = 'default';
	if(_Settings['show']=='onclick'){
		
		this.ParentObj.onclick = function(){
			
			this.DropMenuID.display(true)
		}
	}

	if(!window.dropMenuCollection){
		
		window.dropMenuCollection = new Array(0)
	}
	window.dropMenuCollection[this.Id] = this
	
	/*
		���������� ������ ����
			_title 		- ������������ ��� ������
			_href		- ������ ��� �������� ��� ����� �� ������
	*/
	this.addPoint = function(_Point){
		
		var NewInd = this.Points.length;
		this.Points[NewInd] = _Point;
		return NewInd;
	}
	
	this.addDivider = function(){
		
		var NewInd = this.Points.length
		this.Points[NewInd] = {'divider':1};
	}
	
	this.setPointAttribute = function(_PointInd, _AttributeName, _AttributeValue){
		
		this.Points[_PointInd][_AttributeName] = _AttributeValue
	}
	
	/*
		�������������� ����, �.�. ������� ���
	*/
	this.init = function(){
		
		var obj = document.createElement('ul')
		document.body.appendChild(obj)
		obj.id = this.Id
		obj.className = this.Settings['StyleClass'];
		obj.style.position = "absolute"
		obj.style.visibility = "hidden"
		obj.style.top = '-1000px';
		obj.style.left = '-1000px';
		obj.style.cursor = 'default'
		
		if(this.PId){
			
			var coordO = getLayer(this.PId);
			if(coordO){
				
				if(this.Settings['position']){
					;
				}else{
					var pos = getAbsolutePos(coordO);
					this.setX(pos.x);
					this.setY(pos.y+coordO.offsetHeight);
				}
				
				if(this.Settings['offsetX']){
					this.setX(this.X+this.Settings['offsetX']);
				}
				
				if(this.Settings['offsetY']){
					this.setY(this.Y+this.Settings['offsetY']);
				}
			}
		}
		
		obj.DropMenuID = this
		obj.onmouseover = function(){
			
			this.DropMenuID.display(true)
		}
		obj.onmouseout = function(){
			
			this.DropMenuID.display(false);
//			this.DropMenuID.delayedHide();
		}

		for(i=0; i<this.Points.length; i++){
			
			if(this.Points[i]['divider'])continue;
			var point = document.createElement('li')
			this.Points[i]['li_elem'] = point;
			obj.appendChild(point)
			point.setAttribute('redirect', this.Points[i].href);
			point.setAttribute('_dropMenu', this);
			point.setAttribute('_point_index', i);
			point.innerHTML = '<a href="'+this.Points[i].href+'">'+this.Points[i].title+'</a>';
			if(this.Points[i].onclick){
				
				point.setAttribute('pointonclick', this.Points[i].onclick);
			}
			
			if( this.Points[i-1] && this.Points[i-1]['divider']){
				
				var point_style = this.Settings['PointsDivider'];
				var pointover_style = this.Settings['PointsDividerOver'];
			}else{
				
				var point_style = this.PointStyle;
				var pointover_style = this.PointOverStyle;
			}
			
			point.setAttribute('pointstyle', point_style);
			point.setAttribute('pointoverstyle', pointover_style);

			point.onclick = function(){
				
				if(this.getAttribute('pointonclick')){
					
					var v_return = true;
					var _dropMenu = window._dmManager.focus;
					eval(this.getAttribute('pointonclick'))
					_dropMenu = null;
					if(!v_return)return false;
				}
				document.location.href = this.getAttribute('redirect');
				return false;
			}
			point.onmouseover = function(){
				
				if(window._dmManager.focus)
					window._dmManager.focus.selectPoint(this.getAttribute('_point_index'));
			}
			point.onmouseout = function(){
				
				if(window._dmManager.focus)
					window._dmManager.focus.deselectPoint(this.getAttribute('_point_index'));
			}
			point.className = point.getAttribute('pointstyle')
		}
	}
	
	this.selectPoint = function (point_index){
		
		if(!this.Points[point_index])return;
		this.current_point_index = point_index
		this.Points[point_index]['li_elem'].className = this.Points[point_index]['li_elem'].getAttribute('pointoverstyle');
	}
	
	this.deselectPoint = function (point_index){
		
		if(this.current_point_index == point_index)this.current_point_index = null;
		if(this.Points[point_index])
		this.Points[point_index]['li_elem'].className = this.Points[point_index]['li_elem'].getAttribute('pointstyle');
	}

	this.display = function(_state){
		
		var obj = getLayer(this.Id);
		
		if(!obj){
			this.init();
			obj = getLayer(this.Id);
		}
		
		obj.style.left = this.X+'px';
		obj.style.top = this.Y+'px';

		if(_state){
			
			_dmManager.focus = this;
			this.TimoutCounter++
			obj.style.visibility = 'visible';
			if(this.Settings['GroupID']){
				if(window.DropMenuActiveInGroup[this.Settings['GroupID']]){
					
					if(window.DropMenuActiveInGroup[this.Settings['GroupID']].Id != this.Id)
						window.DropMenuActiveInGroup[this.Settings['GroupID']].display(false);
				}
				window.DropMenuActiveInGroup[this.Settings['GroupID']] = this
			}
		}else{
			
//			if(_dmManager.focus && _dmManager.focus.Id == this.Id)_dmManager.focus = null;
			obj.style.visibility = 'hidden';
		}
	}
	
	this.setX = function (_X){
		
		this.X = _X;
	}
		
	this.setY = function (_Y){
		
		this.Y = _Y;
	}
	
	this.onKey = function (key){
		
		switch (key)
		{
			case 0x26://up
				var select_index;
				var deselect_index = null;
				if(is_null(this.current_point_index)){
					select_index = this.Points.length-1;
				}else if(this.current_point_index == 0){
					select_index = this.Points.length-1;
					deselect_index = this.current_point_index;
				}else{
					select_index = this.current_point_index-1;
					deselect_index = this.current_point_index;					
				}
				this.selectPoint(select_index);
				this.deselectPoint(deselect_index);
				return false;
			case 0x28://down
				var select_index;
				var deselect_index = null;
				if(is_null(this.current_point_index)){
					select_index = 0;
				}else if(this.current_point_index == this.Points.length-1){
					select_index = 0;
					deselect_index = this.current_point_index;
				}else{
					select_index = this.current_point_index+1;
					deselect_index = this.current_point_index;					
				}
				this.selectPoint(select_index);
				this.deselectPoint(deselect_index);
				return false;
			case 0x1B://exit
				this.display(false);
				return false;
			case 0xD://enter
				if(is_null(this.current_point_index))return true;
				
				this.Points[this.current_point_index]['li_elem'].onclick();
				return false;
		}
	}
}