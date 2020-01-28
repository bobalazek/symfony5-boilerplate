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

export class InputGamepadManager implements InputDeviceInterface {
  public hasSupport: boolean = 'GamepadEvent' in window;
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
        if (mappings[i].device === InputDeviceEnum.Keyboard) {
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
        if (mappings[i].device === InputDeviceEnum.Keyboard) {
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

        // Axes
       for (const axis in this._bindings.axes) {
         const actionAxis = this._axesMap[axis].axis;
         const actionScale = this._axesMap[axis].scale;

         GameManager.inputManager.setAxis(
           axis,
           gamepad[
             InputGamepadAxisPropertyEnum[InputGamepadAxisEnum[actionAxis]]
           ] * actionScale
         );
       }

       // Actions
       for (const action in this._bindings.actions) {
         const actionEnum = this._actionsInversedMap[action];

         GameManager.inputManager.setAction(
           action,
           gamepad[
             InputGamepadButtonPropertyEnum[InputGamepadButtonEnum[actionEnum]]
           ]
         );
       }

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
      }
    }
  }

  private _onHandle(e: GamepadEvent) {
    const gamepadIndex = e.gamepad.index;
    const gamepads = GameManager.inputManager.gamepads;
    let gamepad = gamepads[gamepadIndex];
    if (!gamepad) {
      gamepad = new InputGamepad(e.gamepad);
    }

    gamepad.isConnected = e.type === 'gamepadconnected';

    GameManager.inputManager.setGamepad(
      gamepadIndex,
      gamepad
    );
  }
}

export class InputGamepad {
  public data: Gamepad;
  public isConnected: boolean = false;
  public isXboxOne: boolean = false;

  // Buttons
  public buttonA: boolean = false;
  public buttonB: boolean = false;
  public buttonX: boolean = false;
  public buttonY: boolean = false;
  public buttonStart: boolean = false;
  public buttonBack: boolean = false;
  public buttonLeftStick: boolean = false;
  public buttonRightStick: boolean = false;
  public buttonLB: boolean = false;
  public buttonRB: boolean = false;
  public buttonLT: boolean = false;
  public buttonRT: boolean = false;
  public buttonDPadUp: boolean = false;
  public buttonDPadDown: boolean = false;
  public buttonDPadLeft: boolean = false;
  public buttonDPadRight: boolean = false;

  // Axes
  public leftStickX: number = 0;
  public leftStickY: number = 0;
  public rightStickX: number = 0;
  public rightStickY: number = 0;
  public leftTrigger: number = 0;
  public rightTrigger: number = 0;

  constructor(data: Gamepad) {
    this.data = data;
  }

  public update() {
    if (this.isXboxOne) {
      this.leftStickX = this.data.axes[0];
      this.leftStickY = this.data.axes[1];
      this.rightStickX = this.data.axes[3];
      this.rightStickY = this.data.axes[4];
      this.leftTrigger = this.data.axes[2];
      this.rightTrigger = this.data.axes[5];

      this.buttonA = this.data.buttons[0].pressed;
      this.buttonB = this.data.buttons[1].pressed;
      this.buttonX = this.data.buttons[2].pressed;
      this.buttonY = this.data.buttons[3].pressed;
      this.buttonLB = this.data.buttons[4].pressed;
      this.buttonRB = this.data.buttons[5].pressed;
      this.buttonBack = this.data.buttons[9].pressed;
      this.buttonStart = this.data.buttons[8].pressed;
      this.buttonLeftStick = this.data.buttons[6].pressed;
      this.buttonRightStick = this.data.buttons[7].pressed;
      this.buttonDPadUp = this.data.buttons[11].pressed;
      this.buttonDPadDown = this.data.buttons[12].pressed;
      this.buttonDPadLeft = this.data.buttons[13].pressed;
      this.buttonDPadRight = this.data.buttons[14].pressed;
    } else {
      this.leftStickX = this.data.axes[0];
      this.leftStickY = this.data.axes[1];
      this.rightStickX = this.data.axes[2];
      this.rightStickY = this.data.axes[3];
      this.leftTrigger = this.data.buttons[6].value;
      this.rightTrigger = this.data.buttons[7].value;

      this.buttonA = this.data.buttons[0].pressed;
      this.buttonB = this.data.buttons[1].pressed;
      this.buttonX = this.data.buttons[2].pressed;
      this.buttonY = this.data.buttons[3].pressed;
      this.buttonLB = this.data.buttons[4].pressed;
      this.buttonRB = this.data.buttons[5].pressed;
      this.buttonBack = this.data.buttons[8].pressed;
      this.buttonStart = this.data.buttons[9].pressed;
      this.buttonLeftStick = this.data.buttons[10].pressed;
      this.buttonRightStick = this.data.buttons[11].pressed;
      this.buttonDPadUp = this.data.buttons[12].pressed;
      this.buttonDPadDown = this.data.buttons[13].pressed;
      this.buttonDPadLeft = this.data.buttons[14].pressed;
      this.buttonDPadRight = this.data.buttons[15].pressed;
    }
  }
}
