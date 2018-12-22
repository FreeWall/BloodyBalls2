var Wall = function(id){

	const OBJECT_SIZE = 32;

	let _this = this;
	this.id = id || ++Game.IDS;
	this.type = null;

	this.position = new Vector(0,0);

	this.physicsObject = null;
	this.renderObject = null;

	this.getId = function(){
		return this.id;
	};

	this.getPhysicsObject = function(){
		if(this.physicsObject == null){
			this.physicsObject = new p2.Body({
				mass:0,
				fixedRotation:true
			});
			let shape = new p2.Box(OBJECT_SIZE);
			shape.material = Game.physics.materials.wall;
			this.physicsObject.addShape(shape);
		}
		return this.physicsObject;
	};

	this.getRenderObject = function(){
		if(this.renderObject == null){
		}
		return this.renderObject;
	};

	this.render = function(){
	};

	this.tick = function(){
	};
};

Wall.fromData = function(data){
	return new Wall(data.id);
};