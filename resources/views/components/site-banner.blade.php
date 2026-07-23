@props([
    'bannerSlot',
])

@php
    $siteBanners = cache()->remember(
        "site-banners:{$bannerSlot}",
        60,
        fn () => \App\Models\SiteBanner::query()->activeForSlot($bannerSlot)->get(),
    );
@endphp

@if ($siteBanners->isNotEmpty())
    <div {{ $attributes->class(['site-banners', "site-banners--{$bannerSlot}"]) }}>
        @foreach ($siteBanners as $siteBanner)
            @if ($siteBanner->link_url)
                <a
                    class="site-banners__link"
                    href="{{ $siteBanner->link_url }}"
                    target="_blank"
                    rel="noopener noreferrer nofollow"
                >
                    <img
                        class="site-banners__image"
                        alt="{{ $siteBanner->alt_text }}"
                        src="{{ route('authenticated_images.site_banner_image', ['siteBanner' => $siteBanner]) }}"
                        loading="lazy"
                    />
                </a>
            @else
                <img
                    class="site-banners__image"
                    alt="{{ $siteBanner->alt_text }}"
                    src="{{ route('authenticated_images.site_banner_image', ['siteBanner' => $siteBanner]) }}"
                    loading="lazy"
                />
            @endif
        @endforeach
    </div>
@endif
