var Session = function(){

	let _this = this;

	this.id = 0;
	this.name = null;

	this.getId = function(){
		return this.id;
	};

	this.getName = function(){
		return this.name;
	};

	this.isNameValid = function(name){
		var RE = /^[a-zA-Z0-9]+$/;
		return (RE.test(name));
	};

	this.init = function(data){
		this.id = data['id'];
		this.name = data['name'];
		window.localStorage.setItem("game-nickname",this.name);
	};

	this.restoreName = function(){
		return (window.localStorage.getItem("game-nickname") ? window.localStorage.getItem("game-nickname") : null);
	};
};