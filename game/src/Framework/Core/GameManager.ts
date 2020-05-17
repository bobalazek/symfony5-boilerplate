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

  public static parameters: any;

  public static boot(config: GameConfigInterface, parameters?: any) {
    this.canvas = <HTMLCanvasElement>document.getElementById(config.canvasElementId);
    this.engine = new Engine(
      this.canvas,
      true,
      config.engineOptions,
      true
    );

    // Parameters
    this.parameters = parameters;

    // Input manager
    this.inputManager = new InputManager();
    if (config.inputBindings) {
      this.inputManager.setBindings(
        new config.inputBindings()
      );
    }
    this.inputManager.bindEvents();

    // Scene & controller
    this.scene = new config.defaultScene();

    this.setController(new config.controller());
    this.setScene(this.scene);

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

    if (config.disableRightClick) {
      window.addEventListener('contextmenu', (e) => {
        e.preventDefault();
      });
    }
  }

  public static setController(controller: ControllerInterface) {
    this.controller = controller;

    if (this.scene) {
      this.scene.setController(this.controller);
    }
  }

  public static setScene(scene: SceneInterface) {
    this.scene = scene;

    this.prepareScene(this.scene);
  }

  public static prepareScene(scene?: SceneInterface) {
    if (!scene) {
      scene = this.scene;
    }

    if (!scene) {
      throw new Error('No scene set');
    }

    scene.setController(this.controller);
    scene.start();

    return new Promise((resolve) => {
      scene.load()
        .then((scene: SceneInterface) => {
          this.setBabylonScene(scene.babylonScene);

          scene.afterLoadObservable.notifyObservers(scene);

          resolve(this);
        });
    });
  }

  public static setBabylonScene(scene: Scene) {
    this.babylonScene = scene;
  }

  public static isSupported(): boolean {
    return Engine.isSupported();
  }
}

export interface GameConfigInterface {
  canvasElementId: string;
  engineOptions: EngineOptions;
  controller: new () => ControllerInterface;
  defaultScene: new () => SceneInterface;
  inputBindings?: new () => InputBindingsInterface;
  disableRightClick?: boolean;
}
