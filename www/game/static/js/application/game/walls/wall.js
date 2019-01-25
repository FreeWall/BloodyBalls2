var Wall = function(id,type){

	let _this = this;
	this.id = "w"+id;
	this.type = type;

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
			let shape = new p2.Box({width:32,height:32});
			shape.material = Core.client.physics.materials.wall;
			this.physicsObject.addShape(shape);
		}
		return this.physicsObject;
	};

	this.getRenderObject = function(){
		if(this.renderObject == null){
			this.renderObject = new PIXI.Container();
			let sprite = new PIXI.Sprite(new PIXI.Texture.fromImage("static/images/blocks/"+this.type+".png"));
			this.renderObject.addChild(sprite);
		}
		return this.renderObject;
	};

	this.render = function(){
		this.getRenderObject().position.x = this.getPhysicsObject().position[0];
		this.getRenderObject().position.y = this.getPhysicsObject().position[1];
	};

	this.tick = function(){
	};
};

Wall.fromData = function(data){
	return new Wall(data.id);
};