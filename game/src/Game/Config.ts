const DEBUG = process.env.DEBUG && process.env.DEBUG === 'true' || true;
const GAME_SERVER_PORT = Number(process.env.GAME_SERVER_PORT_1242 || 1242);
const GAME_SERVER_HOST = process.env.GAME_SERVER_HOST || typeof window !== 'undefined' ? window.location.hostname : 'localhost';
const GAME_SERVER_UPDATE_RATE = Number(process.env.SERVER_UPDATE_RATE || 10); // how many times per second should we send the updates to the server?

export {
    DEBUG,
    GAME_SERVER_PORT,
    GAME_SERVER_HOST,
    GAME_SERVER_UPDATE_RATE,
}
