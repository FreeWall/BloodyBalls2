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
				_this.onOpenCallback(socket.peer,socket.metadata);
			});
			socket.on("close",function(){
				delete _this.peers[socket.peer];
				_this.onClosedCallback(socket.peer);
			});
			socket.on("data",function(data){
				_this.onDataCallback(socket.peer,data.channel,data.data);
			});
			socket.on("error",function(){
				delete _this.peers[socket.peer];
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

	this.send = function(peer,channel,data){
		data = {channel:channel,data:data};
		this.peers[peer].send(data);
	};

	this.sendToAll = function(channel,data){
		for(let i in this.peers) this.send(i,channel,data);
	};

	this.onOpenCallback = function(peer,data){};
	this.onOpened = function(callback){
		_this.onOpenCallback = callback;
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

	window.onunload = window.onbeforeunload = function(){
		if(!!_this.peer && !_this.peer.destroyed){
			_this.peer.destroy();
		}
	};
};