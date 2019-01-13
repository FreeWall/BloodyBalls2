var Lobby = {};

Lobby.updatePlayers = function(){
	$(".lobby div.player").each(function(){
		if(!Core.client.players.exists($(this).attr("data-id"))){
			$(this).remove();
		}
	});
	for(let i in Core.client.players.getPlayers()){
		let player = Core.client.players.getPlayers()[i];
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
	helper: "clone",
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
			let player = Core.client.players.get($(ui.draggable).attr("data-id"));
			if(player.team != team){
				$(ui.draggable).appendTo(this);
				Core.client.movePlayerRequest(player,team);
			}
		}
	}
};

Lobby.updateSettings = function(){
	$("#game-map").val(Core.client.settings.map);
	$("#game-mode").val(Core.client.settings.mode.id);
	$("#game-timelimit").val(Core.client.settings.time);
	$("[data-scorelimit="+Core.client.settings.mode.id+"]").val(Core.client.settings.score);
	$("div[data-gamemode]").attr("data-gamemode",Core.client.settings.mode.id);
	if(Session.isAdmin() && Core.client.state == State.LOBBY){
		$("#game-map").prop("disabled",false);
		$("#game-mode").prop("disabled",false);
		$("#game-timelimit").prop("disabled",false);
		$("[data-scorelimit]").prop("disabled",false);
	} else {
		$("#game-map").prop("disabled",true);
		$("#game-mode").prop("disabled",true);
		$("#game-timelimit").prop("disabled",true);
		$("[data-scorelimit]").prop("disabled",true);
	}
};

$(function(){
	$("#game-map").change(function(event){
		if(Core.view != View.GAME || Core.client.view != View.GAME_LOBBY || Core.client.state == State.GAME || !Session.isAdmin()){
			event.preventDefault();
			return;
		}
		Core.client.settingsRequest(Settings.MAP,$(this).val());
	});

	$("#game-mode").change(function(event){
		if(Core.view != View.GAME || Core.client.view != View.GAME_LOBBY || Core.client.state == State.GAME || !Session.isAdmin()){
			event.preventDefault();
			return;
		}
		Core.client.settingsRequest(Settings.MODE,$(this).val());
	});

	$("#game-timelimit").change(function(event){
		if(Core.view != View.GAME || Core.client.view != View.GAME_LOBBY || Core.client.state == State.GAME || !Session.isAdmin()){
			event.preventDefault();
			return;
		}
		Core.client.settingsRequest(Settings.TIME,$(this).val());
	});

	$("[data-scorelimit]").change(function(event){
		if(Core.view != View.GAME || Core.client.view != View.GAME_LOBBY || Core.client.state == State.GAME || !Session.isAdmin()){
			event.preventDefault();
			return;
		}
		Core.client.settingsRequest(Settings.SCORE,$(this).val());
	});
	//--------------------------------------------------------------------------
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
		Core.client.leave();
		Core.setView(View.ROOMS);
		RoomList.update();
	});

	$("#leave-room-button").click(function(){
		leaveBox.show();
	});
	//--------------------------------------------------------------------------
	var linkBox = new ModalBox({
		title: "Room link",
		width: 350
	});

	$("#link-room-button").click(function(){
		linkBox.setBody("<span class='label'>Share this link to invite people</span><input type='text' value='"+location.href+"share/"+Core.client.socket.host+"' readonly style='width:100%'/>");
		linkBox.show();
		$("input",linkBox.getContent()).select();
		$("input",linkBox.getContent()).click(function(){
			$(this).select();
		});
		$("input",linkBox.getContent()).on("copy",function(){
			$("span",linkBox.getContent()).html("Share this link to invite people (copied)");
		});
	});
	//--------------------------------------------------------------------------
	$("#play-button").click(function(){
		if(Core.view != View.GAME || Core.client.view != View.GAME_LOBBY || !Session.isAdmin()){
			return;
		}
		Core.client.stateRequest(State.GAME);
	});

	$("#stop-button").click(function(){
		if(Core.view != View.GAME || Core.client.view != View.GAME_LOBBY || !Session.isAdmin()){
			return;
		}
		Core.client.stateRequest(State.LOBBY);
	});

	$("#pause-button").click(function(){
		if(Core.view != View.GAME || Core.client.view != View.GAME_LOBBY || Core.client.state != State.GAME || !Session.isAdmin()){
			return;
		}
		Core.client.pauseRequest(true);
	});

	$("#resume-button").click(function(){
		if(Core.view != View.GAME || Core.client.view != View.GAME_LOBBY || Core.client.state != State.GAME || !Session.isAdmin()){
			return;
		}
		Core.client.pauseRequest(false);
	});
	//--------------------------------------------------------------------------
	$(document).on("keydown",function(event){
		if(Core.view != View.GAME || event.which != Keyboard.ESCAPE){
			return;
		}
		if(Core.client.state == State.GAME){
			if(Core.client.view == View.GAME_LOBBY) Core.client.setView(View.GAME_CANVAS);
			else Core.client.setView(View.GAME_LOBBY);
		}
	});
	//--------------------------------------------------------------------------

	Events.listen(Events.STATE_CHANGE,function(state){
		if(state == State.LOBBY){
			$("#play-button").show();
			$("#stop-button").hide();
			$("#pause-button").hide();
			$("#resume-button").hide();
			Core.client.setView(View.GAME_LOBBY);
		}
		else if(state == State.GAME){
			$("#play-button").hide();
			$("#stop-button").show();
			$("#pause-button").show();
			$("#resume-button").hide();
			Core.client.setView(View.GAME_CANVAS);
		}
		Lobby.updateSettings();
	});

	Events.listen(Events.PAUSE_CHANGE,function(paused){
		if(Core.client.state == State.GAME){
			if(paused){
				$("#pause-button").hide();
				$("#resume-button").show();
			} else {
				$("#pause-button").show();
				$("#resume-button").hide();
			}
		}
	});
	//--------------------------------------------------------------------------
});