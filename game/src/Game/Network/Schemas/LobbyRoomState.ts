import { Schema, ArraySchema, type } from '@colyseus/schema';

import { Player } from './Player';
import { ChatMessage } from './ChatMessage';

export class LobbyRoomState extends Schema {
  @type([ Player ])
  players = new ArraySchema<Player>();

  @type([ ChatMessage ])
  chatMessages = new ArraySchema<ChatMessage>();
}
