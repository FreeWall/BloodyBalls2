var Connector = {};

Connector.connectbox = null;

Connector.join = function(id){
	Core.api("room",{action:"connect",id:id},function(data){
		Core.client.join(data['host']);
	});
};

$(function(){
	Connector.connectbox = new ModalBox({
		title: "Connecting",
		width: 300
	});
	Connector.connectbox.setBody($("[data-modal=create-room]").contents());
});