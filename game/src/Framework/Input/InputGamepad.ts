import { GameManager } from '../Core/GameManager';

export class InputGamepad {

    public browserGamepad: Gamepad
    public type: InputGamepadTypeEnum = InputGamepadTypeEnum.Generic;

    // Buttons
    private _buttonA: boolean = false;
    private _buttonB: boolean = false;
    private _buttonX: boolean = false;
    private _buttonY: boolean = false;
    private _buttonStart: boolean = false;
    private _buttonBack: boolean = false;
    private _buttonLeftStick: boolean = false;
    private _buttonRightStick: boolean = false;
    private _buttonLB: boolean = false;
    private _buttonRB: boolean = false;
    private _buttonLT: boolean = false;
    private _buttonRT: boolean = false;
    private _buttonDPadUp: boolean = false;
    private _buttonDPadDown: boolean = false;
    private _buttonDPadLeft: boolean = false;
    private _buttonDPadRight: boolean = false;

    // Axes
    private _leftStickX: number = 0;
    private _leftStickY: number = 0;
    private _rightStickX: number = 0;
    private _rightStickY: number = 0;
    private _leftTrigger: number = 0;
    private _rightTrigger: number = 0;

    // Deadzones
    private _leftStickXDeadzone: number = 0.1;
    private _leftStickYDeadzone: number = 0.1;
    private _rightStickXDeadzone: number = 0.1;
    private _rightStickYDeadzone: number = 0.1;
    private _leftTriggerDeadzone: number = 0.1;
    private _rightTriggerDeadzone: number = 0.1;

    // Inverts
    private _leftStickYInvert: boolean = false;
    private _rightStickYInvert: boolean = false;

    // Events
    public onButtonChanged: (button: InputGamepadButtonEnum, state: boolean) => void;
    public onLeftStickXChanged: (value: number) => void;
    public onLeftStickYChanged: (value: number) => void;
    public onRightStickXChanged: (value: number) => void;
    public onRightStickYChanged: (value: number) => void;
    public onLeftTriggerChanged: (value: number) => void;
    public onRightTriggerChanged: (value: number) => void;

    constructor (browserGamepad: Gamepad) {

        this.browserGamepad = browserGamepad;

        const isXbox = (<string>this.browserGamepad.id).search('Xbox') !== -1;
        const isXboxOne = (<string>this.browserGamepad.id).search('Xbox One') !== -1;
        if (isXbox) {
            this.type = isXboxOne
                ? InputGamepadTypeEnum.XboxOne
                : InputGamepadTypeEnum.Xbox360;
        }

    }

    public update() {

        if (this.type === InputGamepadTypeEnum.XboxOne) {
            // TODO: make sure those are correct.
            this._leftStickX = this.browserGamepad.axes[0];
            this._leftStickY = this.browserGamepad.axes[1];
            this._rightStickX = this.browserGamepad.axes[3];
            this._rightStickY = this.browserGamepad.axes[4];
            this._leftTrigger = this.browserGamepad.axes[2];
            this._rightTrigger = this.browserGamepad.axes[5];

            this._buttonA = this.browserGamepad.buttons[0].pressed;
            this._buttonB = this.browserGamepad.buttons[1].pressed;
            this._buttonX = this.browserGamepad.buttons[2].pressed;
            this._buttonY = this.browserGamepad.buttons[3].pressed;
            this._buttonLB = this.browserGamepad.buttons[4].pressed;
            this._buttonRB = this.browserGamepad.buttons[5].pressed;
            this._buttonBack = this.browserGamepad.buttons[9].pressed;
            this._buttonStart = this.browserGamepad.buttons[8].pressed;
            this._buttonLeftStick = this.browserGamepad.buttons[6].pressed;
            this._buttonRightStick = this.browserGamepad.buttons[7].pressed;
            this._buttonDPadUp = this.browserGamepad.buttons[11].pressed;
            this._buttonDPadDown = this.browserGamepad.buttons[12].pressed;
            this._buttonDPadLeft = this.browserGamepad.buttons[13].pressed;
            this._buttonDPadRight = this.browserGamepad.buttons[14].pressed;
        } else {
            this._leftStickX = this.browserGamepad.axes[0];
            this._leftStickY = this.browserGamepad.axes[1];
            this._rightStickX = this.browserGamepad.axes[2];
            this._rightStickY = this.browserGamepad.axes[3];
            this._leftTrigger = this.browserGamepad.buttons[6].value;
            this._rightTrigger = this.browserGamepad.buttons[7].value;

            this._buttonA = this.browserGamepad.buttons[0].pressed;
            this._buttonB = this.browserGamepad.buttons[1].pressed;
            this._buttonX = this.browserGamepad.buttons[2].pressed;
            this._buttonY = this.browserGamepad.buttons[3].pressed;
            this._buttonLB = this.browserGamepad.buttons[4].pressed;
            this._buttonRB = this.browserGamepad.buttons[5].pressed;
            this._buttonBack = this.browserGamepad.buttons[8].pressed;
            this._buttonStart = this.browserGamepad.buttons[9].pressed;
            this._buttonLeftStick = this.browserGamepad.buttons[10].pressed;
            this._buttonRightStick = this.browserGamepad.buttons[11].pressed;
            this._buttonDPadUp = this.browserGamepad.buttons[12].pressed;
            this._buttonDPadDown = this.browserGamepad.buttons[13].pressed;
            this._buttonDPadLeft = this.browserGamepad.buttons[14].pressed;
            this._buttonDPadRight = this.browserGamepad.buttons[15].pressed;
        }

    }

    /********** Axes **********/

    /*** Left ***/

    public get leftStickX(): number {
        return this._leftStickX;
    }

    public set leftStickX(value: number) {
        if (this._leftStickX !== value) {
            this._leftStickX = Math.abs(value) > this._leftStickXDeadzone
                ? value
                : 0;

            if (this.onLeftStickXChanged) {
                this.onLeftStickXChanged(value);
            }
        }
    }

    public get leftStickY(): number {
        return this._leftStickYInvert
            ? -this._leftStickY
            : this._leftStickY;
    }

    public set leftStickY(value: number) {
        if (this._leftStickY !== value) {
            this._leftStickY = Math.abs(value) > this._leftStickYDeadzone
                ? value
                : 0;

            if (this.onLeftStickYChanged) {
                this.onLeftStickYChanged(value);
            }
        }
    }

    public get leftStick(): InputEnumStickValues {
        return {
            x: this.leftStickX,
            y: this.leftStickY,
        };
    }

    /*** Right ***/

    public get rightStickX(): number {
        return this._rightStickX;
    }

    public set rightStickX(value: number) {
        if (this._rightStickX !== value) {
            this._rightStickX = Math.abs(value) > this._rightStickXDeadzone
                ? value
                : 0;

            if (this.onRightStickXChanged) {
                this.onRightStickXChanged(value);
            }
        }
    }

    public get rightStickY(): number {
        return this._rightStickYInvert
            ? -this._rightStickY
            : this._rightStickY;
    }

    public set rightStickY(value: number) {
        if (this._rightStickY !== value) {
            this._rightStickY = Math.abs(value) > this._rightStickYDeadzone
                ? value
                : 0;

            if (this.onRightStickYChanged) {
                this.onRightStickYChanged(value);
            }
        }
    }

    public get rightStick(): InputEnumStickValues {
        return {
            x: this.rightStickX,
            y: this.rightStickY,
        };
    }

    /***** Triggers *****/

    /*** Left ***/

    public get leftTrigger(): number {
        return this._leftTrigger;
    }

    public set leftTrigger(value: number) {
        if (this._leftTrigger !== value) {
            this._leftTrigger = value > this._leftTriggerDeadzone
                ? value
                : 0;

            if (this.onLeftTriggerChanged) {
                this.onLeftTriggerChanged(value);
            }
        }
    }

    /*** Right ***/

    public get rightTrigger(): number {
        return this._rightTrigger;
    }

    public set rightTrigger(value: number) {
        if (this._rightTrigger !== value) {
            this._rightTrigger = value > this._rightTriggerDeadzone
                ? value
                : 0;

            if (this.onRightTriggerChanged) {
                this.onLeftTriggerChanged(value);
            }
        }
    }

    /*** Both ***/

    public get triggers(): number {
        if (
            this.leftTrigger > 0 &&
            this.rightTrigger > 0
        ) {
            return this.rightTrigger - this.leftTrigger;
        } else if (
            this.leftTrigger > 0 &&
            this.rightTrigger === 0
        ) {
            return -this.leftTrigger;
        } else if (
            this.rightTrigger > 0 &&
            this.leftTrigger === 0
        ) {
            return this.rightTrigger;
        }

        return 0;
    }

    /********** Buttons **********/

    public get buttonA(): boolean {
        return this._buttonA;
    }

    public set buttonA(state: boolean) {
        if (state !== this._buttonA) {
            this._buttonA = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.A, state);
            }
        }
    }

    public get buttonB(): boolean {
        return this._buttonB;
    }

    public set buttonB(state: boolean) {
        if (state !== this._buttonB) {
            this._buttonB = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.B, state);
            }
        }
    }

    public get buttonX(): boolean {
        return this._buttonX;
    }

    public set buttonX(state: boolean) {
        if (state !== this._buttonX) {
            this._buttonX = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.X, state);
            }
        }
    }

    public get buttonY(): boolean {
        return this._buttonY;
    }

    public set buttonY(state: boolean) {
        if (state !== this._buttonY) {
            this._buttonY = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.Y, state);
            }
        }
    }

    public get buttonStart(): boolean {
        return this._buttonStart;
    }

    public set buttonStart(state: boolean) {
        if (state !== this._buttonStart) {
            this._buttonStart = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.Start, state);
            }
        }
    }

    public get buttonBack(): boolean {
        return this._buttonBack;
    }

    public set buttonBack(state: boolean) {
        if (state !== this._buttonBack) {
            this._buttonBack = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.Back, state);
            }
        }
    }

    /*** Sticks ***/

    public get buttonLeftStick(): boolean {
        return this._buttonLeftStick;
    }

    public set buttonLeftStick(state: boolean) {
        if (state !== this._buttonLeftStick) {
            this._buttonLeftStick = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.LeftStick, state);
            }
        }
    }

    public get buttonRightStick(): boolean {
        return this._buttonRightStick;
    }

    public set buttonRightStick(state: boolean) {
        if (state !== this._buttonRightStick) {
            this._buttonRightStick = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.RightStick, state);
            }
        }
    }

    /*** Bottoms ***/

    public get buttonLB(): boolean {
        return this._buttonLB;
    }

    public set buttonLB(state: boolean) {
        if (state !== this._buttonLB) {
            this._buttonLB = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.LB, state);
            }
        }
    }

    public get buttonRB(): boolean {
        return this._buttonRB;
    }

    public set buttonRB(state: boolean) {
        if (state !== this._buttonRB) {
            this._buttonRB = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.RB, state);
            }
        }
    }

    /*** Triggers ***/

    public get buttonLT(): boolean {
        return this._buttonLT;
    }

    public set buttonLT(state: boolean) {
        if (state !== this._buttonLT) {
            this._buttonLT = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.LT, state);
            }
        }
    }

    public get buttonRT(): boolean {
        return this._buttonRT;
    }

    public set buttonRT(state: boolean) {
        if (state !== this._buttonRT) {
            this._buttonRT = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.RT, state);
            }
        }
    }

    /*** DPad ***/

    public get buttonDPadUp(): boolean {
        return this._buttonDPadUp;
    }

    public set buttonDPadUp(state: boolean) {
        if (state !== this._buttonDPadUp) {
            this._buttonDPadUp = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.DPadUp, state);
            }
        }
    }

    public get buttonDPadDown(): boolean {
        return this._buttonDPadDown;
    }

    public set buttonDPadDown(state: boolean) {
        if (state !== this._buttonDPadDown) {
            this._buttonDPadDown = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.DPadDown, state);
            }
        }
    }

    public get buttonDPadLeft(): boolean {
        return this._buttonDPadLeft;
    }

    public set buttonDPadLeft(state: boolean) {
        if (state !== this._buttonDPadLeft) {
            this._buttonDPadLeft = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.DPadLeft, state);
            }
        }
    }

    public get buttonDPadRight(): boolean {
        return this._buttonDPadRight;
    }

    public set buttonDPadRight(state: boolean) {
        if (state !== this._buttonDPadRight) {
            this._buttonDPadRight = state;

            if (this.onButtonChanged) {
                this.onButtonChanged(InputGamepadButtonEnum.DPadRight, state);
            }
        }
    }

}

export interface InputEnumStickValues {
    x: number;
    y: number;
}

export enum InputGamepadTypeEnum {
    Generic,
    Xbox360,
    XboxOne
}

export enum InputGamepadAxisEnum {
    LeftStickX,
    LeftStickY,
    RightStickX,
    RightStickY,
    LeftTrigger,
    RightTrigger,
    Triggers
}

/**
 * Directly related to the InputGamepadAxisEnum enum and the InputGamepad class.
 */
export enum InputGamepadAxisPropertyEnum {
    LeftStickX = 'leftStickX',
    LeftStickY = 'leftStickY',
    RightStickX = 'rightStickX',
    RightStickY = 'rightStickY',
    LeftTrigger = 'leftTrigger',
    RightTrigger  = 'rightTrigger',
    Triggers  = 'triggers' // A special, virtual field. If left trigger is pressed, the value is negative. If right trigger is pressed, it's positive.
}

export enum InputGamepadButtonEnum {
    A,
    B,
    X,
    Y,
    Start,
    Back,
    LeftStick,
    RightStick,
    LB,
    RB,
    LT,
    RT,
    DPadUp,
    DPadDown,
    DPadLeft,
    DPadRight
}

/**
 * Directly related to the InputGamepadButtonEnum enum and the InputGamepad class.
 */
export enum InputGamepadButtonPropertyEnum {
    A = 'buttonA',
    B = 'buttonB',
    X = 'buttonX',
    Y = 'buttonY',
    Start = 'buttonStart',
    Back = 'buttonBack',
    LeftStick = 'buttonLeftStick',
    RightStick = 'buttonRightStick',
    LB = 'buttonLB',
    RB = 'buttonRB',
    LT = 'leftTrigger',
    RT = 'rightTrigger',
    DPadUp = 'buttonDPadUp',
    DPadDown = 'buttonDPadDown',
    DPadLeft = 'buttonDPadLeft',
    DPadRight = 'buttonDPadRight',
}
