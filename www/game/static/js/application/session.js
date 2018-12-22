var Session = {};

Session.id = 0;
Session.name = null;
Session.country = null;

Session.host = false;
Session.admin = false;

Session.keyboard = new Keyboard();

Session.init = function(data){
	this.id = data.id;
	this.name = data.name;
	this.country = data.country;
	window.sessionStorage.setItem("game-userid",this.id);
	window.localStorage.setItem("game-nickname",this.name);
};

Session.getId = function(){
	return this.id;
};

Session.getName = function(){
	return this.name;
};

Session.isNameValid = function(name){
	var RE = /^[a-zA-Z0-9]+$/;
	return (RE.test(name));
};

Session.isHost = function(){
	return this.host;
};

Session.isAdmin = function(){
	return this.admin;
};

Session.restoreId = function(){
	return (window.sessionStorage.getItem("game-userid") ? window.sessionStorage.getItem("game-userid") : null);
};

Session.restoreName = function(){
	return (window.localStorage.getItem("game-nickname") ? window.localStorage.getItem("game-nickname") : null);
};

Session.toObject = function(){
	return {id:Session.id,name:Session.name,country:Session.country};
};

$(function(){
	$(document).on("keydown",function(event){
		switch(event.which){
			case Keyboard.UP: Session.keyboard.UP = true;
				break;
			case Keyboard.DOWN: Session.keyboard.DOWN = true;
				break;
			case Keyboard.LEFT: Session.keyboard.LEFT = true;
				break;
			case Keyboard.RIGHT: Session.keyboard.RIGHT = true;
				break;
			case Keyboard.SPACE: Session.keyboard.SPACE = true;
				break;
			case Keyboard.RELOAD: Session.keyboard.RELOAD = true;
				break;
		}
	});
	$(document).on("keyup",function(event){
		switch(event.which){
			case Keyboard.UP: Session.keyboard.UP = false;
				break;
			case Keyboard.DOWN: Session.keyboard.DOWN = false;
				break;
			case Keyboard.LEFT: Session.keyboard.LEFT = false;
				break;
			case Keyboard.RIGHT: Session.keyboard.RIGHT = false;
				break;
			case Keyboard.SPACE: Session.keyboard.SPACE = false;
				break;
			case Keyboard.RELOAD: Session.keyboard.RELOAD = false;
				break;
		}
	});
});