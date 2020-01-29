import { GameManager } from '../Core/GameManager';
import {
  InputBindingsInterface,
  InputDeviceInterface,
  InputMappingAxisMouseDataInterface,
  InputMappingActionMouseDataInterface,
  InputDeviceEnum,
  InputModeEnum,
  InputMouseAxisEnum,
  InputMouseButtonEnum,
} from './InputConstants';

export class InputMouse implements InputDeviceInterface {
  private _bindings: InputBindingsInterface;
  private _axesMap: { [key: string]: InputMappingAxisMouseDataInterface } = {}; // ex.: [ moveForward: { axis: 0, scale: 1.0 } ]
  private _actionsMap: { [key: number]: string } = {}; // ex.: [ 0: interact ]; 0 = InputMouseButtonEnum.Left
  private _buttonsPressed: Array<number> = [];

  public setBindings(bindings: InputBindingsInterface) {
    this._bindings = bindings;

    // Attach actions
    this._actionsMap = {}
    for (const action in this._bindings.actions) {
      const mappings = this._bindings.actions[action];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Mouse) {
          const mappingData = <InputMappingActionMouseDataInterface>mappings[i].data;
          this._actionsMap[mappingData.button] = action;
        }
      }
    }

    // Attach axes
    this._axesMap = {};
    for (const axis in this._bindings.axes) {
      const mappings = this._bindings.axes[axis];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Mouse) {
          this._axesMap[axis] = <InputMappingAxisMouseDataInterface>mappings[i].data;
        }
      }
    }
  }

  public bindEvents() {
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
    GameManager.canvas.addEventListener(
      'wheel',
      this._onHandleWheel.bind(this),
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
    GameManager.canvas.removeEventListener(
      'wheel',
      this._onHandleWheel.bind(this),
      false
    );
  }

  public update() {}

  private _onHandleMove(e: MouseEvent) {
    const deltaX = e.movementX;
    const deltaY = e.movementY;

    if (GameManager.engine.isPointerLock) {
      for (const axis in this._axesMap) {
        const mouseAction = this._axesMap[axis];

        if (
          deltaX !== 0 &&
          mouseAction.axis === InputMouseAxisEnum.X
        ) {
          GameManager.inputManager.addToAxis(axis, deltaX * mouseAction.scale);
        } else if (
          deltaY !== 0 &&
          mouseAction.axis === InputMouseAxisEnum.Y
        ) {
          GameManager.inputManager.addToAxis(axis, deltaY * mouseAction.scale);
        }
      }
    }
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

    if (
      isPressed &&
      !GameManager.engine.isPointerLock &&
      GameManager.inputManager.forcePointerLock
    ) {
      GameManager.engine.enterPointerlock();
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

  private _onHandleWheel(e: MouseWheelEvent) {
    const deltaY = e.deltaY;

    for (const axis in this._axesMap) {
      const mouseAction = this._axesMap[axis];

      if (
        deltaY !== 0 &&
        mouseAction.axis === InputMouseAxisEnum.Wheel
      ) {
        GameManager.inputManager.addToAxis(axis, deltaY * mouseAction.scale);
      }
    }
  }
}
