import {
  InputBindingsInterface,
  InputDeviceInterface,
} from './InputConstants';

export class InputDeviceOrientation implements InputDeviceInterface {
  private _bindings: InputBindingsInterface;

  public readonly hasOrientationSupport: boolean = 'DeviceOrientationEvent' in window;
  public readonly hasMotionSupport: boolean = 'DeviceMotionEvent' in window;
  public absolute: boolean;
  public alpha: number;
  public beta: number;
  public gamma: number;
  public acceleration: DeviceMotionEventAcceleration;
  public accelerationIncludingGravity: DeviceMotionEventAcceleration;
  public rotationRate: DeviceRotationRate;
  public interval: number;

  public setBindings(bindings: InputBindingsInterface) {
    this._bindings = bindings;
  }

  public bindEvents() {
    window.addEventListener(
      'deviceorientation',
      this._onHandleOrientation.bind(this),
      false
    );
    window.addEventListener(
      'deviceorientation',
      this._onHandleMotion.bind(this),
      false
    );
  }

  public unbindEvents() {
    window.removeEventListener(
      'deviceorientation',
      this._onHandleOrientation.bind(this),
      false
    );
    window.removeEventListener(
      'deviceorientation',
      this._onHandleMotion.bind(this),
      false
    );
  }

  public update() {}

  private _onHandleOrientation(e: DeviceOrientationEvent) {
    this.absolute = e.absolute;
    this.alpha = e.alpha;
    this.beta = e.beta;
    this.gamma = e.gamma;
  }

  private _onHandleMotion(e: DeviceMotionEvent) {
    this.acceleration = e.acceleration;
    this.accelerationIncludingGravity = e.accelerationIncludingGravity;
    this.rotationRate = e.rotationRate;
    this.interval = e.interval;
  }
}
