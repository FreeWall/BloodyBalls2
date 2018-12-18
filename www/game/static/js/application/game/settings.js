var Settings = function(){

	this.map = null;
	this.mode = GameMode.TDM;
	this.time = 0;
	this.score = 0;

	this.setMap = function(map){
		this.map = map;
	};

	this.setMode = function(mode){
		this.mode = GameMode.fromId(mode);
	};

	this.setTime = function(time){
		this.time = time;
	};

	this.setScore = function(score){
		this.score = score;
	};

	this.toObject = function(){
		return {map:this.map,mode:this.mode.id,time:this.time,score:this.score};
	};

	this.fromData = function(data){
		this.map = data.map;
		this.mode = GameMode.fromId(data.mode);
		this.time = data.time;
		this.score = data.score;
	};
};

Settings.fromData = function(data){
	let settings = new Settings();
	settings.fromData(data);
	return settings;
};

Settings.MAP = 1;
Settings.MODE = 2;
Settings.TIME = 3;
Settings.SCORE = 4;