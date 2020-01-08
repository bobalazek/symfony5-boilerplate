import * as BABYLON from 'babylonjs';
import  * as Colyseus from 'colyseus.js';

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
    this.scene.createDefaultCameraOrLight(true, true, true);
    this.scene.createDefaultEnvironment();

    let camera = <BABYLON.ArcRotateCamera>this.scene.activeCamera;

    camera.alpha = Math.PI / 3;
    camera.beta = Math.PI / 3;
    camera.radius = 10;
    camera.upperBetaLimit = Math.PI / 2;

    BABYLON.MeshBuilder.CreateBox('box', {});
  }
}
