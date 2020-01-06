import * as BABYLON from 'babylonjs';

import { AbstractPlayerController } from './AbstractPlayerController';
import { AbstractScene } from '../Scenes/AbstractScene';

export class GameManager {
  public static canvas: HTMLCanvasElement;
  public static engine: BABYLON.Engine;
  public static scene: BABYLON.Scene;
  public static playerController: AbstractPlayerController;

  public static boot(config: GameConfigInterface) {
    this.canvas = document.getElementById('game') as HTMLCanvasElement;
    this.engine = new BABYLON.Engine(this.canvas, true);

    // Set player controller
    this.playerController = new config.playerController();

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
  playerController: new () => AbstractPlayerController;
}
