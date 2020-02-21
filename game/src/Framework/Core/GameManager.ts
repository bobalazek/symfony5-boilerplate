import {
  Engine,
  EngineOptions,
  Scene,
} from 'babylonjs';

import { InputManager } from './InputManager';
import { ControllerInterface } from '../Gameplay/Controller';
import { InputBindingsInterface } from '../Gameplay/InputBindings';
import { SceneInterface } from '../Scenes/Scene';

export class GameManager {
  public static canvas: HTMLCanvasElement;
  public static engine: Engine;
  public static babylonScene: Scene;

  public static scene: SceneInterface;
  public static inputManager: InputManager;
  public static controller: ControllerInterface;

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
    if (config.inputBindings) {
      this.inputManager.setBindings(
        new config.inputBindings()
      );
    }
    this.inputManager.bindEvents();

    // Game scene
    this.scene = new config.defaultScene();

    // Prepare game scene & controller
    let controller = new config.controller();
    this.scene.setController(controller);
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
  controller: new () => ControllerInterface;
  inputBindings?: new () => InputBindingsInterface;
}
