import * as BABYLON from 'babylonjs';

export class Serializer {
  public static serializeTransformNode(transformNode: BABYLON.TransformNode): string {
    return [
        parseFloat(transformNode.position.x.toFixed(4)),
        parseFloat(transformNode.position.y.toFixed(4)),
        parseFloat(transformNode.position.z.toFixed(4)),
        parseFloat(transformNode.rotation.x.toFixed(4)),
        parseFloat(transformNode.rotation.y.toFixed(4)),
        parseFloat(transformNode.rotation.z.toFixed(4)),
    ].join('|');
  }

  public static deserializeMeshTransformMatrix(serializedTransformNode: string): any {
    const split = serializedTransformNode.split('|');
    return {
        position: {
            x: split[0],
            y: split[1],
            z: split[2],
        },
        rotation: {
            x: split[3],
            y: split[4],
            z: split[5],
        },
    };
  }
}
