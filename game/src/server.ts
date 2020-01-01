import express from 'express';
import cors from 'cors';
import { createServer } from 'http';
import { Server } from 'colyseus';
import { monitor } from '@colyseus/monitor';

import { LobbyRoom } from './Game/Network/Rooms/LobbyRoom';

const GAME_SERVER_PORT = Number(process.env.GAME_SERVER_PORT_1242 || 1242);

const app = express();

app.use(cors());
app.use(express.json());

const server = createServer(app);
const gameServer = new Server({ server: server });

gameServer.define('lobby', LobbyRoom);

app.use('/colyseus', monitor(gameServer));

gameServer.listen(GAME_SERVER_PORT);

console.log(`Game server is listening on http://localhost:${ GAME_SERVER_PORT }`);
