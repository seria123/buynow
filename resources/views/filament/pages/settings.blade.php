<x-filament-panels::page>
    @php
        $settingsSections = $this->getSettingsSections();
    @endphp

    <!-- Responsive Grid for Sections -->
    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

        @forelse ($settingsSections as $section)
            @php
                $color = $section['color'] ?? 'primary';
                $iconBg = "bg-{$color}-50 dark:bg-{$color}-400/10";
                $iconText = "text-{$color}-600 dark:text-{$color}-400";
            @endphp

            <!-- Single Settings Section Card -->
            <x-filament::section
                class="h-full flex flex-col gap-6 p-8 transition-shadow duration-300 hover:shadow-xl hover:shadow-gray-900/5 dark:hover:shadow-gray-50/5">
                <!-- Card Header: Icon and Badge -->
                <div class="flex items-start justify-between gap-6 mb-6">
                    <!-- Icon Container with custom color background -->
                    <div
                        class="flex items-center justify-center w-16 h-16 rounded-xl {{ $iconBg }} {{ $iconText }} mr-2">
                        <x-filament::icon :icon="$section['icon']" class="w-8 h-8" />
                    </div>

                    @if(isset($section['badge']))
                        <x-filament::badge :color="$color" size="lg" class="ml-2 mt-2">
                            {{ $section['badge'] }}
                        </x-filament::badge>
                    @endif
                </div>

                <!-- Card Content: Title and Description -->
                <div class="flex-1 flex flex-col gap-4">
                    <h3 class="text-2xl font-extrabold text-gray-950 dark:text-white mb-2">
                        {{ $section['title'] }}
                    </h3>
                    <p class="text-base text-gray-500 dark:text-gray-400 line-clamp-3">
                        {{ $section['description'] }}
                    </p>
                </div>

                <!-- Card Footer: Configuration Button -->
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex flex-col">
                    <x-filament::button
                        tag="a"
                        :href="$section['url']"
                        color="gray"
                        class="w-full justify-between text-base py-3 font-semibold group mt-2">
                        Configure Settings
                        <x-slot name="iconSuffix">
                            <x-filament::icon
                                icon="heroicon-m-arrow-right"
                                class="w-6 h-6 transition-transform group-hover:translate-x-1" />
                        </x-slot>
                    </x-filament::button>
                </div>
            </x-filament::section>
        @empty
            <!-- Empty State Section -->
            <div class="col-span-full">
                <x-filament::section class="p-8">
                    <div class="flex flex-col items-center justify-center py-12 text-center gap-5">
                        <div class="flex items-center justify-center w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-900 mb-5 ring-2 ring-gray-200 dark:ring-gray-700">
                            <x-filament::icon icon="heroicon-o-cog-6-tooth" class="w-8 h-8 text-gray-400" />
                        </div>
                        <h3 class="text-2xl font-extrabold text-gray-950 dark:text-white">
                            No Configuration Sections Found
                        </h3>
                        <p class="mt-3 text-lg text-gray-500 dark:text-gray-400 max-w-lg">
                            It looks like no settings have been configured for this panel yet.
                            Please check your application's service providers or configuration files.
                        </p>
                    </div>
                </x-filament::section>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
