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

@section('javascripts')
    <style nonce="{{ HDVinnie\SecureHeaders\SecureHeaders::nonce('style') }}">
        .category-tiles {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1.25rem;
            margin-top: 1.5rem;
        }

        .category-tile {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
            text-decoration: none;
            transition: background 0.15s ease, transform 0.15s ease, border-color 0.15s ease;
        }

        .category-tile:hover {
            background: rgba(255, 255, 255, 0.10);
            border-color: rgba(255, 255, 255, 0.20);
            transform: translateY(-3px);
        }

        .category-tile__media {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 110px;
            height: 110px;
            margin-bottom: 0.75rem;
        }

        .category-tile__image {
            max-width: 110px;
            max-height: 110px;
            object-fit: contain;
            border-radius: 8px;
        }

        .category-tile__icon {
            font-size: 3.5rem;
            line-height: 1;
            color: #6ea8fe;
        }

        .category-tile__label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 1.05rem;
        }

        .category-tile__count {
            font-size: 0.8rem;
            font-weight: 400;
            opacity: 0.65;
            background: rgba(255, 255, 255, 0.10);
            padding: 0.05rem 0.45rem;
            border-radius: 999px;
        }
    </style>
@endsection
