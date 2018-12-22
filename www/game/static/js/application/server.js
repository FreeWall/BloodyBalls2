importScripts(
	"/static/js/vendor/p2.min.js",
);

var Server = {};

Server.gameServer = new GameServer();

Server.init = function(){
	Server.gameServer.create();
	postMessage({channel:Channel.SERVER_INIT});
};

Server.init();