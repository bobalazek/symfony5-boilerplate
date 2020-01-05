import {
  Schema,
  MapSchema,
  ArraySchema,
  type,
} from '@colyseus/schema';

import { Player } from './Player';
import { ChatMessage } from './ChatMessage';

export class LobbyRoomState extends Schema {
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
}
