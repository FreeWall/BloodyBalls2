//http://pixijs.download/release/docs/index.html
var Renderer = function(){

	let _this = this;

	this.objects = {};

	this.init = function(){
		if(window.WebGLRenderingContext){
			var canvas = $("#game-canvas")[0];
			var context = canvas.getContext("webgl");
			if(!context){
				Core.error("webGL");
				return;
			}
		}
		this.renderer = new PIXI.WebGLRenderer(800,800,{antialias:true,view:$("#game-canvas")[0]});
		this.stage = new PIXI.Stage();
		this.container = new PIXI.DisplayObjectContainer();
		this.graphics = new PIXI.Graphics();
		this.container.addChild(this.graphics);
		this.stage.addChild(this.container);
	};

	this.add = function(object){
		this.objects[object.getId()] = object;
		this.container.addChild(object.getRenderObject());
	};

	this.remove = function(object){
		delete this.objects[object.getId()];
		this.container.removeChild(object.getRenderObject());
	};

	this.tick = function(){
		this.graphics.clear();
		for(let i in this.objects){
			this.objects[i].render();
		}
		this.renderer.render(this.stage);
	};
};