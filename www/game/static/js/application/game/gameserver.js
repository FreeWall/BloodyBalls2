var GameServer = function(){

	let _this = this;
	this.socket = new SocketServer();
	this.pingInterval = null;

	this.create = function(callback){
		this.destroy();
		this.socket.init(callback);
		this.pingInterval = setInterval(this.sendPingUpdate,1000);
	};

	this.sendPlayers = function(){
		_this.socket.sendToAll(Channel.PLAYERS,Game.players.toObject());
	};

	this.sendPingUpdate = function(){
		_this.socket.sendToAll(Channel.PING,Date.now());
	};

	this.sendSettings = function(){
		_this.socket.sendToAll(Channel.SETTINGS,Game.settings.toObject());
	};

	this.socket.onOpened(function(peer,session){
		Events.dispatch("peerConnected",peer);
		Game.players.add(new Player(null,session.name,session.country,peer));
		_this.sendPlayers();
	});

	this.socket.onClosed(function(peer){
		Events.dispatch("peerDisconnected",peer);
	});

	this.socket.onError(function(error){
	});

	this.socket.onData(function(peer,channel,data){
		if(channel == Channel.PING){
			Game.players.getByPeer(peer).ping = Date.now()-data;
			_this.sendPlayers();
		}
	});

	this.destroy = function(){
		this.socket.destroy();
		clearInterval(this.pingInterval);
	};
};