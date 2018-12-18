var Game = {};

Game.IDS = 0;

Game.id = null;
Game.view = null;
Game.started = false;
Game.paused = false;

Game.client = new GameClient();
Game.server = new GameServer();
Game.settings = new Settings();

Game.stats = new Stats();
Game.physics = new Physics();
Game.renderer = new Renderer();

Game.players = new Players();

Game.init = function(){
	Game.stats.domElement.style.position = 'absolute';
	Game.stats.domElement.style.left = '10px';
	Game.stats.domElement.style.top = '10px';
	document.body.appendChild(Game.stats.dom);

	Game.physics.init();
	Game.renderer.init();

	Game.tick();
};

Game.join = function(data,host){
	host = host || false;
	Game.id = data.id;
	Session.host = host;
	$("[data-js=lobby-title]").text(data.name);
	Core.setView(View.GAME);
	Game.setView(View.GAME_LOBBY);
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