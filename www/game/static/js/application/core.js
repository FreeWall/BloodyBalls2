var Core = {};

Core.view = null;

Core.init = function(){
	Game.init();
	Core.setView(View.LOADING);

	Game.client.init(function(){
		Core.setView(View.LOGIN);
		LoginView.init(function(){
			Core.setView(View.ROOMS);
			RoomList.update();
		});
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
		Game.setView(View.GAME_LOBBY);
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
	//Core.init();

	Core.setView(View.GAME);
	Game.setView(View.GAME_CANVAS);
});