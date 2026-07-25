@extends('layout.with-main')

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('staff.dashboard.index') }}" class="breadcrumb__link">
            {{ __('staff.staff-dashboard') }}
        </a>
    </li>
    <li class="breadcrumb--active">
        Site access settings
    </li>
@endsection

@section('page', 'page__staff-site-access--index')

@section('main')
    @php
        $selectedInviteGroups = old('invite_groups', $registration['invite_groups']);
        $selectedInviteGroups = is_array($selectedInviteGroups) ? $selectedInviteGroups : [];
    @endphp

    <section class="panelV2">
        <header class="panel__header">
            <h2 class="panel__heading">Site access and registration</h2>
        </header>
        <div class="panel__body">
            <form method="POST" action="{{ route('staff.site_access.update') }}" class="form">
                @csrf
                @method('PATCH')

                <p class="form__group form__group--horizontal">
                    <input type="hidden" name="invite_only" value="0" />
                    <input
                        id="invite_only"
                        class="form__checkbox"
                        type="checkbox"
                        name="invite_only"
                        value="1"
                        @checked(old('invite_only', $registration['invite_only']))
                    />
                    <label class="form__label" for="invite_only">
                        Closed registration (invite code required)
                    </label>
                </p>

                <p class="form__group form__group--horizontal">
                    <input type="hidden" name="application_signups" value="0" />
                    <input
                        id="application_signups"
                        class="form__checkbox"
                        type="checkbox"
                        name="application_signups"
                        value="1"
                        @checked(old('application_signups', $registration['application_signups']))
                    />
                    <label class="form__label" for="application_signups">
                        Enable application signups (/application)
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="invite_expire"
                        class="form__text"
                        type="number"
                        min="1"
                        max="365"
                        name="invite_expire"
                        value="{{ old('invite_expire', $registration['invite_expire']) }}"
                        required
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="invite_expire">
                        Invite expiry (days)
                    </label>
                </p>

                <p class="form__group form__group--horizontal">
                    <input type="hidden" name="invites_restriced" value="0" />
                    <input
                        id="invites_restriced"
                        class="form__checkbox"
                        type="checkbox"
                        name="invites_restriced"
                        value="1"
                        @checked(old('invites_restriced', $registration['invites_restriced']))
                    />
                    <label class="form__label" for="invites_restriced">
                        Restrict earning/sending invites by user group
                    </label>
                </p>

                <p class="form__group">
                    <label for="invite_groups">Groups exempt from invite restrictions</label>
                    <select
                        id="invite_groups"
                        name="invite_groups[]"
                        class="form__select"
                        size="8"
                        multiple
                    >
                        @foreach ($groups as $groupName)
                            <option
                                value="{{ $groupName }}"
                                @selected(in_array($groupName, $selectedInviteGroups, true))
                            >
                                {{ $groupName }}
                            </option>
                        @endforeach
                    </select>
                </p>

                <p class="form__group">
                    <input
                        id="hours_until_invite_after_2fa"
                        class="form__text"
                        type="number"
                        min="0"
                        max="8760"
                        name="hours_until_invite_after_2fa"
                        value="{{ old('hours_until_invite_after_2fa', $registration['hours_until_invite_after_2fa']) }}"
                        required
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="hours_until_invite_after_2fa">
                        Required 2FA age before invites (hours)
                    </label>
                </p>

                <p class="form__group">
                    <input
                        id="max_unused_user_invites"
                        class="form__text"
                        type="number"
                        min="0"
                        max="100"
                        name="max_unused_user_invites"
                        value="{{ old('max_unused_user_invites', $registration['max_unused_user_invites']) }}"
                        required
                        placeholder=" "
                    />
                    <label class="form__label form__label--floating" for="max_unused_user_invites">
                        Max unused invites per user
                    </label>
                </p>

                <p class="form__group">
                    <button class="form__button form__button--filled">
                        Save site access settings
                    </button>
                </p>
            </form>
        </div>
    </section>
@endsection
