<?php

namespace App\Utils;

/**
 * Class StringHelper.
 */
class StringHelper
{
    public static function generate(int $length = 10, bool $onlyUpperCase = true): string
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
