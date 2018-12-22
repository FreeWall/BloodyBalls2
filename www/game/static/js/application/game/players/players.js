var Players = function(){

	let _this = this;

	this.IDS = 0;
	this.players = {};
	this.idsByPeer = {};

	this.getPlayers = function(){
		return this.players;
	};

	this.create = function(name,country,peer){
		let player = new Player(++this.IDS,name,country,peer);
		if(this.length() == 0){
			player.host = true;
			player.admin = true;
		}
		this.add(player);
	};

	this.get = function(id){
		return this.players[id];
	};

	this.getByPeer = function(peer){
		return this.players[this.idsByPeer[peer]];
	};

	this.exists = function(id){
		return (typeof this.players[id] !== 'undefined');
	};

	this.add = function(player){
		this.players[player.getId()] = player;
		this.idsByPeer[player.getPeer()] = player.getId();
	};

	this.remove = function(object){
		delete this.players[object.getId()];
		delete this.idsByPeer[object.getPeer()];
	};

	this.clear = function(){
		this.players = {};
	};

	this.length = function(){
		return Object.keys(this.players).length;
	};

	this.toObject = function(){
		let object = {};
		for(let i in this.players){
			object[i] = this.players[i].toObject();
		}
		return object;
	};
};