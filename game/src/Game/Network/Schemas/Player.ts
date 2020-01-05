import {
  Schema,
  type,
} from '@colyseus/schema';
import { Vector3 } from './Vector3';

export class Player extends Schema {
  @type("string")
  name: string;

  @type(Vector3)
  position: Vector3 = new Vector3();

  @type(Vector3)
  rotation: Vector3 = new Vector3();
}
