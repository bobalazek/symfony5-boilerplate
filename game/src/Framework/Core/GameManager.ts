import * as BABYLON from 'babylonjs';

import { InputManager } from './InputManager';
import { AbstractPlayerController } from '../Gameplay/PlayerController';
import { AbstractPlayerInputBindings } from '../Gameplay/PlayerInputBindings';
import { AbstractScene } from '../Scenes/Scene';

export class GameManager {
  public static canvas: HTMLCanvasElement;
  public static engine: BABYLON.Engine;
  public static scene: BABYLON.Scene;
  public static inputManager: InputManager;

  public static playerController: AbstractPlayerController;

  public static boot(config: GameConfigInterface) {
    this.canvas = <HTMLCanvasElement>document.getElementById('game');
    this.engine = new BABYLON.Engine(this.canvas, true);

    // Input manager
    this.inputManager = new InputManager();
    this.inputManager.setBindings(
      new config.playerInputBindings()
    );
    this.inputManager.bindEvents();

    // Set player controller
    this.playerController = new config.playerController();

    // Load default scene
    let defaultScene = new config.defaultScene();
    defaultScene.load();

    // Main render loop
    this.engine.runRenderLoop(() => {
      this.inputManager.update();
      setTimeout(() => { // TODO: any better solution?
        this.inputManager.afterUpdate();
      });

      if (this.scene) {
        this.scene.render();
      }
    });

    // Resize event
    window.addEventListener('resize', () => {
      this.engine.resize();
    });

    // Blur event
    window.addEventListener('blur', () => {
      this.inputManager.unbindEvents();
    });
  }

  public static setScene(scene: BABYLON.Scene) {
    this.scene = scene;
  }
}

export interface GameConfigInterface {
  defaultScene: new () => AbstractScene;
  playerController: new () => AbstractPlayerController;
  playerInputBindings: new () => AbstractPlayerInputBindings;
}
