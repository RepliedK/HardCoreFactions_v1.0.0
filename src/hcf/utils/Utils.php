<?php

namespace hcf\utils;

class Utils
{
    private function __construct() { }

    public static function array_shift_circular(array $array, int $steps = 1): array {
        if($steps == 0) {
            return $array;
        }

        if(($l = count($array)) == 0) {
            return $array;
        }
        $steps = ($steps % $l) * -1;

        return array_merge(
            array_slice($array, $steps),
            array_slice($array, 0, $steps)
        );
    }

    /**
     * Returns a new array with the values of the old array padded
     * on the center of the new array given a specified size
     *
     * @param array $array
     * @param int $size
     * @param mixed $padding
     *
     * @return array
     *
     */
    public static function array_center_pad(array $array, int $size, mixed $padding): array {
        $base = array_fill(0, $size, $padding);
        $startIndex = max((int)(floor($size / 2) - floor(count($array) / 2)), 0);
        $vals = array_values($array);
        foreach($vals as $i => $value) {
            $base[$startIndex + $i] = $value;
        }

        return $base;
    }

    public static function has_string_keys(array $array): bool {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
    
}