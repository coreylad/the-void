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
 * Detects whether a PNG file is an animated PNG (APNG) by scanning for the
 * 'acTL' animation control chunk, which must appear before the first 'IDAT'
 * chunk in a valid APNG file.
 */
final class Apng
{
    public static function isAnimated(string $path): bool
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
}
