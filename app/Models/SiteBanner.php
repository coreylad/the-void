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

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class SiteBanner extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'slot',
        'title',
        'alt_text',
        'link_url',
        'image_path',
        'is_animated',
        'is_active',
        'position',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'is_animated' => 'bool',
            'is_active'   => 'bool',
            'position'    => 'int',
            'starts_at'   => 'datetime',
            'ends_at'     => 'datetime',
        ];
    }

    /**
     * Scope a query to only include banners currently active for a given slot.
     *
     * @param Builder<self> $query
     *
     * @return Builder<self>
     */
    public function scopeActiveForSlot(Builder $query, string $slot): Builder
    {
        return $query
            ->where('slot', '=', $slot)
            ->where('is_active', '=', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('position');
    }
}
