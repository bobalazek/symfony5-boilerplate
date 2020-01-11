import * as BABYLON from 'babylonjs';

export abstract class AbstractPlayerController {
  public posessedTransformNode: BABYLON.TransformNode;

  public posessTransformNode(transformNode: BABYLON.TransformNode) {
    this.posessedTransformNode = transformNode;
  }
}
