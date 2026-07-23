@extends('layout.with-main')

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('staff.dashboard.index') }}" class="breadcrumb__link">
            {{ __('staff.staff-dashboard') }}
        </a>
    </li>
    <li class="breadcrumbV2">
        <a href="{{ route('staff.banners.index') }}" class="breadcrumb__link">
            Site banners
        </a>
    </li>
    <li class="breadcrumb--active">
        {{ __('common.edit') }}
    </li>
@endsection

@section('page', 'page__staff-banner--edit')

@section('main')
    <section class="panelV2">
        <h2 class="panel__heading">Edit site banner</h2>
        <div class="panel__body">
            <p class="form__group">
                <img
                    alt="{{ $banner->alt_text }}"
                    src="{{ route('authenticated_images.site_banner_image', ['siteBanner' => $banner]) }}"
                    style="max-width: 320px; max-height: 160px;"
                />
            </p>
            <form
                class="form"
                method="POST"
                action="{{ route('staff.banners.update', ['banner' => $banner]) }}"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PATCH')
                <p class="form__group">
                    <select name="slot" id="slot" class="form__select" required>
                        @foreach ($slots as $value => $label)
                            <option
                                class="form__option"
                                value="{{ $value }}"
                                @selected($banner->slot === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <label class="form__label form__label--floating" for="slot">Slot</label>
                </p>
                <p class="form__group">
                    <input
                        id="title"
                        class="form__text"
                        type="text"
                        name="title"
                        value="{{ old('title', $banner->title) }}"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="title">
                        Title (internal label)
                    </label>
                </p>
                <p class="form__group">
                    <input
                        id="alt_text"
                        class="form__text"
                        type="text"
                        name="alt_text"
                        value="{{ old('alt_text', $banner->alt_text) }}"
                        placeholder=" "
                        required
                    />
                    <label class="form__label form__label--floating" for="alt_text">
                        Alt text (accessibility)
                    </label>
                </p>
                <p class="form__group">
                    <input
                        id="link_url"
                        class="form__text"
                        type="text"
                        name="link_url"
                        value="{{ old('link_url', $banner->link_url) }}"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="link_url">
                        Link URL (optional, http(s):// only)
                    </label>
                </p>
                <p class="form__group">
                    <input
                        id="position"
                        class="form__text"
                        type="number"
                        min="0"
                        name="position"
                        value="{{ old('position', $banner->position) }}"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="position">
                        Position (lower shows first)
                    </label>
                </p>
                <p class="form__group form__group--horizontal">
                    <input
                        id="is_active"
                        class="form__checkbox"
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $banner->is_active))
                    />
                    <label class="form__label" for="is_active">Active</label>
                </p>
                <p class="form__group">
                    <input
                        id="starts_at"
                        class="form__text"
                        type="datetime-local"
                        name="starts_at"
                        value="{{ optional($banner->starts_at)->format('Y-m-d\TH:i') }}"
                    />
                    <label class="form__label form__label--floating" for="starts_at">
                        Starts at (optional)
                    </label>
                </p>
                <p class="form__group">
                    <input
                        id="ends_at"
                        class="form__text"
                        type="datetime-local"
                        name="ends_at"
                        value="{{ optional($banner->ends_at)->format('Y-m-d\TH:i') }}"
                    />
                    <label class="form__label form__label--floating" for="ends_at">
                        Ends at (optional)
                    </label>
                </p>
                <p class="form__group">
                    <label for="image">
                        Replace banner image (optional, PNG only, animated PNG/APNG supported, max
                        {{ number_format(config('branding.max_upload_kb') / 1024, 1) }} MB)
                    </label>
                    <input id="image" class="form__file" type="file" name="image" accept=".png" />
                </p>
                <p class="form__group">
                    <button class="form__button form__button--filled">
                        {{ __('common.submit') }}
                    </button>
                </p>
            </form>
        </div>
    </section>
@endsection
