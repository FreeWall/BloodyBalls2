var Sounds = {};

Sounds.play = function(name,volume){
	let sound = new Audio("/static/sounds/"+name);
	sound.volume = volume || 1;
	return sound.play();
};

Sounds.preLoad = function(name){
	new Audio("/static/sounds/"+name);
};

Sounds.MESSAGE = "pop.mp3";
Sounds.HIT = "hit.mp3";
Sounds.FIRE = "fire.mp3";
Sounds.RELOAD = "reload.mp3";

Sounds.preLoad(Sounds.MESSAGE);
Sounds.preLoad(Sounds.HIT);
Sounds.preLoad(Sounds.FIRE);
Sounds.preLoad(Sounds.RELOAD);