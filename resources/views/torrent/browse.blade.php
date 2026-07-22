@extends('layout.with-main')

@section('title')
    <title>{{ __('torrent.browse') }} - {{ config('other.title') }}</title>
@endsection

@section('breadcrumbs')
    <li class="breadcrumbV2">
        <a href="{{ route('torrents.index') }}" class="breadcrumb__link">
            {{ __('torrent.torrents') }}
        </a>
    </li>
    <li class="breadcrumb--active">
        {{ __('torrent.browse') }}
    </li>
@endsection

@section('nav-tabs')
    <li class="nav-tabV2">
        <a class="nav-tab__link" href="{{ route('torrents.index') }}">
            {{ __('torrent.search') }}
        </a>
    </li>
    <li class="nav-tab--active">
        <a class="nav-tab--active__link" href="{{ route('torrents.browse') }}">
            {{ __('torrent.browse') }}
        </a>
    </li>
    <li class="nav-tabV2">
        <a class="nav-tab__link" href="{{ route('trending.index') }}">
            {{ __('common.trending') }}
        </a>
    </li>
    <li class="nav-tabV2">
        <a class="nav-tab__link" href="{{ route('rss.index') }}">
            {{ __('rss.rss') }}
        </a>
    </li>
    <li class="nav-tabV2">
        <a class="nav-tab__link" href="{{ route('torrents.create') }}">
            {{ __('common.upload') }}
        </a>
    </li>
@endsection

@section('page', 'page__torrent--browse')

@section('main')
    <section class="panelV2">
        <h2 class="panel__heading">
            <i class="{{ config('other.font-awesome') }} fa-th-large"></i>
            {{ __('torrent.browse') }}
        </h2>
        <div class="panel__body">
            <p class="form__group">
                {{ __('torrent.browse-desc') }}
            </p>
            <div class="category-tiles">
                @foreach ($categories as $category)
                    <a
                        class="category-tile"
                        href="{{ route('torrents.index', ['categoryIds' => [$category->id]]) }}"
                        title="{{ $category->name }} ({{ $category->torrents_count }})"
                    >
                        <span class="category-tile__media">
                            @if ($category->image !== null)
                                <img
                                    class="category-tile__image"
                                    src="{{ route('authenticated_images.category_image', ['category' => $category]) }}"
                                    alt="{{ $category->name }}"
                                    loading="lazy"
                                />
                            @else
                                <i class="{{ $category->icon }} category-tile__icon"></i>
                            @endif
                        </span>
                        <span class="category-tile__label">
                            {{ $category->name }}
                            <span class="category-tile__count">{{ $category->torrents_count }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style nonce="{{ HDVinnie\SecureHeaders\SecureHeaders::nonce('style') }}">
        .category-tiles {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .category-tile {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.04);
            color: inherit;
            text-decoration: none;
            transition: background 0.15s ease, transform 0.15s ease;
        }

        .category-tile:hover {
            background: rgba(255, 255, 255, 0.09);
            transform: translateY(-2px);
        }

        .category-tile__media {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 96px;
            height: 96px;
            margin-bottom: 0.5rem;
        }

        .category-tile__image {
            max-width: 96px;
            max-height: 96px;
            object-fit: contain;
            border-radius: 6px;
        }

        .category-tile__icon {
            font-size: 3rem;
            line-height: 96px;
        }

        .category-tile__label {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 600;
        }

        .category-tile__count {
            font-size: 0.8rem;
            opacity: 0.7;
        }
    </style>
@endsection
