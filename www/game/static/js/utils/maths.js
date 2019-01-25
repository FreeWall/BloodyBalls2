var Maths = {};

Maths.RADIAN = Math.PI/180;

Maths.random = function(min, max){
	return Math.floor(Math.random()*(max-min+1)+min);
};