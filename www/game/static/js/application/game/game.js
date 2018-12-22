var Game = {};

Game.IDS = 0;

Game.id = null;
Game.view = null;
Game.running = false;
Game.paused = false;

Game.client = new GameClient();
Game.server = null;
Game.serverSocket = new SocketServer();

Game.stats = new Stats();
Game.physics = new Physics();
Game.renderer = new Renderer();

Game.players = new Players();
Game.settings = new Settings();

Game.init = function(){
	$("#stats").html(Game.stats.dom);

	Game.physics.init();
	Game.renderer.init();

	Game.tick();
};

Game.join = function(data,host){
	host = host || false;
	Game.reset();
	Game.id = data.id;
	Session.host = host;
	Session.admin = host;
	$("[data-js=lobby-title]").text(data.name);
	Core.setView(View.GAME);
	Game.setView(View.GAME_LOBBY);
};

Game.leave = function(){
	Game.client.leave();
	if(Game.server != null){
		Game.server.terminate();
		Game.serverSocket.destroy();
	}
};

Game.setView = function(view){
	Game.view = view;
	if(Core.view == View.GAME){
		$("[data-game-view]").hide();
		$("[data-game-view="+view+"]").show();
	}
};

Game.pause = function(paused){
	Game.paused = paused;
};

Game.tick = function(){
	if(!Game.paused){
		Game.stats.begin();
		Game.physics.tick();
		Game.renderer.tick();
		Game.stats.end();
	}
	window.requestAnimationFrame(Game.tick);
};

Game.reset = function(){
	Game.running = false;
	Game.paused = false;
	Game.players = new Players();
	Game.settings = new Settings();
};

Game.createServer = function(callback){
	Game.serverSocket.init(function(id){
		Game.server = new Worker("/static/js/server.min.js");
		Game.server.onmessage = function(event){
			if(event.data.channel == Channel.SERVER_INIT){
				callback(id);
			}
			else if(event.data.channel == Channel.BRIDGE_INIT){

			}
			else if(event.data.channel == Channel.BRIDGE_DATA){
				let data = event.data.data;
				Game.serverSocket.send(data.peer,data.channel,data.data);
			}
		};
	});
};