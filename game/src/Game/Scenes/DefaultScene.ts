import * as BABYLON from 'babylonjs';

import {
  GameManager,
  SceneInterface,
} from '../Core/GameManager';

export class DefaultScene implements SceneInterface {
  load() {
    let scene = new BABYLON.Scene(GameManager.engine);

    scene.createDefaultCamera(true, true, true);
    scene.createDefaultEnvironment();

    GameManager.setScene(scene);
  }
}
