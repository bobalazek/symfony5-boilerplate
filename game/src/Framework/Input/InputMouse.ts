import { GameManager } from '../Core/GameManager';
import {
  InputDeviceInterface,
  InputMappingAxisMouseDataInterface,
  InputMappingActionMouseDataInterface,
  InputDeviceEnum,
  InputModeEnum,
  InputAxisEnum,
  InputMouseButtonEnum,
} from './InputConstants';

export class InputMouse implements InputDeviceInterface {
  private _axesMap: { [key: string]: InputMappingAxisMouseDataInterface } = {}; // ex.: [ moveForward: { axis: 0, scale: 1.0 } ]
  private _actionsMap: { [key: number]: string } = {}; // ex.: [ 0: interact ]; 0 = InputMouseButtonEnum.Left
  private _buttonsPressed: Array<number> = [];
  private _interval: any; // ex.: [ 0: 1 ] // 1 = InputMouseButtonEnum.Middle
  private _intervalTime: number = 50; // after how many miliseconds it should clear the values?

  public bindEvents() {
    this.setActionsAndAxes();

    GameManager.canvas.addEventListener(
      'mousemove',
      this._onHandleMove.bind(this),
      false
    );
    GameManager.canvas.addEventListener(
      'pointermove',
      this._onHandleMove.bind(this),
      false
    );
    GameManager.canvas.addEventListener(
      'mouseup',
      this._onHandleUpDown.bind(this),
      false
    );
    GameManager.canvas.addEventListener(
      'pointerup',
      this._onHandleUpDown.bind(this),
      false
    );
    GameManager.canvas.addEventListener(
      'mousedown',
      this._onHandleUpDown.bind(this),
      false
    );
    GameManager.canvas.addEventListener(
      'pointerdown',
      this._onHandleUpDown.bind(this),
      false
    );
  }

  public unbindEvents() {
    GameManager.canvas.removeEventListener(
      'mousemove',
      this._onHandleMove.bind(this),
      false
    );
    GameManager.canvas.removeEventListener(
      'pointermove',
      this._onHandleMove.bind(this),
      false
    );
    GameManager.canvas.removeEventListener(
      'mouseup',
      this._onHandleUpDown.bind(this),
      false
    );
    GameManager.canvas.removeEventListener(
      'pointerup',
      this._onHandleUpDown.bind(this),
      false
    );
    GameManager.canvas.removeEventListener(
      'mousedown',
      this._onHandleUpDown.bind(this),
      false
    );
    GameManager.canvas.removeEventListener(
      'pointerdown',
      this._onHandleUpDown.bind(this),
      false
    );
  }

  public update() {}

  public setActionsAndAxes() {
    // Attach actions
    for (const action in GameManager.inputManager.bindings.actions) {
      const mappings = GameManager.inputManager.bindings.actions[action];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Mouse) {
          const mappingData = <InputMappingActionMouseDataInterface>mappings[i].data;
          this._actionsMap[mappingData.button] = action;
        }
      }
    }

    // Attach axes
    for (const axis in GameManager.inputManager.bindings.axes) {
      const mappings = GameManager.inputManager.bindings.axes[axis];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Mouse) {
          this._axesMap[axis] = <InputMappingAxisMouseDataInterface>mappings[i].data;
        }
      }
    }
  }

  private _onHandleMove(e: MouseEvent) {
    const deltaX = e.movementX;
    const deltaY = e.movementY;

    for (const axis in this._axesMap) {
      const mouseAction = this._axesMap[axis];

      if (
        deltaX !== 0 &&
        mouseAction.axis === InputAxisEnum.X
      ) {
        GameManager.inputManager.setAxis(axis, deltaX * mouseAction.scale);
      } else if (
        deltaY !== 0 &&
        mouseAction.axis === InputAxisEnum.Y
      ) {
        GameManager.inputManager.setAxis(axis, deltaY * mouseAction.scale);
      }
    }

    // Clear the position after a few miliseconds
    clearTimeout(this._interval);
    this._interval = setTimeout(() => {
      for (const axis in this._axesMap) {
        GameManager.inputManager.setAxis(axis, 0);
      }
    }, this._intervalTime);
  }

  private _onHandleUpDown(e: MouseEvent) {
    const isPressed = e.type === 'mousedown' || e.type === 'pointerdown';
    // TODO: make sure those bindings are correct, especially in IE
    const button = e.which === 3
      ? InputMouseButtonEnum.Right
      : (e.which === 2
        ? InputMouseButtonEnum.Middle
        : (e.which === 1
          ? InputMouseButtonEnum.Left
          : null
        )
      );

    if (button === null) {
      return;
    }

    const action = typeof this._actionsMap[button] !== 'undefined'
      ? this._actionsMap[button]
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
      var index = this._buttonsPressed.indexOf(button);
      if (index === -1) {
        this._buttonsPressed.push(button);
      }
    } else {
      var index = this._buttonsPressed.indexOf(button);
      if (index > -1) {
        this._buttonsPressed.splice(index, 1);
      }
    }
  }
}
