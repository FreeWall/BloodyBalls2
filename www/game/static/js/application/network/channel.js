var Channel = {};

Channel.CONNECT = 1;
Channel.INIT = 2;

Channel.PING = 3;

Channel.SERVER_PLAYERS = 4;
Channel.SERVER_PLAYER_REMOVE = 5;
Channel.SERVER_SETTINGS = 6;
Channel.SERVER_CHAT = 7;
Channel.SERVER_STATE = 8;
Channel.SERVER_PAUSE = 9;

Channel.CLIENT_MOVE_PLAYER = 30;
Channel.CLIENT_SETTINGS = 31;
Channel.CLIENT_MESSAGE = 32;
Channel.CLIENT_STATE = 33;
Channel.CLIENT_PAUSE = 34;

Channel.BRIDGE_INIT = 60;
Channel.BRIDGE_OPENED = 61;
Channel.BRIDGE_CLOSED = 62;
Channel.BRIDGE_DATA = 63;
Channel.BRIDGE_ERROR = 64;