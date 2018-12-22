var LoginView = {};

LoginView.initialized = false;

LoginView.init = function(callback){
	var name = Session.restoreName();
	if(name) $("#nickinput").val(name);
	$("#nickinput").keydown(function(event){
		if(event.which == 13 && !LoginView.initialized){
			if(Core.view == View.LOGIN){
				var name = $(this).val();
				if(Session.isNameValid(name)){
					LoginView.initialized = true;
					$(this).prop("disabled",true);
					$(this).next("div.loader").toggleClass("show",true);
					Core.api("user",{action:"init",id:Session.restoreId(),host:Game.client.socket.id,name:name},function(data){
						Session.init(data);
						if(callback && typeof(callback) === "function") callback(data);
					});
				} else {
					$("#nickinput").toggleClass("error",true);
				}
			}
		}
	});
};