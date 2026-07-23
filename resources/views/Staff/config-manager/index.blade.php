@extends('layout.with-main')

@section('title')
    <title>
        {{ __('staff.config-manager') }} - {{ __('staff.staff-dashboard') }} -
        {{ config('other.title') }}
    </title>
@endsection

@section('meta')
    <meta
        name="description"
        content="{{ __('staff.config-manager') }} - {{ __('staff.staff-dashboard') }}"
    />
@endsection

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('staff.dashboard.index') }}" class="breadcrumb__link">
            {{ __('staff.staff-dashboard') }}
        </a>
    </li>
    <li class="breadcrumb--active">
        {{ __('staff.config-manager') }}
    </li>
@endsection

@section('page', 'page__staff-config-manager--index')

@section('main')
    <section class="panelV2">
        <h2 class="panel__heading">{{ __('staff.config-manager') }}</h2>
        <div class="panel__body">
            <form
                class="form"
                method="GET"
                action="{{ route('staff.config_manager.index') }}"
                x-data="{ autosubmit: true }"
            >
                <p class="form__group">
                    <select
                        id="file"
                        class="form__select"
                        name="file"
                        required
                        x-on:change="if (autosubmit) { $el.form.requestSubmit(); }"
                    >
                        @foreach ($editableFiles as $file)
                            <option value="{{ $file }}" @selected($file === $selectedFile)>
                                {{ $file }}
                            </option>
                        @endforeach
                    </select>
                    <label class="form__label form__label--floating" for="file">
                        Config file
                    </label>
                </p>
                <p class="form__group" x-show="!autosubmit" x-cloak>
                    <button class="form__button form__button--filled" type="submit">
                        Load file settings
                    </button>
                </p>
                <noscript>
                    <p class="form__group">
                        <button class="form__button form__button--filled" type="submit">
                            Load file settings
                        </button>
                    </p>
                </noscript>
            </form>

            <form class="form" method="POST" action="{{ route('staff.config_manager.update') }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="selected_file" value="{{ $selectedFile }}" />

                @foreach ($options as $option)
                    <fieldset class="form__fieldset">
                        <legend class="form__legend">
                            {{ $option['label'] }}
                            @if ($option['is_overridden'])
                                (Overridden)
                            @endif
                        </legend>

                        <p style="margin-bottom: 0.35rem; opacity: 0.8;">{{ $option['key'] }}</p>
                        <p style="margin-top: 0; opacity: 0.75;">
                            Default: {{ is_bool($option['default']) ? ($option['default'] ? 'true' : 'false') : ($option['default'] ?? 'null') }}
                        </p>

                        @if ($option['type'] === 'boolean')
                            <p class="form__group">
                                <input type="hidden" name="settings[{{ $option['key'] }}]" value="0" />
                                <input
                                    id="setting_{{ str_replace(['.', '-'], '_', $option['key']) }}"
                                    class="form__checkbox"
                                    type="checkbox"
                                    name="settings[{{ $option['key'] }}]"
                                    value="1"
                                    @checked((bool) $option['current'])
                                />
                                <label
                                    class="form__label"
                                    for="setting_{{ str_replace(['.', '-'], '_', $option['key']) }}"
                                >
                                    Enabled
                                </label>
                            </p>
                        @elseif ($option['type'] === 'integer')
                            <p class="form__group">
                                <input
                                    id="setting_{{ str_replace(['.', '-'], '_', $option['key']) }}"
                                    class="form__text"
                                    type="number"
                                    step="1"
                                    name="settings[{{ $option['key'] }}]"
                                    value="{{ (int) $option['current'] }}"
                                />
                                <label
                                    class="form__label form__label--floating"
                                    for="setting_{{ str_replace(['.', '-'], '_', $option['key']) }}"
                                >
                                    Value
                                </label>
                            </p>
                        @elseif ($option['type'] === 'float')
                            <p class="form__group">
                                <input
                                    id="setting_{{ str_replace(['.', '-'], '_', $option['key']) }}"
                                    class="form__text"
                                    type="number"
                                    step="any"
                                    name="settings[{{ $option['key'] }}]"
                                    value="{{ (float) $option['current'] }}"
                                />
                                <label
                                    class="form__label form__label--floating"
                                    for="setting_{{ str_replace(['.', '-'], '_', $option['key']) }}"
                                >
                                    Value
                                </label>
                            </p>
                        @else
                            <p class="form__group">
                                <input
                                    id="setting_{{ str_replace(['.', '-'], '_', $option['key']) }}"
                                    class="form__text"
                                    type="text"
                                    name="settings[{{ $option['key'] }}]"
                                    value="{{ (string) $option['current'] }}"
                                />
                                <label
                                    class="form__label form__label--floating"
                                    for="setting_{{ str_replace(['.', '-'], '_', $option['key']) }}"
                                >
                                    Value
                                </label>
                            </p>
                        @endif

                        @error('settings.'.$option['key'])
                            <p class="form__feedback form__feedback--invalid" style="display: block;">
                                {{ $message }}
                            </p>
                        @enderror
                    </fieldset>
                @endforeach

                <p class="form__group">
                    <button class="form__button form__button--filled">
                        {{ __('common.save') }}
                    </button>
                </p>
            </form>
        </div>
    </section>
@endsection
