import express from 'express';
import cors from 'cors';
import { createServer } from 'http';
import { Server } from 'colyseus';
import { monitor } from '@colyseus/monitor';

import { LobbyRoom } from './Game/Network/Rooms/LobbyRoom';

import {
  GAME_SERVER_HOST,
  GAME_SERVER_PORT,
} from './Game/Config';

const app = express();

app.use(cors());
app.use(express.json());

const server = createServer(app);
const gameServer = new Server({ server: server });

gameServer.define('lobby', LobbyRoom);

app.use('/colyseus', monitor(gameServer));

gameServer.listen(GAME_SERVER_PORT);

console.log(`Game server is listening on http://${GAME_SERVER_HOST}:${GAME_SERVER_PORT}`);
