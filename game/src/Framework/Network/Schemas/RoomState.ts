import {
  Schema,
  MapSchema,
  type,
} from '@colyseus/schema';

import { Transform } from './Transform';
import { Player } from './Player';

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
  }

  removePlayer(id: string) {
    delete this.players[id];
  }

  addTransform(id: string, transformMatrix: any) {
    let transform = new Transform();
    transform.id = id;
    transform.position.set(transformMatrix.position);
    transform.rotation.set(transformMatrix.rotation);

    this.transforms[id] = transform;
  }

  setTransform(id: string, transformMatrix: any) {
    this.transforms[id].position.set(transformMatrix.position);
    this.transforms[id].rotation.set(transformMatrix.rotation);
  }

  removeTransform(id: string) {
    delete this.transforms[id];
  }
}
