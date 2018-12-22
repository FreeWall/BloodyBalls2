var Channel = {};

Channel.PING = 1;

Channel.PLAYERS = 2;
Channel.PLAYERS_REMOVE = 3;
Channel.SETTINGS = 4;

Channel.REQUEST_MOVE_PLAYER = 10;
Channel.REQUEST_SETTINGS = 11;

Channel.SERVER_INIT = 50;

Channel.BRIDGE_INIT = 60;
Channel.BRIDGE_OPENED = 61;
Channel.BRIDGE_CLOSED = 62;
Channel.BRIDGE_DATA = 63;
Channel.BRIDGE_ERROR = 64;