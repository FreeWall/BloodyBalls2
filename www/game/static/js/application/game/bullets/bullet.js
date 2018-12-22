var Bullet = function(id){

	const OBJECT_SIZE = 4;

	let _this = this;
	this.id = id || ++Game.IDS;

	this.physicsObject = null;
	this.renderObject = null;

	this.getId = function(){
		return this.id;
	};

	this.getPhysicsObject = function(){
		if(this.physicsObject == null){
			this.physicsObject = new p2.Body({
				mass:1,
				damping:0.1,
				fixedRotation:true,
			});
			let shape = new p2.Circle(OBJECT_SIZE);
			this.physicsObject.addShape(shape);
		}
		return this.physicsObject;
	};

	this.getRenderObject = function(){
		if(this.renderObject == null){
			this.renderObject = new PIXI.Container();
			let graphics = new PIXI.Graphics();
			graphics.beginFill(0xFFFFFF);
			graphics.drawCircle(this.getPhysicsObject().position.x,this.getPhysicsObject().position.y,OBJECT_SIZE);
			this.renderObject.addChild(graphics);
		}
		return this.renderObject;
	};

	this.render = function(){
		this.getRenderObject().position.x = this.getPhysicsObject().position.x;
		this.getRenderObject().position.y = this.getPhysicsObject().position.y;
	};

	this.tick = function(){

	};
};

Bullet.fromData = function(data){
	return new Bullet(data.id);
};