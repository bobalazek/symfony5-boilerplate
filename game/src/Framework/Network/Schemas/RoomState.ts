import {
  Schema,
  MapSchema,
  ArraySchema,
  type,
} from '@colyseus/schema';

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

  @type([ ChatMessage ])
  chatMessages = new ArraySchema<ChatMessage>();

  createPlayer(id: string) {
    this.players[id] = new Player();
  }

  removePlayer(id: string) {
    delete this.players[id];
  }

  setPlayerCharacterData(id: string, data: any) {
    if (data.position) {
      this.players[id].character.position.set(data.position);
    }

    if (data.rotation) {
      this.players[id].character.rotation.set(data.rotation);
    }
  }
}
