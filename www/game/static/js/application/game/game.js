var Game = {};

Game.stats = new Stats();
Game.physics = new Physics();
Game.renderer = new Renderer();

Game.init = function(){
	Game.stats.domElement.style.position = 'absolute';
	Game.stats.domElement.style.left = '10px';
	Game.stats.domElement.style.top = '10px';
	document.body.appendChild(Game.stats.dom);

	Game.physics.init();
	Game.renderer.init();

	Game.tick();
};

Game.tick = function(){
	Game.stats.begin();
	Game.physics.tick();
	Game.renderer.tick();
	Game.stats.end();
	window.requestAnimationFrame(Game.tick);
};