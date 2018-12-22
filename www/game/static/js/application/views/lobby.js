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
		if(!Session.isAdmin()) event.preventDefault();
	}
};

Lobby.droppable = {
	tolerance: "pointer",
	drop: function(event,ui){
		if(Session.isAdmin()){
			let target = $(event.target);
			let team = Team.fromId(target.closest("[data-team]").attr("data-team"));
			let player = Game.players.get($(ui.draggable).attr("data-id"));
			if(player.team != team){
				$(ui.draggable).appendTo(this);
				Game.client.movePlayerRequest(player,team);
			}
		}
	}
};

Lobby.updateSettings = function(){
	$("#game-map").val(Game.settings.map);
	$("#game-mode").val(Game.settings.mode.id);
	$("#game-timelimit").val(Game.settings.time);
	$("[data-scorelimit="+Game.settings.mode.id+"]").val(Game.settings.score);
	$("div[data-gamemode]").attr("data-gamemode",Game.settings.mode.id);
	if(Session.isAdmin()){
		$("#game-map").prop("disabled",false);
		$("#game-mode").prop("disabled",false);
		$("#game-timelimit").prop("disabled",false);
		$("[data-scorelimit]").prop("disabled",false);
	}
};

$(function(){
	$("#game-map").change(function(event){
		if(Core.view != View.GAME || Game.view != View.GAME_LOBBY || Game.running || !Session.isAdmin()){
			event.preventDefault();
			return;
		}
		Game.client.settingsRequest(Settings.MAP,$(this).val());
	});

	$("#game-mode").change(function(event){
		if(Core.view != View.GAME || Game.view != View.GAME_LOBBY || Game.running || !Session.isAdmin()){
			event.preventDefault();
			return;
		}
		Game.client.settingsRequest(Settings.MODE,$(this).val());
	});

	$("#game-timelimit").change(function(event){
		if(Core.view != View.GAME || Game.view != View.GAME_LOBBY || Game.running || !Session.isAdmin()){
			event.preventDefault();
			return;
		}
		Game.client.settingsRequest(Settings.TIME,$(this).val());
	});

	$("[data-scorelimit]").change(function(event){
		if(Core.view != View.GAME || Game.view != View.GAME_LOBBY || Game.running || !Session.isAdmin()){
			event.preventDefault();
			return;
		}
		Game.client.settingsRequest(Settings.SCORE,$(this).val());
	});

	var leaveBox = new ModalBox({
		title: "Leave room?",
		width: 300,
		buttons: [{
			name: "Leave",
			color: "red"
		}]
	});
	leaveBox.setBody("Are you sure you want to leave the room?");
	leaveBox.onSubmit(function(){
		leaveBox.hide();
		Game.leave();
		Core.setView(View.ROOMS);
		RoomList.update();
	});

	$("#leave-room-button").click(function(){
		leaveBox.show();
	});
});