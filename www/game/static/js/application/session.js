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
		for(let i in Session.keyboard.data){
			if(event.which == i){
				Session.keyboard.data[i] = true;
				break;
			}
		}
	});

	$(document).on("keyup",function(event){
		for(let i in Session.keyboard.data){
			if(event.which == i){
				Session.keyboard.data[i] = false;
				break;
			}
		}
	});

	$(document).on("mousedown",function(event){
		if(event.which != 1 && event.which != 2 && event.which != 3) return;
		Session.keyboard.data[event.which] = true;
	});

	$(document).on("mouseup",function(event){
		if(event.which != 1 && event.which != 2 && event.which != 3) return;
		Session.keyboard.data[event.which] = false;
	});
});