var Chat = {};

Chat.NOTICE = 1;
Chat.NOTICE_JOIN = 2;
Chat.NOTICE_LEAVE = 3;
Chat.MESSAGE = 4;

Chat.addNotice = function(message,type){
	$("[data-js=messages]").append("<div class='row notice "+(type ? type : "")+"'>"+Strings.escapeHtml(message)+"</div>");
	Chat.scrollToBottom();
};

Chat.addMessage = function(player,message){
	$("[data-js=messages]").append("<div class='row nessage'><span class='user'>"+player.getName()+":</span><span>"+Strings.escapeHtml(message)+"</span></div>");
	Chat.scrollToBottom();
	Sounds.play(Sounds.MESSAGE,0.5);
};

Chat.process = function(data){
	if(data.type == Chat.NOTICE) Chat.addNotice(data.message);
	else if(data.type == Chat.NOTICE_JOIN) Chat.addNotice(data.message,"join");
	else if(data.type == Chat.NOTICE_LEAVE) Chat.addNotice(data.message,"leave");
	else if(data.type == Chat.MESSAGE){
		let player = Core.client.players.get(data.player);
		if(player) Chat.addMessage(player,data.message);
	}
};

Chat.scrollToBottom = function(){
	$("[data-js=messages]").scrollTop($("[data-js=messages]")[0].scrollHeight);
};

Chat.reset = function(){
	$("[data-js=messages]").empty();
};

if(typeof GameClient !== 'undefined'){
	$(function(){
		$(document).on("keydown",function(event){
			if(Core.view == View.GAME && event.which == Keyboard.TAB){
				event.preventDefault();
			}
			if(Core.view == View.GAME && (event.which == Keyboard.ENTER || event.which == Keyboard.TAB || event.which == Keyboard.LETTER_T) && !$("#chat-input").is(":focus")){
				event.preventDefault();
				$("#chat-input").focus();
			}
		});

		$("#chat-input").on("keydown",function(event){
			if(Core.view != View.GAME){
				event.preventDefault();
				return;
			}
			if(event.which == Keyboard.ENTER){
				let message = $(this).val();
				if(!Validators.isEmpty(message)){
					Core.client.messageRequest(message);
				}
				$(this).val("").blur();
				let _this = $(this);
				setTimeout(function(){
					_this.blur();
				},1);
			}
			else if(event.which == Keyboard.ESCAPE || event.which == Keyboard.TAB){
				$(this).val("").blur();
				let _this = $(this);
				setTimeout(function(){
					_this.blur();
				},1);
			}
		});
	});
}