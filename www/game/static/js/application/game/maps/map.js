var Map = function(id,name){

	let _this = this;
	this.id = id;
	this.name = name || null;

	this.walls = {};

	this.getId = function(){
		return this.id;
	};
};

Map.fromData = function(data){
	return new Map(data.id,data.name);
};