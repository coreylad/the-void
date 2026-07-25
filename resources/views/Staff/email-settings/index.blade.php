@extends('layout.with-main')

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('staff.dashboard.index') }}" class="breadcrumb__link">
            {{ __('staff.staff-dashboard') }}
        </a>
    </li>
    <li class="breadcrumb--active">
        Email settings
    </li>
@endsection

@section('page', 'page__staff-email-settings--index')

@section('main')
    <section class="panelV2">
        <header class="panel__header">
            <h2 class="panel__heading">Email discovery and setup</h2>
        </header>
        <div class="panel__body">
            <p>
                Centralized email configuration for transactional mail, invite delivery, password reset, and account notices.
                Settings saved here are persisted in your environment file.
            </p>
            <div class="form__group form__group--horizontal">
                <div>
                    <strong>Active mailer:</strong> {{ strtoupper($smtp['mail_mailer']) }}
                </div>
                <div>
                    <strong>From identity:</strong> {{ $smtp['mail_from_name'] }} &lt;{{ $smtp['mail_from_address'] }}&gt;
                </div>
                <div>
                    <strong>Default test recipient:</strong> {{ $siteContactEmail }}
                </div>
            </div>
        </div>
    </section>

    <section class="panelV2">
        <header class="panel__header">
            <h2 class="panel__heading">Provider quick reference</h2>
        </header>
        <div class="panel__body">
            <p>Use these defaults as a starting point, then validate by sending a test email below.</p>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Host</th>
                            <th>Port</th>
                            <th>Encryption</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Mailgun SMTP</td>
                            <td>smtp.mailgun.org</td>
                            <td>587</td>
                            <td>TLS</td>
                        </tr>
                        <tr>
                            <td>Amazon SES SMTP</td>
                            <td>email-smtp.&lt;region&gt;.amazonaws.com</td>
                            <td>587</td>
                            <td>TLS</td>
                        </tr>
                        <tr>
                            <td>Postmark SMTP</td>
                            <td>smtp.postmarkapp.com</td>
                            <td>587</td>
                            <td>TLS</td>
                        </tr>
                        <tr>
                            <td>SendGrid SMTP</td>
                            <td>smtp.sendgrid.net</td>
                            <td>587</td>
                            <td>TLS</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="panelV2">
        <header class="panel__header">
            <h2 class="panel__heading">SMTP / Email transport</h2>
        </header>
        <div class="panel__body">
            <form method="POST" action="{{ route('staff.email_settings.update') }}" class="form">
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
                    <button class="form__button form__button--filled">Save email settings</button>
                </p>
            </form>
        </div>
    </section>

    <section class="panelV2">
        <header class="panel__header">
            <h2 class="panel__heading">Delivery test</h2>
        </header>
        <div class="panel__body">
            <form method="POST" action="{{ route('staff.email_settings.test') }}" class="form">
                @csrf
                <p class="form__group">
                    <input
                        id="recipient"
                        class="form__text"
                        type="email"
                        name="recipient"
                        value="{{ old('recipient', $siteContactEmail) }}"
                        maxlength="255"
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="recipient">
                        Test recipient email
                    </label>
                </p>
                <p class="form__group">
                    <button class="form__button form__button--text">Send test email</button>
                </p>
            </form>
            @error('smtp_test')
                <p class="text-red">{{ $message }}</p>
            @enderror
        </div>
    </section>
@endsection
