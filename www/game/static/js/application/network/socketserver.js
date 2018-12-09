var SocketServer = function(){

	let _this = this;

	this.id     = null;
	this.peer   = null;
	this.peers  = [];

	this.init = function(callback){
		this.peer = new Peer({host:'46.28.107.69'});
		this.peer.on("open",function(id){
			_this.id = id;
			if(callback && typeof(callback) === "function") callback(id);
		});
		this.peer.on("connection",function(socket){
			_this.peers[socket.peer] = socket;
			socket.on("open",function(){
				_this.onOpenCallback(socket.peer,socket.id);
			});
			socket.on("close",function(){
				_this.onClosedCallback(socket.peer);
			});
			socket.on("data",function(data){
				_this.onDataCallback(socket.peer,data);
			});
		});
		this.peer.on("error",function(error){
			_this.onErrorCallback(error);
		});
	};

	this.destroy = function(){
		if(this.peer != null){
			this.peer.destroy();
			this.peer = null;
		}
	};

	this.send = function(peer,data){
		this.peers[peer].send(data);
	};

	this.sendToAll = function(data){
		for(let i in this.peers) this.send(i,data);
	};

	let onOpenCallback = function(){};
	this.onOpened = function(callback){
		_this.onOpenCallback = callback;
	};

	let onClosedCallback = function(){};
	this.onClosed = function(callback){
		_this.onClosedCallback = callback;
	};

	let onDataCallback = function(peer,data){};
	this.onData = function(callback){
		_this.onDataCallback = callback;
	};

	let onErrorCallback = function(error){};
	this.onError = function(callback){
		_this.onErrorCallback = callback;
	};

	window.onunload = window.onbeforeunload = function(e){
		if(!!_this.peer && !_this.peer.destroyed){
			_this.peer.destroy();
		}
	};
};