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

use App\Helpers\Apng;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreSiteBannerRequest;
use App\Http\Requests\Staff\UpdateSiteBannerRequest;
use App\Models\SiteBanner;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * @see \Tests\Feature\Http\Controllers\Staff\BannerControllerTest
 */
class BannerController extends Controller
{
    /**
     * Display All Site Banners.
     */
    public function index(): Factory|View
    {
        return view('Staff.banner.index', [
            'banners' => SiteBanner::query()->orderBy('slot')->orderBy('position')->get(),
            'slots'   => config('branding.banner_slots'),
        ]);
    }

    /**
     * Show Form For Creating A New Site Banner.
     */
    public function create(): Factory|View
    {
        return view('Staff.banner.create', [
            'slots' => config('branding.banner_slots'),
        ]);
    }

    /**
     * Store A Site Banner.
     */
    public function store(StoreSiteBannerRequest $request): RedirectResponse
    {
        $image = $request->file('image');

        abort_if(\is_array($image), 400);
        abort_unless($image !== null && $image->isValid(), 400);

        // Ignore the client supplied filename/extension entirely and force a
        // random name with a fixed .png extension to prevent extension
        // spoofing / double-extension attacks.
        $filename = Str::uuid().'.png';
        $realPath = $image->getRealPath();
        abort_if($realPath === false, 400);

        $image->storeAs('', $filename, 'site-banners');

        SiteBanner::query()->create([
            ...$request->validated(),
            'image_path'  => $filename,
            'is_animated' => Apng::isAnimated($realPath),
            'is_active'   => $request->boolean('is_active'),
            'position'    => $request->integer('position'),
            'created_by'  => $request->user()?->id,
        ]);

        return to_route('staff.banners.index')
            ->with('success', 'Site banner successfully added');
    }

    /**
     * Site Banner Edit Form.
     */
    public function edit(SiteBanner $banner): Factory|View
    {
        return view('Staff.banner.edit', [
            'banner' => $banner,
            'slots'  => config('branding.banner_slots'),
        ]);
    }

    /**
     * Update A Site Banner.
     */
    public function update(UpdateSiteBannerRequest $request, SiteBanner $banner): RedirectResponse
    {
        $attributes = [
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'position'  => $request->integer('position'),
        ];

        $image = $request->file('image');

        if ($image !== null) {
            abort_if(\is_array($image), 400);
            abort_unless($image->isValid(), 400);

            $realPath = $image->getRealPath();
            abort_if($realPath === false, 400);

            $filename = Str::uuid().'.png';
            $image->storeAs('', $filename, 'site-banners');

            Storage::disk('site-banners')->delete($banner->image_path);

            $attributes['image_path'] = $filename;
            $attributes['is_animated'] = Apng::isAnimated($realPath);
        }

        $banner->update($attributes);

        return to_route('staff.banners.index')
            ->with('success', 'Site banner successfully modified');
    }

    /**
     * Destroy A Site Banner.
     */
    public function destroy(SiteBanner $banner): RedirectResponse
    {
        Storage::disk('site-banners')->delete($banner->image_path);
        $banner->delete();

        return to_route('staff.banners.index')
            ->with('success', 'Site banner successfully deleted');
    }
}
