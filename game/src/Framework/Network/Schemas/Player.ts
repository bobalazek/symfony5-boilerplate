import {
  Schema,
  type,
} from '@colyseus/schema';

export class Player extends Schema {
  @type("string")
  sessionId: string;

  @type("string")
  name: string;

  @type("boolean")
  connected: boolean = true;

  @type("boolean")
  ready: boolean = false;

  @type("int16")
  ping: number = -1;

  @type("string")
  posessedTransformNodeId: string;

  set(player: any) {
    this.sessionId = player.sessionId;
    this.name = player.name;
    this.ready = player.ready;
    this.ping = player.ping;
    this.posessedTransformNodeId = player.posessedTransformNodeId;
  }
}
