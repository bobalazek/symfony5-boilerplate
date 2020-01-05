import {
  Schema,
  type,
} from '@colyseus/schema';

export class Vector3 extends Schema {
  @type("int32")
  x: number = 0;

  @type("int32")
  y: number = 0;

  @type("int32")
  z: number = 0;
}
