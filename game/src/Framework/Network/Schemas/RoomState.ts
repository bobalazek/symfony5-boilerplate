import {
  Schema,
  MapSchema,
  type,
} from '@colyseus/schema';

import { Transform } from './Transform';
import { Player } from './Player';
import { ChatMessage } from './ChatMessage';

export enum RoomStateStatus {
  PENDING,
  STARTED,
  ENDED,
};

export class RoomState extends Schema {
  @type("uint8")
  status: number = RoomStateStatus.PENDING;

  @type({ map: Player })
  players = new MapSchema<Player>();

  @type({ map: Transform })
  transforms = new MapSchema<Transform>();

  @type({ map: Transform })
  chatMessages = new MapSchema<ChatMessage>();

  addPlayer(id: string, name: string) {
    let player = new Player();

    player.set({
      sessionId: id,
      name: name,
      ready: false,
      ping: 0,
      posessedTransformNodeId: null,
    });

    this.players[id] = player;

    // TODO: spawn point?
    const spawnTransformMatrix = {
      position: {x: 0, y: 0, z: 0},
      rotation: {x: 0, y: 0, z: 0},
    };

    const transformId = 'player_' + id;
    this.addTransform(
      transformId,
      spawnTransformMatrix,
      'player',
      '{}',
      id
    );
  }

  removePlayer(id: string) {
    const player = this.players[id];
    if (player) {
      for (let i = 0; i < this.transforms.length; i++) {
        if (this.transforms[i].sessionId === id) {
          delete this.transforms[i];
        }
      }

      delete this.players[id];
    }
  }

  addTransform(
    id: string,
    transformMatrix: any,
    type: string,
    parameters: string,
    sessionId: string
  ) {
    let transform = new Transform();

    transform.id = id;
    transform.position.set(transformMatrix.position);
    transform.rotation.set(transformMatrix.rotation);
    transform.type = type;
    transform.parameters = parameters;
    transform.sessionId = sessionId;

    this.transforms[id] = transform;
  }

  setTransform(id: string, transformMatrix: any) {
    this.transforms[id].position.set(transformMatrix.position);
    this.transforms[id].rotation.set(transformMatrix.rotation);
  }

  removeTransform(id: string) {
    delete this.transforms[id]; // TODO: set to undefined, as it's faster?
  }
}
