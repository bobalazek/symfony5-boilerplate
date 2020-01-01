import { Room } from 'colyseus.js';

export class Entity {

    constructor (private _mesh: BABYLON.AbstractMesh) {}

    public getMesh(): BABYLON.AbstractMesh {
        return this._mesh;
    }

    public syncWithServer(
        serverRoom: Room,
        serverTransformUpdateTolerance: number,
        serverUpdateInterval: number
    ) {
        let lastMeshTransform = null;
        setInterval(() => {
            if (
                lastMeshTransform === null ||
                !this.isMeshTransformSameAs(
                    lastMeshTransform,
                    serverTransformUpdateTolerance
                )
            ) {
                const meshTransform = this.getMeshTransform();
                const meshId = this._mesh.id;

                let detail: any = {
                    transformMatrix: Entity.serializeMeshTransformMatrix(meshTransform),
                };
                if (
                    this._mesh.metadata === null ||
                    !this._mesh.metadata.serverReplicated
                ) {
                    detail.id = meshId;
                    detail.mesh = meshId.split('_')[0];
                }
                
                serverRoom.send({
                    action: 'entity:transform:update',
                    detail: detail,
                });
                lastMeshTransform = meshTransform;
            }
        }, serverUpdateInterval);
    }

    public getMeshTransform() {
        const mesh = this.getMesh();
        // const meshLinearVelocity = mesh.physicsImpostor.getLinearVelocity();
        // const meshAngularVelocity = mesh.physicsImpostor.getAngularVelocity();

        const position = {
            x: mesh.position.x,
            y: mesh.position.y,
            z: mesh.position.z,
        };
        const rotation = {
            x: mesh.rotationQuaternion.x,
            y: mesh.rotationQuaternion.y,
            z: mesh.rotationQuaternion.z,
            w: mesh.rotationQuaternion.w,
        };
        // const scale = { x: mesh.scaling.x, y: mesh.scaling.y, z: mesh.scaling.z };
        // const linearVelocity = { x: meshLinearVelocity.x, y: meshLinearVelocity.y, z: meshLinearVelocity.z };
        // const angularVelocity = { x: meshAngularVelocity.x, y: meshAngularVelocity.y, z: meshAngularVelocity.z };

        let transform = {
            position: position,
            rotation: rotation,
            // scale: scale,
            // linearVelocity: linearVelocity,
            // angularVelocity: angularVelocity,
        };

        return transform;
    }

    public isMeshTransformSameAs(lastTransform?, tolerance?: number): boolean {
        if (lastTransform === null) {
            return false;
        }

        const meshTransform = this.getMeshTransform();

        if (
            lastTransform.position.x === meshTransform.position.x &&
            lastTransform.position.y === meshTransform.position.y &&
            lastTransform.position.z === meshTransform.position.z &&
            lastTransform.rotation.x === meshTransform.rotation.x &&
            lastTransform.rotation.y === meshTransform.rotation.y &&
            lastTransform.rotation.z === meshTransform.rotation.z &&
            lastTransform.rotation.w === meshTransform.rotation.w /* &&
            lastTransform.scale.x === meshTransform.scale.x &&
            lastTransform.scale.y === meshTransform.scale.y &&
            lastTransform.scale.z === meshTransform.scale.z */
        ) {
            return true;
        }

        if (
            tolerance !== undefined && (
                Math.abs(lastTransform.position.x - meshTransform.position.x) < tolerance &&
                Math.abs(lastTransform.position.y - meshTransform.position.y) < tolerance &&
                Math.abs(lastTransform.position.z - meshTransform.position.z) < tolerance &&
                Math.abs(lastTransform.rotation.x - meshTransform.rotation.x) < tolerance &&
                Math.abs(lastTransform.rotation.y - meshTransform.rotation.y) < tolerance &&
                Math.abs(lastTransform.rotation.z - meshTransform.rotation.z) < tolerance &&
                Math.abs(lastTransform.rotation.w - meshTransform.rotation.w) < tolerance /* &&
                Math.abs(lastTransform.scale.x - meshTransform.rotation.x) > tolerance &&
                Math.abs(lastTransform.scale.y - meshTransform.rotation.y) > tolerance &&
                Math.abs(lastTransform.scale.z - meshTransform.rotation.z) > tolerance && */
            )
        ) {
            return true;
        }

        return false;
    }

    public static serializeMeshTransformMatrix(meshTransform: any): string {
        return [
            meshTransform.position.x,
            meshTransform.position.y,
            meshTransform.position.z,
            meshTransform.rotation.x,
            meshTransform.rotation.y,
            meshTransform.rotation.z,
            meshTransform.rotation.w,
        ].join('|');
    }

    public static deserializeMeshTransformMatrix(serializedMeshTransformMatrix: string): any {
        const serializedMeshTransformMatrixSplit = serializedMeshTransformMatrix.split('|');
        return {
            position: {
                x: serializedMeshTransformMatrixSplit[0],
                y: serializedMeshTransformMatrixSplit[1],
                z: serializedMeshTransformMatrixSplit[2],
            },
            rotation: {
                x: serializedMeshTransformMatrixSplit[3],
                y: serializedMeshTransformMatrixSplit[4],
                z: serializedMeshTransformMatrixSplit[5],
                w: serializedMeshTransformMatrixSplit[6],
            },
        };
    }

}
