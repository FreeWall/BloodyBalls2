var Keyboard = function(data){

	this.UP = data ? data[Keyboard.UP] : false;
	this.DOWN = data ? data[Keyboard.DOWN] : false;
	this.LEFT = data ? data[Keyboard.LEFT] : false;
	this.RIGHT = data ? data[Keyboard.RIGHT] : false;
	this.SPACE = data ? data[Keyboard.SPACE] : false;
	this.RELOAD = data ? data[Keyboard.RELOAD] : false;

	this.isUP = function(){
		return this.UP;
	};

	this.isDOWN = function(){
		return this.DOWN;
	};

	this.isLEFT = function(){
		return this.LEFT;
	};

	this.isRIGHT = function(){
		return this.RIGHT;
	};

	this.isSPACE = function(){
		return this.SPACE;
	};

	this.isRELOAD = function(){
		return this.RELOAD;
	};

	this.toObject = function(){
		return {
			up: this.UP,
			down: this.DOWN,
			left: this.LEFT,
			right: this.RIGHT,
			space: this.SPACE,
			reload: this.RELOAD,
		};
	};
};

Keyboard.UP = 87;
Keyboard.DOWN = 83;
Keyboard.LEFT = 65;
Keyboard.RIGHT = 68;
Keyboard.SPACE = 32;
Keyboard.RELOAD = 82;

Keyboard.ENTER = 13;
Keyboard.TAB = 9;
Keyboard.ESCAPE = 27;
Keyboard.LETTER_T = 84;

Keyboard.fromData = function(data){
	return new Keyboard(data);
};