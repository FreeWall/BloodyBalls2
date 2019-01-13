var SocketServerBridge = function(){

	let _this = this;
	this.peers = {};

	this.send = function(peer,channel,data){
		postMessage({channel:Channel.BRIDGE_DATA,data:{peer:peer,channel:channel,data:data}});
	};

	this.sendToAll = function(channel,data){
		for(let i in this.peers) this.send(i,channel,data);
	};

	this.onOpenedCallback = function(peer,data){};
	this.onOpened = function(callback){
		_this.onOpenedCallback = callback;
	};

	this.onClosedCallback = function(){};
	this.onClosed = function(callback){
		_this.onClosedCallback = callback;
	};

	this.onDataCallback = function(peer,channel,data){};
	this.onData = function(callback){
		_this.onDataCallback = callback;
	};

	this.onErrorCallback = function(error){};
	this.onError = function(callback){
		_this.onErrorCallback = callback;
	};

	onmessage = function(event){
		let channel = event.data.channel;
		let data = event.data.data;
		if(channel == Channel.BRIDGE_INIT){
			Server.password = data.password;
			Server.maxplayers = data.maxplayers;
			postMessage({channel:Channel.BRIDGE_INIT});
		}
		else if(channel == Channel.BRIDGE_OPENED){
			_this.peers[data.peer] = data.peer;
			_this.onOpenedCallback(data.peer,data.metadata);
		}
		else if(channel == Channel.BRIDGE_CLOSED){
			delete _this.peers[data.peer];
			_this.onClosedCallback(data.peer);
		}
		else if(channel == Channel.BRIDGE_DATA){
			_this.onDataCallback(data.peer,data.channel,data.data);
		}
		else if(channel == Channel.BRIDGE_ERROR){
			_this.onErrorCallback(event.data.peer);
		}
	};
};