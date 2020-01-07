import {
  Schema,
  type,
} from '@colyseus/schema';

export class ChatMessage extends Schema {
  @type("string")
  playerSessionId: string;

  @type("string")
  text: string;
}
