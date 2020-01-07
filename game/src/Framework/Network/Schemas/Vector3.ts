import {
  Schema,
  type,
} from '@colyseus/schema';

export class Vector3 extends Schema {
  @type("float32")
  x: number = 0;

  @type("float32")
  y: number = 0;

  @type("float32")
  z: number = 0;

  set(object: any) {
    this.x = object.x;
    this.y = object.y;
    this.z = object.z;
  }
}
