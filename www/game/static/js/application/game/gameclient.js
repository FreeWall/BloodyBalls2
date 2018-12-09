var GameClient = function(){

	let _this = this;
	_this.socket = new SocketClient();

	this.init = function(callback){
		this.socket.init(callback);
	};

	this.join = function(id){
		this.socket.open(id);
	};

	this.socket.onOpened(function(){
		console.log("client opened");
	});

	this.socket.onClosed(function(){
		console.log("client closed");
	});

	this.socket.onError(function(error){
		console.log("client error "+error);
	});

	this.socket.onData(function(data){
		console.log("client data "+data);
	});
};