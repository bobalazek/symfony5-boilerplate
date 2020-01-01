import { GameManager } from "../../../Framework/Core/GameManager";
import { AbstractController } from './AbstractController';
import { Camera } from '../../Camera/Camera';

export class PlayerController extends AbstractController {

    // Input
    private _inputLocation: BABYLON.Vector2 = BABYLON.Vector2.Zero();
    private _inputRotation: BABYLON.Vector2 = BABYLON.Vector2.Zero();

    /**
     * Is the input currently enabled?
     */
    private _inputEnabled: boolean = true;

    /**
     * What are we controlling?
     */
    private _targetMesh: BABYLON.AbstractMesh;

    /**
     * With which camera?
     */
    private _camera: Camera;

    /**
     * In which mode are we in?
     */
    private _mode: PlayerControllerModeEnum = PlayerControllerModeEnum.Character;

    /**
     * How far back on the forward axis, the camera should be behind the target character.
     */
    public cameraRadius: number = 6;

    /**
     * This is the speed of the mesh for the forward/right direction.
     */
    public locationMultiplier: number = 2;

    /**
     * This is the look speed of the input device (mouse/controller).
     */
    public rotationMultiplier: number = 0.05;

    /**
     * This is the rotation speed of the input device (mouse/controller),
     * when this.moveMeshYawAsStrafing is set to false. In other words that means,
     * how fast should the character and camera rotate left/right, when you press left/right
     * movement keys. If the this.moveMeshYawAsStrafing would be set to true,
     * the movement keys would strafe the character left/right (like in first-person).
     */
    public moveRotationMultiplier: number = 0.01;

    /**
     * This will rotate the character on the x axis (yaw), onto the same direction,
     * as you are looking with the camera.
     */
    public rotateMeshYawByLook: boolean = true;

    /**
     * This will allow you to arc rotate around you character,
     * without the chatacter being moved instanteniously into the same direction.
     * It will rotate the chatacter, after you start moving.
     */
    public rotateMeshYawByLookAfterMove: boolean = false;

    /**
     * When this is set to true, the this._inputLocation.x will be used,
     * to strafe the character. Else it will act the same as the mouse look,
     * where it will turn the character on the yaw axis.
     */
    public moveMeshYawAsStrafing: boolean = true;

    public start () {

        super.start();

        GameManager.inputManager.addPointerLock();

        let scene = GameManager.activeLevel.getScene();

        this._targetMesh = GameManager.activeLevel.getPlayer().getMesh();

        scene.activeCamera = this._camera = new Camera(
            'camera',
            this._targetMesh.position,
            scene
        );
        this._camera.alpha = -Math.PI / 2;
        this._camera.beta = Math.PI / 2;
        this._camera.radius = this.cameraRadius;
        this._camera.lockedTarget = this._targetMesh;
        this._camera.checkCollisions = true;

    }

    /********** Update methods **********/

    public update () {

        if (this._inputEnabled) {
            this.updateInput();
        }

        this.updateMesh();
        this.updateCamera();

    }

    public updateInput() {

        const inputAxes = GameManager.inputManager.getAxes();

        // Location/Move
        this._inputLocation = BABYLON.Vector2.Zero();

        if (inputAxes['moveForward'] !== 0) {
            this._inputLocation.addInPlace(
                new BABYLON.Vector2(0, inputAxes['moveForward'] * this.locationMultiplier)
            );
        }

        if (inputAxes['moveRight'] !== 0) {
            this._inputLocation.addInPlace(
                new BABYLON.Vector2(inputAxes['moveRight'] * this.locationMultiplier, 0)
            );
        }

        // Rotation/Look
        this._inputRotation = BABYLON.Vector2.Zero();

        if (inputAxes['lookRight'] !== 0) {
            this._inputRotation.addInPlace(
                new BABYLON.Vector2(inputAxes['lookRight'] * this.rotationMultiplier, 0)
            );
        }

        if (inputAxes['lookUp'] !== 0) {
            this._inputRotation.addInPlace(
                new BABYLON.Vector2(0, inputAxes['lookUp'] * this.rotationMultiplier)
            );
        }

    }

    public updateMesh() {

        const player = GameManager.activeLevel.getPlayer();
        if (player) {
            if (this._inputRotation.x !== 0) {
                this._targetMesh.addRotation(
                    0,
                    this._inputRotation.x * this.rotationMultiplier,
                    0
                );
            }

            if (
                !this.moveMeshYawAsStrafing &&
                this._inputLocation.x !== 0
            ) {
                this._targetMesh.addRotation(
                    0,
                    this._inputLocation.x * this.moveRotationMultiplier,
                    0
                );
            }

            if (this._inputLocation !== BABYLON.Vector2.Zero()) {
                const direction = new BABYLON.Vector3(
                    this.moveMeshYawAsStrafing ? this._inputLocation.x : 0,
                    0,
                    this._inputLocation.y
                ).normalize().scaleInPlace(this.locationMultiplier);

                this._targetMesh.translate(
                    direction,
                    0.1,
                    BABYLON.Space.LOCAL
                );
            }
        }

    }

    public updateCamera() {

        this._camera.alpha += this._inputRotation.x * this.rotationMultiplier * -1;
        this._camera.beta += this._inputRotation.y * this.rotationMultiplier * -1;

        if (
            !this.moveMeshYawAsStrafing &&
            this._inputLocation.x !== 0
        ) {
            this._camera.alpha += this._inputLocation.x * this.moveRotationMultiplier * -1;
        }

        // TODO: must be able to look upwards
        // const forwardRay = this._camera.getForwardRay(this.cameraRadius);
        // const hits = forwardRay.intersectsMeshes(this._camera.getScene().meshes);
        // console.log(hits)

    }

    /********** Target mesh **********/
    public getTargetMesh(): BABYLON.AbstractMesh {
        return this._targetMesh;
    }

    public setTargetMesh(targetMesh: BABYLON.AbstractMesh) {
        this._targetMesh = targetMesh;
    }

    /********** Mode **********/
    public getMode(): PlayerControllerModeEnum {
        return this._mode;
    }

    public setMode(mode: PlayerControllerModeEnum) {
        this._mode = mode;
    }

    /********** Input **********/
    public enableInput(preventReset?: boolean) {

        this._inputEnabled = true;
        if (preventReset !== true) {
            this.resetInput();
        }

    }

    public disableInput(preventReset?: boolean) {

        this._inputEnabled = false;
        if (preventReset !== true) {
            this.resetInput();
        }

    }

    public resetInput() {

        this._inputLocation = BABYLON.Vector2.Zero();
        this._inputRotation = BABYLON.Vector2.Zero();

    }

    /********** Jump **********/
    public doJump() {
        // TODO
    }

}

export enum PlayerControllerModeEnum {
    Character,
    Spectator
}
