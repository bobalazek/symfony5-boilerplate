import * as BABYLON from 'babylonjs';

export class GameManager {
  public static canvas: HTMLCanvasElement;
  public static engine: BABYLON.Engine;
  public static scene: BABYLON.Scene;

  public config: GameConfigInterface;

  public static boot(config: GameConfigInterface) {
    this.canvas = document.getElementById('game') as HTMLCanvasElement;
    this.engine = new BABYLON.Engine(this.canvas, true);

    // Load default scene
    config.defaultScene.load();

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
    defaultScene: SceneInterface;
}

export interface SceneInterface {
    load: () => void;
}
