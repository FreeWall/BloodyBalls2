var GameServer = function(){

	let _this = this;
	this.socket = new SocketServer();

	this.create = function(callback){
		this.destroy();
		this.socket.init(callback);
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

	this.destroy = function(){
		this.socket.destroy();
	};
};