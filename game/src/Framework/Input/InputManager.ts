import { GameManager } from './../Core/GameManager';

import {
  InputGamepad,
  InputGamepadAxisEnum,
  InputGamepadAxisPropertyEnum,
  InputGamepadButtonEnum,
  InputGamepadButtonPropertyEnum,
} from './InputGamepad';
import { InputMouseButtonEnum } from './InputMouse';
import {
  InputModeEnum,
  InputAxisEnum,
  InputDeviceEnum,
  InputBindingsInterface,
  InputMappingAxisKeyboardDataInterface,
  InputMappingAxisMouseDataInterface,
  InputMappingAxisGamepadDataInterface,
  InputMappingActionKeyboardDataInterface,
  InputMappingActionMouseDataInterface,
  InputMappingActionGamepadDataInterface,
} from './InputHelpers';

export class InputManager {
  public hasGamepadSupport: boolean = 'GamepadEvent' in window;

  private _bindings: InputBindingsInterface;
  private _mode: InputModeEnum = InputModeEnum.KeyboardAndMouse;

  // Axes & actions
  private _axes: { [key: string]: number } = {};
  private _actions: { [key: string]: boolean } = {};

  // Keyboard stuff
  private _keyboardAxesMap: { [key: string]: InputMappingAxisKeyboardDataInterface } = {}; // ex.: [ moveForward: { keyCode: 68, scale: 1 } ]
  private _keyboardAxesKeyScaleMap: { [key: number]: { axis: string, scale: number } } = {}; // ex.: [ 68: { axis: 'moveForward', scale: 1 } ]
  private _keyboardActionsMap: { [key: number]: string } = {}; // ex.: { 68: moveForward }
  private _keyboardKeysPressed: { [key: number]: number } = {}; // ex.: { 68: 123456789 /* unix time */ }

  // Mouse staff
  private _mouseAxesMap: { [key: string]: InputMappingAxisMouseDataInterface } = {}; // ex.: [ moveForward: { axis: 0, scale: 1.0 } ]
  private _mouseActionsMap: { [key: number]: string } = {}; // ex.: [ 0: interact ]; 0 = InputMouseButtonEnum.Left
  private _mouseButtonsPressed: Array<number> = [];
  private _mouseInterval: any; // ex.: [ 0: 1 ] // 1 = InputMouseButtonEnum.Middle
  private _mouseIntervalTime: number = 50; // after how many miliseconds it should clear the values?

  // Gamepad stuff
  private _gamepads: Array<InputGamepad> = [];
  private _gamepadAxesMap: { [key: string]: InputMappingAxisGamepadDataInterface } = {};
  private _gamepadActionsMap: { [key: string]: string } = {};
  private _gamepadActionsInversedMap: { [key: string]: number } = {}; // have the actions on the left & button on the right

  constructor(bindings: InputBindingsInterface) {
    this.setBindings(bindings);

    // Gamepads
    if (this.hasGamepadSupport) {
      this.prepareGamepads();
    }
  }

  /********** General **********/

  public watch() {
    const canvas = GameManager.engine.getRenderingCanvas();

    // Keyboard events
    canvas.addEventListener(
      'keydown',
      this.handleKeyboardKeyDownAndUpEvent.bind(this),
      false
    );
    canvas.addEventListener(
      'keyup',
      this.handleKeyboardKeyDownAndUpEvent.bind(this),
      false
    );

    // Mouse events
    canvas.addEventListener(
      'mousemove',
      this.handleMouseMoveEvent.bind(this),
      false
    );
    canvas.addEventListener(
      'pointermove',
      this.handleMouseMoveEvent.bind(this),
      false
    );

    canvas.addEventListener(
      'mousedown',
      this.handleMouseDownAndUpEvent.bind(this),
      false
    );
    canvas.addEventListener(
      'pointerdown',
      this.handleMouseDownAndUpEvent.bind(this),
      false
    );

    canvas.addEventListener(
      'mouseup',
      this.handleMouseDownAndUpEvent.bind(this),
      false
    );
    canvas.addEventListener(
      'pointerup',
      this.handleMouseDownAndUpEvent.bind(this),
      false
    );

    // Gamepad events
    if (this.hasGamepadSupport) {
      window.addEventListener(
        'gamepadconnected',
        this.handleGamepadConnectedEvent.bind(this),
        false
      );
      window.addEventListener(
        'gamepaddisconnected',
        this.handleGamepadDisconnectedEvent.bind(this),
        false
      );
    }
  }

  public unwatch() {
    const canvas = GameManager.engine.getRenderingCanvas();

    // Keyboard events
    canvas.removeEventListener(
      'keydown',
      this.handleKeyboardKeyDownAndUpEvent.bind(this),
      false
    );
    canvas.removeEventListener(
      'keyup',
      this.handleKeyboardKeyDownAndUpEvent.bind(this),
      false
    );

    // Mouse events
    canvas.removeEventListener(
      'mousemove',
      this.handleMouseMoveEvent.bind(this),
      false
    );
    canvas.removeEventListener(
      'pointermove',
      this.handleMouseMoveEvent.bind(this),
      false
    );

    canvas.removeEventListener(
      'mousedown',
      this.handleMouseDownAndUpEvent.bind(this),
      false
    );
    canvas.removeEventListener(
      'pointerdown',
      this.handleMouseDownAndUpEvent.bind(this),
      false
    );

    canvas.removeEventListener(
      'mouseup',
      this.handleMouseDownAndUpEvent.bind(this),
      false
    );
    canvas.removeEventListener(
      'pointerup',
      this.handleMouseDownAndUpEvent.bind(this),
      false
    );

    // Gamepad events
    if (this.hasGamepadSupport) {
      window.removeEventListener(
        'gamepadconnected',
        this.handleGamepadConnectedEvent.bind(this),
        false
      );
      window.removeEventListener(
        'gamepaddisconnected',
        this.handleGamepadDisconnectedEvent.bind(this),
        false
      );
    }
  }

  public update() {
    const gamepads = this.getGamepads();
    if (gamepads.length) {
      for (let i = 0; i < gamepads.length; i++) {
        gamepads[i].update();

        if (
          this._mode !== InputModeEnum.Gamepad &&
          (
            gamepads[i].buttonA ||
            gamepads[i].buttonB ||
            gamepads[i].buttonX ||
            gamepads[i].buttonY
          )
        ) {
          this._mode = InputModeEnum.Gamepad;
          this.resetAxesAndActions();
        }

        if (this._mode === InputModeEnum.Gamepad) {
          this.updateAxesAndActionsByGamepad(gamepads[i]);
        }
      }
    }

    if (this._mode === InputModeEnum.KeyboardAndMouse) {
      this.updateAxesByKeyboard();
    }
  }

  public getMode() {
    return this._mode;
  }

  /***** Axes & Actions *****/

  public getAxes(axis?: string) {
    if (axis) {
      if (typeof this._axes[axis] === 'undefined') {
        throw new Error('The axis ' + axis + ' does not exist.');
      }

      return this._axes[axis];
    }

    return this._axes;
  }

  public getActions(action?: string) {
    if (action) {
      if (typeof this._actions[action] === 'undefined') {
        throw new Error('The action ' + action + ' does not exist.');
      }

      return this._actions[action];
    }

    return this._actions;
  }

  public resetAxesAndActions() {
    // Axes
    for (const axis in this._bindings.axes) {
      this._axes[axis] = 0.0;
    }

    // Actions
    for (const action in this._bindings.actions) {
      this._actions[action] = false;
    }
  }

  /***** Keyboard & Mouse Handlers *****/

  public handleKeyboardKeyDownAndUpEvent(e: KeyboardEvent) {
    const isPressed = e.type === 'keydown';
    const keyCode = e.keyCode;
    const action = typeof this._keyboardActionsMap[keyCode] !== 'undefined'
      ? this._keyboardActionsMap[keyCode]
      : null;

    if (
      this._mode !== InputModeEnum.KeyboardAndMouse &&
      isPressed
    ) {
      this._mode = InputModeEnum.KeyboardAndMouse;
      this.resetAxesAndActions();
    }

    if (action !== null) {
      this._actions[action] = isPressed;
    }

    if (isPressed) {
      this._keyboardKeysPressed[keyCode] = (new Date()).getTime();
    } else {
      if (typeof this._keyboardKeysPressed[keyCode] !== 'undefined') {
        delete this._keyboardKeysPressed[keyCode];
      }
    }

    this.dispatchEvent('input:device:keyboard', {
      isPressed: isPressed,
      keyCode: keyCode,
      action: action,
    });
  }

  public handleMouseMoveEvent(e: MouseEvent) {
    const deltaX = e.movementX;
    const deltaY = e.movementY;

    for (const axis in this._mouseAxesMap) {
      const mouseAction = this._mouseAxesMap[axis];

      if (
        deltaX !== 0 &&
        mouseAction.axis === InputAxisEnum.X
      ) {
        this._axes[axis] = deltaX * mouseAction.scale;
      } else if (
        deltaY !== 0 &&
        mouseAction.axis === InputAxisEnum.Y
      ) {
        this._axes[axis] = deltaY * mouseAction.scale;
      }
    }

    this.dispatchEvent('input:device:mouse:move', {
      deltaX: deltaX,
      deltaY: deltaY,
    });

    // Clear the position after a few miliseconds
    clearTimeout(this._mouseInterval);
    this._mouseInterval = setTimeout(
      () => {
        for (const axis in this._mouseAxesMap) {
          this._axes[axis] = 0;
        }
      },
      this._mouseIntervalTime
    );
  }

  public handleMouseDownAndUpEvent(e: MouseEvent) {
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

    const action = typeof this._mouseActionsMap[button] !== 'undefined'
      ? this._mouseActionsMap[button]
      : null;

    if (
      this._mode !== InputModeEnum.KeyboardAndMouse &&
      isPressed
    ) {
      this._mode = InputModeEnum.KeyboardAndMouse;
      this.resetAxesAndActions();
    }

    if (action !== null) {
      this._actions[action] = isPressed;
    }

    if (isPressed) {
      var index = this._mouseButtonsPressed.indexOf(button);
      if (index === -1) {
        this._mouseButtonsPressed.push(button);
      }
    } else {
      var index = this._mouseButtonsPressed.indexOf(button);
      if (index > -1) {
        this._mouseButtonsPressed.splice(index, 1);
      }
    }

    this.dispatchEvent('input:device:mouse', {
      isPressed: isPressed,
      button: button,
      action: action,
    });
  }

  // TODO: probably needs optimization
  public updateAxesByKeyboard() {
    let affectedAxes = {};
    for (let keyCode in this._keyboardKeysPressed) {
      if (this._keyboardAxesKeyScaleMap[keyCode]) {
        const axis = this._keyboardAxesKeyScaleMap[keyCode].axis;
        const scale = this._keyboardAxesKeyScaleMap[keyCode].scale;

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
      var value = 0.0;

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

      this._axes[axis] = value;
    }
  }

  /***** Gamepad Handlers *****/

  public handleGamepadConnectedEvent(e: GamepadEvent) {
    this.prepareGamepads();
  }

  public handleGamepadDisconnectedEvent(e: GamepadEvent) {
    this.prepareGamepads();
  }

  public getGamepads() {
    return this._gamepads;
  }

  public prepareGamepads() {
    const gamepads = navigator.getGamepads
      ? navigator.getGamepads()
      : (navigator.webkitGetGamepads
        ? navigator.webkitGetGamepads()
        : []
      );

    this._gamepads = new Array();
    for (let i = 0; i < gamepads.length; i++) {
      const gamepad = gamepads[i];
      if (gamepad === null) {
        continue;
      }

      this._gamepads.push(
        new InputGamepad(gamepad)
      );
    }
  }

  public updateAxesAndActionsByGamepad(gamepad: InputGamepad) {
    // Axes
    for (const key in this._axes) {
      const axis = this._axes[key];
      const actionAxis = this._gamepadAxesMap[axis].axis;
      const actionScale = this._gamepadAxesMap[axis].scale;
      this._axes[axis] = gamepad[
        InputGamepadAxisPropertyEnum[InputGamepadAxisEnum[actionAxis]]
      ] * actionScale;
    }

    // Actions
    for (const key in this._actions) {
      const action = this._actions[key];
      const actionEnum = this._gamepadActionsInversedMap[key];
      this._actions[key] = gamepad[
        InputGamepadButtonPropertyEnum[InputGamepadButtonEnum[actionEnum]]
      ];
    }
  }

  /***** Events ******/

  public dispatchEvent(name: string, data: any) {
    GameManager.engine.getRenderingCanvas().dispatchEvent(
      new CustomEvent(name, data)
    );
  }

  /***** Pointer Lock *****/

  public addPointerLock() {
    this.requestPointerLockOnClick();

    document.addEventListener(
      'pointerlockchange',
      this.onPointerLockChange.bind(this),
      false
    );
    document.addEventListener(
      'mspointerlockchange',
      this.onPointerLockChange.bind(this),
      false
    );
    document.addEventListener(
      'mozpointerlockchange',
      this.onPointerLockChange.bind(this),
      false
    );
    document.addEventListener(
      'webkitpointerlockchange',
      this.onPointerLockChange.bind(this),
      false
    );
  }

  public removePointerLock() {
    document.removeEventListener(
      'pointerlockchange',
      this.onPointerLockChange.bind(this),
      false
    );
    document.removeEventListener(
      'mspointerlockchange',
      this.onPointerLockChange.bind(this),
      false
    );
    document.removeEventListener(
      'mozpointerlockchange',
      this.onPointerLockChange.bind(this),
      false
    );
    document.removeEventListener(
      'webkitpointerlockchange',
      this.onPointerLockChange.bind(this),
      false
    );
  }

  public onPointerLockChange() {
    let activeCamera = GameManager.activeLevel.getScene().activeCamera;

    // TODO: GameManager.engine.isPointerLock - do something with it?
  }

  public requestPointerLockOnClick() {
    GameManager.activeLevel.getScene().onPointerDown = (e) => {
      let engine = GameManager.engine;
      let canvas = engine.getRenderingCanvas();
      if (!engine.isPointerLock) {
        canvas.requestPointerLock = canvas.requestPointerLock
          || canvas.msRequestPointerLock
          || canvas.mozRequestPointerLock
          || canvas.webkitRequestPointerLock
          || null;
        if (canvas.requestPointerLock) {
          canvas.requestPointerLock();
        }
      }
    };
  }

  public exitPointerLock() {
    let engine = GameManager.engine;
    document.exitPointerLock = document.exitPointerLock
      || (<any>document).mozExitPointerLock
      || null;

    if (
      engine.isPointerLock &&
      document.exitPointerLock
    ) {
      document.exitPointerLock();
    }
  }

  /********** Bindings **********/

  public setBindings(bindings: InputBindingsInterface): InputManager {
    this._bindings = bindings;

    // Populate the axes & actions
    for (const axis in this._bindings.axes) {
      this._axes[axis] = 0.0;

      const mappings = this._bindings.axes[axis];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Keyboard) {
          let mappingData = <InputMappingAxisKeyboardDataInterface>mappings[i].data;
          this._keyboardAxesMap[axis] = mappingData;
          this._keyboardAxesKeyScaleMap[mappingData.keyCode] = {
            axis: axis,
            scale: mappingData.scale,
          };
        } else if (mappings[i].device === InputDeviceEnum.Mouse) {
          this._mouseAxesMap[axis] = <InputMappingAxisMouseDataInterface>mappings[i].data;
        } else if (mappings[i].device === InputDeviceEnum.Gamepad) {
          this._gamepadAxesMap[axis] = <InputMappingAxisGamepadDataInterface>mappings[i].data;
        }
      }
    }

    for (const action in this._bindings.actions) {
      this._actions[action] = false;

      const mappings = this._bindings.actions[action];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Keyboard) {
          let mappingData = <InputMappingActionKeyboardDataInterface>mappings[i].data;
          this._keyboardActionsMap[mappingData.keyCode] = action;
        } else if (mappings[i].device === InputDeviceEnum.Mouse) {
          let mappingData = <InputMappingActionMouseDataInterface>mappings[i].data;
          this._mouseActionsMap[mappingData.button] = action;
        } else if (mappings[i].device === InputDeviceEnum.Gamepad) {
          let mappingData = <InputMappingActionGamepadDataInterface>mappings[i].data;
          this._gamepadActionsMap[mappingData.button] = action;
          this._gamepadActionsInversedMap[action] = mappingData.button;
        }
      }
    }

    return this;
  }

  public getBindings(): InputBindingsInterface {
    return this._bindings;
  }
}
