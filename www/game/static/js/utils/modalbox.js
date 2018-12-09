var ModalBox = function(_options){

	let _this = this;
	_this.callback = null;
	_this.content = null;
	_this.opened = false;
	_this.close = true;
	_this.body = "";

	_this.options = {
		close: true,
		title: "",
		width: 0,
		responsive: false,
		buttons: []
	};

	_this.buttonOptions = {
		name: "",
		color: "blue",
		close: false,
		form: null,
		confirm: null
	};

	this.options = jQuery.extend({},this.options,_options || {});
	if(this.options.buttons.length > 0){
		for(var button in this.options.buttons){
			this.options.buttons[button] = jQuery.extend({},this.buttonOptions,this.options.buttons[button] || {});
		}
	}

	this.content = $("\
	<div class='modalbox'>\
		<div class='shadow'></div>\
		<div class='box'>\
			<div class='head'></div>\
			<div class='close'><i class='fa fa-close'></i></div>\
			<div class='wrap-body'>\
				<div class='body'></div>\
			</div>\
			<div class='foot'></div>\
		</div>\
	</div>");
	$(this.content).appendTo("body");
	$("div.body",this.content).css("max-height",$(window).height()-160);

	this.show = function(){
		this.opened = true;
		if(!this.options.close) $("div.close",this.content).remove();

		if(Validators.isEmpty(this.options.title)) $("div.head",this.content).remove();
		else $("div.head",this.content).html(this.options.title);

		if(this.options.buttons.length == 0) $("div.foot",this.content).remove();
		else {
			$("div.foot",this.content).html("");
			$("div.foot",this.content).append("<div class='loader'></div>");
			for(var button in this.options.buttons){
				button = this.options.buttons[button];
				$("div.foot",this.content).append("<div class='button "+button.color+"' "+(button.close ? "data-modalbox-close='true'" : "")+">"+button.name+"</div>");
			}
		}

		$("body").toggleClass("modalbox-blured",true);
		$(this.content).toggleClass("show",true);
		this.resize();
		setTimeout(function(){
			$(_this.content).toggleClass("smooth",true);
		},100);
		//Controls.update();
		$("input[autofocus]",_this.content).focus();
		return this;
	};

	this.hide = function(){
		this.opened = false;
		$("body").toggleClass("modalbox-blured",false);
		$(this.content).toggleClass("show",false).toggleClass("smooth",false);
		return this;
	};

	this.loading = function(loading){
		$("div.foot div.loader",this.content).toggleClass("show",loading);
	};

	this.setClose = function(close){
		this.close = close;
	};

	this.getContent = function(){
		return this.content;
	};

	this.setBody = function(body){
		this.body = body;
		$("div.body",this.content).html(this.body);
		if(this.opened) this.show();
		return this;
	};

	this.onSubmit = function(callback){
		this.callback = callback;
		return this;
	};

	this.submit = function(data){
		if(this.callback && typeof(this.callback) === "function"){
			this.callback(data);
		}
		return this;
	};

	this.setWidth = function(width){
		_this.options.width = width;
		this.resize();
	};

	this.resize = function(){
		var width = _this.options.width;
		if($(window).width()-20 < width) width = $(window).width()-20;
		$("div.body",_this.content).css((_this.options.responsive ? "max-width" : "width"),_this.options.width);
		$("div.body",_this.content).css("max-height",$(window).height()-160);
		$("div.box",_this.content).width(width);
		$("div.box",_this.content).css("margin-left",-$("div.box",_this.content).width()/2);
		$("div.box",_this.content).css("margin-top",-$("div.box",_this.content).height()/2);
	};

	$("div.close, div.shadow",_this.content).click(function(){
		if(_this.close) _this.hide();
	});

	$(_this.content).on("click","div.button[data-modalbox-close=true]",function(){
		if(_this.close) _this.hide();
	});

	$(_this.content).on("click","div.button:not([data-modalbox-close=true])",function(){
		var buttonClicked = null;
		for(var button in _this.options.buttons){
			button = _this.options.buttons[button];
			if(button.name == $(this).text()) buttonClicked = button;
		}
		if(buttonClicked != null){
			if(buttonClicked.confirm == null || confirm(buttonClicked.confirm)){
				_this.submit({
					button: buttonClicked
				});
				if(buttonClicked.form != null){
					if(buttonClicked.form.startsWith("#")) $("form"+buttonClicked.form,_this.content).trigger("submit");
					else $("form[name="+buttonClicked.form+"]",_this.content).trigger("submit");
				}
			}
		}
	});

	$(window).resize(function(){
		_this.resize();
	});

	$(document).keydown(function (event){
		if(event.which == 27 && _this.close) _this.hide();
	});
};