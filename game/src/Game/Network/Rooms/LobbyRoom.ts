import * as http from 'http';
import { Room, Client } from 'colyseus';

import { LobbyRoomState } from '../Schemas/LobbyRoomState';

export class LobbyRoom extends Room {
  onCreate (options: any) {
    console.log('LobbyRoom created!', options);

    this.setState(new LobbyRoomState());
  }

  onJoin (client: Client, options: any, auth: any) {
    this.state.createPlayer(client.sessionId);
  }

  onLeave (client: Client, consented: boolean) {
    this.state.removePlayer(client.sessionId);
  }

  onMessage (client: Client, message: any) {
    console.log('LobbyRoom received message from', client.sessionId, ':', message);
  }

  onDispose () {
    console.log('Dispose LobbyRoom');
  }
}
