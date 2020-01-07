import * as http from 'http';
import { Room, Client } from 'colyseus';

import { NetworkSerializer } from '../../../Framework/Network/NetworkSerializer';
import { NetworkConstants } from '../../../Framework/Network/NetworkConstants';
import { LobbyRoomState } from '../Schemas/LobbyRoomState';

export class LobbyRoom extends Room {
  onCreate(options: any) {
    console.log('LobbyRoom created!', options);

    this.setState(new LobbyRoomState());
  }

  onJoin(client: Client, options: any, auth: any) {
    this.state.createPlayer(client.sessionId);
  }

  onLeave(client: Client, consented: boolean) {
    this.state.removePlayer(client.sessionId);
  }

  onMessage(client: Client, message: any) {
    if (message[0] === NetworkConstants.PLAYER_TRANSFORM_UPDATE) {
      this.state.setPlayerCharacterData(
        client.sessionId,
        NetworkSerializer.deserializeTransformNode(message[1])
      );
    }
  }

  onDispose() {
    console.log('Dispose LobbyRoom.');
  }
}
