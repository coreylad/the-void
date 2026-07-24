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
use App\Http\Requests\Staff\UpdateSiteBrandingRequest;
use App\Http\Requests\Staff\UpdateSiteBannerRequest;
use App\Http\Requests\Staff\UpdateSmtpSettingsRequest;
use App\Mail\TestEmail;
use App\Models\SiteBanner;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
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
            'banners'  => SiteBanner::query()->orderBy('slot')->orderBy('position')->get(),
            'slots'    => config('branding.banner_slots'),
            'branding' => [
                'title'            => (string) config('other.title'),
                'subTitle'         => (string) config('other.subTitle'),
                'meta_description' => (string) config('other.meta_description'),
                'birthdate'        => (string) config('other.birthdate'),
                'owner_email'      => (string) config('other.email'),
            ],
            'smtp' => [
                'mail_mailer'       => (string) config('mail.default'),
                'mail_host'         => (string) config('mail.mailers.smtp.host'),
                'mail_port'         => (string) config('mail.mailers.smtp.port'),
                'mail_encryption'   => (string) (config('mail.mailers.smtp.encryption') ?? 'null'),
                'mail_username'     => (string) (config('mail.mailers.smtp.username') ?? ''),
                'mail_ehlo_domain'  => (string) (config('mail.mailers.smtp.local_domain') ?? ''),
                'mail_from_address' => (string) config('mail.from.address'),
                'mail_from_name'    => (string) config('mail.from.name'),
                'mail_sendmail_path'=> (string) config('mail.mailers.sendmail.path'),
                'mail_log_channel'  => (string) (config('mail.mailers.log.channel') ?? ''),
                'password_is_set'   => config('mail.mailers.smtp.password') !== null,
            ],
        ]);
    }

    /**
     * Update global site branding details.
     */
    public function updateBranding(UpdateSiteBrandingRequest $request): RedirectResponse
    {
        $configPath = config_path('other.php');
        $configContents = file_get_contents($configPath);

        abort_if($configContents === false, 500, 'Unable to read branding config');

        $replacements = [
            'title'            => (string) $request->string('title'),
            'subTitle'         => (string) $request->string('subTitle'),
            'meta_description' => (string) $request->string('meta_description'),
            'birthdate'        => (string) $request->string('birthdate'),
        ];

        foreach ($replacements as $key => $value) {
            $quotedValue = var_export($value, true);

            $configContents = preg_replace(
                "/('".preg_quote($key, '/')."'\\s*=>\\s*)'(?:\\\\'|[^'])*'(,)/",
                '$1'.$quotedValue.'$2',
                $configContents,
                1,
                $count
            );

            abort_if($count !== 1, 500, "Unable to update branding key: {$key}");
        }

        $bytesWritten = file_put_contents($configPath, $configContents);

        abort_if($bytesWritten === false, 500, 'Unable to write branding config');

        self::updateEnvironmentValues([
            'DEFAULT_OWNER_EMAIL' => (string) $request->string('owner_email'),
        ]);

        Artisan::call('config:clear');

        return to_route('staff.banners.index')
            ->with('success', 'Site branding details successfully updated');
    }

    /**
     * Update SMTP/email transport settings.
     */
    public function updateSmtp(UpdateSmtpSettingsRequest $request): RedirectResponse
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

        Artisan::call('config:clear');

        return to_route('staff.banners.index')
            ->with('success', 'SMTP settings successfully updated');
    }

    /**
     * Send a test email using the current settings.
     */
    public function testSmtp(): RedirectResponse
    {
        try {
            Mail::to(config('other.email'))->send(new TestEmail());
        } catch (Throwable) {
            return to_route('staff.banners.index')
                ->withErrors(['smtp_test' => 'Test email failed. Please review your SMTP settings.']);
        }

        return to_route('staff.banners.index')
            ->with('success', 'Test email was sent successfully');
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
}
