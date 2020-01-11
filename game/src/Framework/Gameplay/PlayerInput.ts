import { PlayerInputBindingsInterface } from './PlayerInputBindings';

export abstract class AbstractPlayerInput {
  public bindings: PlayerInputBindingsInterface;

  constructor(bindings: PlayerInputBindingsInterface) {
    this.bindings = bindings;
  }
}
