/**
 * This is a tweaked version ob BabylonJS's TargetCamera + ArcRotateCamera, tweaked for my own neeeds.
 *
 * Resources:
 * - https://github.com/BabylonJS/Babylon.js/blob/master/src/Cameras/babylon.targetCamera.ts
 * - https://github.com/BabylonJS/Babylon.js/blob/master/src/Cameras/babylon.arcRotateCamera.ts
 */

export class Camera extends BABYLON.Camera {

    // General
    public alpha: number;
    public beta: number;
    public radius: number;
    public speed = 2.0;
    public rotation: BABYLON.Vector3 = new BABYLON.Vector3(0, 0, 0);
    public rotationQuaternion: BABYLON.Quaternion;
    public inertialAlphaOffset: number = 0;
    public inertialBetaOffset: number = 0;
    public inertialRadiusOffset: number = 0;
    public targetScreenOffset: BABYLON.Vector2 = BABYLON.Vector2.Zero();

    // Target
    public lockedTarget: any = null;
    public get target(): BABYLON.Vector3 {
        return this._target;
    }
    public set target(value: BABYLON.Vector3) {
        this.setTarget(value);
    }

    // Limits
    public allowUpsideDown: boolean = true;
    public lowerAlphaLimit: BABYLON.Nullable<number> = null;
    public upperAlphaLimit: BABYLON.Nullable<number> = null;
    public lowerBetaLimit: number = 0.01;
    public upperBetaLimit: number = Math.PI;
    public lowerRadiusLimit: BABYLON.Nullable<number> = null;
    public upperRadiusLimit: BABYLON.Nullable<number> = null;

    // Panning
    public panningAxis: BABYLON.Vector3 = new BABYLON.Vector3(1, 1, 0);
    public inertialPanningX: number = 0;
    public inertialPanningY: number = 0;
    public panningDistanceLimit: BABYLON.Nullable<number> = null;
    public panningOriginTarget: BABYLON.Vector3 = BABYLON.Vector3.Zero();
    public panningInertia: number = 0.9;

    // Collisions
    public onCollide: (collidedMesh: BABYLON.AbstractMesh) => void;
    public checkCollisions: boolean = false;
    public collisionRadius: BABYLON.Vector3 = new BABYLON.Vector3(0.5, 0.5, 0.5);

    // Private
    private _currentTarget: BABYLON.Vector3 = BABYLON.Vector3.Zero();
    private _viewMatrix: BABYLON.Matrix = BABYLON.Matrix.Zero();
    private _cameraTransformMatrix: BABYLON.Matrix = BABYLON.Matrix.Zero();
    private _target: BABYLON.Vector3 = BABYLON.Vector3.Zero();
    private _targetHost: BABYLON.Nullable<BABYLON.AbstractMesh>;
    private _localDirection: BABYLON.Vector3;
    private _transformedDirection: BABYLON.Vector3;
    private _collider: BABYLON.Collider;
    private _previousPosition = BABYLON.Vector3.Zero();
    private _collisionVelocity = BABYLON.Vector3.Zero();
    private _newPosition = BABYLON.Vector3.Zero();
    private _targetBoundingCenter: BABYLON.Nullable<BABYLON.Vector3>;
    private _computationVector: BABYLON.Vector3 = BABYLON.Vector3.Zero();
    private _storedPosition: BABYLON.Vector3;
    private _storedRotation: BABYLON.Vector3;
    private _storedRotationQuaternion: BABYLON.Quaternion;
    private _storedAlpha: number;
    private _storedBeta: number;
    private _storedRadius: number;
    private _storedTarget: BABYLON.Vector3;

    constructor(name: string, position: BABYLON.Vector3, scene: BABYLON.Scene) {
        super(name, position, scene);
    }

    /**
     * Return the current target position of the camera. This value is expressed in local space.
     */
    public getTarget(): BABYLON.Vector3 {
        return this._currentTarget;
    }

    public _getLockedTargetPosition(): BABYLON.Nullable<BABYLON.Vector3> {
        if (!this.lockedTarget) {
            return null;
        }

        if (this.lockedTarget.absolutePosition) {
            this.lockedTarget.computeWorldMatrix();
        }

        return this.lockedTarget.absolutePosition || this.lockedTarget;
    }

    public storeState(): BABYLON.Camera {
        this._storedPosition = this.position.clone();
        this._storedRotation = this.rotation.clone();
        if (this.rotationQuaternion) {
            this._storedRotationQuaternion = this.rotationQuaternion.clone();
        }

        this._storedAlpha = this.alpha;
        this._storedBeta = this.beta;
        this._storedRadius = this.radius;
        this._storedTarget = this._getTargetPosition().clone();

        return super.storeState();
    }

    /**
     * Restored camera state. You must call storeState() first
     */
    public _restoreStateValues(): boolean {
        if (!super._restoreStateValues()) {
            return false;
        }

        this.position = this._storedPosition.clone();
        this.rotation = this._storedRotation.clone();

        if (this.rotationQuaternion) {
            this.rotationQuaternion = this._storedRotationQuaternion.clone();
        }

        this.alpha = this._storedAlpha;
        this.beta = this._storedBeta;
        this.radius = this._storedRadius;
        this.setTarget(this._storedTarget.clone());

        this.inertialAlphaOffset = 0;
        this.inertialBetaOffset = 0;
        this.inertialRadiusOffset = 0;
        this.inertialPanningX = 0;
        this.inertialPanningY = 0;

        return true;
    }

    // Cache
    public _initCache() {
        super._initCache();
        this._cache.lockedTarget = new BABYLON.Vector3(
            Number.MAX_VALUE,
            Number.MAX_VALUE,
            Number.MAX_VALUE
        );
        this._cache.rotation = new BABYLON.Vector3(
            Number.MAX_VALUE,
            Number.MAX_VALUE,
            Number.MAX_VALUE
        );
        this._cache.rotationQuaternion = new BABYLON.Quaternion(
            Number.MAX_VALUE,
            Number.MAX_VALUE,
            Number.MAX_VALUE,
            Number.MAX_VALUE
        );
        this._cache._target = new BABYLON.Vector3(
            Number.MAX_VALUE,
            Number.MAX_VALUE,
            Number.MAX_VALUE
        );
        this._cache.alpha = undefined;
        this._cache.beta = undefined;
        this._cache.radius = undefined;
        this._cache.targetScreenOffset = BABYLON.Vector2.Zero();
    }

    public _updateCache(ignoreParentClass?: boolean): void {
        if (!ignoreParentClass) {
            super._updateCache();
        }

        const lockedTargetPosition = this._getLockedTargetPosition();
        if (!lockedTargetPosition) {
            this._cache.lockedTarget = null;
        } else {
            if (!this._cache.lockedTarget) {
                this._cache.lockedTarget = lockedTargetPosition.clone();
            } else {
                this._cache.lockedTarget.copyFrom(lockedTargetPosition);
            }
        }

        this._cache.rotation.copyFrom(this.rotation);
        if (this.rotationQuaternion) {
            this._cache.rotationQuaternion.copyFrom(this.rotationQuaternion);
        }

        this._cache._target.copyFrom(this._getTargetPosition());
        this._cache.alpha = this.alpha;
        this._cache.beta = this.beta;
        this._cache.radius = this.radius;
        this._cache.targetScreenOffset.copyFrom(this.targetScreenOffset);
    }

    public _getViewMatrix(): BABYLON.Matrix {
        const cosA = Math.cos(this.alpha);
        const sinA = Math.sin(this.alpha);
        const cosB = Math.cos(this.beta);
        let sinB = Math.sin(this.beta);

        if (sinB === 0) {
            sinB = 0.0001;
        }

        const target = this._getTargetPosition();
        this._computationVector.copyFromFloats(
            this.radius * cosA * sinB,
            this.radius * cosB,
            this.radius * sinA * sinB
        );
        target.addToRef(
            this._computationVector,
            this._newPosition
        );

        if (this.getScene().collisionsEnabled && this.checkCollisions) {
            if (!this._collider) {
                this._collider = new BABYLON.Collider();
            }

            this._collider._radius = this.collisionRadius;
            this._newPosition.subtractToRef(
                this.position,
                this._collisionVelocity
            );
            this.getScene().collisionCoordinator.getNewPosition(
                this.position,
                this._collisionVelocity,
                this._collider,
                3,
                null,
                this._onCollisionPositionChange,
                this.uniqueId
            );
        } else {
            this.position.copyFrom(this._newPosition);

            let up = this.upVector;
            if (this.allowUpsideDown && sinB < 0) {
                up = up.clone();
                up = up.negate();
            }

            if (this.getScene().useRightHandedSystem) {
                BABYLON.Matrix.LookAtRHToRef(this.position, target, up, this._viewMatrix);
            } else {
                BABYLON.Matrix.LookAtLHToRef(this.position, target, up, this._viewMatrix);
            }

            this._viewMatrix.addAtIndex(12, this.targetScreenOffset.x);
            this._viewMatrix.addAtIndex(13, this.targetScreenOffset.y);
        }
        this._currentTarget = target;

        return this._viewMatrix;
    }

    private _getTargetPosition(): BABYLON.Vector3 {
        if (this._targetHost && this._targetHost.getAbsolutePosition) {
            let pos: BABYLON.Vector3 = this._targetHost.getAbsolutePosition();
            if (this._targetBoundingCenter) {
                pos.addToRef(this._targetBoundingCenter, this._target);
            } else {
                this._target.copyFrom(pos);
            }
        }

        const lockedTargetPosition = this._getLockedTargetPosition();
        if (lockedTargetPosition) {
            return lockedTargetPosition;
        }

        return this._target;
    }

    public _isSynchronizedViewMatrix(): boolean {
        if (!super._isSynchronizedViewMatrix()) {
            return false;
        }

        return this._cache._target.equals(this._getTargetPosition()) &&
            this._cache.alpha === this.alpha &&
            this._cache.beta === this.beta &&
            this._cache.radius === this.radius &&
            this._cache.targetScreenOffset.equals(this.targetScreenOffset);
    }

    public _checkInputs(): void {
        // Inertia
        if (this.inertialAlphaOffset !== 0 || this.inertialBetaOffset !== 0 || this.inertialRadiusOffset !== 0) {
            if (this.getScene().useRightHandedSystem) {
                this.alpha -= this.beta <= 0 ? -this.inertialAlphaOffset : this.inertialAlphaOffset;
            } else {
                this.alpha += this.beta <= 0 ? -this.inertialAlphaOffset : this.inertialAlphaOffset;
            }

            this.beta += this.inertialBetaOffset;

            this.radius -= this.inertialRadiusOffset;
            this.inertialAlphaOffset *= this.inertia;
            this.inertialBetaOffset *= this.inertia;
            this.inertialRadiusOffset *= this.inertia;

            if (Math.abs(this.inertialAlphaOffset) < BABYLON.Epsilon) {
                this.inertialAlphaOffset = 0;
            }

            if (Math.abs(this.inertialBetaOffset) < BABYLON.Epsilon) {
                this.inertialBetaOffset = 0;
            }

            if (Math.abs(this.inertialRadiusOffset) < this.speed * BABYLON.Epsilon) {
                this.inertialRadiusOffset = 0;
            }
        }

        // Panning inertia
        if (this.inertialPanningX !== 0 || this.inertialPanningY !== 0) {
            if (!this._localDirection) {
                this._localDirection = BABYLON.Vector3.Zero();
                this._transformedDirection = BABYLON.Vector3.Zero();
            }

            this._localDirection.copyFromFloats(
                this.inertialPanningX,
                this.inertialPanningY,
                this.inertialPanningY
            );
            this._localDirection.multiplyInPlace(this.panningAxis);
            this._viewMatrix.invertToRef(this._cameraTransformMatrix);
            BABYLON.Vector3.TransformNormalToRef(
                this._localDirection,
                this._cameraTransformMatrix,
                this._transformedDirection
            );
            // Eliminate y if map panning is enabled (panningAxis == 1,0,1)
            if (!this.panningAxis.y) {
                this._transformedDirection.y = 0;
            }

            if (!this._targetHost) {
                if (this.panningDistanceLimit) {
                    this._transformedDirection.addInPlace(this._target);
                    const distanceSquared = BABYLON.Vector3.DistanceSquared(
                        this._transformedDirection,
                        this.panningOriginTarget
                    );
                    if (distanceSquared <= (this.panningDistanceLimit * this.panningDistanceLimit)) {
                        this._target.copyFrom(this._transformedDirection);
                    }
                } else {
                    this._target.addInPlace(this._transformedDirection);
                }
            }

            this.inertialPanningX *= this.panningInertia;
            this.inertialPanningY *= this.panningInertia;

            if (Math.abs(this.inertialPanningX) < this.speed * BABYLON.Epsilon) {
                this.inertialPanningX = 0;
            }

            if (Math.abs(this.inertialPanningY) < this.speed * BABYLON.Epsilon) {
                this.inertialPanningY = 0;
            }
        }

        // Limits
        this._checkLimits();

        // Inputs
        super._checkInputs();
    }

    private _checkLimits() {
        if (this.lowerBetaLimit === null || this.lowerBetaLimit === undefined) {
            if (this.allowUpsideDown && this.beta > Math.PI) {
                this.beta = this.beta - (2 * Math.PI);
            }
        } else {
            if (this.beta < this.lowerBetaLimit) {
                this.beta = this.lowerBetaLimit;
            }
        }

        if (this.upperBetaLimit === null || this.upperBetaLimit === undefined) {
            if (this.allowUpsideDown && this.beta < -Math.PI) {
                this.beta = this.beta + (2 * Math.PI);
            }
        } else {
            if (this.beta > this.upperBetaLimit) {
                this.beta = this.upperBetaLimit;
            }
        }

        if (this.lowerAlphaLimit && this.alpha < this.lowerAlphaLimit) {
            this.alpha = this.lowerAlphaLimit;
        }

        if (this.upperAlphaLimit && this.alpha > this.upperAlphaLimit) {
            this.alpha = this.upperAlphaLimit;
        }

        if (this.lowerRadiusLimit && this.radius < this.lowerRadiusLimit) {
            this.radius = this.lowerRadiusLimit;
        }

        if (this.upperRadiusLimit && this.radius > this.upperRadiusLimit) {
            this.radius = this.upperRadiusLimit;
        }
    }

    public rebuildAnglesAndRadius() {
        this.position.subtractToRef(this._getTargetPosition(), this._computationVector);
        this.radius = this._computationVector.length();

        if (this.radius === 0) {
            this.radius = 0.0001; // Just to avoid division by zero
        }

        // Alpha
        this.alpha = Math.acos(
            this._computationVector.x / Math.sqrt(
                Math.pow(this._computationVector.x, 2) +
                Math.pow(this._computationVector.z, 2)
            )
        );

        if (this._computationVector.z < 0) {
            this.alpha = 2 * Math.PI - this.alpha;
        }

        // Beta
        this.beta = Math.acos(this._computationVector.y / this.radius);

        this._checkLimits();
    }

    public setPosition(position: BABYLON.Vector3): void {
        if (this.position.equals(position)) {
            return;
        }
        this.position.copyFrom(position);

        this.rebuildAnglesAndRadius();
    }

    public setTarget(
        target: BABYLON.AbstractMesh | BABYLON.Vector3,
        toBoundingCenter = false,
        allowSamePosition = false
    ): void {
        if ((<any>target).getBoundingInfo) {
            if (toBoundingCenter) {
                this._targetBoundingCenter = (<any>target).getBoundingInfo().boundingBox.centerWorld.clone();
            } else {
                this._targetBoundingCenter = null;
            }

            this._targetHost = <BABYLON.AbstractMesh>target;
            this._target = this._getTargetPosition();
        } else {
            const newTarget = <BABYLON.Vector3>target;
            const currentTarget = this._getTargetPosition();
            if (currentTarget && !allowSamePosition && currentTarget.equals(newTarget)) {
                return;
            }
            this._targetHost = null;
            this._target = newTarget;
            this._targetBoundingCenter = null;
        }

        this.rebuildAnglesAndRadius();
    }

    private _onCollisionPositionChange = (
        collisionId: number,
        newPosition: BABYLON.Vector3,
        collidedMesh: BABYLON.Nullable<BABYLON.AbstractMesh> = null
    ) => {
        if (!collidedMesh) {
            this._previousPosition.copyFrom(this.position);
        } else {
            this.setPosition(newPosition);

            if (this.onCollide) {
                this.onCollide(collidedMesh);
            }
        }

        // Recompute because of constraints
        const cosA = Math.cos(this.alpha);
        const sinA = Math.sin(this.alpha);
        const cosB = Math.cos(this.beta);
        let sinB = Math.sin(this.beta);

        if (sinB === 0) {
            sinB = 0.0001;
        }

        const target = this._getTargetPosition();
        this._computationVector.copyFromFloats(
            this.radius * cosA * sinB,
            this.radius * cosB,
            this.radius * sinA * sinB
        );
        target.addToRef(
            this._computationVector,
            this._newPosition
        );
        this.position.copyFrom(this._newPosition);

        let up = this.upVector;
        if (this.allowUpsideDown && this.beta < 0) {
            up = up.clone();
            up = up.negate();
        }

        BABYLON.Matrix.LookAtLHToRef(
            this.position,
            target,
            up,
            this._viewMatrix
        );

        this._viewMatrix.addAtIndex(12, this.targetScreenOffset.x);
        this._viewMatrix.addAtIndex(13, this.targetScreenOffset.y);
    }

    public dispose(): void {
        super.dispose();
    }

    public getClassName(): string {
        return 'Camera';
    }

}
