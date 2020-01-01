import * as BABYLON from 'babylonjs';

import { AbstractLevel } from '../Level/AbstractLevel';
import { AbstractController } from '../Gameplay/Controller/AbstractController';
import { InputManager } from '../Input/InputManager';
import { AbstractInputBindings } from '../Input/InputHelpers';

export class GameManager {
    public static canvas: HTMLCanvasElement;
    public static engine: BABYLON.Engine;

    public static debug: boolean;
    public static inputManager: InputManager;
    public static activeLevel: AbstractLevel;
    public static controller: AbstractController;

    public static boot(config: GameConfigInterface) {
      if (!BABYLON.Engine.isSupported()) {
        alert('Sorry, but your device is unable to run this game. Please use a more modern browser.');

        return false;
      }

      this.debug = config.debug;

      this.canvas = document.getElementById('game') as HTMLCanvasElement;
      this.engine = new BABYLON.Engine(this.canvas, true);

      const inputBindings = new (<any>config.inputBindings)();
      this.inputManager = new InputManager(inputBindings);
      this.activeLevel = new (<any>config.startupLevel)();
      this.controller = new (<any>config.controller)();

      this.activeLevel.onPostLoad(() => {
        this.inputManager.watch();
        this.controller.start();

        this.engine.runRenderLoop(() => {
          this.inputManager.update();
          this.activeLevel.render();
        });
      });

      window.addEventListener('resize', () => {
        this.engine.resize();
      });
    }

    public static switchLevel(level: typeof AbstractLevel) {
      let newActiveLevel = new (<any>level)();
      newActiveLevel.onLevelReady(() => {
        this.activeLevel = newActiveLevel;
      });
    }
}

export interface GameConfigInterface {
  debug: boolean;
  startupLevel: typeof AbstractLevel;
  inputBindings: typeof AbstractInputBindings;
  controller: typeof AbstractController;
}
