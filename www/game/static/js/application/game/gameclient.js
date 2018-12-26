var GameClient = function(){

	let _this = this;
	this.joined = false;
	this.socket = new SocketClient();

	this.joinCallback = function(){};
	this.initCallback = function(){};
	this.errorCallback = function(){};

	this.init = function(callback,errorCallback){
		_this.errorCallback = errorCallback;
		this.socket.init(callback);
	};

	this.join = function(id,password,callback,initCallback,errorCallback){
		_this.joined = false;
		_this.joinCallback = callback;
		_this.initCallback = initCallback;
		_this.errorCallback = errorCallback;
		this.socket.open(id,password);
	};

	this.leave = function(){
		this.socket.close();
	};

	this.movePlayerRequest = function(player,team){
		this.socket.send(Channel.CLIENT_MOVE_PLAYER,{id:player.getId(),team:team.id});
	};

	this.settingsRequest = function(type,value){
		this.socket.send(Channel.CLIENT_SETTINGS,{type:type,value:value});
	};

	this.messageRequest = function(message){
		this.socket.send(Channel.CLIENT_MESSAGE,{message:message});
	};

	this.stateRequest = function(state){
		this.socket.send(Channel.CLIENT_STATE,{state:state});
	};

	this.pauseRequest = function(paused){
		this.socket.send(Channel.CLIENT_PAUSE,{paused:paused});
	};

	this.socket.onOpened(function(){
	});

	this.socket.onClosed(function(){
		if(_this.joined == false) _this.errorCallback();
	});

	this.socket.onError(function(error){
		_this.errorCallback(error);
	});

	this.socket.onData(function(channel,data){
		if(channel == Channel.CONNECT){
			_this.joined = true;
			_this.joinCallback();
			_this.socket.send(Channel.INIT);
		}
		else if(channel == Channel.INIT){
			_this.initCallback();
		}
		else if(channel == Channel.SERVER_PLAYERS){
			for(let i in data){
				if(Game.players.exists(data[i].id)){
					Game.players.get(data[i].id).fromData(data[i]);
				} else {
					Game.players.add(Player.fromData(data[i]));
				}
			}
			Lobby.updatePlayers();
		}
		else if(channel == Channel.SERVER_PLAYER_REMOVE){
			let player = Game.players.get(data.id);
			Game.players.remove(player);
			Lobby.updatePlayers();
		}
		else if(channel == Channel.PING){
			_this.socket.send(Channel.PING,data);
		}
		else if(channel == Channel.SERVER_SETTINGS){
			Game.settings = Settings.fromData(data);
			Lobby.updateSettings();
		}
		else if(channel == Channel.SERVER_CHAT){
			Chat.process(data);
		}
		else if(channel == Channel.SERVER_STATE){
			Game.state = data.state;
			Game.paused = false;
			Events.dispatch(Events.STATE_CHANGE,Game.state);
		}
		else if(channel == Channel.SERVER_PAUSE){
			Game.paused = data.paused;
			Events.dispatch(Events.PAUSE_CHANGE,Game.paused);
		}
	});
};