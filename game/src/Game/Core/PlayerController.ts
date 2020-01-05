import * as BABYLON from 'babylonjs';

export class PlayerController {
  public posessedTransformNode: BABYLON.TransformNode;

  public posessTransformNode(transformNode: BABYLON.TransformNode) {
    this.posessedTransformNode = transformNode;
  }
}
