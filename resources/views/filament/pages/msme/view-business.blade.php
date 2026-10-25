<x-filament-panels::page>
    <div class="grid grid-cols-12 gap-6">
        @if ($section === 'menu')
            {{-- Left column --}}
            <div class="col-span-12 lg:col-span-3">
                <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

                    {{-- Store icon --}}
                    <div class="flex justify-center">
                        <span class="flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-white/5">
                            <x-heroicon-o-building-storefront class="h-18 w-18 text-blue-400 dark:text-blue-300" />
                        </span>
                    </div>

                    {{-- Name --}}
                    <h2 class="mt-4 text-center text-lg font-semibold">
                        {{ $business['juri_name'] }}
                    </h2>

                    {{-- Entity number --}}
                    <p class="text-center text-sm text-gray-500">
                        Entity #{{ $business['entity_no'] }}
                    </p>

                    {{-- Information --}}
                    <div class="mt-6 divide-y divide-gray-200 dark:divide-gray-700">

                        <div class="flex justify-between py-2 text-sm">
                            <span class="font-medium">BIN</span>
                            <span class="text-gray-500">{{ $business['bin_etracs'] }}</span>
                        </div>

                        <div class="flex justify-between py-2 text-sm">
                            <span class="font-medium">Status</span>
                            <span class="text-gray-500">{{ $business['status'] }}</span>
                        </div>

                        <div class="flex justify-between py-2 text-sm">
                            <span class="font-medium">Owner</span>
                            <span class="text-gray-500 text-right break-words min-w-[50%] flex-1">{{ $business['owner_fullname'] }}</span>
                        </div>

                        <div class="flex justify-between py-2 text-sm">
                            <span class="font-medium">Employer</span>
                            <span class="text-gray-500 text-right break-words min-w-[50%] flex-1">{{ $business['employer_fullname'] }}</span>
                        </div>

                    </div>

                </div>

                {{-- Permanent Address --}}
                <div
                    class="mt-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

                    <div class="flex items-center gap-2 font-semibold">
                        <x-heroicon-o-map-pin class="h-5 w-5 text-gray-500" />

                        <span>Permanent Address</span>
                    </div>

                    <p class="mt-3 text-sm text-gray-500">
                        {{ $business['full_business_address'] }}
                    </p>

                </div>
            </div>
        @endif

        {{-- Right column --}}
        <div class="{{ $section === 'menu' ? 'col-span-12 lg:col-span-9' : 'col-span-12' }}">

            @if ($section === 'menu')
                    <div
                        class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

                        {{-- Menu item --}}
                        <button type="button" wire:click="showSection('owner')"
                            class="flex w-full items-center justify-between border-b border-gray-200 px-5 py-4 text-left transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">
                            <span class="flex items-center gap-3">

                                <x-heroicon-o-user class="h-5 w-5 text-gray-500" />

                                <span class="text-sm font-medium">
                                    Owner Information
                                </span>

                            </span>

                            <x-heroicon-o-chevron-right class="h-5 w-5 text-gray-400" />

                        </button>

                        {{-- Menu item --}}
                        <button type="button" wire:click="showSection('business')"
                            class="flex w-full items-center justify-between border-b border-gray-200 px-5 py-4 text-left transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-white/5">
                            <span class="flex items-center gap-3">

                                <x-heroicon-o-identification class="h-5 w-5 text-gray-500" />

                                <span class="text-sm font-medium">
                                    Business Information
                                </span>

                            </span>

                            <x-heroicon-o-chevron-right class="h-5 w-5 text-gray-400" />

                        </button>

                        {{-- Menu item --}}
                        <button type="button" wire:click="showSection('employees')"
                            class="flex w-full items-center justify-between px-5 py-4 text-left transition hover:bg-gray-50 dark:hover:bg-white/5">
                            <span class="flex items-center gap-3">

                                <x-heroicon-o-briefcase class="h-5 w-5 text-gray-500" />

                                <span class="text-sm font-medium">
                                    Employees
                                </span>

                            </span>

                            <x-heroicon-o-chevron-right class="h-5 w-5 text-gray-400" />

                        </button>

                    </div>

                </div>

            @elseif ($section === 'owner')
            <div class="msme-form">
                {{ $this->ownerForm }}
            </div>

        @elseif ($section === 'business')
            <div class="msme-form">
                {{ $this->businessForm }}
            </div>
        @elseif ($section === 'employees')
            <div class="msme-form">
            </div>
            </div>

        @endif
    </div>
</x-filament-panels::page>