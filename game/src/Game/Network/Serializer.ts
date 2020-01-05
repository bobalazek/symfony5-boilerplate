import * as BABYLON from 'babylonjs';

export class Serializer {
  public static serializeTransformNode(transformNode: BABYLON.TransformNode): string {
    return [
        parseFloat(transformNode.position.x.toFixed(5)),
        parseFloat(transformNode.position.y.toFixed(5)),
        parseFloat(transformNode.position.z.toFixed(5)),
        parseFloat(transformNode.rotation.x.toFixed(5)),
        parseFloat(transformNode.rotation.y.toFixed(5)),
        parseFloat(transformNode.rotation.z.toFixed(5)),
    ].join('|');
  }

  public static deserializeTransformNode(serializedTransformNode: string): any {
    const split = serializedTransformNode.split('|');
    return {
        position: {
            x: parseFloat(split[0]),
            y: parseFloat(split[1]),
            z: parseFloat(split[2]),
        },
        rotation: {
            x: parseFloat(split[3]),
            y: parseFloat(split[4]),
            z: parseFloat(split[5]),
        },
    };
  }
}
