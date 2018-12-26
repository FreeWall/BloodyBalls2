var Sounds = {};

Sounds.play = function(name,volume){
	let sound = new Audio("/static/sounds/"+name);
	sound.volume = volume || 1;
	return sound.play();
};

Sounds.MESSAGE = "pop.mp3";