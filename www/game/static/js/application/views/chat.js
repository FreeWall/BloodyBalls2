var Chat = {};

Chat.NOTICE = 1;
Chat.MESSAGE = 2;

Chat.addNotice = function(message){
	$("[data-js=messages]").append("<div class='row notice'>"+Strings.escapeHtml(message)+"</div>");
	Chat.scrollToBottom();
};

Chat.addMessage = function(player,message){
	$("[data-js=messages]").append("<div class='row nessage'><span class='user'>"+player.getName()+":</span><span>"+Strings.escapeHtml(message)+"</span></div>");
	Chat.scrollToBottom();
	Sounds.play(Sounds.MESSAGE,0.5);
};

Chat.scrollToBottom = function(){
	$("[data-js=messages]").scrollTop($("[data-js=messages]")[0].scrollHeight);
};

Chat.process = function(data){
	if(data.type == Chat.NOTICE) Chat.addNotice(data.message);
	else if(data.type == Chat.MESSAGE){
		let player = Game.players.get(data.player);
		if(player) Chat.addMessage(player,data.message);
	}
};

Chat.reset = function(){
	$("[data-js=messages]").empty();
};

if(typeof Game !== 'undefined'){
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
					Game.client.messageRequest(message);
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