import * as BABYLON from 'babylonjs';

import { InputManager } from './InputManager';
import { AbstractPlayerController } from '../Gameplay/PlayerController';
import { AbstractPlayerInputBindings } from '../Gameplay/PlayerInputBindings';
import { AbstractScene, SceneInterface } from '../Scenes/Scene';

export class GameManager {
  public static canvas: HTMLCanvasElement;
  public static engine: BABYLON.Engine;
  public static scene: BABYLON.Scene;

  public static gameScene: SceneInterface;
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
    const inputManagerBindingsEnabled = this.inputManager.bindingsEnabled;

    // Player controller
    this.playerController = new config.playerController();
    this.playerController.start();

    // Game scene
    this.gameScene = new config.defaultScene();
    this.gameScene.start();
    this.gameScene.load()
      .then((gameScene) => {
        this.setScene(gameScene.scene);

        if (gameScene.afterLoadObservable) {
          gameScene.afterLoadObservable.notifyObservers(gameScene);
        }
      });

    // Main render loop
    this.engine.runRenderLoop(() => {
      if (!this.scene) {
        return;
      }

      if (inputManagerBindingsEnabled) {
        this.inputManager.update();
      }

      this.playerController.update();

      this.scene.render();

      if (inputManagerBindingsEnabled) {
        this.inputManager.afterRender();
      }
    });

    /***** Events *****/
    window.addEventListener('resize', () => {
      this.engine.resize();
    });

    window.addEventListener('focus', () => {
      this.inputManager.bindEvents();
    });

    window.addEventListener('blur', () => {
      this.inputManager.unbindEvents();
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
