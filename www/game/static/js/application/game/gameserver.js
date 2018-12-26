var GameServer = function(){

	let _this = this;
	this.socket = new SocketServerBridge();
	this.pingInterval = null;

	this.state = State.LOBBY;
	this.paused = false;

	this.password = null;
	this.maxplayers = null;

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
		this.socket.sendToAll(Channel.SERVER_PLAYERS,this.players.toObject());
	};

	this.sendSettings = function(peer){
		if(peer) this.socket.send(peer,Channel.SERVER_SETTINGS,this.settings.toObject());
		else this.socket.sendToAll(Channel.SERVER_SETTINGS,this.settings.toObject());
	};

	this.sendChatNotice = function(message){
		this.socket.sendToAll(Channel.SERVER_CHAT,{type:Chat.NOTICE,message:message});
	};

	this.sendChatMessage = function(peer,message){
		this.socket.sendToAll(Channel.SERVER_CHAT,{type:Chat.MESSAGE,player:this.players.getByPeer(peer).getId(),message:message});
	};

	this.socket.onOpened(function(peer,session){
		_this.players.create(session.name,session.country,peer);
		_this.socket.send(peer,Channel.CONNECT);
	});

	this.socket.onClosed(function(peer){
		Events.dispatch(Events.DISCONNECTED,peer,_this.players.getByPeer(peer));
	});

	this.socket.onError(function(error){
	});

	this.socket.onData(function(peer,channel,data){
		if(channel == Channel.INIT){
			_this.socket.send(peer,Channel.INIT);
			Events.dispatch(Events.CONNECTED,peer,_this.players.getByPeer(peer));
		}
		else if(channel == Channel.PING){
			_this.players.getByPeer(peer).ping = Date.now()-data;
			_this.sendPlayers();
		}
		else if(channel == Channel.CLIENT_MOVE_PLAYER){
			if(_this.players.getByPeer(peer).isAdmin()){
				let player = _this.players.get(data.id);
				if(player){
					player.team = Team.fromId(data.team);
					_this.sendPlayers();
				}
			}
		}
		else if(channel == Channel.CLIENT_SETTINGS){
			if(_this.players.getByPeer(peer).isAdmin()){
				if(data.type == Settings.MAP) _this.settings.setMap(data.value);
				if(data.type == Settings.MODE) _this.settings.setMode(data.value);
				if(data.type == Settings.TIME) _this.settings.setTime(data.value);
				if(data.type == Settings.SCORE) _this.settings.setScore(data.value);
				_this.sendSettings();
			}
		}
		else if(channel == Channel.CLIENT_MESSAGE){
			data.message = data.message.substring(0,256);
			if(!Validators.isEmpty(data.message)){
				_this.sendChatMessage(peer,data.message);
			}
		}
		else if(channel == Channel.CLIENT_STATE){
			if(_this.players.getByPeer(peer).isAdmin()){
				let state = data.state;
				if(_this.state != state){
					_this.state = state;
					_this.paused = false;
					Events.dispatch(Events.STATE_CHANGE,_this.state);
				}
			}
		}
		else if(channel == Channel.CLIENT_PAUSE){
			if(_this.players.getByPeer(peer).isAdmin() && _this.state == State.GAME){
				let paused = !!data.paused;
				if(_this.paused != paused){
					_this.paused = paused;
					Events.dispatch(Events.PAUSE_CHANGE,_this.paused);
				}
			}
		}
	});

	this.destroy = function(){
		clearInterval(this.pingInterval);
	};

	Events.listen(Events.CONNECTED,function(peer,player){
		_this.sendPlayers();
		_this.sendSettings(peer);
		_this.sendChatNotice(player.getName()+" has joined the room");
	});

	Events.listen(Events.DISCONNECTED,function(peer,player){
		_this.players.remove(player);
		_this.socket.sendToAll(Channel.SERVER_PLAYER_REMOVE,player.toObject());
		_this.sendChatNotice(player.getName()+" has left the room");
	});

	Events.listen(Events.STATE_CHANGE,function(state){
		_this.socket.sendToAll(Channel.SERVER_STATE,{state:state});
	});

	Events.listen(Events.PAUSE_CHANGE,function(paused){
		_this.socket.sendToAll(Channel.SERVER_PAUSE,{paused:paused});
	});

	this.tick = function(){
		if(_this.state == State.GAME && !_this.paused){

		}
		requestAnimationFrame(_this.tick);
	};

	this.tick();
};