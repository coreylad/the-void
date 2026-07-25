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

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRegistrationSettingsRequest extends FormRequest
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
            'invite_only' => [
                'required',
                'boolean',
            ],
            'application_signups' => [
                'required',
                'boolean',
            ],
            'invites_restriced' => [
                'required',
                'boolean',
            ],
            'invite_expire' => [
                'required',
                'integer',
                'min:1',
                'max:365',
            ],
            'hours_until_invite_after_2fa' => [
                'required',
                'integer',
                'min:0',
                'max:8760',
            ],
            'max_unused_user_invites' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],
            'invite_groups' => [
                Rule::requiredIf((bool) $this->boolean('invites_restriced')),
                'array',
                'min:1',
            ],
            'invite_groups.*' => [
                'string',
                Rule::exists('groups', 'name'),
            ],
        ];
    }
}
