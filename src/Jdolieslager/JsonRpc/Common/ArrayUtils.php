<?php
namespace Jdolieslager\JsonRpc\Common;

/**
 * @category    Jdolieslager
 * @package     JsonRpc
 * @subpackage  Common
 */
class ArrayUtils
{
    /**
     * Checks if the array is numeric
     *
     * @param  array $array
     * @return boolean
     */
    public static function isNumeric(array $array)
    {
        $isNumeric = true;
        foreach ($array as $key => $item) {
            if ((int) $key !== $key) {
                $isNumeric = false;
                break;
            }
        }

        return $isNumeric;
    }

    /**
     * Checks if the array is associative
     *
     * @param  array $array
     * @return boolean
     */
    public static function isAssociative(array $array)
    {
        return (bool) (static::isNumeric($array) === false);
    }
}
