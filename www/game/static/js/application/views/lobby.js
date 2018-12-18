var Lobby = {};

Lobby.updatePlayers = function(){
	for(let i in Game.players.getPlayers()){
		let player = Game.players.getPlayers()[i];
		let element = $("div.player[data-id="+player.getId()+"]");
		if(element.length == 0){
			$("[data-team="+player.getTeam().id+"] div.list").append('\
				<div class="player" data-id="'+player.getId()+'">\
					<div class="name"><div class="flag flag-'+player.getCountry()+'"></div><span>'+player.getName()+'</span></div>\
					<div class="ping">0</div>\
				</div>\
			');
		}
		else if(element.closest("[data-team]").attr("data-team") != player.getTeam().id){
			element.detach();
			$("[data-team="+player.getTeam().id+"] div.list").append(element);
		}
		element = $("div.player[data-id="+player.getId()+"]");
		$(element).find("div.ping").text(player.getPing());
	}
	$(".lobby .team .list div.player").draggable(Lobby.draggable);
	$(".lobby .team div.list").droppable(Lobby.droppable);
};

Lobby.draggable = {
	revert: true,
	containment: "div.lobby",
	distance: 4,
	revertDuration: 0,
	refreshPositions: true,
	zIndex: 10,
	start: function(event,ui){
		if(!Session.isHost()) event.preventDefault();
	}
};

Lobby.droppable = {
	tolerance: "pointer",
	drop: function(event,ui){
		if(Session.isHost()){
			let target = $(event.target);
			let team = Team.fromId(target.closest("[data-team]").attr("data-team"));
			let player = Game.players.get($(ui.draggable).attr("data-id"));
			if(player.team != team){
				player.team = team;
				$(ui.draggable).appendTo(this);
				Game.server.sendPlayers();
			}
		}
	}
};

Events.listen("settingsChanged",function(args){
	$("#game-map").val(Game.settings.map);
	$("#game-mode").val(Game.settings.mode.id);
	$("#game-timelimit").val(Game.settings.time);
	$("#game-scorelimit").val(Game.settings.score);
	$("div[data-gamemode]").attr("data-gamemode",Game.settings.mode.id);
	if(Session.isHost()) Game.server.sendSettings();
});

$(function(){
	$("#game-map").change(function(event){
		if(Core.view != View.GAME || Game.view != View.GAME_LOBBY || Game.started || !Session.isHost()){
			event.preventDefault();
			return;
		}
		Game.settings.setMap($(this).val());
		Events.dispatch("settingsChanged",{type:Settings.MAP});
	});

	$("#game-mode").change(function(event){
		if(Core.view != View.GAME || Game.view != View.GAME_LOBBY || Game.started || !Session.isHost()){
			event.preventDefault();
			return;
		}
		Game.settings.setMode($(this).val());
		Events.dispatch("settingsChanged",{type:Settings.MODE});
	});

	$("#game-timelimit").change(function(event){
		if(Core.view != View.GAME || Game.view != View.GAME_LOBBY || Game.started || !Session.isHost()){
			event.preventDefault();
			return;
		}
		Game.settings.setTime($(this).val());
		Events.dispatch("settingsChanged",{type:Settings.TIME});
	});

	$("[data-scorelimit]").change(function(event){
		if(Core.view != View.GAME || Game.view != View.GAME_LOBBY || Game.started || !Session.isHost()){
			event.preventDefault();
			return;
		}
		Game.settings.setScore($(this).val());
		Events.dispatch("settingsChanged",{type:Settings.SCORE});
	});
});