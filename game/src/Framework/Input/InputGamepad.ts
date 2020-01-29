export class InputGamepad {
  public index: number;
  public data: Gamepad;
  public isConnected: boolean = false;
  public isXbox: boolean = false;
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
    this.index = data.index;
    this.isXbox = data.id.indexOf('Xbox') !== -1;
    this.isXboxOne = data.id.indexOf('Xbox One') !== -1;
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
    } else if (this.isXbox) {
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
    } else {
      // TODO
    }
  }
}
