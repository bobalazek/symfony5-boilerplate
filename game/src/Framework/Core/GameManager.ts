import {
  Engine,
  EngineOptions,
  Scene,
} from 'babylonjs';

import { InputManager } from './InputManager';
import { AbstractPlayerController } from '../Gameplay/PlayerController';
import { AbstractPlayerInputBindings } from '../Gameplay/PlayerInputBindings';
import { AbstractScene, SceneInterface } from '../Scenes/Scene';

export class GameManager {
  public static canvas: HTMLCanvasElement;
  public static engine: Engine;
  public static scene: Scene;

  public static gameScene: SceneInterface;
  public static inputManager: InputManager;
  public static playerController: AbstractPlayerController;

  public static boot(config: GameConfigInterface) {
    this.canvas = <HTMLCanvasElement>document.getElementById('game');
    this.engine = new Engine(
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

    // Player controller
    this.playerController = new config.playerController();

    // Game scene
    this.gameScene = new config.defaultScene();

    // Prepare game scene & controller
    this.gameScene.start();
    this.playerController.start();

    // Start game scene loading
    this.gameScene.load()
      .then((gameScene) => {
        this.setScene(gameScene.scene);

        gameScene.afterLoadObservable.notifyObservers(gameScene);
      });

    // Main render loop
    this.engine.runRenderLoop(() => {
      if (!this.scene) {
        return;
      }

      this.inputManager.update();
      this.playerController.update();
      this.scene.render();
      this.inputManager.afterRender();
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

  public static setScene(scene: Scene) {
    this.scene = scene;
  }
}

export interface GameConfigInterface {
  engineOptions: EngineOptions;
  defaultScene: new () => AbstractScene;
  playerController: new () => AbstractPlayerController;
  playerInputBindings?: new () => AbstractPlayerInputBindings;
}
