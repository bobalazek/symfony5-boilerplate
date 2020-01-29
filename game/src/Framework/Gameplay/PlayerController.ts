import * as BABYLON from 'babylonjs';

import { GameManager } from '../Core/GameManager';

export abstract class AbstractPlayerController {
  public posessedTransformNode: BABYLON.TransformNode;

  public start() {
    GameManager.inputManager.setForcePointerLock(true);
  }

  public update() {
    /***** Input *****/
    const inputAxes = GameManager.inputManager.axes;

    // Location
    let inputLocation = BABYLON.Vector2.Zero();

    if (inputAxes['moveForward'] !== 0) {
      inputLocation.addInPlace(
        new BABYLON.Vector2(0, inputAxes['moveForward'])
      );
    }

    if (inputAxes['moveRight'] !== 0) {
      inputLocation.addInPlace(
        new BABYLON.Vector2(inputAxes['moveRight'], 0)
      );
    }

    // Rotation
    let inputRotation = BABYLON.Vector2.Zero();

    if (inputAxes['lookRight'] !== 0) {
      inputRotation.addInPlace(
        new BABYLON.Vector2(inputAxes['lookRight'], 0)
      );
    }

    if (inputAxes['lookUp'] !== 0) {
      inputRotation.addInPlace(
        new BABYLON.Vector2(0, inputAxes['lookUp'])
      );
    }

    /***** Mesh & camera update *****/
    if (this.posessedTransformNode) {
      const camera = <BABYLON.ArcRotateCamera>GameManager.scene.activeCamera;

      if (inputRotation !== BABYLON.Vector2.Zero()) {
        camera.alpha += inputRotation.x * -0.002;
        camera.beta += inputRotation.y * -0.0005;
      }

      if (inputLocation !== BABYLON.Vector2.Zero()) {
        const direction = new BABYLON.Vector3(
          inputLocation.x,
          0,
          inputLocation.y,
        ).normalize();

        this.posessedTransformNode.translate(
          direction,
          0.1,
          BABYLON.Space.LOCAL
        );
      }
    }
  }

  public posessTransformNode(transformNode: BABYLON.TransformNode) {
    this.posessedTransformNode = transformNode;

    const camera = <BABYLON.ArcRotateCamera>GameManager.scene.activeCamera;
    camera.lockedTarget = this.posessedTransformNode;
  }
}
