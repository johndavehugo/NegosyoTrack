@php
    $trainings = collect($trainings)
        ->map(fn (array $training): array => $training['attributes'] ?? [])
        ->values();

    $formatDate = function (?string $date): string {
        if (blank($date) || $date === '0000-00-00') {
            return '—';
        }

        return \Illuminate\Support\Carbon::parse($date)->format('M d, Y');
    };

    $completedCount = $trainings->where('status', 'COMPLETED')->count();
    $ongoingCount = $trainings->where('status', 'ONGOING')->count();

    $statusSummary = collect([
        $completedCount > 0 ? $completedCount.' completed' : null,
        $ongoingCount > 0 ? $ongoingCount.' ongoing' : null,
    ])->filter()->implode(' · ');
@endphp

@if ($trainings->isEmpty())
    <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
        <div class="flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-white/5">
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::OutlinedAcademicCap"
                :size="\Filament\Support\Enums\IconSize::Large"
                class="text-gray-400 dark:text-gray-500"
            />
        </div>

        <h3 class="mt-4 text-sm font-semibold text-gray-950 dark:text-white">
            No trainings recorded
        </h3>

        <p class="mt-1 max-w-xs text-xs text-gray-500 dark:text-gray-400">
            This employee has no training records in SCIMS yet.
        </p>
    </div>
@else
    <div class="mb-4 flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3 ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400">
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::OutlinedAcademicCap"
                :size="\Filament\Support\Enums\IconSize::Medium"
            />
        </div>

        <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-950 dark:text-white">
                {{ $trainings->count() }} {{ $trainings->count() === 1 ? 'training' : 'trainings' }} recorded
            </p>

            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                {{ $statusSummary !== '' ? $statusSummary : 'Synced from SCIMS' }}
            </p>
        </div>
    </div>

    <div class="-me-1 max-h-[26rem] space-y-3 overflow-y-auto pe-1">
        @foreach ($trainings as $training)
            @php
                $status = $training['status'] ?? null;

                $statusColor = match ($status) {
                    'COMPLETED' => 'success',
                    'ONGOING' => 'warning',
                    default => 'gray',
                };

                $statusLabel = match ($status) {
                    'COMPLETED' => 'Completed',
                    'ONGOING' => 'Ongoing',
                    null, '' => 'Unknown',
                    default => \Illuminate\Support\Str::headline($status),
                };

                $hours = $training['hours_no'] ?? null;

                $hoursLabel = filled($hours)
                    ? (is_numeric($hours) ? $hours.' hrs' : $hours)
                    : '—';

                $graduatedLabel = $formatDate($training['graduated_at'] ?? null);

                if (($graduatedLabel === '—') && ($status === 'ONGOING')) {
                    $graduatedLabel = 'Not yet';
                }
            @endphp

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-white/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-sm font-semibold leading-5 text-gray-950 dark:text-white">
                            {{ $training['training_program'] ?? 'Untitled training' }}
                        </h3>

                        @if (filled($training['training_type'] ?? null))
                            <p class="mt-1 text-xs font-medium text-primary-600 dark:text-primary-400">
                                {{ $training['training_type'] }}
                            </p>
                        @endif
                    </div>

                    <x-filament::badge :color="$statusColor" size="sm" class="shrink-0">
                        {{ $statusLabel }}
                    </x-filament::badge>
                </div>

                @if (filled($training['conducted_by'] ?? null))
                    <div class="mt-3 flex items-start gap-2 text-xs text-gray-600 dark:text-gray-300">
                        <x-filament::icon
                            :icon="\Filament\Support\Icons\Heroicon::OutlinedBuildingOffice2"
                            :size="\Filament\Support\Enums\IconSize::Small"
                            class="mt-0.5 shrink-0 text-gray-400 dark:text-gray-500"
                        />

                        <span>{{ $training['conducted_by'] }}</span>
                    </div>
                @endif

                <dl class="mt-3 grid grid-cols-2 gap-3 border-t border-gray-100 pt-3 text-xs dark:border-white/10 sm:grid-cols-4">
                    <div class="flex items-start gap-2">
                        <x-filament::icon
                            :icon="\Filament\Support\Icons\Heroicon::OutlinedCalendarDays"
                            :size="\Filament\Support\Enums\IconSize::Small"
                            class="mt-0.5 shrink-0 text-gray-400 dark:text-gray-500"
                        />

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Schedule</dt>
                            <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">
                                {{ $formatDate($training['from_date'] ?? null) }} – {{ $formatDate($training['to_date'] ?? null) }}
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <x-filament::icon
                            :icon="\Filament\Support\Icons\Heroicon::OutlinedClock"
                            :size="\Filament\Support\Enums\IconSize::Small"
                            class="mt-0.5 shrink-0 text-gray-400 dark:text-gray-500"
                        />

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Hours</dt>
                            <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">
                                {{ $hoursLabel }}
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <x-filament::icon
                            :icon="\Filament\Support\Icons\Heroicon::OutlinedUserGroup"
                            :size="\Filament\Support\Enums\IconSize::Small"
                            class="mt-0.5 shrink-0 text-gray-400 dark:text-gray-500"
                        />

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Sector</dt>
                            <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">
                                {{ filled($training['sector'] ?? null) ? $training['sector'] : '—' }}
                            </dd>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <x-filament::icon
                            :icon="\Filament\Support\Icons\Heroicon::OutlinedCheckBadge"
                            :size="\Filament\Support\Enums\IconSize::Small"
                            class="mt-0.5 shrink-0 text-gray-400 dark:text-gray-500"
                        />

                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Graduated</dt>
                            <dd class="mt-0.5 font-medium text-gray-950 dark:text-white">
                                {{ $graduatedLabel }}
                            </dd>
                        </div>
                    </div>
                </dl>
            </div>
        @endforeach
    </div>
@endif
