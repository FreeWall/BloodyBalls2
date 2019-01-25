var Walls = function(){

	let _this = this;

	this.IDS = 0;
	this.walls = {};

	this.getWalls = function(){
		return this.walls;
	};

	this.create = function(type){
		let wall = new Wall(++this.IDS,type);
		this.add(wall);
		return wall;
	};

	this.get = function(id){
		return this.walls[id];
	};

	this.exists = function(id){
		return (typeof this.walls[id] !== 'undefined');
	};

	this.add = function(wall){
		this.walls[wall.getId()] = wall;
	};

	this.remove = function(object){
		delete this.walls[object.getId()];
	};

	this.clear = function(){
		this.walls = {};
	};

	this.length = function(){
		return Object.keys(this.walls).length;
	};

	this.toObject = function(){
		let object = {};
		for(let i in this.walls){
			object[i] = this.walls[i].toObject();
		}
		return object;
	};
};