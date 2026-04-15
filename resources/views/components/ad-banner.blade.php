<!-- Ad Banner Component -->
{{-- Usage: <x-ad-banner position="home" limit="2" random="false" /> --}}

@props([
    'position' => 'home',
    'limit' => 1,
    'random' => false,
])

@php
    $ads = \App\Models\Ad::query()
        ->active()
        ->valid()
        ->byPosition($position)
        ->ordered()
        ->{ $random ? 'inRandomOrder' : 'orderBy' }('sort_order', 'asc')
        ->take($limit)
        ->get();
    
    $deviceType = request()->header('X-Device-Type', 'desktop');
@endphp

@if($ads->isNotEmpty())
    <div class="ad-banner ad-banner-{{ $position }}">
        <div class="ad-banner-grid ad-banner-grid-{{ $ads->count() }}">
            @foreach($ads as $ad)
                @php
                    $image = $deviceType === 'mobile' && $ad->mobile_image ? $ad->mobile_image : $ad->image;
                    $url = $ad->link ?: ($ad->product?->url());
                @endphp
                <a 
                    href="{{ $url }}" 
                    class="ad-banner-item ad-banner-item-{{ $ad->type }}"
                    @if(!$url) onclick="event.preventDefault()" @endif
                    target="{{ $ad->link && !Str::startsWith($url, request()->root()) ? '_blank' : '_self' }}"
                    rel="{{ $ad->link && !Str::startsWith($url, request()->root()) ? 'noopener noreferrer' : '' }}"
                    data-ad-id="{{ $ad->id }}"
                >
                    <img 
                        src="{{ $image }}" 
                        alt="{{ $ad->title }}" 
                        class="ad-banner-image"
                        loading="lazy"
                    />
                    @if($ad->title || $ad->subtitle)
                        <div class="ad-banner-content">
                            @if($ad->title)
                                <h3 class="ad-banner-title">{{ $ad->title }}</h3>
                            @endif
                            @if($ad->subtitle)
                                <p class="ad-banner-subtitle">{{ $ad->subtitle }}</p>
                            @endif
                        </div>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

    @push('styles')
    <style>
        .ad-banner {
            width: 100%;
            margin: 1rem 0;
        }

        .ad-banner-grid {
            display: grid;
            gap: 1rem;
        }

        .ad-banner-grid-1 {
            grid-template-columns: 1fr;
        }

        .ad-banner-grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .ad-banner-grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .ad-banner-grid-4 {
            grid-template-columns: repeat(2, 1fr);
        }

        @media (max-width: 768px) {
            .ad-banner-grid-2,
            .ad-banner-grid-3,
            .ad-banner-grid-4 {
                grid-template-columns: 1fr;
            }
        }

        .ad-banner-item {
            position: relative;
            display: block;
            overflow: hidden;
            border-radius: 8px;
            text-decoration: none;
        }

        .ad-banner-image {
            width: 100%;
            height: auto;
            object-fit: cover;
            display: block;
            transition: transform 0.3s ease;
        }

        .ad-banner-item:hover .ad-banner-image {
            transform: scale(1.02);
        }

        .ad-banner-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
            color: white;
        }

        .ad-banner-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }

        .ad-banner-subtitle {
            font-size: 0.875rem;
            margin: 0.25rem 0 0;
            opacity: 0.9;
        }

        /* Banner types */
        .ad-banner-item-banner {
            aspect-ratio: 16/9;
        }

        .ad-banner-item-promotion {
            aspect-ratio: 3/1;
        }

        .ad-banner-item-flash_sale {
            aspect-ratio: 4/1;
            border: 2px solid #ff6b35;
        }

        .ad-banner-item-featured {
            aspect-ratio: 1/1;
        }

        .ad-banner-item-category {
            aspect-ratio: 2/1;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Track ad impressions
        document.addEventListener('DOMContentLoaded', function() {
            const adBanners = document.querySelectorAll('[data-ad-id]');
            adBanners.forEach(banner => {
                const adId = banner.dataset.adId;
                
                // Track click
                banner.addEventListener('click', function() {
                    fetch(`/ads/${adId}/click`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        }
                    });
                });
            });
        });
    </script>
    @endpush
@endif
