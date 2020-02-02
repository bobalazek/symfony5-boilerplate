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

  private readonly _forward = new BABYLON.Vector3(0, 0, 1);
  private readonly _forwardInverted = new BABYLON.Vector3(0, 0, -1);
  private readonly _right = new BABYLON.Vector3(1, 0, 0);
  private readonly _rightInverted = new BABYLON.Vector3(-1, 0, 0);

  private readonly _cameraAlphaMultiplier: number = -0.002;
  private readonly _cameraBetaMultiplier: number = -0.0003;
  private readonly _cameraRadiusMultiplier: number = 0.01;

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

      if (inputAxes['lookZoom']) {
        camera.radius = camera.radius + (inputAxes['lookZoom'] * this._cameraRadiusMultiplier);
      }

      if (
        inputRotation.x !== 0 ||
        inputRotation.y !== 0
      ) {
        camera.alpha += inputRotation.x * this._cameraAlphaMultiplier;
        camera.beta += inputRotation.y * this._cameraBetaMultiplier;
      }

      // TODO: rotate posessedTransformNode towards the direction it's moving

      if (
        inputLocation.x !== 0 ||
        inputLocation.y !== 0
      ) {
        const cameraRight = BABYLON.Vector3.TransformNormal(
          GameManager.scene.useRightHandedSystem ? this._rightInverted : this._right,
          camera.getWorldMatrix()
        ).normalize().scaleInPlace(inputLocation.x);
        const cameraForward = BABYLON.Vector3.TransformNormal(
          GameManager.scene.useRightHandedSystem ? this._forwardInverted : this._forward,
          camera.getWorldMatrix()
        ).normalize().scaleInPlace(inputLocation.y);
        const direction = new BABYLON.Vector3(
          cameraRight.x + cameraForward.x,
          0,
          cameraRight.z + cameraForward.z
        ).normalize();

        this.posessedTransformNode.position.addInPlaceFromFloats(
          direction.x,
          0,
          direction.z
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
