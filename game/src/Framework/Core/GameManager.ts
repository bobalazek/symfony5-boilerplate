import {
  Engine,
  EngineOptions,
  Scene,
} from 'babylonjs';

import { InputManager } from './InputManager';
import { PlayerControllerInterface } from '../Gameplay/PlayerController';
import { PlayerInputBindingsInterface } from '../Gameplay/PlayerInputBindings';
import { SceneInterface } from '../Scenes/Scene';

export class GameManager {
  public static canvas: HTMLCanvasElement;
  public static engine: Engine;
  public static babylonScene: Scene;

  public static scene: SceneInterface;
  public static inputManager: InputManager;
  public static playerController: PlayerControllerInterface;

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

    // Game scene
    this.scene = new config.defaultScene();

    // Prepare game scene & controller
    let playerController = new config.playerController();
    this.scene.setPlayerController(playerController);
    this.scene.start();

    // Start scene loading
    this.scene.load()
      .then((scene: SceneInterface) => {
        this.setBabylonScene(scene.babylonScene);

        scene.afterLoadObservable.notifyObservers(scene);
      });

    // Main render loop
    this.engine.runRenderLoop(() => {
      if (!this.babylonScene) {
        return;
      }

      this.inputManager.update();
      this.scene.update();
      this.babylonScene.render();
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

  public static setBabylonScene(scene: Scene) {
    this.babylonScene = scene;
  }
}

export interface GameConfigInterface {
  engineOptions: EngineOptions;
  defaultScene: new () => SceneInterface;
  playerController: new () => PlayerControllerInterface;
  playerInputBindings?: new () => PlayerInputBindingsInterface;
}
