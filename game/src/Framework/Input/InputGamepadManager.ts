import { GameManager } from '../Core/GameManager';
import {
  InputBindingsInterface,
  InputDeviceInterface,
  InputMappingAxisGamepadDataInterface,
  InputMappingActionGamepadDataInterface,
  InputModeEnum,
  InputDeviceEnum,
  InputGamepadAxisEnum,
  InputGamepadAxisPropertyEnum,
  InputGamepadButtonEnum,
  InputGamepadButtonPropertyEnum,
} from './InputConstants';
import { InputGamepad } from './InputGamepad';

export class InputGamepadManager implements InputDeviceInterface {
  public hasSupport: boolean = 'GamepadEvent' in window;
  public isAnyConnected: boolean = false;

  private _bindings: InputBindingsInterface;
  private _axesMap: { [key: string]: InputMappingAxisGamepadDataInterface } = {};
  private _actionsMap: { [key: string]: string } = {};
  private _actionsInversedMap: { [key: string]: number } = {}; // have the actions on the left & button on the right

  public setBindings(bindings: InputBindingsInterface) {
    this._bindings = bindings;

    // Attach actions
    this._actionsMap = {};
    this._actionsInversedMap = {};
    for (const action in this._bindings.actions) {
      const mappings = this._bindings.actions[action];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Gamepad) {
          let mappingData = <InputMappingActionGamepadDataInterface>mappings[i].data;
          this._actionsMap[mappingData.button] = action;
          this._actionsInversedMap[action] = mappingData.button;
        }
      }
    }

    // Attach axes
    this._axesMap = {};
    for (const axis in this._bindings.axes) {
      const mappings = this._bindings.axes[axis];
      for (let i = 0; i < mappings.length; i++) {
        if (mappings[i].device === InputDeviceEnum.Gamepad) {
          this._axesMap[axis] = <InputMappingAxisGamepadDataInterface>mappings[i].data;
        }
      }
    }
  }

  public bindEvents() {
    if (this.hasSupport) {
      const hostWindow = GameManager.scene
        ? GameManager.engine.getHostWindow()
        : window;

        hostWindow.addEventListener(
          'gamepadconnected',
          this._onHandle.bind(this),
          false
        );
        hostWindow.addEventListener(
          'gamepaddisconnected',
          this._onHandle.bind(this),
          false
        );
    }
  }

  public unbindEvents() {
    if (this.hasSupport) {
      const hostWindow = GameManager.scene
        ? GameManager.engine.getHostWindow()
        : window;

        hostWindow.removeEventListener(
          'gamepadconnected',
          this._onHandle.bind(this),
          false
        );
        hostWindow.removeEventListener(
          'gamepaddisconnected',
          this._onHandle.bind(this),
          false
        );
    }
  }

  public update() {
    this._updateGamepads();

    const gamepads = GameManager.inputManager.gamepads;
    if (gamepads.length) {

      for (const index in gamepads) {
        if (index !== '0') {
          break; // TODO
        }

        const gamepad = gamepads[index];
        if (!gamepad.isConnected) {
          continue;
        }

        gamepad.update();

        if (
          GameManager.inputManager.mode !== InputModeEnum.Gamepad &&
          (
            gamepad.buttonA ||
            gamepad.buttonB ||
            gamepad.buttonX ||
            gamepad.buttonY
          )
        ) {
          GameManager.inputManager.setMode(
            InputModeEnum.Gamepad
          );
        }

        if (GameManager.inputManager.mode === InputModeEnum.Gamepad) {
          // Axes
          for (const axis in this._bindings.axes) {
            const axisData = this._axesMap[axis];
            if (axisData) {
              const actionAxis = axisData.axis;
              const actionScale = axisData.scale;
              const value = gamepad[
                InputGamepadAxisPropertyEnum[InputGamepadAxisEnum[actionAxis]]
              ];

              if (Math.abs(value) > 0.1) { // TODO: implement deadzone
                console.log(axis, value)
                GameManager.inputManager.addToAxis(axis, value * actionScale);
              }
            }
          }

          // Actions
          for (const action in this._bindings.actions) {
            const actionEnum = this._actionsInversedMap[action];
            let value: boolean = false;

            if (actionEnum) {
              value = gamepad[
                InputGamepadButtonPropertyEnum[InputGamepadButtonEnum[actionEnum]]
              ];
            }

            GameManager.inputManager.setAction(action, value);
          }
        }
      }
    }
  }

  private _onHandle(e: GamepadEvent) {
    const gamepadIndex = e.gamepad.index;
    let gamepad = GameManager.inputManager.gamepads[gamepadIndex];
    if (!gamepad) {
      gamepad = new InputGamepad(e.gamepad);
    }

    gamepad.isConnected = e.type === 'gamepadconnected';

    GameManager.inputManager.setGamepad(
      gamepadIndex,
      gamepad
    );

    this.isAnyConnected = false;
    for (const index in GameManager.inputManager.gamepads) {
      const gamepad = GameManager.inputManager.gamepads[index];
      if (gamepad.isConnected) {
        this.isAnyConnected = true;
        break;
      }
    }
  }

  private _updateGamepads() {
    const browserGamepads = navigator.getGamepads
      ? navigator.getGamepads()
      : (navigator.webkitGetGamepads
        ? navigator.webkitGetGamepads()
        : []
      );
    for (let index = 0; index < browserGamepads.length; index++) {
      const browserGamepad = browserGamepads[index];

      if (!browserGamepad) {
        continue;
      }

      let gamepad = GameManager.inputManager.gamepads[index];
      if (!gamepad) {
        gamepad = new InputGamepad(browserGamepad);
      }

      gamepad.data = browserGamepad;

      GameManager.inputManager.setGamepad(
        index,
        gamepad
      );
    }
  }
}
