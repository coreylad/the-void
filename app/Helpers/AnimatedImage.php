<?php

declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     UNIT3D
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Helpers;

/**
 * Detects whether an image file is animated. Supports:
 *  - Animated PNG (APNG), detected via the 'acTL' animation control chunk,
 *    which must appear before the first 'IDAT' chunk in a valid APNG file.
 *  - Animated GIF, detected via the presence of more than one frame
 *    (Graphic Control Extension immediately followed by another block).
 */
final class AnimatedImage
{
    public static function isAnimated(string $path): bool
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 8);
        fclose($handle);

        if ($header === false) {
            return false;
        }

        if ($header === "\x89PNG\x0d\x0a\x1a\x0a") {
            return self::isAnimatedPng($path);
        }

        if (str_starts_with($header, 'GIF87a') || str_starts_with($header, 'GIF89a')) {
            return self::isAnimatedGif($path);
        }

        return false;
    }

    private static function isAnimatedPng(string $path): bool
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return false;
        }

        // Skip the 8-byte PNG signature.
        fseek($handle, 8);

        $isAnimated = false;

        while (!feof($handle)) {
            $lengthData = fread($handle, 4);

            if ($lengthData === false || \strlen($lengthData) < 4) {
                break;
            }

            $length = unpack('N', $lengthData)[1] ?? 0;
            $type = fread($handle, 4);

            if ($type === false || $type === '') {
                break;
            }

            if ($type === 'acTL') {
                $isAnimated = true;

                break;
            }

            if ($type === 'IDAT') {
                // acTL must appear before the first IDAT chunk in a valid APNG.
                break;
            }

            // Skip the chunk data plus the trailing 4-byte CRC.
            fseek($handle, $length + 4, SEEK_CUR);
        }

        fclose($handle);

        return $isAnimated;
    }

    private static function isAnimatedGif(string $path): bool
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            return false;
        }

        // A GIF is considered animated if it contains more than one
        // Graphic Control Extension block immediately followed by either
        // an Image Descriptor (frame) or another Extension block.
        $frameCount = preg_match_all('/\x00\x21\xF9\x04.{4}\x00[\x2C\x21]/s', $contents);

        return $frameCount !== false && $frameCount > 1;
    }
}
