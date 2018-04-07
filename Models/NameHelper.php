<?php
/**
 * Created by PhpStorm.
 * User: Arbor
 * Date: 2/11/18
 * Time: 13:00
 */

namespace CZJPScraping\Models;

/**
 * Class NameHelper
 * @package CZJPScraping\Models
 */
class NameHelper
{
    public static function flushSpecialChars(string $name) : string
    {
        if (strpos($name, 'Š') !== false) {
            $name = str_replace('Š', 'S', $name);
        }

        if (strpos($name, 'š') !== false) {
            $name = str_replace('š', 's', $name);
        }

        if (strpos($name, 'Č') !== false) {
            $name = str_replace('Č', 'C', $name);
        }

        if (strpos($name, 'č') !== false) {
            $name = str_replace('č', 'c', $name);
        }

        if (strpos($name, 'Ć') !== false) {
            $name = str_replace('Ć', 'C', $name);
        }

        if (strpos($name, 'ć') !== false) {
            $name = str_replace('ć', 'c', $name);
        }

        if (strpos($name, 'Đ') !== false) {
            $name = str_replace('Đ', 'Dj', $name);
        }

        if (strpos($name, 'đ') !== false) {
            $name = str_replace('đ', 'dj', $name);
        }

        if (strpos($name, 'Ž') !== false) {
            $name = str_replace('Ž', 'Z', $name);
        }

        if (strpos($name, 'ž') !== false) {
            $name = str_replace('ž', 'z', $name);
        }

        $name = preg_replace("/[^a-zA-Z ]/", '', $name);

        return $name;
    }
}