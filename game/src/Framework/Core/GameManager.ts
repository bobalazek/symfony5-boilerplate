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
    this.engine = new BABYLON.Engine(
      this.canvas,
      true,
      config.engineOptions,
      true
    );

    // Input manager
    this.inputManager = new InputManager();
    if (config.playerInputBindings) {
      this.inputManager.setBindings(
        new config.playerInputBindings()
      );
    }
    this.inputManager.bindEvents();
    const inputManagerEnabled = this.inputManager.enabled;

    // Set player controller
    this.playerController = new config.playerController();
    this.playerController.start();

    // Load default scene
    let defaultScene = new config.defaultScene();
    defaultScene.load();

    // Main render loop
    this.engine.runRenderLoop(() => {
      if (!this.scene) {
        return;
      }

      if (inputManagerEnabled) {
        this.inputManager.update();
      }

      this.playerController.update();

      this.scene.render();

      if (inputManagerEnabled) {
        this.inputManager.afterRender();
      }
    });

    /***** Events *****/
    window.addEventListener('resize', () => {
      this.engine.resize();
    });

    window.addEventListener('blur', () => {
      if (inputManagerEnabled) {
        this.inputManager.unbindEvents();
      }
    });
  }

  public static setScene(scene: BABYLON.Scene) {
    this.scene = scene;
  }
}

export interface GameConfigInterface {
  engineOptions: BABYLON.EngineOptions;
  defaultScene: new () => AbstractScene;
  playerController: new () => AbstractPlayerController;
  playerInputBindings?: new () => AbstractPlayerInputBindings;
}
