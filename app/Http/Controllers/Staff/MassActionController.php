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
use App\Models\Comment;
use App\Models\FailedLoginAttempt;
use App\Models\FreeleechToken;
use App\Models\Group;
use App\Models\History;
use App\Models\Like;
use App\Models\Message;
use App\Models\Participant;
use App\Models\Peer;
use App\Models\Post;
use App\Models\PrivateMessage;
use App\Models\Scopes\ApprovedScope;
use App\Models\Thank;
use App\Models\Topic;
use App\Models\Torrent;
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

        $purged = 0;
        $failed = 0;

        User::query()
            ->withTrashed()
            ->where('group_id', '=', $prunedGroupId)
            ->each(function (User $user) use (&$purged, &$failed): void {
                try {
                    Torrent::query()->withoutGlobalScope(ApprovedScope::class)->where('user_id', '=', $user->id)->update([
                        'user_id' => User::SYSTEM_USER_ID,
                    ]);

                    Comment::query()->where('user_id', '=', $user->id)->update([
                        'user_id' => User::SYSTEM_USER_ID,
                    ]);

                    Post::query()->where('user_id', '=', $user->id)->update([
                        'user_id' => User::SYSTEM_USER_ID,
                    ]);

                    Topic::query()->where('first_post_user_id', '=', $user->id)->update([
                        'first_post_user_id' => User::SYSTEM_USER_ID,
                    ]);

                    Topic::query()->where('last_post_user_id', '=', $user->id)->update([
                        'last_post_user_id' => User::SYSTEM_USER_ID,
                    ]);

                    PrivateMessage::query()->where('sender_id', '=', $user->id)->update([
                        'sender_id' => User::SYSTEM_USER_ID,
                    ]);

                    Participant::query()->where('user_id', '=', $user->id)->delete();
                    Message::query()->where('user_id', '=', $user->id)->delete();
                    Like::query()->where('user_id', '=', $user->id)->delete();
                    Thank::query()->where('user_id', '=', $user->id)->delete();
                    Peer::query()->where('user_id', '=', $user->id)->delete();
                    History::query()->where('user_id', '=', $user->id)->delete();
                    FailedLoginAttempt::query()->where('user_id', '=', $user->id)->delete();

                    $user->followers()->detach();
                    $user->following()->detach();

                    foreach (FreeleechToken::query()->where('user_id', '=', $user->id)->get() as $token) {
                        $token->delete();
                        cache()->forget('freeleech_token:'.$user->id.':'.$token->torrent_id);
                    }

                    cache()->forget('user:'.$user->passkey);

                    Unit3dAnnounce::removeUser($user);

                    $user->forceDelete();
                    ++$purged;
                } catch (Throwable) {
                    ++$failed;
                }
            }, 100);

        if ($failed > 0) {
            return to_route('staff.dashboard.index')
                ->with('warning', "Pruned users purged: {$purged}. Failed: {$failed}. Check logs for details.");
        }

        return to_route('staff.dashboard.index')
            ->with('success', "Pruned users purged: {$purged}");
    }
}
