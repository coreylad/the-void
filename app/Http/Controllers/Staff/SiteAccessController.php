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
use App\Http\Requests\Staff\UpdateRegistrationSettingsRequest;
use App\Models\Group;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;

class SiteAccessController extends Controller
{
    /**
     * Show site access and registration settings.
     */
    public function index(): Factory|View
    {
        return view('Staff.site-access.index', [
            'registration' => [
                'invite_only'                  => (bool) config('other.invite-only'),
                'application_signups'          => (bool) config('other.application_signups'),
                'invites_restriced'            => (bool) config('other.invites_restriced'),
                'invite_expire'                => (int) config('other.invite_expire'),
                'hours_until_invite_after_2fa' => (int) config('other.hours-until-invite-after-2fa'),
                'max_unused_user_invites'      => (int) config('other.max_unused_user_invites', 1),
                'invite_groups'                => config('other.invite_groups', []),
            ],
            'groups' => Group::query()->orderBy('position')->pluck('name')->all(),
        ]);
    }

    /**
     * Update site access and registration settings.
     */
    public function update(UpdateRegistrationSettingsRequest $request): RedirectResponse
    {
        $inviteGroups = Group::query()
            ->whereIn('name', $request->collect('invite_groups')->all())
            ->orderBy('position')
            ->pluck('name')
            ->all();

        self::updateEnvironmentValues([
            'INVITE_ONLY'                  => $request->boolean('invite_only') ? 'true' : 'false',
            'APPLICATION_SIGNUPS'          => $request->boolean('application_signups') ? 'true' : 'false',
            'INVITES_RESTRICED'            => $request->boolean('invites_restriced') ? 'true' : 'false',
            'INVITE_EXPIRE'                => (string) $request->integer('invite_expire'),
            'HOURS_UNTIL_INVITE_AFTER_2FA' => (string) $request->integer('hours_until_invite_after_2fa'),
            'MAX_UNUSED_USER_INVITES'      => (string) $request->integer('max_unused_user_invites'),
            'INVITE_GROUPS'                => implode(',', $inviteGroups),
        ]);

        self::refreshRuntimeConfigurationCache();

        return to_route('staff.site_access.index')
            ->with('success', 'Site access settings successfully updated');
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
