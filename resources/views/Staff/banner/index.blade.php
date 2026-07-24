@extends('layout.with-main')

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('staff.dashboard.index') }}" class="breadcrumb__link">
            {{ __('staff.staff-dashboard') }}
        </a>
    </li>
    <li class="breadcrumb--active">
        Site banners
    </li>
@endsection

@section('page', 'page__staff-banner--index')

@section('main')
    <section class="panelV2">
        <header class="panel__header">
            <h2 class="panel__heading">Branding details</h2>
        </header>
        <div class="panel__body">
            <form method="POST" action="{{ route('staff.banners.branding.update') }}" class="form">
                @csrf
                @method('PATCH')
                <p class="form__group">
                    <input
                        id="title"
                        class="form__text"
                        type="text"
                        name="title"
                        value="{{ old('title', $branding['title']) }}"
                        maxlength="255"
                        required
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="title">
                        Site name
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="subTitle"
                        class="form__text"
                        type="text"
                        name="subTitle"
                        value="{{ old('subTitle', $branding['subTitle']) }}"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="subTitle">
                        Site subtitle
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="meta_description"
                        class="form__text"
                        type="text"
                        name="meta_description"
                        value="{{ old('meta_description', $branding['meta_description']) }}"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="meta_description">
                        Meta description
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="birthdate"
                        class="form__text"
                        type="text"
                        name="birthdate"
                        value="{{ old('birthdate', $branding['birthdate']) }}"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="birthdate">
                        Site birthdate label
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="owner_email"
                        class="form__text"
                        type="email"
                        name="owner_email"
                        value="{{ old('owner_email', $branding['owner_email']) }}"
                        maxlength="255"
                        required
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="owner_email">
                        Site contact email
                    </label>
                </p>

                <p class="form__group">
                    <button class="form__button form__button--filled">Save branding details</button>
                </p>
            </form>
        </div>
    </section>

    <section class="panelV2">
        <header class="panel__header">
            <h2 class="panel__heading">SMTP / Email transport</h2>
        </header>
        <div class="panel__body">
            <form method="POST" action="{{ route('staff.banners.smtp.update') }}" class="form">
                @csrf
                @method('PATCH')

                <p class="form__group">
                    <label for="mail_mailer">Mailer</label>
                    <select id="mail_mailer" name="mail_mailer" class="form__select">
                        @foreach (['smtp', 'sendmail', 'log', 'array', 'failover'] as $mailer)
                            <option
                                value="{{ $mailer }}"
                                @selected(old('mail_mailer', $smtp['mail_mailer']) === $mailer)
                            >
                                {{ strtoupper($mailer) }}
                            </option>
                        @endforeach
                    </select>
                </p>

                <p class="form__group">
                    <input
                        id="mail_host"
                        class="form__text"
                        type="text"
                        name="mail_host"
                        value="{{ old('mail_host', $smtp['mail_host']) }}"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="mail_host">
                        SMTP host
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="mail_port"
                        class="form__text"
                        type="number"
                        min="1"
                        max="65535"
                        name="mail_port"
                        value="{{ old('mail_port', $smtp['mail_port']) }}"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="mail_port">
                        SMTP port
                    </label>
                </p>

                <p class="form__group">
                    <label for="mail_encryption">Encryption</label>
                    <select id="mail_encryption" name="mail_encryption" class="form__select">
                        @foreach (['null' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL'] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('mail_encryption', $smtp['mail_encryption']) === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </p>

                <p class="form__group">
                    <input
                        id="mail_username"
                        class="form__text"
                        type="text"
                        name="mail_username"
                        value="{{ old('mail_username', $smtp['mail_username']) }}"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="mail_username">
                        SMTP username
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="mail_password"
                        class="form__text"
                        type="password"
                        name="mail_password"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="mail_password">
                        SMTP password (leave blank to keep current)
                    </label>
                    @if ($smtp['password_is_set'])
                        <small>A password is already configured.</small>
                    @endif
                </p>

                <p class="form__group">
                    <input
                        id="mail_ehlo_domain"
                        class="form__text"
                        type="text"
                        name="mail_ehlo_domain"
                        value="{{ old('mail_ehlo_domain', $smtp['mail_ehlo_domain']) }}"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="mail_ehlo_domain">
                        EHLO domain (optional)
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="mail_from_address"
                        class="form__text"
                        type="email"
                        name="mail_from_address"
                        value="{{ old('mail_from_address', $smtp['mail_from_address']) }}"
                        maxlength="255"
                        required
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="mail_from_address">
                        From email address
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="mail_from_name"
                        class="form__text"
                        type="text"
                        name="mail_from_name"
                        value="{{ old('mail_from_name', $smtp['mail_from_name']) }}"
                        maxlength="255"
                        required
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="mail_from_name">
                        From name
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="mail_sendmail_path"
                        class="form__text"
                        type="text"
                        name="mail_sendmail_path"
                        value="{{ old('mail_sendmail_path', $smtp['mail_sendmail_path']) }}"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="mail_sendmail_path">
                        Sendmail path (for sendmail mailer)
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="mail_log_channel"
                        class="form__text"
                        type="text"
                        name="mail_log_channel"
                        value="{{ old('mail_log_channel', $smtp['mail_log_channel']) }}"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="mail_log_channel">
                        Log channel (for log mailer)
                    </label>
                </p>

                <p class="form__group">
                    <button class="form__button form__button--filled">Save SMTP settings</button>
                </p>
            </form>

            <form method="POST" action="{{ route('staff.banners.smtp.test') }}" class="form">
                @csrf
                <p class="form__group">
                    <button class="form__button form__button--text">Send test email to site contact address</button>
                </p>
            </form>
            @error('smtp_test')
                <p class="text-red">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <section class="panelV2">
        <header class="panel__header">
            <h2 class="panel__heading">Site banners</h2>
            <div class="panel__actions">
                <form class="panel__action" action="{{ route('staff.banners.create') }}">
                    <button class="form__button form__button--text">
                        {{ __('common.add') }}
                    </button>
                </form>
            </div>
        </header>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Preview</th>
                        <th>Slot</th>
                        <th>Title</th>
                        <th>Animated</th>
                        <th>Active</th>
                        <th>Position</th>
                        <th>{{ __('common.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($banners as $banner)
                        <tr>
                            <td>
                                <img
                                    alt="{{ $banner->alt_text }}"
                                    src="{{ route('authenticated_images.site_banner_image', ['siteBanner' => $banner]) }}"
                                    style="max-width: 120px; max-height: 60px;"
                                />
                            </td>
                            <td>{{ $slots[$banner->slot] ?? $banner->slot }}</td>
                            <td>
                                <a href="{{ route('staff.banners.edit', ['banner' => $banner]) }}">
                                    {{ $banner->title ?? '—' }}
                                </a>
                            </td>
                            <td>
                                @if ($banner->is_animated)
                                    <i
                                        class="{{ config('other.font-awesome') }} fa-check text-green"
                                    ></i>
                                @else
                                    <i
                                        class="{{ config('other.font-awesome') }} fa-times text-red"
                                    ></i>
                                @endif
                            </td>
                            <td>
                                @if ($banner->is_active)
                                    <i
                                        class="{{ config('other.font-awesome') }} fa-check text-green"
                                    ></i>
                                @else
                                    <i
                                        class="{{ config('other.font-awesome') }} fa-times text-red"
                                    ></i>
                                @endif
                            </td>
                            <td>{{ $banner->position }}</td>
                            <td>
                                <menu class="data-table__actions">
                                    <li class="data-table__action">
                                        <a
                                            class="form__button form__button--text"
                                            href="{{ route('staff.banners.edit', ['banner' => $banner]) }}"
                                        >
                                            {{ __('common.edit') }}
                                        </a>
                                    </li>
                                    <li class="data-table__action">
                                        <form
                                            action="{{ route('staff.banners.destroy', ['banner' => $banner]) }}"
                                            method="POST"
                                            x-data="confirmation"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                x-on:click.prevent="confirmAction"
                                                data-b64-deletion-message="{{ base64_encode('Are you sure you want to delete this banner: ' . ($banner->title ?? $banner->slot) . '?') }}"
                                                class="form__button form__button--text"
                                            >
                                                {{ __('common.delete') }}
                                            </button>
                                        </form>
                                    </li>
                                </menu>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
