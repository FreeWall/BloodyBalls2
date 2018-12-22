var GameServer = function(){

	let _this = this;
	this.socket = new SocketServerBridge();
	this.pingInterval = null;

	this.players = new Players();
	this.settings = new Settings();

	this.create = function(){
		this.destroy();
		this.pingInterval = setInterval(this.sendPingUpdate,1000);
	};

	this.sendPingUpdate = function(){
		_this.socket.sendToAll(Channel.PING,Date.now());
	};

	this.sendPlayers = function(){
		this.socket.sendToAll(Channel.PLAYERS,this.players.toObject());
	};

	this.sendSettings = function(peer){
		if(peer) this.socket.send(peer,Channel.SETTINGS,this.settings.toObject());
		else this.socket.sendToAll(Channel.SETTINGS,this.settings.toObject());
	};

	this.socket.onOpened(function(peer,session){
		Events.dispatch("peerConnected",peer);
		_this.players.create(session.name,session.country,peer);
		_this.sendPlayers();
		_this.sendSettings(peer);
	});

	this.socket.onClosed(function(peer){
		Events.dispatch("peerDisconnected",peer);
	});

	this.socket.onError(function(error){
	});

	this.socket.onData(function(peer,channel,data){
		if(channel == Channel.PING){
			_this.players.getByPeer(peer).ping = Date.now()-data;
			_this.sendPlayers();
		}
		else if(channel == Channel.REQUEST_MOVE_PLAYER){
			if(_this.players.getByPeer(peer).isAdmin()){
				let player = _this.players.get(data.id);
				if(player){
					player.team = Team.fromId(data.team);
					_this.sendPlayers();
				}
			}
		}
		else if(channel == Channel.REQUEST_SETTINGS){
			if(_this.players.getByPeer(peer).isAdmin()){
				if(data.type == Settings.MAP) _this.settings.setMap(data.value);
				if(data.type == Settings.MODE) _this.settings.setMode(data.value);
				if(data.type == Settings.TIME) _this.settings.setTime(data.value);
				if(data.type == Settings.SCORE) _this.settings.setScore(data.value);
				_this.sendSettings();
			}
		}
	});

	this.destroy = function(){
		clearInterval(this.pingInterval);
	};
};