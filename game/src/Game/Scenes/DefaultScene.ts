import * as BABYLON from 'babylonjs';

import {
  GameManager,
  SceneInterface,
} from '../Core/GameManager';

export class DefaultScene implements SceneInterface {
  load() {
    GameManager.engine.displayLoadingUI();

    let scene = new BABYLON.Scene(GameManager.engine);

    scene.createDefaultCameraOrLight(true, true, true);
    scene.createDefaultEnvironment();

    let camera = <BABYLON.ArcRotateCamera> scene.activeCamera;
    camera.alpha = Math.PI / 3;
    camera.beta = Math.PI / 3;
    camera.radius = 5;

    let box = BABYLON.MeshBuilder.CreateBox('box', {});

    GameManager.setScene(scene);

    GameManager.engine.hideLoadingUI();
  }
}
