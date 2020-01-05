import * as http from 'http';
import { Room, Client } from 'colyseus';

import { LobbyRoomState } from '../Schemas/LobbyRoomState';

export class LobbyRoom extends Room {
  constructor() {
    super();

    this.setState(new LobbyRoomState());
  }

  onCreate (options: any) {}

  onAuth (client: Client, options: any, request: http.IncomingMessage) { }

  onJoin (client: Client, options: any, auth: any) {
    console.log(client)
  }

  onMessage (client: Client, message: any) {}

  onLeave (client: Client, consented: boolean) {}

  onDispose () {}
}
