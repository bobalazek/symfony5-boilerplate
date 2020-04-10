<?php

namespace App\Utils;

/**
 * Class StringHelper.
 */
class StringHelper
{
    /**
     * @param bool  $length
     * @param mixed $onlyUpperCase
     */
    public static function generate(int $length = 10, $onlyUpperCase = true): string
    {
        $characters = $onlyUpperCase
            ? '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
