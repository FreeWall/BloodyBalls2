var RoomList = {};

RoomList.selectedId = 0;
RoomList.updating = false;

RoomList.update = function(){
	if(!RoomList.updating){
		RoomList.updating = true;
		$("[data-view=rooms] div.loader").toggleClass("show",true);
		Core.api("room",{action:"rooms-users"},function(data){
			$("[data-js=rooms]").html(data['rooms']);
			$("[data-js=users]").html(data['users']);
			$("[data-view=rooms] div.loader").toggleClass("show",false);
			RoomList.updating = false;
		});
	}
};

RoomList.join = function(){
	if(RoomList.selectedId != 0){
		Connector.join(RoomList.selectedId);
	}
};

RoomList.create = function(name,password,maxplayers,callback){
	Core.server.create(function(id){
		Core.api("room",{action:"create",host:id,name:name,password:password,maxplayers:maxplayers},function(){
			callback();
		});
	});
};

$(function(){

	$(document).on("click","[data-js=rooms] tr[data-id]",function(){
		$(this).toggleClass("selected",true);
		$(this).trigger("selected");
	});

	$(document).on("dblclick","[data-js=rooms] tr[data-id]",function(){
		RoomList.join();
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
		event.stopPropagation();
		RoomList.join();
	});

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

	$("#create-room-button").click(function(){
		newbox.show();
	});

	$("#create-room-form:not(.creating)").submit(function(){
		$(this).toggleClass("creating",true);
		var name = $("input#name").val();
		var password = $("input#password").val();
		var maxplayers = Number($("select#maxplayers").val());
		if(Validators.isEmpty(name)){
			$("input#name").toggleClass("error",true);
			return false;
		}
		newbox.setClose(false);
		newbox.loading(true);
		$("#create-room-form input, #create-room-form select").prop("disabled",true);
		$("div.button",newbox.getContent()).toggleClass("disabled",true);
		RoomList.create(name,password,maxplayers,function(){
			newbox.hide();
		});
		return false;
	});

	$(document).on("keydown",function (event){
		if(Core.view == View.ROOMS){
			if((event.which || event.keyCode) == 116){
				event.preventDefault();
				RoomList.update();
			}
		}
	});
});