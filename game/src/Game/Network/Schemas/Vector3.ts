import {
  Schema,
  type,
} from '@colyseus/schema';

export class Vector3 extends Schema {
  @type("int32")
  x: number;

  @type("int32")
  y: number;

  @type("int32")
  z: number;
}
