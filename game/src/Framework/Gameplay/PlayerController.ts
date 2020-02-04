import {
  TransformNode,
  Vector2,
  Vector3,
  ArcRotateCamera,
} from 'babylonjs';

import { GameManager } from '../Core/GameManager';

export interface PlayerControllerInterface {
  start(): void;
  update(): void;
  posessTransformNode(transformNode: TransformNode): void;
}

export class AbstractPlayerController implements PlayerControllerInterface {
  public start() {}
  public update() {}
  public posessTransformNode(transformNode: TransformNode) {}
}

export class ThirdPersonPlayerController extends AbstractPlayerController {
  public posessedTransformNode: TransformNode;

  private readonly _forward = new Vector3(0, 0, 1);
  private readonly _forwardInverted = new Vector3(0, 0, -1);
  private readonly _right = new Vector3(1, 0, 0);
  private readonly _rightInverted = new Vector3(-1, 0, 0);

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
    let inputLocation = Vector2.Zero();

    if (inputAxes['moveForward'] !== 0) {
      inputLocation.addInPlace(
        new Vector2(0, inputAxes['moveForward'])
      );
    }

    if (inputAxes['moveRight'] !== 0) {
      inputLocation.addInPlace(
        new Vector2(inputAxes['moveRight'], 0)
      );
    }

    // Rotation
    let inputRotation = Vector2.Zero();

    if (inputAxes['lookRight'] !== 0) {
      inputRotation.addInPlace(
        new Vector2(inputAxes['lookRight'], 0)
      );
    }

    if (inputAxes['lookUp'] !== 0) {
      inputRotation.addInPlace(
        new Vector2(0, inputAxes['lookUp'])
      );
    }

    /***** Mesh & camera update *****/
    if (this.posessedTransformNode) {
      const camera = <ArcRotateCamera>GameManager.gameScene.scene.activeCamera;

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
        const cameraRight = Vector3.TransformNormal(
          GameManager.gameScene.scene.useRightHandedSystem ? this._rightInverted : this._right,
          camera.getWorldMatrix()
        ).normalize().scaleInPlace(inputLocation.x);
        const cameraForward = Vector3.TransformNormal(
          GameManager.gameScene.scene.useRightHandedSystem ? this._forwardInverted : this._forward,
          camera.getWorldMatrix()
        ).normalize().scaleInPlace(inputLocation.y);
        const direction = new Vector3(
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

  public posessTransformNode(transformNode: TransformNode) {
    this.posessedTransformNode = transformNode;

    const camera = <ArcRotateCamera>GameManager.gameScene.scene.activeCamera;
    camera.lockedTarget = this.posessedTransformNode;
  }
}
