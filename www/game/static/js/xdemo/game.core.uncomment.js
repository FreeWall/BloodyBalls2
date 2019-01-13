/*  Copyright 2012-2016 Sven "underscorediscovery" Bergström
    written by : http:
    written for : http:
    MIT Licensed.
*/
var frame_time = 60/1000;
if('undefined' != typeof(global)) frame_time = 45;
( function () {
	var lastTime = 0;
	var vendors = [ 'ms', 'moz', 'webkit', 'o' ];
	for ( var x = 0; x < vendors.length && !window.requestAnimationFrame; ++ x ) {
		window.requestAnimationFrame = window[ vendors[ x ] + 'RequestAnimationFrame' ];
		window.cancelAnimationFrame = window[ vendors[ x ] + 'CancelAnimationFrame' ] || window[ vendors[ x ] + 'CancelRequestAnimationFrame' ];
	}
}() );
/* The game_core class */
var game_core = function(game_instance){
	this.instance = game_instance;
	this.server = this.instance !== undefined;
	this.world = {
		width : 720,
		height : 480
	};
	if(this.server) {
		this.players = {
			self : new game_player(this,this.instance.player_host),
			other : new game_player(this,this.instance.player_client)
		};
		this.players.self.pos = {x:20,y:20};
	} else {
		this.players = {
			self : new game_player(this),
			other : new game_player(this)
		};
		this.ghosts = {
			server_pos_self : new game_player(this),
			server_pos_other : new game_player(this),
			pos_other : new game_player(this)
		};
		this.ghosts.pos_other.state = 'dest_pos';
		this.ghosts.server_pos_self.state = 'server_pos';
		this.ghosts.server_pos_other.state = 'server_pos';
		this.ghosts.server_pos_self.pos = { x:20, y:20 };
		this.ghosts.pos_other.pos = { x:500, y:200 };
		this.ghosts.server_pos_other.pos = { x:500, y:200 };
	}
	this.playerspeed = 120;
	this._pdt = 0.0001;
	this._pdte = new Date().getTime();
	this.local_time = 0.016;
	this._dt = new Date().getTime();
	this._dte = new Date().getTime();
	this.create_physics_simulation();
	this.create_timer();
	if(!this.server) {
		this.keyboard = new THREEx.KeyboardState();
		this.client_create_configuration();
		this.server_updates = [];
		this.client_connect_to_server();
		this.client_create_ping_timer();
		this.color = localStorage.getItem('color') || '#cc8822' ;
		localStorage.setItem('color', this.color);
		this.players.self.color = this.color;
		if(String(window.location).indexOf('debug') != -1) {
			this.client_create_debug_gui();
		}
	} else {
		this.server_time = 0;
		this.laststate = {};
	}
};
if( 'undefined' != typeof global ) {
	module.exports = global.game_core = game_core;
}
/*
    Helper functions for the game code
        Here we have some common maths and game related code to make working with 2d vectors easy,
        as well as some helpers for rounding numbers to fixed point.
*/
Number.prototype.fixed = function(n) { n = n || 3; return parseFloat(this.toFixed(n)); };
game_core.prototype.pos = function(a) { return {x:a.x,y:a.y}; };
game_core.prototype.v_add = function(a,b) { return { x:(a.x+b.x).fixed(), y:(a.y+b.y).fixed() }; };
game_core.prototype.v_sub = function(a,b) { return { x:(a.x-b.x).fixed(),y:(a.y-b.y).fixed() }; };
game_core.prototype.v_mul_scalar = function(a,b) { return {x: (a.x*b).fixed() , y:(a.y*b).fixed() }; };
game_core.prototype.stop_update = function() {  window.cancelAnimationFrame( this.updateid );  };
game_core.prototype.lerp = function(p, n, t) { var _t = Number(t); _t = (Math.max(0, Math.min(1, _t))).fixed(); return (p + _t * (n - p)).fixed(); };
game_core.prototype.v_lerp = function(v,tv,t) { return { x: this.lerp(v.x, tv.x, t), y:this.lerp(v.y, tv.y, t) }; };
/*
    The player class
        A simple class to maintain state of a player on screen,
        as well as to draw that state when required.
*/
var game_player = function( game_instance, player_instance ) {
	this.instance = player_instance;
	this.game = game_instance;
	this.pos = { x:0, y:0 };
	this.size = { x:16, y:16, hx:8, hy:8 };
	this.state = 'not-connected';
	this.id = '';
	this.old_state = {pos:{x:0,y:0}};
	this.cur_state = {pos:{x:0,y:0}};
	this.state_time = new Date().getTime();
	this.inputs = [];
	this.pos_limits = {
		x_min: this.size.hx,
		x_max: this.game.world.width - this.size.hx,
		y_min: this.size.hy,
		y_max: this.game.world.height - this.size.hy
	};
	if(player_instance) {
		this.pos = { x:20, y:20 };
	} else {
		this.pos = { x:500, y:200 };
	}
};
game_player.prototype.draw = function(){
};
game_core.prototype.update = function(t) {
	this.dt = this.lastframetime ? ( (t - this.lastframetime)/1000.0).fixed() : 0.016;
	this.lastframetime = t;
	if(!this.server) {
		this.client_update();
	} else {
		this.server_update();
	}
	this.updateid = window.requestAnimationFrame( this.update.bind(this), this.viewport );
};
game_core.prototype.check_collision = function( item ) {
};
game_core.prototype.process_input = function( player ) {
	var x_dir = 0;
	var y_dir = 0;
	var ic = player.inputs.length;
	if(ic) {
		for(var j = 0; j < ic; ++j) {
			if(player.inputs[j].seq <= player.last_input_seq) continue;
			var input = player.inputs[j].inputs;
			var c = input.length;
			for(var i = 0; i < c; ++i) {
				var key = input[i];
				if(key == 'l') {
					x_dir -= 1;
				}
				if(key == 'r') {
					x_dir += 1;
				}
				if(key == 'd') {
					y_dir += 1;
				}
				if(key == 'u') {
					y_dir -= 1;
				}
			}
		}
	}
	var resulting_vector = this.physics_movement_vector_from_direction(x_dir,y_dir);
	if(player.inputs.length) {
		player.last_input_time = player.inputs[ic-1].time;
		player.last_input_seq = player.inputs[ic-1].seq;
	}
	return resulting_vector;
};
game_core.prototype.physics_movement_vector_from_direction = function(x,y) {
	return {
		x : (x * (this.playerspeed * 0.015)).fixed(3),
		y : (y * (this.playerspeed * 0.015)).fixed(3)
	};
};
game_core.prototype.update_physics = function() {
	if(this.server) {
		this.server_update_physics();
	} else {
		this.client_update_physics();
	}
};
/* SERVER =================================================================== */
game_core.prototype.server_update_physics = function() {
	this.players.self.old_state.pos = this.pos( this.players.self.pos );
	var new_dir = this.process_input(this.players.self);
	this.players.self.pos = this.v_add( this.players.self.old_state.pos, new_dir );
	this.players.other.old_state.pos = this.pos( this.players.other.pos );
	var other_new_dir = this.process_input(this.players.other);
	this.players.other.pos = this.v_add( this.players.other.old_state.pos, other_new_dir);
	this.check_collision( this.players.self );
	this.check_collision( this.players.other );
	this.players.self.inputs = [];
	this.players.other.inputs = [];
};
game_core.prototype.server_update = function(){
	this.server_time = this.local_time;
	this.laststate = {
		hp  : this.players.self.pos,
		cp  : this.players.other.pos,
		his : this.players.self.last_input_seq,
		cis : this.players.other.last_input_seq,
		t   : this.server_time
	};
	if(this.players.self.instance) {
		this.players.self.instance.emit( 'onserverupdate', this.laststate );
	}
	if(this.players.other.instance) {
		this.players.other.instance.emit( 'onserverupdate', this.laststate );
	}
};
game_core.prototype.handle_server_input = function(client, input, input_time, input_seq) {
	var player_client =
		(client.userid == this.players.self.instance.userid) ?
			this.players.self : this.players.other;
	player_client.inputs.push({inputs:input, time:input_time, seq:input_seq});
};
/* CLIENT =================================================================== */
game_core.prototype.client_handle_input = function(){
	var x_dir = 0;
	var y_dir = 0;
	var input = [];
	this.client_has_input = false;
	if( this.keyboard.pressed('A') ||
		this.keyboard.pressed('left')) {
		x_dir = -1;
		input.push('l');
	}
	if( this.keyboard.pressed('D') ||
		this.keyboard.pressed('right')) {
		x_dir = 1;
		input.push('r');
	}
	if( this.keyboard.pressed('S') ||
		this.keyboard.pressed('down')) {
		y_dir = 1;
		input.push('d');
	}
	if( this.keyboard.pressed('W') ||
		this.keyboard.pressed('up')) {
		y_dir = -1;
		input.push('u');
	}
	if(input.length) {
		this.input_seq += 1;
		this.players.self.inputs.push({
			inputs : input,
			time : this.local_time.fixed(3),
			seq : this.input_seq
		});
		var server_packet = 'i.';
		server_packet += input.join('-') + '.';
		server_packet += this.local_time.toFixed(3).replace('.','-') + '.';
		server_packet += this.input_seq;
		this.socket.send(  server_packet  );
		return this.physics_movement_vector_from_direction( x_dir, y_dir );
	} else {
		return {x:0,y:0};
	}
};
game_core.prototype.client_process_net_prediction_correction = function() {
	if(!this.server_updates.length) return;
	var latest_server_data = this.server_updates[this.server_updates.length-1];
	var my_server_pos = this.players.self.host ? latest_server_data.hp : latest_server_data.cp;
	this.ghosts.server_pos_self.pos = this.pos(my_server_pos);
	var my_last_input_on_server = this.players.self.host ? latest_server_data.his : latest_server_data.cis;
	if(my_last_input_on_server) {
		var lastinputseq_index = -1;
		for(var i = 0; i < this.players.self.inputs.length; ++i) {
			if(this.players.self.inputs[i].seq == my_last_input_on_server) {
				lastinputseq_index = i;
				break;
			}
		}
		if(lastinputseq_index != -1) {
			var number_to_clear = Math.abs(lastinputseq_index - (-1));
			this.players.self.inputs.splice(0, number_to_clear);
			this.players.self.cur_state.pos = this.pos(my_server_pos);
			this.players.self.last_input_seq = lastinputseq_index;
			this.client_update_physics();
			this.client_update_local_position();
		}
	}
};
game_core.prototype.client_process_net_updates = function() {
	if(!this.server_updates.length) return;
	var current_time = this.client_time;
	var count = this.server_updates.length-1;
	var target = null;
	var previous = null;
	for(var i = 0; i < count; ++i) {
		var point = this.server_updates[i];
		var next_point = this.server_updates[i+1];
		if(current_time > point.t && current_time < next_point.t) {
			target = next_point;
			previous = point;
			break;
		}
	}
	if(!target) {
		target = this.server_updates[0];
		previous = this.server_updates[0];
	}
	if(target && previous) {
		this.target_time = target.t;
		var difference = this.target_time - current_time;
		var max_difference = (target.t - previous.t).fixed(3);
		var time_point = (difference/max_difference).fixed(3);
		if( isNaN(time_point) ) time_point = 0;
		if(time_point == -Infinity) time_point = 0;
		if(time_point == Infinity) time_point = 0;
		var latest_server_data = this.server_updates[ this.server_updates.length-1 ];
		var other_server_pos = this.players.self.host ? latest_server_data.cp : latest_server_data.hp;
		var other_target_pos = this.players.self.host ? target.cp : target.hp;
		var other_past_pos = this.players.self.host ? previous.cp : previous.hp;
		this.ghosts.server_pos_other.pos = this.pos(other_server_pos);
		this.ghosts.pos_other.pos = this.v_lerp(other_past_pos, other_target_pos, time_point);
		if(this.client_smoothing) {
			this.players.other.pos = this.v_lerp( this.players.other.pos, this.ghosts.pos_other.pos, this._pdt*this.client_smooth);
		} else {
			this.players.other.pos = this.pos(this.ghosts.pos_other.pos);
		}
		if(!this.client_predict && !this.naive_approach) {
			var my_server_pos = this.players.self.host ? latest_server_data.hp : latest_server_data.cp;
			var my_target_pos = this.players.self.host ? target.hp : target.cp;
			var my_past_pos = this.players.self.host ? previous.hp : previous.cp;
			this.ghosts.server_pos_self.pos = this.pos(my_server_pos);
			var local_target = this.v_lerp(my_past_pos, my_target_pos, time_point);
			if(this.client_smoothing) {
				this.players.self.pos = this.v_lerp( this.players.self.pos, local_target, this._pdt*this.client_smooth);
			} else {
				this.players.self.pos = this.pos( local_target );
			}
		}
	}
};
game_core.prototype.client_onserverupdate_recieved = function(data){
	var player_host = this.players.self.host ?  this.players.self : this.players.other;
	var player_client = this.players.self.host ?  this.players.other : this.players.self;
	var this_player = this.players.self;
	this.server_time = data.t;
	this.client_time = this.server_time - (this.net_offset/1000);
	if(this.naive_approach) {
		if(data.hp) {
			player_host.pos = this.pos(data.hp);
		}
		if(data.cp) {
			player_client.pos = this.pos(data.cp);
		}
	} else {
		this.server_updates.push(data);
		if(this.server_updates.length >= ( 60*this.buffer_size )) {
			this.server_updates.splice(0,1);
		}
		this.oldest_tick = this.server_updates[0].t;
		this.client_process_net_prediction_correction();
	}
};
game_core.prototype.client_update_local_position = function(){
	if(this.client_predict) {
		var t = (this.local_time - this.players.self.state_time) / this._pdt;
		var old_state = this.players.self.old_state.pos;
		var current_state = this.players.self.cur_state.pos;
		this.players.self.pos = current_state;
		this.check_collision( this.players.self );
	}
};
game_core.prototype.client_update_physics = function() {
	if(this.client_predict) {
		this.players.self.old_state.pos = this.pos( this.players.self.cur_state.pos );
		var nd = this.process_input(this.players.self);
		this.players.self.cur_state.pos = this.v_add( this.players.self.old_state.pos, nd);
		this.players.self.state_time = this.local_time;
	}
};
game_core.prototype.client_update = function() {
	this.ctx.clearRect(0,0,720,480);
	this.client_draw_info();
	this.client_handle_input();
	if( !this.naive_approach ) {
		this.client_process_net_updates();
	}
	this.players.other.draw();
	this.client_update_local_position();
	this.players.self.draw();
	if(this.show_dest_pos && !this.naive_approach) {
		this.ghosts.pos_other.draw();
	}
	if(this.show_server_pos && !this.naive_approach) {
		this.ghosts.server_pos_self.draw();
		this.ghosts.server_pos_other.draw();
	}
	this.client_refresh_fps();
};
game_core.prototype.create_timer = function(){
	setInterval(function(){
		this._dt = new Date().getTime() - this._dte;
		this._dte = new Date().getTime();
		this.local_time += this._dt/1000.0;
	}.bind(this), 4);
};
game_core.prototype.create_physics_simulation = function() {
	setInterval(function(){
		this._pdt = (new Date().getTime() - this._pdte)/1000.0;
		this._pdte = new Date().getTime();
		this.update_physics();
	}.bind(this), 15);
};
game_core.prototype.client_create_ping_timer = function() {
};
game_core.prototype.client_create_configuration = function() {
	this.show_help = false;
	this.naive_approach = false;
	this.show_server_pos = false;
	this.show_dest_pos = false;
	this.client_predict = true;
	this.input_seq = 0;
	this.client_smoothing = true;
	this.client_smooth = 25;
	this.net_latency = 0.001;
	this.net_ping = 0.001;
	this.last_ping_time = 0.001;
	this.fake_lag = 0;
	this.fake_lag_time = 0;
	this.net_offset = 100;
	this.buffer_size = 2;
	this.target_time = 0.01;
	this.oldest_tick = 0.01;
	this.client_time = 0.01;
	this.server_time = 0.01;
	this.dt = 0.016;
	this.fps = 0;
	this.fps_avg_count = 0;
	this.fps_avg = 0;
	this.fps_avg_acc = 0;
	this.lit = 0;
	this.llt = new Date().getTime();
};
game_core.prototype.client_create_debug_gui = function() {
};
game_core.prototype.client_reset_positions = function() {
	var player_host = this.players.self.host ?  this.players.self : this.players.other;
	var player_client = this.players.self.host ?  this.players.other : this.players.self;
	player_host.pos = { x:20,y:20 };
	player_client.pos = { x:500, y:200 };
	this.players.self.old_state.pos = this.pos(this.players.self.pos);
	this.players.self.pos = this.pos(this.players.self.pos);
	this.players.self.cur_state.pos = this.pos(this.players.self.pos);
	this.ghosts.server_pos_self.pos = this.pos(this.players.self.pos);
	this.ghosts.server_pos_other.pos = this.pos(this.players.other.pos);
	this.ghosts.pos_other.pos = this.pos(this.players.other.pos);
};
game_core.prototype.client_onreadygame = function(data) {
};
game_core.prototype.client_onjoingame = function(data) {
	this.client_reset_positions();
};
game_core.prototype.client_onping = function(data) {
	this.net_ping = new Date().getTime() - parseFloat( data );
	this.net_latency = this.net_ping/2;
};
game_core.prototype.client_onnetmessage = function(data) {
};
game_core.prototype.client_ondisconnect = function(data) {
};
game_core.prototype.client_connect_to_server = function() {
};
game_core.prototype.client_refresh_fps = function() {
};
game_core.prototype.client_draw_info = function() {
};