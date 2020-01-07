import {
  Schema,
  type,
} from '@colyseus/schema';
import { Transform } from './Transform';

export class Player extends Transform {
  @type("string")
  sessionId: string;

  @type("string")
  name: string;

  @type("boolean")
  ready: boolean = false;

  @type("uint16")
  ping: number = 0;

  @type(Transform)
  character: Transform = new Transform();
}
