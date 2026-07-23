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

namespace App\Http\Requests\Staff;

use App\Rules\GenuineImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiteBannerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<mixed>>
     */
    public function rules(): array
    {
        return [
            'slot' => [
                'required',
                'string',
                Rule::in(array_keys(config('branding.banner_slots'))),
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'alt_text' => [
                'required',
                'string',
                'max:255',
            ],
            'link_url' => [
                'nullable',
                'string',
                'max:2048',
                'url',
                'regex:/^https?:\/\//i',
            ],
            'is_active' => [
                'boolean',
            ],
            'position' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'starts_at' => [
                'nullable',
                'date',
            ],
            'ends_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],
            'image' => [
                'required',
                'file',
                'mimes:png,gif',
                'max:'.config('branding.max_upload_kb'),
                new GenuineImage(),
            ],
        ];
    }
}
