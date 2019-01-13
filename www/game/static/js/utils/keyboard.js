var Keyboard = function(data){

	this.data = {};
	this.data[Keyboard.UP] = data ? data[Keyboard.UP] : false;
	this.data[Keyboard.DOWN] = data ? data[Keyboard.DOWN] : false;
	this.data[Keyboard.LEFT] = data ? data[Keyboard.LEFT] : false;
	this.data[Keyboard.RIGHT] = data ? data[Keyboard.RIGHT] : false;
	this.data[Keyboard.SPACE] = data ? data[Keyboard.SPACE] : false;
	this.data[Keyboard.RELOAD] = data ? data[Keyboard.RELOAD] : false;
	this.data[Keyboard.MOUSE_LEFT] = data ? data[Keyboard.MOUSE_LEFT] : false;
	this.data[Keyboard.MOUSE_RIGHT] = data ? data[Keyboard.MOUSE_RIGHT] : false;
	this.data[Keyboard.MOUSE_MIDDLE] = data ? data[Keyboard.MOUSE_MIDDLE] : false;

	this.isUp = function(){
		return this.data[Keyboard.UP];
	};

	this.isDown = function(){
		return this.data[Keyboard.DOWN];
	};

	this.isLeft = function(){
		return this.data[Keyboard.LEFT];
	};

	this.isRight = function(){
		return this.data[Keyboard.RIGHT];
	};

	this.isSpace = function(){
		return this.data[Keyboard.SPACE];
	};

	this.isReload = function(){
		return this.data[Keyboard.RELOAD];
	};

	this.isMouseLeft = function(){
		return this.data[Keyboard.MOUSE_LEFT];
	};

	this.isMouseRight = function(){
		return this.data[Keyboard.MOUSE_RIGHT];
	};

	this.isMouseMiddle = function(){
		return this.data[Keyboard.MOUSE_MIDDLE];
	};

	this.toObject = function(){
		return data;
	};

	this.toDifferenceObject = function(keyboard){
		keyboard = keyboard.toObject();
		let object = {};
		for(let i in keyboard){
			if(this.data[i] != keyboard[i]){
				object[i] = this.data[i];
			}
		}
		return object;
	};

	this.equals = function(keyboard){
		keyboard = keyboard.toObject();
		for(let i in keyboard){
			if(this.data[i] != keyboard[i]) return false;
		}
		return true;
	};
};

Keyboard.MOUSE_LEFT = 1;
Keyboard.MOUSE_RIGHT = 3;
Keyboard.MOUSE_MIDDLE = 2;

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