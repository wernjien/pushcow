<?php

namespace App\Support;

class StringParser
{
    /**
     * Constant representing the value is array.
     *
     * @var string
     */
    const TYPE_ARRAY = 'ARRAY';

    /**
     * Constant representing the value is JSON.
     *
     * @var string
     */
    const TYPE_JSON = 'JSON';

    /**
     * Detect the format and parse the string accordingly.
     *
     * @param  string  $string
     * @return mixed
     */
    public static function auto($string)
    {
        switch (static::detect($string)) {
            case static::TYPE_ARRAY:
                return static::parseArray($string);
            case static::TYPE_JSON:
                return static::parseJson($string);
            default:
                return $string;
        }
    }

    /**
     * Detect the format to the given string.
     *
     * @param  string  $string
     * @return string|bool
     */
    public static function detect($string)
    {
        if (static::isArray($string)) {
            return static::TYPE_ARRAY;
        }

        if (static::isJson($string)) {
            return static::TYPE_JSON;
        }

        return false;
    }

    /**
     * Determind whether the string is in array format.
     *
     * @param  string  $string
     * @return bool
     */
    public static function isArray($string)
    {
        $first = substr($string, 0, 1);
        $last = substr($string, -1);

        return $first.$last == '[]';
    }

    /**
     * Parse the array string into array.
     *
     * @param  string  $string
     * @return array
     */
    public static function parseArray($string)
    {
        $string = trim($string, '[ ]');
        $array = explode(',', $string);

        array_walk($array, function (&$value) {
            $value = trim($value, '"\' ');
        });

        return $array;
    }

    /**
     * Determind whether the string is in JSON format.
     *
     * @param  string  $string
     * @return bool
     */
    public static function isJson($string)
    {
        return (bool) json_decode($string);
    }

    /**
     * Parse the JSON string into associative array.
     *
     * @param  string  $string
     * @return array
     */
    public static function parseJson($string)
    {
        return json_decode($string, true);
    }
}
