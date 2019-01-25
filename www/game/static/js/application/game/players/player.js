var Player = function(id,name,country,peer){

	let SIZE = 14;

	let _this = this;
	this.id = "p"+id;
	this.name = name || null;
	this.country = country || null;
	this.team = Team.SPEC;

	this.peer = peer || null;
	this.ping = 0;

	this.host = false;
	this.admin = false;

	this.physicsObject = null;
	this.renderObject = null;

	this.keyboard = new Keyboard();
	this.inputs = [];
	this.input_seq = 0;

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

	this.isHost = function(){
		return this.host;
	};

	this.isAdmin = function(){
		return this.admin;
	};

	this.getPhysicsObject = function(){
		if(this.physicsObject == null){
			this.physicsObject = new p2.Body({
				mass:1,
				damping:0,
				fixedRotation:true,
				position:[190+Maths.random(0,200),190+Maths.random(0,200)],
				velocity:[Maths.random(-100.5,100.5),Maths.random(-100.5,100.5)]
			});
			let shape = new p2.Circle({radius:SIZE});
			shape.material = Physics.materials.player;
			this.physicsObject.addShape(shape);
		}
		return this.physicsObject;
	};

	this.getRenderObject = function(){
		if(this.renderObject == null){
			this.renderObject = new PIXI.Container();
			let graphics = new PIXI.Graphics();
			graphics.beginFill(0xFFFFFF,1);
			graphics.lineStyle(2,0x000000);
			graphics.drawCircle(SIZE+2,SIZE+2,SIZE-1);
			graphics.endFill();
			this.renderObject.addChild(graphics);
		}
		return this.renderObject;
	};

	this.render = function(){
		this.getRenderObject().position.x = this.getPhysicsObject().position[0];
		this.getRenderObject().position.y = this.getPhysicsObject().position[1];
	};

	this.tick = function(){
		//this.getPhysicsObject().velocity[0] += Maths.random(-10.5,10.5);
		//this.getPhysicsObject().velocity[1] += Maths.random(-10.5,10.5);
	};

	this.toObject = function(){
		return {
			id:this.id,
			name:this.name,
			team:this.team.id,
			ping:this.ping,
			country:this.country,
			admin:this.admin
		};
	};

	this.fromData = function(data){
		this.id = data.id;
		this.name = data.name;
		this.team = Team.fromId(data.team);
		this.ping = data.ping;
		this.country = data.country;
		this.admin = data.admin;
	};

	this.handleInput = function(keyboard){
		let input = [];
		this.input_seq ++;
		this.inputs.push({
			keyboard:keyboard.toDifferenceObject(this.keyboard),
			seq:this.input_seq,
		});
	};
};

Player.fromData = function(data){
	let player = new Player(data.id);
	player.fromData(data);
	return player;
};