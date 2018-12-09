var SocketClient = function(){

	let _this = this;

	this.id     = null;
	this.peer   = null;
	this.server = null;

	this.init = function(callback){
		this.peer = new Peer({host:'46.28.107.69'});
		this.peer.on("open",function(id){
			_this.id = id;
			if(callback && typeof(callback) === "function") callback();
		});
		this.peer.on("error",function(error){
			_this.onErrorCallback(error);
		});
	};

	this.open = function(id){
		this.server = this.peer.connect(id,{id:Session.id});
		this.server.on("open",function(){
			_this.onOpenCallback();
		});
		this.server.on("close",function(){
			_this.onClosedCallback();
		});
		this.server.on("data",function(data){
			_this.onDataCallback(data);
		});
	};

	this.close = function(){
		if(this.server != null){
			this.server.close();
			this.server = null;
		}
	};

	this.send = function(data){
		this.server.send(data);
	};

	this.onOpenCallback = function(){};
	this.onOpened = function(callback){
		_this.onOpenCallback = callback;
	};

	this.onClosedCallback = function(){};
	this.onClosed = function(callback){
		_this.onClosedCallback = callback;
	};

	this.onDataCallback = function(data){};
	this.onData = function(callback){
		_this.onDataCallback = callback;
	};

	this.onErrorCallback = function(error){};
	this.onError = function(callback){
		_this.onErrorCallback = callback;
	};

	window.onunload = window.onbeforeunload = function(e){
		if(!!_this.peer && !_this.peer.destroyed){
			_this.peer.destroy();
		}
	};
};