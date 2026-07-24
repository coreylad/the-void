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

class UpdateSmtpSettingsRequest extends FormRequest
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
            'mail_mailer' => [
                'required',
                Rule::in(['smtp', 'sendmail', 'log', 'array', 'failover']),
            ],
            'mail_host' => [
                'nullable',
                'required_if:mail_mailer,smtp',
                'string',
                'max:255',
            ],
            'mail_port' => [
                'nullable',
                'required_if:mail_mailer,smtp',
                'integer',
                'between:1,65535',
            ],
            'mail_encryption' => [
                'nullable',
                Rule::in(['tls', 'ssl', 'null']),
            ],
            'mail_username' => [
                'nullable',
                'string',
                'max:255',
            ],
            'mail_password' => [
                'nullable',
                'string',
                'max:255',
            ],
            'mail_ehlo_domain' => [
                'nullable',
                'string',
                'max:255',
            ],
            'mail_from_address' => [
                'required',
                'email',
                'max:255',
            ],
            'mail_from_name' => [
                'required',
                'string',
                'max:255',
            ],
            'mail_sendmail_path' => [
                'nullable',
                'required_if:mail_mailer,sendmail',
                'string',
                'max:255',
            ],
            'mail_log_channel' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
