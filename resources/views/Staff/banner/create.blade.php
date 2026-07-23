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
        {{ __('common.new-adj') }}
    </li>
@endsection

@section('page', 'page__staff-banner--create')

@section('main')
    <section class="panelV2">
        <h2 class="panel__heading">Add a site banner</h2>
        <div class="panel__body">
            <form
                class="form"
                method="POST"
                action="{{ route('staff.banners.store') }}"
                enctype="multipart/form-data"
            >
                @csrf
                <p class="form__group">
                    <select name="slot" id="slot" class="form__select" required>
                        <option hidden selected disabled value=""></option>
                        @foreach ($slots as $value => $label)
                            <option class="form__option" value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <label class="form__label form__label--floating" for="slot">Slot</label>
                </p>
                <p class="form__group">
                    <input id="title" class="form__text" type="text" name="title" placeholder=" " />
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
                        value="0"
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
                        checked
                    />
                    <label class="form__label" for="is_active">Active</label>
                </p>
                <p class="form__group">
                    <input id="starts_at" class="form__text" type="datetime-local" name="starts_at" />
                    <label class="form__label form__label--floating" for="starts_at">
                        Starts at (optional)
                    </label>
                </p>
                <p class="form__group">
                    <input id="ends_at" class="form__text" type="datetime-local" name="ends_at" />
                    <label class="form__label form__label--floating" for="ends_at">
                        Ends at (optional)
                    </label>
                </p>
                <p class="form__group">
                    <label for="image">
                        Banner image (PNG only, animated PNG/APNG supported, max
                        {{ number_format(config('branding.max_upload_kb') / 1024, 1) }} MB)
                    </label>
                    <input id="image" class="form__file" type="file" name="image" accept=".png" required />
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
