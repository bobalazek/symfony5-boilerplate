let GAME_SERVER_HOST = 'localhost';
let GAME_SERVER_PORT = 1242;
let GAME_SERVER_TICK_RATE = 10; // How many times in a seconds we send the updates to the server?

if (process.env.GAME_SERVER_HOST) {
  GAME_SERVER_HOST = process.env.GAME_SERVER_HOST;
}

if (process.env.GAME_SERVER_PORT) {
  GAME_SERVER_PORT = Number(process.env.GAME_SERVER_PORT);
}

export {
  GAME_SERVER_HOST,
  GAME_SERVER_PORT,
  GAME_SERVER_TICK_RATE,
}
