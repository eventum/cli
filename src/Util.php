<?php

namespace Eventum\Console;

/**
 * Various utility methods
 *
 * @package Eventum\Console
 */
class Util
{
    /**
     * Format a byte count into a human-readable representation.
     *
     * @link http://api.propelorm.org/2.0-master/Propel/Runtime/Util/Profiler.html#method_formatMemory
     * @param integer $bytes Byte count to convert. Can be negative.
     * @param integer $precision How many decimals to include.
     *
     * @return string
     */
    public function formatMemory($bytes, $precision = 3)
    {
        $absBytes = abs($bytes);
        $sign = ($bytes == $absBytes) ? 1 : -1;
        $suffix = array('B', 'kiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
        $total = count($suffix);

        for ($i = 0; $absBytes > 1024 && $i < $total; $i++) {
            $absBytes /= 1024;
        }

        return self::toPrecision($sign * $absBytes, $precision) . $suffix[$i];
    }

    /**
     * Rounding to significant digits (sort of like JavaScript's toPrecision()).
     *
     * @link http://api.propelorm.org/2.0-master/Propel/Runtime/Util/Profiler.html#method_toPrecision
     * @param float $number Value to round
     * @param integer $significantFigures Number of significant figures
     *
     * @return float
     */
    public function toPrecision($number, $significantFigures = 3)
    {
        if (0 === $number) {
            return 0;
        }

        $significantDecimals = floor($significantFigures - log10(abs($number)));
        $magnitude = pow(10, $significantDecimals);
        $shifted = round($number * $magnitude);

        return number_format($shifted / $magnitude, $significantDecimals);
    }
}