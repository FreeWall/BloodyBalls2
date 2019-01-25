var Core = {};

Core.DEBUG = false;

Core.view = null;

Core.client = new GameClient();

Core.init = function(){
	Core.setView(View.LOADING);

	Core.client.init(function(){
		Core.setView(View.LOGIN);
		LoginView.init(function(){
			Core.setView(View.ROOMS);
			RoomList.update();
			if(Core.DEBUG){
				RoomList.create(Session.getName()+"'s room",null,10,function(){});
			}
		});
		if(Core.DEBUG){
			$("#nickinput").trigger(jQuery.Event("keydown",{which:13}));
		}
	},function(){
		Core.error("Could not connect to server");
	});
};

Core.setView = function(view){
	$("[data-body-view]").attr("data-body-view",view);
	$("[data-view]").fadeOut(0);
	$("[data-view="+view+"]").fadeIn((Core.view == view ? 0 : 200));
	Core.view = view;

	if(view == View.LOGIN){
		setTimeout(function(){
			$("#nickinput").focus();
		},100);
	}
	else if(view == View.GAME){
		Core.client.setView(View.GAME_LOBBY);
	}
};

Core.error = function(error){
	Core.setView(View.ERROR);
	$("[data-js=error]").html(error);
};

Core.api = function(path,args,callback){
	args = args || null;
	callback = callback || null;
	$.post("/api/"+Session.getId()+"/"+path,args).done(function(data){
		if(callback && typeof(callback) === "function") callback(data);
	});
};

$(function(){
	$("div.wrapper").show();

	let url = new URL(window.location.href);
	if(url.searchParams.get("game") != null) Core.DEBUG = true;

	Core.init();
});