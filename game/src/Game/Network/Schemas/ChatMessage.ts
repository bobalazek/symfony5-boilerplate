import { Schema, type } from '@colyseus/schema';

export class ChatMessage extends Schema {
  @type("string")
  sender: string;

  @type("string")
  text: number;
}
