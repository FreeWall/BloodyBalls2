var Core = {};

Core.session = new Session();
Core.client = new GameClient();
Core.server = new GameServer();

Core.init = function(){
	Game.init();
	Core.setView(View.LOADING);

	Core.client.init(function(){
		Core.setView(View.LOGIN);
		LoginView.init(function(){
			Core.setView(View.ROOMS);
			RoomList.update();
		});
	});
};

Core.setView = function(view){
	Core.view = view;
	$("[data-view]").fadeOut(0);
	$("[data-view="+view+"]").fadeIn(200);

	if(view == View.LOGIN){
		$("#nickinput").focus();
	}
};

Core.error = function(error){
	alert(error);
};

Core.api = function(path,args,callback){
	args = args || null;
	callback = callback || null;
	$.post("/api/"+Core.session.getId()+"/"+path,args).done(function(data){
		if(callback && typeof(callback) === "function") callback(data);
	});
};

$(function(){
	//Core.setView(View.GAME);
	Core.init();
});