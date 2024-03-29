<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\String;

class EncodingConverter
{
    /**
     * @param string $stringCp1250
     * @return string
     */
    protected static function stringCp1250ToUtf8($stringCp1250)
    {
        return iconv('CP1250', 'UTF-8//TRANSLIT', $stringCp1250);
    }

    /**
     * @param array $array
     * @return array
     */
    protected static function arrayCp1250ToUtf8(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = self::arrayCp1250ToUtf8($value);
            } elseif (is_string($value)) {
                $array[$key] = self::stringCp1250ToUtf8($value);
            }
        }

        return $array;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function cp1250ToUtf8($value)
    {
        if (is_array($value)) {
            $value = self::arrayCp1250ToUtf8($value);
        } elseif (is_string($value)) {
            $value = self::stringCp1250ToUtf8($value);
        }

        return $value;
    }
}
