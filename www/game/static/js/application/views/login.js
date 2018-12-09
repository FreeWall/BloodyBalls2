var LoginView = {};

LoginView.initialized = false;

LoginView.init = function(callback){
	var name = Core.session.restoreName();
	if(name) $("#nickinput").val(name);
	$("#nickinput").keydown(function(event){
		if(event.which == 13 && !LoginView.initialized){
			if(Core.view == View.LOGIN){
				var name = $(this).val();
				if(Core.session.isNameValid(name)){
					LoginView.initialized = true;
					$(this).prop("disabled",true);
					$(this).next("div.loader").toggleClass("show",true);
					Core.api("user",{action:"init",host:Core.client.socket.id,name:name},function(data){
						Core.session.init(data);
						if(callback && typeof(callback) === "function") callback(data);
					});
				} else {
					$("#nickinput").toggleClass("error",true);
				}
			}
		}
	});
};