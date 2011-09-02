jQuery.fn.autocomplete = function(url, settings ) 
{
	return this.each( function()//do it for each matched element
	{
		var input = $(this);
		var list = $('<ul class="autocomplete"></ul>');
		$("body").append(list);
		list.css({top: input.offset().top + input.height(), left: input.offset().left, width: input.width()});
		var oldText = '';
		var typingTimeout;
		var size = 0;
		var selected = 0;
		var value = "";
		var is_load = true;

		settings = jQuery.extend({
			minChars : 1,
			limit: 10,
			timeout: 500,
			parameters : {'name' : 'text'},
			selection: "<b>$1</b>",
			hiddenField: false,
			insert: function (elem, text) {
				elem.val(text);
			}, 
			value: function (elem) {
				return elem.val();
			}
		}, settings);

		var data_json;
				
		var updateList = function(data)
		{
			data_json = data;
			selected = -1;
			var items = '';
			if (data) {
				size = data.length;
				if (!size) {
					list.hide();
				}
				for (var i in data)
				{
					if (typeof(data[i]) != 'string') continue;
					var value = $('<div></div>').html(data[i]).html();

					var v = settings.value(input);

					if (v.replace(/\s/g, '').length > 0) {
						value = value.replace(new RegExp("(" + v.replace(/\s/g, '|') + ")","gi"), settings.selection);
					}
					items += '<li value="' + i + '">' + value + '</li>';
				  list.html(items);

				  list.show().css({top: input.offset().top + input.outerHeight(), left: input.offset().left, width: input.width()}).children().
				  hover(function() { 
				  	$(this).addClass("selected").siblings().removeClass("selected");
				  }, function() { 
				  	$(this).removeClass("selected") 
				  }).
				  click(function () {
				  	settings.insert(input, $(this).text());
				  	if (settings.hiddenField) {
				  		$("#" + settings.hiddenField).val($(this).attr('value'))
				  	}
				  	input.focus();
			  		clear();
				  });
				  list.children(":first").addClass("selected");		
				  selected = 0;
				  if ($.browser.msie) {
				  	$("select:visible").hide().addClass("autoHide");
				  }

				}
			}
		} 
		
		function getData(text)
		{
			window.clearInterval(typingTimeout);
			if (text != oldText && (settings.minChars != null && text.length >= settings.minChars))
			{
				var parameters = {};
				parameters[settings.parameters.name] = text;
				oldText = text;
				$.getJSON(url, parameters, updateList);
				is_load = false;
			}
		}
		
		function clear()
		{
			list.html("");
			list.hide();
			if ($.browser.msie) {
				$("select.autoHide").show().removeClass("autoHide");
			}
			size = 0;
			selected = -1;
		}	
		
		input.keydown(function (e) {
			if(e.which == 13)//enter 
			{ 
				if (list.is(":hidden")) { 
					getData(settings.value(input));
				} else {
					settings.insert(input, list.children().eq(selected).text());
				  	if (settings.hiddenField) {
				  		$("#" + settings.hiddenField).val(list.children().eq(selected).attr('value'));
				  	}					
					clear();
				}
				e.preventDefault();
				return false;
			}
			else if (e.which == 9) {
				clear();
			}
			new_value = settings.value(input);
			if (new_value.indexOf(value) == -1) {
				is_load = true;
			}
			value = new_value;
		});
		input.keyup(function(e) 
		{
			if (e.which == 16) return;
			window.clearInterval(typingTimeout);
			//escape
			if(e.which == 27 || e.which == 9) {
				clear();
			} 
			else if (e.which == 13) {
				return false;	
			}
			//move up, down
			else if(e.which == 40 || e.which == 38) {
			  switch(e.which) {
				case 40: 
				  selected = selected >= size - 1 ? 0 : selected + 1; break;
				case 38:
				  selected = selected <= 0 ? size - 1 : selected - 1; break;
				default: break;
			  }
			  list.children().removeClass('selected').eq(selected).addClass('selected').text();	        
			} else { 
				if (settings.value(input).length == 0) {
					clear();
				}
				if (!is_load) {
					is_load = settings.value(input).length > 1 && settings.value(input).substr(-2, 1) == ' ';
				}
				if (is_load || e.which == 46 || e.which == 8 || list.children().length == 0 || list.children().length >= 10) {
					typingTimeout = window.setTimeout(function() { getData(settings.value(input)) },settings.timeout);
				}
				if (data_json == undefined) {
					return false;
				}
				for (var i in data_json) {
					if (typeof(data_json[i]) != "string") continue;
					var words = settings.value(input).toLowerCase().split(' ');
					var del = false;
					for (var k = 0; k < words.length; k++) {
						del = del || (data_json[i].toLowerCase().indexOf(words[k]) == -1);
					}
					if (del) {
						delete data_json[i];
						i--;
					}
				}
				updateList(data_json);
			}
		});
	});
};
