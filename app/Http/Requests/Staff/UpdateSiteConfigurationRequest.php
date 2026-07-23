<?php

declare(strict_types=1);

namespace App\Http\Requests\Staff;

use App\Services\SiteConfigurationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateSiteConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Request $request): bool
    {
        return $request->user()->group->is_owner;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'selected_file' => [
                'required',
                'string',
                'in:'.implode(',', config('dashboard-settings.editable_files', [])),
            ],
            'settings' => [
                'required',
                'array',
            ],
            'settings.*' => [
                'nullable',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            /** @var SiteConfigurationService $service */
            $service = app(SiteConfigurationService::class);
            $editable = collect($service->editableOptions())->keyBy('key');

            foreach ($this->input('settings', []) as $key => $value) {
                if (!$editable->has($key)) {
                    $validator->errors()->add('settings.'.$key, 'This setting cannot be edited from the dashboard.');

                    continue;
                }

                $type = $editable->get($key)['type'] ?? null;

                if ($type === 'integer' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $validator->errors()->add('settings.'.$key, 'The value must be an integer.');
                }

                if ($type === 'float' && filter_var($value, FILTER_VALIDATE_FLOAT) === false) {
                    $validator->errors()->add('settings.'.$key, 'The value must be numeric.');
                }

                if ($type === 'boolean' && !in_array($value, ['0', '1', 0, 1, true, false], true)) {
                    $validator->errors()->add('settings.'.$key, 'The value must be true or false.');
                }
            }
        });
    }
}
