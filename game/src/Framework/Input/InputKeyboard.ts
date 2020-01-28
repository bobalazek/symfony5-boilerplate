import { Key as KeyboardKey } from 'ts-keycode-enum';

import { GameManager } from '../Core/GameManager';
import {
  InputBindingsInterface,
  InputDeviceInterface,
  InputMappingAxisKeyboardDataInterface,
  InputMappingActionKeyboardDataInterface,
  InputModeEnum,
  InputDeviceEnum,
} from './InputConstants';

export class InputKeyboard implements InputDeviceInterface {
  private _bindings: InputBindingsInterface;
  private _axesKeyScaleMap: { [key: number]: { axis: string, scale: number } } = {}; // ex.: [ 68: { axis: 'moveForward', scale: 1 } ]
  private _actionsMap: { [key: number]: string } = {}; // ex.: { 68: moveForward }
  private _keysPressed: { [key: number]: number } = {}; // ex.: { 68: 123456789 /* unix time */ }

  public setBindings(bindings: InputBindingsInterface) {
    this._bindings = bindings;

    // Attach actions
    this._actionsMap = {};
    for (const action in this._bindings.actions) {
      const mappings = this._bindings.actions[action];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Keyboard) {
          const mappingData = <InputMappingActionKeyboardDataInterface>mappings[i].data;
          this._actionsMap[mappingData.keyCode] = action;
        }
      }
    }

    // Attach axes
    this._axesKeyScaleMap = {};
    for (const axis in this._bindings.axes) {
      const mappings = this._bindings.axes[axis];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Keyboard) {
          const mappingData = <InputMappingAxisKeyboardDataInterface>mappings[i].data;
          this._axesKeyScaleMap[mappingData.keyCode] = {
            axis: axis,
            scale: mappingData.scale,
          };
        }
      }
    }
  }

  public bindEvents() {
    GameManager.canvas.addEventListener(
      'keydown',
      this._onHandle.bind(this),
      false
    );
    GameManager.canvas.addEventListener(
      'keyup',
      this._onHandle.bind(this),
      false
    );
  }

  public unbindEvents() {
    GameManager.canvas.removeEventListener(
      'keydown',
      this._onHandle.bind(this),
      false
    );
    GameManager.canvas.removeEventListener(
      'keyup',
      this._onHandle.bind(this),
      false
    );
  }

  public update() {
    let affectedAxes = {};
    for (let keyCode in this._keysPressed) {
      if (this._axesKeyScaleMap[keyCode]) {
        const axis = this._axesKeyScaleMap[keyCode].axis;
        const scale = this._axesKeyScaleMap[keyCode].scale;

        if (typeof affectedAxes[axis] === 'undefined') {
          affectedAxes[axis] = { min: 0, max: 0 };
        }

        if (scale < affectedAxes[axis].min) {
          affectedAxes[axis].min = scale;
        }
        if (scale > affectedAxes[axis].max) {
          affectedAxes[axis].max = scale;
        }
      }
    }

    for (const axis in this._bindings.axes) {
      let value = 0.0;

      if (typeof affectedAxes[axis] !== 'undefined') {
        const affectedAxis = affectedAxes[axis];

        if (affectedAxis.min !== 0 || affectedAxis.max !== 0) {
          if (affectedAxis.min !== 0 && affectedAxis.max === 0) {
            value = affectedAxis.min;
          } else if (affectedAxis.min === 0 && affectedAxis.max !== 0) {
            value = affectedAxis.max;
          }
        }
      }

      GameManager.inputManager.setAxis(axis, value);
    }
  }

  public isKeyPressed(keyCode: number) {
    return typeof this._keysPressed[keyCode] !== 'undefined';
  }

  private _onHandle(e: KeyboardEvent) {
    const isPressed = e.type === 'keydown';
    const keyCode = e.keyCode;
    const action = typeof this._actionsMap[keyCode] !== 'undefined'
        ? this._actionsMap[keyCode]
        : null;

    if (
      isPressed &&
      GameManager.inputManager.mode !== InputModeEnum.KeyboardAndMouse
    ) {
      GameManager.inputManager.setMode(
        InputModeEnum.KeyboardAndMouse
      );
    }

    if (action !== null) {
      GameManager.inputManager.setAction(action, isPressed);
    }

    if (isPressed) {
      this._keysPressed[keyCode] = (new Date()).getTime();
    } else {
      if (typeof this._keysPressed[keyCode] !== 'undefined') {
        delete this._keysPressed[keyCode];
      }
    }
  }
}
