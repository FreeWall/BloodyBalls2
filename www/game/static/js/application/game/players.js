var Players = function(){

	let _this = this;

	this.players = {};
	this.idsByPeer = {};

	this.getPlayers = function(){
		return this.players;
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

	this.toObject = function(){
		let object = {};
		for(let i in this.players){
			object[i] = this.players[i].toObject();
		}
		return object;
	};
};