var SocketServer = function(){

	let _this = this;

	this.id     = null;
	this.peer   = null;
	this.peers  = {};

	this.init = function(callback){
		this.peer = new Peer({host:'46.28.107.69'});
		this.peer.on("open",function(id){
			_this.id = id;
			if(callback && typeof(callback) === "function") callback(id);
		});
		this.peer.on("connection",function(socket){
			_this.peers[socket.peer] = socket;
			socket.on("open",function(){
				_this.onOpened(socket.peer,socket.metadata);
			});
			socket.on("close",function(){
				delete _this.peers[socket.peer];
				_this.onClosed(socket.peer);
			});
			socket.on("data",function(data){
				_this.onData(socket.peer,data.channel,data.data);
			});
			socket.on("error",function(){
				delete _this.peers[socket.peer];
				_this.onError(socket.peer);
			});
		});
	};

	this.destroy = function(){
		if(this.peer != null){
			this.peer.destroy();
			this.peer = null;
		}
	};

	this.send = function(peer,channel,data){
		data = {channel:channel,data:data};
		this.peers[peer].send(data);
	};

	this.onOpened = function(peer,metadata){
		Game.server.postMessage({channel:Channel.BRIDGE_OPENED,data:{peer:peer,metadata:metadata}});
	};

	this.onClosed = function(peer){
		Game.server.postMessage({channel:Channel.BRIDGE_CLOSED,data:{peer:peer}});
	};

	this.onData = function(peer,channel,data){
		Game.server.postMessage({channel:Channel.BRIDGE_DATA,data:{peer:peer,channel:channel,data:data}});
	};

	this.onError = function(peer){
		Game.server.postMessage({channel:Channel.BRIDGE_ERROR,data:{peer:peer}});
	};

	window.onunload = window.onbeforeunload = function(){
		if(!!_this.peer && !_this.peer.destroyed){
			_this.peer.destroy();
		}
	};
};