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

use App\Helpers\AnimatedImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreSiteBannerRequest;
use App\Http\Requests\Staff\UpdateSiteBannerRequest;
use App\Models\SiteBanner;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Intervention\Image\Facades\Image;

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
        // random name with the real detected extension to prevent extension
        // spoofing / double-extension attacks.
        $realPath = $image->getRealPath();
        abort_if($realPath === false, 400);

        $filename = Str::uuid().'.'.self::detectExtension($realPath);
        $isAnimated = AnimatedImage::isAnimated($realPath);

        self::storeResized($image, $realPath, $filename, $isAnimated);

        $banner = SiteBanner::query()->create([
            ...$request->validated(),
            'image_path'  => $filename,
            'is_animated' => $isAnimated,
            'is_active'   => $request->boolean('is_active'),
            'position'    => $request->integer('position'),
            'created_by'  => $request->user()?->id,
        ]);

        cache()->forget("site-banners:{$banner->slot}");

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
        $previousSlot = $banner->slot;

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

            $filename = Str::uuid().'.'.self::detectExtension($realPath);
            $isAnimated = AnimatedImage::isAnimated($realPath);

            self::storeResized($image, $realPath, $filename, $isAnimated);

            Storage::disk('site-banners')->delete($banner->image_path);

            $attributes['image_path'] = $filename;
            $attributes['is_animated'] = $isAnimated;
        }

        $banner->update($attributes);

        cache()->forget("site-banners:{$previousSlot}");
        cache()->forget("site-banners:{$banner->slot}");

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

        cache()->forget("site-banners:{$banner->slot}");

        return to_route('staff.banners.index')
            ->with('success', 'Site banner successfully deleted');
    }

    /**
     * Store the uploaded banner image, resizing it to fit within the
     * configured maximum dimensions when it isn't animated. Animated
     * images (APNG/GIF) are stored unmodified since resizing them would
     * discard their animation frames.
     */
    private static function storeResized(\Illuminate\Http\UploadedFile $image, string $realPath, string $filename, bool $isAnimated): void
    {
        if ($isAnimated) {
            $image->storeAs('', $filename, 'site-banners');

            return;
        }

        $path = Storage::disk('site-banners')->path($filename);

        Image::make($realPath)
            ->resize(
                config('branding.banner_max_width'),
                config('branding.banner_max_height'),
                function ($constraint): void {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                }
            )
            ->encode(self::detectExtension($realPath), 100)
            ->save($path);
    }

    /**
     * Determine the real file extension ('png' or 'gif') from an uploaded
     * image's binary signature. The GenuineImage validation rule guarantees
     * the file is one of these two types before this is called.
     */
    private static function detectExtension(string $realPath): string
    {
        $handle = fopen($realPath, 'rb');

        if ($handle === false) {
            return 'png';
        }

        $signature = fread($handle, 8);
        fclose($handle);

        return str_starts_with((string) $signature, 'GIF87a') || str_starts_with((string) $signature, 'GIF89a')
            ? 'gif'
            : 'png';
    }
}
