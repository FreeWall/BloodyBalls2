var Player = function(id,name,country,peer){

	const OBJECT_SIZE = 22;

	let _this = this;
	this.id = id || ++Game.IDS;
	this.name = name || null;
	this.country = country || null;
	this.team = Team.SPEC;

	this.peer = peer || null;
	this.ping = 0;

	this.keyboard = new Keyboard();

	this.physicsObject = null;
	this.renderObject = null;

	this.getId = function(){
		return this.id;
	};

	this.getName = function(){
		return this.name;
	};

	this.getCountry = function(){
		return this.country;
	};

	this.getTeam = function(){
		return this.team;
	};

	this.getPeer = function(){
		return this.peer;
	};

	this.getPing = function(){
		return this.ping;
	};

	this.getPhysicsObject = function(){
		if(this.physicsObject == null){
			this.physicsObject = new p2.Body({
				mass:1,
				damping:0.1,
				fixedRotation:true,
			});
			let shape = new p2.Circle(OBJECT_SIZE/2);
			shape.material = Game.physics.materials.player;
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
		this.getRenderObject().position.x = this.getPhysicsObject().position.x;
		this.getRenderObject().position.y = this.getPhysicsObject().position.y;
	};

	this.tick = function(){
	};

	this.toObject = function(){
		return {id:this.id,name:this.name,team:this.team.id,ping:this.ping,country:this.country};
	};

	this.fromData = function(data){
		this.id = data.id;
		this.name = data.name;
		this.team = Team.fromId(data.team);
		this.ping = data.ping;
		this.country = data.country;
	};
};

Player.fromData = function(data){
	let player = new Player(data.id);
	player.fromData(data);
	return player;
};