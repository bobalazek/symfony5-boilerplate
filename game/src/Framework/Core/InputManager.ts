import {
  InputModeEnum,
  InputBindingsInterface,
} from '../Input/InputConstants';
import { InputKeyboard } from '../Input/InputKeyboard';
import { InputMouse } from '../Input/InputMouse';
import { InputDeviceOrientation } from '../Input/InputDeviceOrientation';
import { InputGamepad } from '../Input/InputGamepad';
import { InputGamepadManager } from '../Input/InputGamepadManager';

export class InputManager {
  public bindings: InputBindingsInterface;
  public mode: InputModeEnum = InputModeEnum.KeyboardAndMouse;
  public axes: { [key: string]: number } = {};
  public actions: { [key: string]: boolean } = {};
  public keyboard: InputKeyboard;
  public mouse: InputMouse;
  public deviceOrientation: InputDeviceOrientation;
  public gamepadManager: InputGamepadManager;
  public gamepads: Array<InputGamepad> = [];
  public forcePointerLock: boolean = false;

  constructor() {
    this.keyboard = new InputKeyboard();
    this.mouse = new InputMouse();
    this.deviceOrientation = new InputDeviceOrientation();
    this.gamepadManager = new InputGamepadManager();
  }

  public setBindings(bindings: InputBindingsInterface) {
    this.bindings = bindings;

    this.keyboard.setBindings(bindings);
    this.mouse.setBindings(bindings);
    this.deviceOrientation.setBindings(bindings);
    this.gamepadManager.setBindings(bindings);

    this.reset();
  }

  public bindEvents() {
    this.keyboard.bindEvents();
    this.mouse.bindEvents();
    this.deviceOrientation.bindEvents();
    this.gamepadManager.bindEvents();
  }

  public unbindEvents() {
    this.keyboard.unbindEvents();
    this.mouse.unbindEvents();
    this.deviceOrientation.unbindEvents();
    this.gamepadManager.unbindEvents();
  }

  public update() {
    this.keyboard.update();
    this.mouse.update();
    this.deviceOrientation.update();
    this.gamepadManager.update();
  }

  public afterRender() {
    this.reset();
  }

  public setMode(mode: InputModeEnum) {
    this.mode = mode;

    this.reset();
  }

  public setAxis(axis: string, scale: number) {
    this.axes[axis] = scale;
  }

  public addToAxis(axis: string, value: number) {
    this.axes[axis] += value;
  }

  public setAction(action: string, value: boolean) {
    this.actions[action] = value;
  }

  public setGamepad(index: number, gamepad: InputGamepad) {
    this.gamepads[index] = gamepad;
  }

  public setForcePointerLock(value: boolean) {
    this.forcePointerLock = value;
  }

  public reset() {
    this.axes = {};
    const axesKeys = Object.keys(this.bindings.axisMappings);
    for (let i = 0; i < axesKeys.length; i++) {
      this.axes[axesKeys[i]] = 0;
    }

    this.actions = {};
    const actionsKeys = Object.keys(this.bindings.actionMappings);
    for (let i = 0; i < actionsKeys.length; i++) {
      this.actions[actionsKeys[i]] = false;
    }
  }
}
