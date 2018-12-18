var Team = {};

Team.RED = {
	id:"red",
	color:"CC0000"
};

Team.BLUE = {
	id:"blue",
	color:"0078CC"
};

Team.SPEC = {
	id:"spec",
	color:"0078CC"
};

Team.fromId = function(id){
	if(id == Team.RED.id) return Team.RED;
	if(id == Team.BLUE.id) return Team.BLUE;
	if(id == Team.SPEC.id) return Team.SPEC;
	return null;
};