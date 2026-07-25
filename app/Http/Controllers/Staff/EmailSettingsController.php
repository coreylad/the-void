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

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateSmtpSettingsRequest;
use App\Mail\TestEmail;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class EmailSettingsController extends Controller
{
    /**
     * Show email discovery and setup settings.
     */
    public function index(): Factory|View
    {
        return view('Staff.email-settings.index', [
            'smtp' => [
                'mail_mailer'        => (string) config('mail.default'),
                'mail_host'          => (string) config('mail.mailers.smtp.host'),
                'mail_port'          => (string) config('mail.mailers.smtp.port'),
                'mail_encryption'    => (string) (config('mail.mailers.smtp.encryption') ?? 'null'),
                'mail_username'      => (string) (config('mail.mailers.smtp.username') ?? ''),
                'mail_ehlo_domain'   => (string) (config('mail.mailers.smtp.local_domain') ?? ''),
                'mail_from_address'  => (string) config('mail.from.address'),
                'mail_from_name'     => (string) config('mail.from.name'),
                'mail_sendmail_path' => (string) config('mail.mailers.sendmail.path'),
                'mail_log_channel'   => (string) (config('mail.mailers.log.channel') ?? ''),
                'password_is_set'    => config('mail.mailers.smtp.password') !== null,
            ],
            'siteContactEmail' => (string) config('other.email'),
        ]);
    }

    /**
     * Update SMTP/email transport settings.
     */
    public function update(UpdateSmtpSettingsRequest $request): RedirectResponse
    {
        $mailEncryption = (string) $request->string('mail_encryption', 'null');
        $mailEncryption = $mailEncryption === '' ? 'null' : $mailEncryption;

        $envUpdates = [
            'MAIL_MAILER'       => (string) $request->string('mail_mailer'),
            'MAIL_FROM_ADDRESS' => (string) $request->string('mail_from_address'),
            'MAIL_FROM_NAME'    => (string) $request->string('mail_from_name'),
            'MAIL_ENCRYPTION'   => $mailEncryption,
            'MAIL_EHLO_DOMAIN'  => $request->string('mail_ehlo_domain')->trim()->toString() !== ''
                ? (string) $request->string('mail_ehlo_domain')
                : null,
            'MAIL_LOG_CHANNEL'  => $request->string('mail_log_channel')->trim()->toString() !== ''
                ? (string) $request->string('mail_log_channel')
                : null,
        ];

        if ($request->string('mail_mailer')->toString() === 'smtp') {
            $envUpdates['MAIL_HOST'] = (string) $request->string('mail_host');
            $envUpdates['MAIL_PORT'] = (string) $request->integer('mail_port');
            $envUpdates['MAIL_USERNAME'] = $request->string('mail_username')->trim()->toString() !== ''
                ? (string) $request->string('mail_username')
                : null;

            if ($request->string('mail_password')->trim()->toString() !== '') {
                $envUpdates['MAIL_PASSWORD'] = (string) $request->string('mail_password');
            }
        }

        if ($request->string('mail_mailer')->toString() === 'sendmail') {
            $envUpdates['MAIL_SENDMAIL_PATH'] = (string) $request->string('mail_sendmail_path');
        }

        self::updateEnvironmentValues($envUpdates);
        self::refreshRuntimeConfigurationCache();

        return to_route('staff.email_settings.index')
            ->with('success', 'Email settings successfully updated');
    }

    /**
     * Send a test email using the current settings.
     */
    public function test(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient' => [
                'nullable',
                'email',
                'max:255',
            ],
        ]);

        $recipient = $validated['recipient'] ?? config('other.email');

        try {
            Mail::to($recipient)->send(new TestEmail());
        } catch (Throwable) {
            return to_route('staff.email_settings.index')
                ->withErrors(['smtp_test' => 'Test email failed. Please review your settings and transport credentials.']);
        }

        return to_route('staff.email_settings.index')
            ->with('success', "Test email sent to {$recipient}");
    }

    /**
     * Update one or more .env keys while preserving other entries.
     *
     * @param  array<string, string|null>  $entries
     */
    private static function updateEnvironmentValues(array $entries): void
    {
        $envPath = base_path('.env');
        $envContents = file_get_contents($envPath);

        abort_if($envContents === false, 500, 'Unable to read environment file');

        foreach ($entries as $key => $value) {
            $formattedValue = self::formatEnvironmentValue($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            if (preg_match($pattern, $envContents) === 1) {
                $envContents = preg_replace($pattern, $key.'='.$formattedValue, $envContents, 1, $count);
                abort_if($count !== 1, 500, "Unable to update environment key: {$key}");

                continue;
            }

            $envContents .= PHP_EOL.$key.'='.$formattedValue;
        }

        $bytesWritten = file_put_contents($envPath, $envContents);
        abort_if($bytesWritten === false, 500, 'Unable to write environment file');
    }

    private static function formatEnvironmentValue(?string $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if ($value === '') {
            return "''";
        }

        if (!preg_match("/[\\s#\"'\\$=]/", $value)) {
            return $value;
        }

        return '"'.addcslashes($value, "\\\"").'"';
    }

    private static function refreshRuntimeConfigurationCache(): void
    {
        Artisan::call('config:clear');
    }
}
