var Controls = {};

Controls.updating = false;

Controls.update = function(){
	Controls.updating = true;
	$("label.checker input[type=checkbox]").trigger("change");
	$("label.switcher input[type=checkbox]").trigger("change");
	$("label.radiorer input[type=radio]").trigger("change");
	Controls.updating = false;
};

$(function(){

	$(document).on("change","label.checker input[type=checkbox]",function(){
		$(this).parent("label").toggleClass("on",$(this).is(":checked")).toggleClass("disabled",$(this).is(":disabled"));
	});

	$(document).on("change","label.switcher input[type=checkbox]",function(){
		$(this).parent("label").toggleClass("on",$(this).is(":checked")).toggleClass("disabled",$(this).is(":disabled"));
		if(!Controls.updating && $(this).attr("data-action") !== undefined){
			$(this).parent("label").toggleClass("loading",true);
			var action = $(this).attr("data-action");
			var id = $(this).val();
			var _this = this;
			var url = $(this).attr("data-url");
			if(url === undefined) url = "";
			$.post(url,{action:action,id:id}).done(function(data){
				$(_this).parent("label").toggleClass("loading",false).toggleClass("on",!!data);
			});
		}
	});

	$(document).on("change","label.radiorer input[type=radio]",function(){
		var name = $(this).attr("name").replace(/(:|\.|\[|\]|,|=)/g,"\\$1");
		if($(this).is(":checked")){
			$("label.radiorer input[type=radio][name="+name+"]").each(function(){
				$(this).parent("label").toggleClass("on",false);
			});
		}
		if(!Controls.updating && $(this).attr("data-action") !== undefined){
			$(this).parent("label").toggleClass("loading",true);
			var action = $(this).attr("data-action");
			var id = $(this).val();
			var _this = this;
			var url = $(this).attr("data-url");
			if(url === undefined) url = "";
			$.post(url,{action:action,id:id}).done(function(data){
				if(!!data){
					$("label.radiorer input[type=radio][name="+name+"]").each(function(){
						$(this).parent("label").toggleClass("on",false);
					});
				}
				$(_this).parent("label").toggleClass("loading",false).toggleClass("on",!!data);
			});
		}
		$(this).parent("label").toggleClass("on",$(this).is(":checked")).toggleClass("disabled",$(this).is(":disabled"));
	});

	$(document).on("focus","input.error",function(){
		$(this).toggleClass("error",false);
	});

	Controls.update();
});