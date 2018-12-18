var GameMode = {};

GameMode.TDM = {
	id:"tdm",
	name:"Team Deathmatch"
};

GameMode.CTF = {
	id:"ctf",
	name:"Capture the Flag"
};

GameMode.DM = {
	id:"dm",
	name:"Deathmatch"
};

GameMode.fromId = function(id){
	if(id == GameMode.TDM.id) return GameMode.TDM;
	if(id == GameMode.CTF.id) return GameMode.CTF;
	if(id == GameMode.DM.id) return GameMode.DM;
	return null;
};