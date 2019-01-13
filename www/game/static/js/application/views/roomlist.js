var RoomList = {};

RoomList.selectedId = 0;
RoomList.connectingId = 0;
RoomList.updating = false;

RoomList.update = function(){
	if(!RoomList.updating){
		RoomList.updating = true;
		$("[data-view=rooms] div.loader").toggleClass("show",true);
		$("#refresh-rooms-button").toggleClass("disabled",true);
		Core.api("room",{action:"rooms-users"},function(data){
			$("[data-js=rooms]").html(data['rooms']);
			$("[data-js=users]").html(data['users']);
			$("[data-view=rooms] div.loader").toggleClass("show",false);
			$("#refresh-rooms-button").toggleClass("disabled",false);
			RoomList.updating = false;
			if(RoomList.selectedId != 0){
				$("[data-js=rooms] tr[data-id="+RoomList.selectedId+"]").trigger("click");
			}
		});
	}
};

RoomList.join = function(connectBox,connectErrorBox,password){
	if(RoomList.connectingId != 0){
		connectBox.show();
		connectBox.loading(true);
		$(".rows div.row",connectBox.getContent()).html("&nbsp;");
		$(".rows div.row:nth-child(1)",connectBox.getContent()).text("Connecting to server ...");
		Core.api("room",{action:"join",id:RoomList.connectingId},function(data){
			if(data){
				$(".rows div.row:nth-child(2)",connectBox.getContent()).text("Connecting to host ...");
				Core.client.join(data,password,function(){
					$(".rows div.row:nth-child(3)",connectBox.getContent()).text("Awaiting state ...");
				},function(){
					connectBox.hide();
				},function(){
					connectBox.hide();
					connectErrorBox.show();
				});
			} else {
				connectBox.hide();
				connectErrorBox.show();
			}
		});
	}
};

RoomList.create = function(name,password,maxplayers,callback){
	Core.client.createServer(password,maxplayers,function(id){
		Core.api("room",{action:"create",host:id,name:name,password:password,maxplayers:maxplayers},function(data){
			Core.client.join(data,password,function(){
			},function(){
				callback(data);
			});
		});
	});
};

$(function(){
	var connectBox = new ModalBox({
		title: "Connecting",
		width: 250,
		close: false
	});
	connectBox.setClose(false);
	connectBox.setBody($("[data-modal=connecting]").contents());
	$("div.box",connectBox.getContent()).append("<div class='headloader loader'></div>");

	var passwordBox = new ModalBox({
		title: "Enter the password",
		width: 300,
		buttons: [{
			name: "Connect",
			color: "red",
			form: "#password-form"
		}]
	});
	passwordBox.setBody($("[data-modal=password]").contents());
	$("#password-form").submit(function(){
		passwordBox.hide();
		RoomList.join(connectBox,connectErrorBox,$("#connect-password").val());
		return false;
	});

	var connectErrorBox = new ModalBox({
		title: "Error",
		width: 250,
	});
	connectErrorBox.setBody("Could not connect to host");

	var newbox = new ModalBox({
		title: "Create room",
		width: 300,
		buttons: [{
			name: "Create",
			color: "red",
			form: "#create-room-form"
		}]
	});
	newbox.setBody($("[data-modal=create-room]").contents());

	$(document).on("click","[data-js=rooms] tr[data-id]",function(){
		$(this).toggleClass("selected",true);
		$(this).trigger("selected");
	});

	$(document).on("dblclick","[data-js=rooms] tr[data-id]",function(){
		$("#join-room-button").trigger("click");
	});

	$("html").click(function(){
		$("[data-js=rooms] tr[data-id]").toggleClass("selected",false);
		if($("[data-js=rooms] tr[data-id].selected").length < 1){
			$("#join-room-button").toggleClass("disabled",true).toggleClass("red",false);
			RoomList.selectedId = 0;
		}
	});

	$(document).on("selected","[data-js=rooms] tr[data-id]",function(){
		RoomList.selectedId = $(this).attr("data-id");
		$("#join-room-button").toggleClass("disabled",false).toggleClass("red",true);
	});

	$("#join-room-button").click(function(){
		if(RoomList.selectedId != 0){
			RoomList.connectingId = RoomList.selectedId;
			RoomList.selectedId = 0;
			event.stopPropagation();
			let password = $("[data-js=rooms] tr[data-id="+RoomList.connectingId+"]").is("[data-password]");
			if(password){
				$("#connect-password",passwordBox.getContent()).val("");
				passwordBox.show();
			} else {
				RoomList.join(connectBox,connectErrorBox);
			}
		}
	});

	$("#create-room-button").click(function(){
		newbox.show();
		$("input#room-name").val(Session.getName()+"'s room");
	});

	$("#create-room-form:not(.creating)").submit(function(){
		$(this).toggleClass("creating",true);
		var name = $("input#room-name").val();
		var password = $("input#room-password").val();
		var maxplayers = Number($("select#room-maxplayers").val());
		if(Validators.isEmpty(name)){
			$("input#room-name").toggleClass("error",true);
			return false;
		}
		newbox.setClose(false);
		newbox.loading(true);
		$("#create-room-form input, #create-room-form select").prop("disabled",true);
		$("div.button",newbox.getContent()).toggleClass("disabled",true);
		RoomList.create(name,password,maxplayers,function(data){
			newbox.setClose(true);
			newbox.hide();
			$("#create-room-form input, #create-room-form select").prop("disabled",false);
			//Game.join(data,true);
		});
		return false;
	});

	$(document).on("keydown",function(event){
		if(Core.view == View.ROOMS){
			if(event.which == 116){
				event.preventDefault();
				RoomList.update();
			}
			else if(event.which == 13){
				$("[data-js=rooms] tr[data-id].selected").trigger("dblclick");
			}
		}
	});

	$("#refresh-rooms-button").click(function(){
		RoomList.update();
	});
});