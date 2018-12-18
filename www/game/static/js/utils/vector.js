var Vector = function(x,y){

	this.x = x || 0;
	this.y = y || 0;

	this.add = function(vec){
		this.x += vec.x;
		this.y += vec.y;
		return this;
	};

	this.subtract = function(vec){
		this.x -= vec.x;
		this.y -= vec.y;
		return this;
	};

	this.multiply = function(vec){
		this.x *= vec.x;
		this.y *= vec.y;
		return this;
	};

	this.divide = function(vec){
		this.x /= vec.x;
		this.y /= vec.y;
		return this;
	};

	this.dot = function(vec){
		return (this.x*vec.x)+(this.y*vec.y);
	};

	this.cross = function(vec){
		return (this.x*vec.x)-(this.y*vec.y);
	};

	this.length = function(){
		return Math.sqrt(this.lengthSq());
	};

	this.lengthSq = function(){
		return (this.x*this.x)+(this.y*this.y);
	};

	this.toString = function(){
		return "{x:"+this.x+",y:"+this.y+"}";
	};

	this.toArray = function(){
		return [this.x,this.y];
	};

	this.toObject = function(){
		return {x:this.x,y:this.y};
	};

	this.clone = function(){
		return new Vector(this.x,this.y);
	};
};