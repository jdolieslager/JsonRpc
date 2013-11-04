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

    /**
     * Get an item from the config file
     *
     * @param string $needle   Dot separated string with the path you want
     * @param array  $haystack Dot separated string with the path you want
     * @param mixed  $default  The default value when the item has not be found
     * @example daemonizer.locations.pids => $config['daemonizer']['locations']['pids']
     *
     * @return mixed The value from the target | mixed on not found
     */
    public static function arrayTarget($needle, $haystack, $default = null)
    {
        // Split requested target
        $parts = explode('.', $needle);

        // Loop through the target
        foreach ($parts as $part) {
            if (is_array($haystack) === false) {
                return $default;
            }

            // When not exists return default value
            if (array_key_exists($part, $haystack) === false) {
                return $default;
            }

            $haystack = $haystack[$part];
        }

        return $haystack;
    }
}
