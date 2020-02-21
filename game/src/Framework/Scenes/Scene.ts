import {
  Scene,
  Observable,
  ArcRotateCamera,
  Vector3,
  HemisphericLight,
  Mesh,
  MeshBuilder,
  StandardMaterial,
  Texture,
} from 'babylonjs';
import { SkyMaterial } from 'babylonjs-materials';

import { ControllerInterface } from '../Gameplay/Controller';
import { GameManager } from '../Core/GameManager';

export interface SceneInterface {
  babylonScene: Scene;
  afterLoadObservable: Observable<SceneInterface>;
  controller: ControllerInterface;
  setController(controller: ControllerInterface): void;
  start(): void;
  load(): Promise<any>;
  update(): void;
}

export abstract class AbstractScene implements SceneInterface {
  public babylonScene: Scene;
  public afterLoadObservable = new Observable<SceneInterface>();
  public controller: ControllerInterface;

  setController(controller: ControllerInterface) {
    this.controller = controller;

    this.controller.start();
  }

  start() {
    this.babylonScene = new Scene(GameManager.engine);
  }

  load() {
    return new Promise((resolve) => {
      // Show preloader
      GameManager.engine.displayLoadingUI();

      // Prepare scene
      this.prepareScene();

      // Hide preloader
      GameManager.engine.hideLoadingUI();

      resolve(this);
    });
  }

  update() {
    this.controller.update();
  }

  /***** Helpers *****/
  prepareScene() {
    this.prepareCamera();
    this.prepareLights();
    this.prepareEnvironment();
  }

  prepareCamera() {
    let camera = new ArcRotateCamera(
      'camera',
      Math.PI / -2,
      Math.PI / 3,
      10,
      Vector3.Zero(),
      this.babylonScene
    );

    camera.upperBetaLimit = Math.PI / 2;
    camera.lowerRadiusLimit = 10;
    camera.upperRadiusLimit = 20;

    this.babylonScene.activeCamera = camera;
  }

  prepareLights() {
    new HemisphericLight(
      'light',
      new Vector3(0, 1, 0),
      this.babylonScene
    );
  }

  prepareEnvironment() {
    // Skybox
    let skybox = Mesh.CreateBox('skybox', 1024, this.babylonScene);
    var skyboxMaterial = new SkyMaterial('skyboxMaterial', this.babylonScene);
    skyboxMaterial.backFaceCulling = false;
    skyboxMaterial.useSunPosition = true;
    skyboxMaterial.sunPosition = new Vector3(0, 100, 0);
    skybox.material = skyboxMaterial;

    // Ground
    let ground = MeshBuilder.CreateGround('ground', {
      width: 1024,
      height: 1024,
    });
    let groundMaterial = new StandardMaterial('groundMaterial', this.babylonScene);
    let groundTexture = new Texture('/static/textures/ground_diffuse.jpg', this.babylonScene);
    groundTexture.uScale = groundTexture.vScale = 128;
    groundMaterial.diffuseTexture = groundTexture;
    ground.material = groundMaterial;
  }
}
