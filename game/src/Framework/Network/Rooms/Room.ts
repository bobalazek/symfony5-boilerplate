import { Room, Client } from 'colyseus';

import { NetworkSerializer } from '../NetworkSerializer';
import { NetworkConstants } from '../NetworkConstants';
import { RoomState } from '../Schemas/RoomState';

export abstract class AbstractRoom extends Room {
  onCreate(options: any) {
    console.log('Room created!', options);

    this.setState(new RoomState());

    this.onMessageTransformMovementUpdate = this.onMessageTransformMovementUpdate.bind(this);

    this.onMessage(
      NetworkConstants.TRANSFORM_MOVEMENT_UPDATE,
      this.onMessageTransformMovementUpdate
    );
  }

  onJoin(client: Client, options: any, auth: any) {
    this.state.addPlayer(client.sessionId, 'John Doe');
  }

  async onLeave(client: Client, consented: boolean) {
    this.state.players[client.sessionId].connected = false;

    try {
      if (consented) {
          throw new Error('Consented leave');
      }

      await this.allowReconnection(client, 10);

      this.state.players[client.sessionId].connected = true;
    } catch (e) {
      this.state.removePlayer(client.sessionId);
    }
  }

  onMessageTransformMovementUpdate(client: Client, message: any) {
    const id = message[0];
    const transformMatrix = NetworkSerializer.deserializeTransformNode(message[1]);

    if (typeof this.state.transforms[id] === 'undefined') {
      this.state.addTransform(id, transformMatrix);
    } else {
      this.state.setTransform(id, transformMatrix);
    }
  }

  onDispose() {
    console.log('Dispose Room.');
  }
}
