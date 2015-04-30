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

    /**
     * Attempt to detect the MIME type of a file using available extensions
     *
     * This method will try to detect the MIME type of a file. If the fileinfo
     * extension is available, it will be used. If not, the mime_magic
     * extension which is deprecated but is still available in many PHP setups
     * will be tried.
     *
     * If neither extension is available, the default application/octet-stream
     * MIME type will be returned
     *
     * @author Elan Ruusamäe <glen@delfi.ee>
     * @param string $file File path
     * @param string $default Optional default mime type to return if the file's mime type can not be identified.
     * @return string MIME type
     */
    public static function getFileMimeType($file, $default = 'application/octet-stream')
    {
        $mime_type = null;

        // First try with fileinfo functions
        if (function_exists('finfo_open')) {
            static $fileInfoDb;
            if ($fileInfoDb === null) {
                $fileInfoDb = finfo_open(FILEINFO_MIME);
            }

            if ($fileInfoDb) {
                $mime_type = finfo_file($fileInfoDb, $file);
            }

        } elseif (function_exists('mime_content_type')) {
            $mime_type = mime_content_type($file);

        } else {
            // fall back to getimagesize(). works for images at least
            $meta = getimagesize($file);
            if (!empty($meta['mime'])) {
                $mime_type = $meta['mime'];
            }
        }

        // Fallback to the default
        if (!$mime_type) {
            $mime_type = $default;
        }

        // strip "; charset=" value
        $mime_type = current(explode(';', $mime_type, 2));

        return $mime_type;
    }
}