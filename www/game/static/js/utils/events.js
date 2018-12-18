var Events = {};

Events.listeners = {};

Events.listen = function(name,callback){
	if(typeof Events.listeners[name] === 'undefined'){
		Events.listeners[name] = [];
	}
	Events.listeners[name].push(callback);
};

Events.dispatch = function(name,args){
	if(typeof Events.listeners[name] !== 'undefined'){
		for(let i in Events.listeners[name]){
			Events.listeners[name][i](args);
		}
	}
};