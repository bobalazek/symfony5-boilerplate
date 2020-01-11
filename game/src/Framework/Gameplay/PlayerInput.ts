import { PlayerInputBindingsInterface } from './PlayerInputBindings';

export abstract class AbstractPlayerInput {
  public bindings: PlayerInputBindingsInterface;
  public axes: { [key: string]: number } = {};
  public actions: { [key: string]: boolean } = {};

  constructor(bindings: PlayerInputBindingsInterface) {
    this.bindings = bindings;

    const axesKeys = Object.keys(bindings);
    for (let i = 0; i < axesKeys.length; i++) {
      this.axes[axesKeys[i]] = 0;
    }

    const actionsKeys = Object.keys(bindings);
    for (let i = 0; i < actionsKeys.length; i++) {
      this.axes[actionsKeys[i]] = 0;
    }
  }

  public update() {
    // TODO
  }
}
