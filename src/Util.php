<?php

/*
 * This file is part of the Eventum (Issue Tracking System) package.
 *
 * @copyright (c) Eventum Team
 * @license GNU General Public License, version 2 or later (GPL-2+)
 *
 * For the full copyright and license information,
 * please see the LICENSE and AUTHORS files
 * that were distributed with this source code.
 */

namespace Eventum\Console;

/**
 * Various utility methods
 */
class Util
{
    /**
     * Format a byte count into a human-readable representation.
     *
     * @see http://api.propelorm.org/2.0-master/Propel/Runtime/Util/Profiler.html#method_formatMemory
     * @param int $bytes Byte count to convert. Can be negative.
     * @param int $precision how many decimals to include
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

        return $this->toPrecision($sign * $absBytes, $precision) . $suffix[$i];
    }

    /**
     * Rounding to significant digits (sort of like JavaScript's toPrecision()).
     *
     * @see http://api.propelorm.org/2.0-master/Propel/Runtime/Util/Profiler.html#method_toPrecision
     * @param float $number Value to round
     * @param int $significantFigures Number of significant figures
     *
     * @return float
     */
    private function toPrecision($number, $significantFigures = 3)
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
     * @param string $default optional default mime type to return if the file's mime type can not be identified
     * @return string MIME type
     */
    public function getFileMimeType($file, $default = 'application/octet-stream')
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
