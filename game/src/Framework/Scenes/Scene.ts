import * as BABYLON from 'babylonjs';

import { GameManager } from '../Core/GameManager';

export interface SceneInterface {
    load: () => void;
}

export abstract class AbstractScene implements SceneInterface {
  public scene: BABYLON.Scene;

  load() {
    // Show preloader
    GameManager.engine.displayLoadingUI();

    // Prepare scene
    this.prepareScene();

    // Set scene & hide preloader
    GameManager.setScene(this.scene);
    GameManager.engine.hideLoadingUI();
  }

  prepareScene() {
    this.scene = new BABYLON.Scene(GameManager.engine);
    this.prepareCamera();
    this.prepareLights();
    this.prepareEnvironment();

    let camera = <BABYLON.ArcRotateCamera>this.scene.activeCamera;

    camera.alpha = Math.PI / 3;
    camera.beta = Math.PI / 3;
    camera.radius = 10;
    camera.upperBetaLimit = Math.PI / 2;

    BABYLON.MeshBuilder.CreateBox('box', {});
  }

  prepareCamera() {
    let camera = new BABYLON.ArcRotateCamera(
      'camera',
      Math.PI / -2,
      Math.PI / 3,
      10,
      BABYLON.Vector3.Zero(),
      this.scene
    );

    camera.upperBetaLimit = Math.PI / 2;

    this.scene.activeCamera = camera;
  }

  prepareLights() {
    new BABYLON.HemisphericLight(
      'light',
      new BABYLON.Vector3(0, 1, 0),
      this.scene
    );
  }

  prepareEnvironment() {
    let ground = BABYLON.MeshBuilder.CreateGround('ground', {
      width: 128,
      height: 128,
    });
    let groundMaterial = new BABYLON.StandardMaterial('groundMaterial', this.scene);
    let groundTexture = new BABYLON.Texture('/static/images/game/ground.jpg', this.scene);
    groundTexture.uScale = groundTexture.vScale = 16;
    groundMaterial.diffuseTexture = groundTexture;
    ground.material = groundMaterial;
  }
}
