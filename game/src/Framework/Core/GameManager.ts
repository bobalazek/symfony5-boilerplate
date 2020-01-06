import * as BABYLON from 'babylonjs';

import { PlayerController } from './PlayerController';
import { AbstractScene } from '../Scenes/AbstractScene';

export class GameManager {
  public static canvas: HTMLCanvasElement;
  public static engine: BABYLON.Engine;
  public static scene: BABYLON.Scene;
  public static playerController: PlayerController;

  public static boot(config: GameConfigInterface) {
    this.canvas = document.getElementById('game') as HTMLCanvasElement;
    this.engine = new BABYLON.Engine(this.canvas, true);
    this.playerController = new PlayerController();

    // Load default scene
    let defaultScene = new config.defaultScene();
    defaultScene.load();

    // Main render loop
    this.engine.runRenderLoop(() => {
      if (this.scene) {
        this.scene.render();
      }
    });

    // Resize event
    window.addEventListener('resize', () => {
      this.engine.resize();
    });
  }

  public static setScene(scene: BABYLON.Scene) {
    this.scene = scene;
  }
}

export interface GameConfigInterface {
  defaultScene: new () => AbstractScene;
}
