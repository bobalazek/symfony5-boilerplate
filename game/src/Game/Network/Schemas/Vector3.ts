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

  set(object: any) {
    this.x = object.x;
    this.y = object.y;
    this.z = object.z;
  }
}
