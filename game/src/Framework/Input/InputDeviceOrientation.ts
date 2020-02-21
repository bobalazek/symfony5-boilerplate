import { Observable } from 'babylonjs';

import { InputDeviceInterface } from './InputConstants';
import {
  InputBindingsInterface,
  AbstractInputBindings,
} from '../Gameplay/InputBindings';

export class InputDeviceOrientation implements InputDeviceInterface {
  public readonly hasOrientationSupport: boolean = 'DeviceOrientationEvent' in window;
  public readonly hasMotionSupport: boolean = 'DeviceMotionEvent' in window;

  public orientationObservable = new Observable<DeviceOrientationEvent>();
  public motionObservable = new Observable<DeviceMotionEvent>();

  public absolute: boolean;
  public alpha: number;
  public beta: number;
  public gamma: number;
  public acceleration: DeviceMotionEventAcceleration;
  public accelerationIncludingGravity: DeviceMotionEventAcceleration;
  public rotationRate: DeviceRotationRate;
  public interval: number;

  private _bindings: InputBindingsInterface = new AbstractInputBindings();

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

  public reset() {
    this.absolute = undefined;
    this.alpha = undefined;
    this.beta = undefined;
    this.gamma = undefined;
    this.acceleration = undefined;
    this.accelerationIncludingGravity = undefined;
    this.rotationRate = undefined;
    this.interval = undefined;
  }

  private _onHandleOrientation(e: DeviceOrientationEvent) {
    this.absolute = e.absolute;
    this.alpha = e.alpha;
    this.beta = e.beta;
    this.gamma = e.gamma;

    this.orientationObservable.notifyObservers(e);
  }

  private _onHandleMotion(e: DeviceMotionEvent) {
    this.acceleration = e.acceleration;
    this.accelerationIncludingGravity = e.accelerationIncludingGravity;
    this.rotationRate = e.rotationRate;
    this.interval = e.interval;

    this.motionObservable.notifyObservers(e);
  }
}
