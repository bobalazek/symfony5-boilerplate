import { Room, Client } from 'colyseus';

import { NetworkSerializer } from '../NetworkSerializer';
import { NetworkConstants } from '../NetworkConstants';
import { RoomState } from '../Schemas/RoomState';

export abstract class AbstractRoom extends Room {
  onCreate(options: any) {
    console.log('Room created!', options);

    this.setState(new RoomState());

    this.onMessageTransformMovementUpdate = this.onMessageTransformMovementUpdate.bind(this);
    this.onMessagePlayerTransformNodeIdSet = this.onMessagePlayerTransformNodeIdSet.bind(this);

    this.onMessage(
      NetworkConstants.TRANSFORM_MOVEMENT_UPDATE,
      this.onMessageTransformMovementUpdate
    );
    this.onMessage(
      NetworkConstants.PLAYER_TRANSFORM_NODE_ID_SET,
      this.onMessagePlayerTransformNodeIdSet
    );
  }

  onJoin(client: Client, options: any, auth: any) {
    this.state.addPlayer(client.sessionId, 'John Doe');
  }

  onLeave(client: Client, consented: boolean) {
    this.state.removePlayer(client.sessionId);
  }

  onMessageTransformMovementUpdate(client: Client, message: any) {
  const sessionId = client.sessionId;

  const id = message[0];
  const transformMatrix = NetworkSerializer.deserializeTransformNode(message[1]);

  if (typeof this.state.transforms[id] === 'undefined') {
    this.state.addTransform(id, transformMatrix);
  } else {
    this.state.setTransform(id, transformMatrix);
  }
}

  onMessagePlayerTransformNodeIdSet(client: Client, message: any) {
    const sessionId = client.sessionId;

    const id = message;

    this.state.players[sessionId].posessedTransformNodeId = id;
  }

  onDispose() {
    console.log('Dispose Room.');
  }
}
