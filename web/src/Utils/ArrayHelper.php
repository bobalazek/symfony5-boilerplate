<?php

namespace App\Utils;

/**
 * Class ArrayHelper.
 */
class ArrayHelper
{
    /**
     * @param $array1
     * @param $array2
     */
    public static function changes($array1, $array2): array
    {
        $changes = [];

        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    if ($value !== $array2[$key]) {
                        $changes[$key] = [
                            $value,
                            $array2[$key],
                        ];
                    }
                } else {
                    if ($array2[$key] !== $value) {
                        $changes[$key] = [
                            $value,
                            $array2[$key],
                        ];
                    }
                }
            } else {
                $changes[$key] = [
                    null,
                    $value,
                ];
            }
        }

        return $changes;
    }
}
