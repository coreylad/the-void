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

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Override;

/**
 * Verifies that an uploaded file is a genuine PNG image by checking its
 * binary signature and true image type, rather than trusting the client
 * supplied extension or MIME type. This blocks polyglot/renamed uploads.
 */
class GenuinePng implements ValidationRule
{
    private const string PNG_SIGNATURE = "\x89PNG\x0d\x0a\x1a\x0a";

    #[Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The :attribute must be an uploaded file.');

            return;
        }

        $path = $value->getRealPath();

        if ($path === false || !is_file($path)) {
            $fail('The :attribute could not be read.');

            return;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $fail('The :attribute could not be read.');

            return;
        }

        $signature = fread($handle, 8);
        fclose($handle);

        if ($signature !== self::PNG_SIGNATURE) {
            $fail('The :attribute must be a genuine PNG image.');

            return;
        }

        $imageInfo = @getimagesize($path);

        if ($imageInfo === false || ($imageInfo[2] ?? null) !== \IMAGETYPE_PNG) {
            $fail('The :attribute must be a genuine PNG image.');
        }
    }
}
