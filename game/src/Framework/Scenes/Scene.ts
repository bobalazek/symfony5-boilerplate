import * as BABYLON from 'babylonjs';
import * as BABYLONMATERIALS from 'babylonjs-materials';

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
    camera.lowerRadiusLimit = 10;
    camera.upperRadiusLimit = 20;

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
    // Skybox
    let skybox = BABYLON.Mesh.CreateBox('skybox', 1024, this.scene);
    var skyboxMaterial = new BABYLONMATERIALS.SkyMaterial('skyboxMaterial', this.scene);
    skyboxMaterial.backFaceCulling = false;
    skyboxMaterial.useSunPosition = true;
    skyboxMaterial.sunPosition = new BABYLON.Vector3(0, 100, 0);
    skybox.material = skyboxMaterial;

    // Ground
    let ground = BABYLON.MeshBuilder.CreateGround('ground', {
      width: 1024,
      height: 1024,
    });
    let groundMaterial = new BABYLON.StandardMaterial('groundMaterial', this.scene);
    let groundTexture = new BABYLON.Texture('/static/textures/ground_diffuse.jpg', this.scene);
    groundTexture.uScale = groundTexture.vScale = 128;
    groundMaterial.diffuseTexture = groundTexture;
    ground.material = groundMaterial;
  }
}
