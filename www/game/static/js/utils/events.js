var Events = {};

Events.listeners = {};

Events.listen = function(name,callback){
	if(typeof Events.listeners[name] === 'undefined'){
		Events.listeners[name] = [];
	}
	Events.listeners[name].push(callback);
};

Events.dispatch = function(name,...args){
	if(typeof Events.listeners[name] !== 'undefined'){
		for(let i in Events.listeners[name]){
			Events.listeners[name][i](...args);
		}
	}
};

Events.CONNECTED = 1;
Events.DISCONNECTED = 2;

Events.STATE_CHANGE = 3;
Events.PAUSE_CHANGE = 4;