import * as BABYLON from 'babylonjs';

import { GameManager } from '../Core/GameManager';

export interface PlayerControllerInterface {
  start(): void;
  update(): void;
  posessTransformNode(transformNode: BABYLON.TransformNode): void;
}

export class AbstractPlayerController implements PlayerControllerInterface {
  public start() {}
  public update() {}
  public posessTransformNode(transformNode: BABYLON.TransformNode) {}
}

export class ThirdPersonPlayerController extends AbstractPlayerController {
  public posessedTransformNode: BABYLON.TransformNode;

  private _forward = new BABYLON.Vector3(0, 0, 1);
  private _forwardInverted = new BABYLON.Vector3(0, 0, -1);
  private _right = new BABYLON.Vector3(1, 0, 0);
  private _rightInverted = new BABYLON.Vector3(-1, 0, 0);

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

      if (
        inputRotation.x !== 0 ||
        inputRotation.y !== 0
      ) {
        camera.alpha += inputRotation.x * -0.002;
        camera.beta += inputRotation.y * -0.0005;
      }

      if (
        inputLocation.x !== 0 ||
        inputLocation.y !== 0
      ) {
        let cameraRight = BABYLON.Vector3.Normalize(BABYLON.Vector3.TransformNormal(
          GameManager.scene.useRightHandedSystem ? this._rightInverted : this._right,
          camera.getWorldMatrix()
        ));
        cameraRight.normalize().scaleInPlace(inputLocation.x);

        let cameraForward = BABYLON.Vector3.Normalize(BABYLON.Vector3.TransformNormal(
          GameManager.scene.useRightHandedSystem ? this._forwardInverted : this._forward,
          camera.getWorldMatrix()
        ));
        cameraForward.normalize().scaleInPlace(inputLocation.y);

        this.posessedTransformNode.position.addInPlaceFromFloats(
          cameraRight.x + cameraForward.x,
          0,
          cameraRight.z + cameraForward.z
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
