import { Room, Client } from 'colyseus';

import { NetworkSerializer } from '../NetworkSerializer';
import { NetworkConstants } from '../NetworkConstants';
import { RoomState } from '../Schemas/RoomState';

export abstract class AbstractRoom extends Room {
  onCreate(options: any) {
    console.log('Room created!', options);

    this.setState(new RoomState());
  }

  onJoin(client: Client, options: any, auth: any) {
    this.state.addPlayer(client.sessionId, 'John Doe');
  }

  onLeave(client: Client, consented: boolean) {
    this.state.removePlayer(client.sessionId);
  }

  onMessage(client: Client, message: any) {
    const sessionId = client.sessionId;
    const action = message[0];

    if (action === NetworkConstants.TRANSFORM_MOVEMENT_UPDATE) {
      const id = message[1][0];
      const transformMatrix = NetworkSerializer.deserializeTransformNode(message[1][1]);

      if (typeof this.state.transforms[id] === 'undefined') {
        this.state.addTransform(id, transformMatrix);
      } else {
        this.state.setTransform(id, transformMatrix);
      }
    } else if (action === NetworkConstants.PLAYER_TRANSFORM_NODE_ID_SET) {
      const id = message[1];

      this.state.players[sessionId].posessedTransformNodeId = id;
    }
  }

  onDispose() {
    console.log('Dispose Room.');
  }
}
