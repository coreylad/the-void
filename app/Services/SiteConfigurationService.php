<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SiteConfiguration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class SiteConfigurationService
{
    private const CACHE_KEY = 'site-configurations:all';

    /**
     * @return array<string, mixed>
     */
    public function editableOptions(?string $file = null): array
    {
        $files = config('dashboard-settings.editable_files', []);

        if ($file !== null && in_array($file, $files, true)) {
            $files = [$file];
        }

        $overrides = $this->overridesByKey();
        $options = [];

        foreach ($files as $configFile) {
            $configPath = config_path($configFile.'.php');
            if (!is_file($configPath)) {
                continue;
            }

            /** @var mixed $configArray */
            $configArray = require $configPath;

            if (!is_array($configArray)) {
                continue;
            }

            $flattened = $this->flatten($configFile, $configArray);

            foreach ($flattened as $key => $defaultValue) {
                if ($this->isBlockedKey($key)) {
                    continue;
                }

                if (!is_scalar($defaultValue) && $defaultValue !== null) {
                    continue;
                }

                $valueType = $this->detectValueType($defaultValue);

                if ($valueType === 'unsupported') {
                    continue;
                }

                $currentValue = array_key_exists($key, $overrides)
                    ? $this->castValue($overrides[$key]['value'], $overrides[$key]['value_type'])
                    : $defaultValue;

                $options[$key] = [
                    'key' => $key,
                    'file' => $configFile,
                    'label' => $this->toLabel($key),
                    'type' => $valueType,
                    'default' => $defaultValue,
                    'current' => $currentValue,
                    'is_overridden' => array_key_exists($key, $overrides),
                ];
            }
        }

        ksort($options);

        return $options;
    }

    /**
     * @param array<string, mixed> $inputs
     */
    public function updateOverrides(array $inputs, int $updatedBy): void
    {
        $allowed = collect($this->editableOptions())->keyBy('key');

        foreach ($inputs as $key => $rawValue) {
            $option = $allowed->get($key);
            if (!is_array($option)) {
                continue;
            }

            $valueType = $option['type'];

            if ($valueType === 'null') {
                continue;
            }

            $normalizedValue = $this->normalizeSubmittedValue($rawValue, $valueType);
            $defaultValue = $option['default'];

            if ($normalizedValue === $defaultValue) {
                SiteConfiguration::query()->where('key', '=', $key)->delete();

                continue;
            }

            SiteConfiguration::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $this->valueToStorage($normalizedValue, $valueType),
                    'value_type' => $valueType,
                    'updated_by' => $updatedBy,
                ]
            );
        }

        Cache::forget(self::CACHE_KEY);
        $this->applyOverrides();
    }

    public function applyOverrides(): void
    {
        if (!$this->canReadSettingsTable()) {
            return;
        }

        /** @var array<int, array{key: string, value: ?string, value_type: string}> $settings */
        $settings = Cache::remember(self::CACHE_KEY, now()->addMinutes(5), static fn (): array => SiteConfiguration::query()
            ->select(['key', 'value', 'value_type'])
            ->get()
            ->map(static fn (SiteConfiguration $setting): array => [
                'key' => (string) $setting->key,
                'value' => $setting->value,
                'value_type' => (string) $setting->value_type,
            ])
            ->all());

        foreach ($settings as $setting) {
            config([$setting['key'] => $this->castValue($setting['value'], $setting['value_type'])]);
        }
    }

    /**
     * @return array<string, array{value: ?string, value_type: string}>
     */
    private function overridesByKey(): array
    {
        if (!$this->canReadSettingsTable()) {
            return [];
        }

        return SiteConfiguration::query()
            ->select(['key', 'value', 'value_type'])
            ->get()
            ->mapWithKeys(static fn (SiteConfiguration $setting): array => [
                (string) $setting->key => [
                    'value' => $setting->value,
                    'value_type' => (string) $setting->value_type,
                ],
            ])
            ->all();
    }

    private function canReadSettingsTable(): bool
    {
        try {
            return Schema::hasTable('site_configurations');
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, mixed>
     */
    private function flatten(string $prefix, array $array): array
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            $dotKey = $prefix.'.'.$key;

            if (is_array($value) && array_is_list($value)) {
                continue;
            }

            if (is_array($value)) {
                $flattened += $this->flatten($dotKey, $value);

                continue;
            }

            $flattened[$dotKey] = $value;
        }

        return $flattened;
    }

    private function detectValueType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_string($value) => 'string',
            $value === null => 'null',
            default => 'unsupported',
        };
    }

    private function toLabel(string $dotKey): string
    {
        $segments = explode('.', $dotKey);
        $leaf = (string) end($segments);

        return ucfirst(str_replace(['-', '_'], ' ', $leaf));
    }

    private function isBlockedKey(string $dotKey): bool
    {
        $patterns = config('dashboard-settings.blocked_key_patterns', []);

        foreach ($patterns as $pattern) {
            if (!is_string($pattern)) {
                continue;
            }

            if (preg_match($pattern, $dotKey) === 1) {
                return true;
            }
        }

        return false;
    }

    private function castValue(?string $value, string $valueType): mixed
    {
        return match ($valueType) {
            'boolean' => $value === '1',
            'integer' => $value === null ? 0 : (int) $value,
            'float' => $value === null ? 0.0 : (float) $value,
            'null' => null,
            default => $value,
        };
    }

    private function normalizeSubmittedValue(mixed $rawValue, string $valueType): mixed
    {
        return match ($valueType) {
            'boolean' => in_array($rawValue, [true, 1, '1', 'true', 'on'], true),
            'integer' => (int) $rawValue,
            'float' => (float) $rawValue,
            'null' => null,
            default => (string) $rawValue,
        };
    }

    private function valueToStorage(mixed $value, string $valueType): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($valueType) {
            'boolean' => $value ? '1' : '0',
            'integer', 'float', 'string' => (string) $value,
            default => (string) $value,
        };
    }
}
