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
    window.addEventListener(
        'gamepadconnected',
        this._onHandle.bind(this),
        false
    );
    window.addEventListener(
        'gamepaddisconnected',
        this._onHandle.bind(this),
        false
    );
  }

  public unbindEvents() {
    window.removeEventListener(
        'gamepadconnected',
        this._onHandle.bind(this),
        false
    );
    window.removeEventListener(
        'gamepaddisconnected',
        this._onHandle.bind(this),
        false
    );
  }

  public update() {
    const gamepads = GameManager.inputManager.gamepads;

    if (gamepads.length) {
      for (const index in gamepads) {
        const gamepad = gamepads[index];
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
            let value: number = 0;

            if (axisData) {
              const actionAxis = axisData.axis;
              const actionScale = axisData.scale;
              value = gamepad[
              InputGamepadAxisPropertyEnum[InputGamepadAxisEnum[actionAxis]]
              ] * actionScale;
            }

            GameManager.inputManager.setAxis(axis, value);
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

    for (const index in GameManager.inputManager.gamepads) {
      const gamepad = GameManager.inputManager.gamepads[index];
      if (gamepad.isConnected) {
        this.isAnyConnected = true;
        break;
      }
    }
  }
}
