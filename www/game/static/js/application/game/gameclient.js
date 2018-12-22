var GameClient = function(){

	let _this = this;
	this.socket = new SocketClient();

	this.joinCallback = function(){};
	this.errorCallback = function(){};

	this.init = function(callback,errorCallback){
		_this.errorCallback = errorCallback;
		this.socket.init(callback);
	};

	this.join = function(id,callback,errorCallback){
		_this.joinCallback = callback;
		_this.errorCallback = errorCallback;
		this.socket.open(id);
	};

	this.leave = function(){
		this.socket.close();
	};

	this.movePlayerRequest = function(player,team){
		this.socket.send(Channel.REQUEST_MOVE_PLAYER,{id:player.getId(),team:team.id});
	};

	this.settingsRequest = function(type,value){
		this.socket.send(Channel.REQUEST_SETTINGS,{type:type,value:value});
	};

	this.socket.onOpened(function(){
		_this.joinCallback();
	});

	this.socket.onClosed(function(){
	});

	this.socket.onError(function(error){
		_this.errorCallback(error);
	});

	this.socket.onData(function(channel,data){
		if(channel == Channel.PLAYERS){
			for(let i in data){
				if(Game.players.exists(data[i].id)){
					Game.players.get(data[i].id).fromData(data[i]);
				} else {
					Game.players.add(Player.fromData(data[i]));
				}
			}
			Lobby.updatePlayers(true);
		}
		else if(channel == Channel.PING){
			_this.socket.send(Channel.PING,data);
		}
		else if(channel == Channel.SETTINGS){
			Game.settings = Settings.fromData(data);
			Lobby.updateSettings();
		}
	});
};