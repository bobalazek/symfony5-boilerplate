import {
  InputModeEnum,
  InputBindingsInterface,
} from '../Input/InputConstants';
import { InputKeyboard } from '../Input/InputKeyboard';
import { InputMouse } from '../Input/InputMouse';
import { InputGamepad } from '../Input/InputGamepad';

export class InputManager {
  public bindings: InputBindingsInterface;
  public mode: InputModeEnum = InputModeEnum.KeyboardAndMouse;
  public axes: { [key: string]: number } = {};
  public actions: { [key: string]: boolean } = {};
  public keyboard: InputKeyboard;
  public mouse: InputMouse;
  public gamepads: Array<InputGamepad>;

  constructor() {
    this.keyboard = new InputKeyboard();
    this.mouse = new InputMouse();
    // TODO: gamepads
  }

  public bindEvents() {
    if (this.keyboard) {
      this.keyboard.bindEvents();
    }

    if (this.mouse) {
      this.mouse.bindEvents();
    }
  }

  public unbindEvents() {
    if (this.keyboard) {
      this.keyboard.unbindEvents();
    }

    if (this.mouse) {
      this.mouse.unbindEvents();
    }
  }

  public update() {
    if (this.keyboard) {
      this.keyboard.update();
    }

    if (this.mouse) {
      this.mouse.update();
    }
  }

  public setBindings(bindings: InputBindingsInterface) {
    this.bindings = bindings;
    this.reset();
  }

  public setMode(mode: InputModeEnum) {
    this.mode = mode;
    this.reset();
  }

  public setAxis(axis: string, scale: number) {
    this.axes[axis] = scale;
  }

  public setAction(action: string, value: boolean) {
    this.actions[action] = value;
  }

  public reset() {
    this.axes = {};
    const axesKeys = Object.keys(this.bindings.axes);
    for (let i = 0; i < axesKeys.length; i++) {
      this.axes[axesKeys[i]] = 0;
    }

    this.actions = {};
    const actionsKeys = Object.keys(this.bindings.actions);
    for (let i = 0; i < actionsKeys.length; i++) {
      this.actions[actionsKeys[i]] = false;
    }
  }
}
