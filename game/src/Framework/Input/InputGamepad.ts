export class InputGamepad {
  public index: number;
  public data: Gamepad;
  public isConnected: boolean = false;
  public isXbox: boolean = false;
  public isXboxOne: boolean = false;

  // TODO: implement observables here

  // Buttons
  public _buttonA: boolean = false;
  public _buttonB: boolean = false;
  public _buttonX: boolean = false;
  public _buttonY: boolean = false;
  public _buttonStart: boolean = false;
  public _buttonBack: boolean = false;
  public _buttonLeftStick: boolean = false;
  public _buttonRightStick: boolean = false;
  public _buttonLB: boolean = false;
  public _buttonRB: boolean = false;
  public _buttonLT: boolean = false;
  public _buttonRT: boolean = false;
  public _buttonDPadUp: boolean = false;
  public _buttonDPadDown: boolean = false;
  public _buttonDPadLeft: boolean = false;
  public _buttonDPadRight: boolean = false;

  // Axes
  public _leftStickX: number = 0;
  public _leftStickY: number = 0;
  public _rightStickX: number = 0;
  public _rightStickY: number = 0;
  public _leftTrigger: number = 0;
  public _rightTrigger: number = 0;

  constructor(data: Gamepad) {
    this.data = data;
    this.index = data.index;
    this.isXbox = data.id.indexOf('Xbox') !== -1;
    this.isXboxOne = data.id.indexOf('Xbox One') !== -1;
  }

  public update() {
    if (this.isXboxOne) {
      this._leftStickX = this.data.axes[0];
      this._leftStickY = this.data.axes[1];
      this._rightStickX = this.data.axes[3];
      this._rightStickY = this.data.axes[4];
      this._leftTrigger = this.data.axes[2];
      this._rightTrigger = this.data.axes[5];

      this._buttonA = this.data.buttons[0].pressed;
      this._buttonB = this.data.buttons[1].pressed;
      this._buttonX = this.data.buttons[2].pressed;
      this._buttonY = this.data.buttons[3].pressed;
      this._buttonLB = this.data.buttons[4].pressed;
      this._buttonRB = this.data.buttons[5].pressed;
      this._buttonBack = this.data.buttons[9].pressed;
      this._buttonStart = this.data.buttons[8].pressed;
      this._buttonLeftStick = this.data.buttons[6].pressed;
      this._buttonRightStick = this.data.buttons[7].pressed;
      this._buttonDPadUp = this.data.buttons[11].pressed;
      this._buttonDPadDown = this.data.buttons[12].pressed;
      this._buttonDPadLeft = this.data.buttons[13].pressed;
      this._buttonDPadRight = this.data.buttons[14].pressed;
    } else if (this.isXbox) {
      this._leftStickX = this.data.axes[0];
      this._leftStickY = this.data.axes[1];
      this._rightStickX = this.data.axes[2];
      this._rightStickY = this.data.axes[3];
      this._leftTrigger = this.data.buttons[6].value;
      this._rightTrigger = this.data.buttons[7].value;

      this._buttonA = this.data.buttons[0].pressed;
      this._buttonB = this.data.buttons[1].pressed;
      this._buttonX = this.data.buttons[2].pressed;
      this._buttonY = this.data.buttons[3].pressed;
      this._buttonLB = this.data.buttons[4].pressed;
      this._buttonRB = this.data.buttons[5].pressed;
      this._buttonBack = this.data.buttons[8].pressed;
      this._buttonStart = this.data.buttons[9].pressed;
      this._buttonLeftStick = this.data.buttons[10].pressed;
      this._buttonRightStick = this.data.buttons[11].pressed;
      this._buttonDPadUp = this.data.buttons[12].pressed;
      this._buttonDPadDown = this.data.buttons[13].pressed;
      this._buttonDPadLeft = this.data.buttons[14].pressed;
      this._buttonDPadRight = this.data.buttons[15].pressed;
    } else {
      // TODO
    }
  }

  /***** Getters *****/
  public get buttonA() {
    return this._buttonA;
  }

  public get buttonB() {
    return this._buttonB;
  }

  public get buttonX() {
    return this._buttonX;
  }

  public get buttonY() {
    return this._buttonY;
  }

  public get buttonStart() {
    return this._buttonStart;
  }

  public get buttonBack() {
    return this._buttonBack;
  }

  public get buttonLeftStick() {
    return this._buttonLeftStick;
  }

  public get buttonRightStick() {
    return this._buttonRightStick;
  }

  public get buttonLB() {
    return this._buttonLB;
  }

  public get buttonRB() {
    return this._buttonRB;
  }

  public get buttonLT() {
    return this._buttonLT;
  }

  public get buttonRT() {
    return this._buttonRT;
  }

  public get buttonDPadUp() {
    return this._buttonDPadUp;
  }

  public get buttonDPadDown() {
    return this._buttonDPadDown;
  }

  public get buttonDPadLeft() {
    return this._buttonDPadLeft;
  }

  public get buttonDPadRight() {
    return this._buttonDPadRight;
  }

  public get leftStickX() {
    return this._leftStickX;
  }

  public get leftStickY() {
    return this._leftStickY;
  }

  public get rightStickX() {
    return this._rightStickX;
  }

  public get rightStickY() {
    return this._rightStickY;
  }

  public get leftTrigger() {
    return this._leftTrigger;
  }

  public get rightTrigger() {
    return this._rightTrigger;
  }
}
