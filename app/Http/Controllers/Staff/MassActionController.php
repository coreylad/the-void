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
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Services\Unit3dAnnounce;
use Exception;
use Throwable;

/**
 * @see \Tests\Feature\Http\Controllers\Staff\MassActionControllerTest
 */
class MassActionController extends Controller
{
    /**
     * Mass Validate Unvalidated Users.
     *
     * @throws Exception
     */
    public function update(): \Illuminate\Http\RedirectResponse
    {
        $validatingGroupId = Group::query()->where('slug', '=', 'validating')->soleValue('id');
        $memberGroupId = Group::query()->where('slug', '=', 'user')->soleValue('id');

        foreach (User::query()->where('group_id', '=', $validatingGroupId)->get() as $user) {
            $user->update([
                'group_id'          => $memberGroupId,
                'can_download'      => 1,
                'email_verified_at' => now(),
            ]);

            cache()->forget('user:'.$user->passkey);

            Unit3dAnnounce::addUser($user);
        }

        return to_route('staff.dashboard.index')
            ->with('success', 'Unvalidated accounts are now validated');
    }

    /**
     * Permanently purge already-pruned users.
     */
    public function purgePrunedUsers(): \Illuminate\Http\RedirectResponse
    {
        $prunedGroupId = Group::query()->where('slug', '=', 'pruned')->value('id');

        if ($prunedGroupId === null) {
            return to_route('staff.dashboard.index')
                ->with('warning', 'Pruned group not found. No users were purged.');
        }

        try {
            $deleted = User::query()
                ->onlyTrashed()
                ->where('group_id', '=', $prunedGroupId)
                ->forceDelete();
        } catch (Throwable) {
            return to_route('staff.dashboard.index')
                ->with('error', 'Failed to purge pruned users. Check logs for details.');
        }

        return to_route('staff.dashboard.index')
            ->with('success', "Pruned users purged: {$deleted}");
    }
}
