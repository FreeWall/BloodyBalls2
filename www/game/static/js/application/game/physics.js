//http://schteppe.github.io/p2.js/docs/
var Physics = function(){

	let _this = this;

	this.objects = {};

	this.world = null;

	this.fixedTimeStep = 1/60;
	this.lastTime = 0;
	this.time = 0;

	this.materials = {
		wall: new p2.Material(),
		player: new p2.Material(),
	};

	this.init = function(){
		this.world = new p2.World({gravity:[0,0]});

		this.world.addContactMaterial(new p2.ContactMaterial(this.materials.wall,this.materials.player,{
			friction:0,
			restitution:0.5
		}));

		this.world.addContactMaterial(new p2.ContactMaterial(this.materials.player,this.materials.player,{
			friction:0,
			restitution:1
		}));
	};

	this.add = function(object){
		this.objects[object.getId()] = object;
		this.world.addBody(object.getPhysicsBody());
	};

	this.remove = function(object){
		delete this.objects[object.getId()];
		this.world.removeBody(object.getPhysicsBody());
	};

	this.getWorld = function(){
		return this.world;
	};

	this.tick = function(){
		this.time = Date.now();
		for(let i in this.objects){
			this.objects[i].tick();
		}
		this.world.step(this.fixedTimeStep,this.lastTime ? (this.time-this.lastTime)/1000 : 0);
		this.lastTime = this.time;
	};
};