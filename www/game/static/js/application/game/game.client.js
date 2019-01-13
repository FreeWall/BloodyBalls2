var GameClient = function(){

	let _this = this;

	this.id = null;
	this.joined = false;
	this.view = null;

	this.socket = new SocketClient();
	this.server = null;
	this.serverSocket = new SocketServer();

	this.state = State.LOBBY;
	this.paused = false;

	this.stats = new Stats();
	this.physics = new Physics();
	this.renderer = new Renderer();

	this.players = new Players();
	this.settings = new Settings();

	this.joinCallback = function(){};
	this.initCallback = function(){};
	this.errorCallback = function(){};

	this.init = function(callback,errorCallback){
		$("#stats").html(this.stats.dom);

		this.physics.init();
		this.renderer.init();

		_this.errorCallback = errorCallback;
		this.socket.init(callback);

		this.tick();
	};

	this.join = function(data,password,callback,initCallback,errorCallback){
		this.joined = false;
		this.joinCallback = function(){
			callback();
		};
		this.initCallback = function(){
			initCallback();
			this.reset();
			this.id = data.host;
			Session.host = data.host;
			Session.admin = data.host;
			$("[data-js=lobby-title]").text(data.name);
			Core.setView(View.GAME);
			this.setView(View.GAME_LOBBY);
		};
		this.errorCallback = errorCallback;
		this.socket.open(data.host,password);
	};

	this.leave = function(){
		this.socket.close();
		if(this.server != null){
			this.server.terminate();
			this.serverSocket.destroy();
		}
	};

	this.setView = function(view){
		this.view = view;
		if(Core.view == View.GAME){
			$("[data-game-view]").hide();
			$("[data-game-view="+view+"]").show();
		}
	};

	this.reset = function(){
		this.state = State.LOBBY;
		this.paused = false;
		this.players = new Players();
		this.settings = new Settings();
		Chat.reset();
	};

	this.createServer = function(password,maxplayers,callback){
		this.serverSocket.init(password,maxplayers,function(id){
			_this.server = new Worker("/static/js/server.min.js?"+Date.now());
			_this.server.onmessage = function(event){
				if(event.data.channel == Channel.BRIDGE_INIT){
					callback(id);
				}
				else if(event.data.channel == Channel.BRIDGE_DATA){
					let data = event.data.data;
					_this.serverSocket.send(data.peer,data.channel,data.data);
				}
			};
			_this.server.postMessage({channel:Channel.BRIDGE_INIT,data:{password:password,maxplayers:maxplayers}});
		});
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
				if(_this.players.exists(data[i].id)){
					_this.players.get(data[i].id).fromData(data[i]);
				} else {
					_this.players.add(Player.fromData(data[i]));
				}
			}
			Lobby.updatePlayers();
		}
		else if(channel == Channel.SERVER_PLAYER_REMOVE){
			let player = _this.players.get(data.id);
			_this.players.remove(player);
			Lobby.updatePlayers();
		}
		else if(channel == Channel.PING){
			_this.socket.send(Channel.PING,data);
		}
		else if(channel == Channel.SERVER_SETTINGS){
			_this.settings = Settings.fromData(data);
			Lobby.updateSettings();
		}
		else if(channel == Channel.SERVER_CHAT){
			Chat.process(data);
		}
		else if(channel == Channel.SERVER_STATE){
			_this.state = data.state;
			_this.paused = false;
			Events.dispatch(Events.STATE_CHANGE,_this.state);
		}
		else if(channel == Channel.SERVER_PAUSE){
			_this.paused = data.paused;
			Events.dispatch(Events.PAUSE_CHANGE,_this.paused);
		}
	});

	Events.listen(Events.STATE_CHANGE,function(state){
		if(state == State.LOBBY){
			_this.physics.clear();
			_this.renderer.clear();
		}
		else if(state == State.GAME){
			for(let i in _this.players.getPlayers()){
				_this.physics.add(_this.players.get(i));
				_this.renderer.add(_this.players.get(i));
			}
		}
	});

	this.tick = function(){
		if(_this.state == State.GAME && !_this.paused){
			_this.stats.begin();
			_this.physics.tick();
			_this.renderer.tick();
			_this.stats.end();
		}
		window.requestAnimationFrame(_this.tick);
	};
};