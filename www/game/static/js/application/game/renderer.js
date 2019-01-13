//http://pixijs.download/release/docs/index.html
var Renderer = function(){

	let _this = this;

	this.canvas = null;
	this.objects = {};

	this.scope = null;

	this.init = function(){
		if(window.WebGLRenderingContext){
			this.canvas = $("#game-canvas")[0];
			let context = this.canvas.getContext("webgl");
			if(!context){
				Core.error("WebGL is not available");
				return;
			}
		}
		this.renderer = new PIXI.WebGLRenderer(750,750,{antialias:true,transparent:true,view:this.canvas});
		this.stage = new PIXI.Stage();
		this.container = new PIXI.DisplayObjectContainer();
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

	this.clear = function(){
		for(let i in this.objects){
			this.remove(this.objects[i]);
		}
	};

	this.tick = function(){
		for(let i in this.objects){
			this.objects[i].render();
		}
		this.renderer.render(this.stage);
	};
};