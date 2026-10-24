<?php

namespace App\Services;

use App\Models\CalamityMonitoring\IncidentDraft;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IncidentDraftService
{
    /**
     * @param  array<int, string>  $ids
     * @param  array<string, array{name: string, barangay: ?string}>  $snapshots
     */
    public function syncSelection(
        string $draftKey,
        ?int $userId,
        int $calamityId,
        array $ids,
        array $snapshots,
    ): void {
        $ids = array_values(array_unique(array_filter(array_map('strval', $ids))));
        $existing = IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->get()
            ->keyBy('juridical_id');

        $removed = $existing->keys()->diff($ids);

        if ($removed->isNotEmpty()) {
            $removedRows = $existing->only($removed->all());

            $this->deletePhotos(
                $removedRows->flatMap(fn (IncidentDraft $row): array => $row->photos ?? [])->all(),
            );

            IncidentDraft::query()
                ->where('draft_key', $draftKey)
                ->whereIn('juridical_id', $removed->all())
                ->delete();
        }

        $now = now();
        $inserts = [];

        foreach ($ids as $index => $id) {
            if ($existing->has($id)) {
                continue;
            }

            $snapshot = $snapshots[$id] ?? [];

            $inserts[] = [
                'draft_key' => $draftKey,
                'user_id' => $userId,
                'calamity_id' => $calamityId,
                'juridical_id' => $id,
                'juridical_name' => $snapshot['name'] ?? $id,
                'barangay' => $snapshot['barangay'] ?? null,
                'estimated_cost_of_damages' => null,
                'photos' => json_encode([]),
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($inserts !== []) {
            DB::table('calamity_incident_drafts')->insertOrIgnore($inserts);
        }

        IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->update(['updated_at' => $now]);
    }

    /**
     * @return array<int, string>
     */
    public function existingIds(string $draftKey): array
    {
        return IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->pluck('juridical_id')
            ->map(fn (mixed $id): string => (string) $id)
            ->all();
    }

    /**
     * @param  array<int, string>  $ids
     * @return Collection<string, IncidentDraft>
     */
    public function findMany(string $draftKey, array $ids): Collection
    {
        $ids = array_values(array_unique(array_filter(array_map('strval', $ids))));

        if ($ids === []) {
            return collect();
        }

        return IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->whereIn('juridical_id', $ids)
            ->get()
            ->keyBy('juridical_id');
    }

    public function page(string $draftKey, int $page, int $perPage): LengthAwarePaginator
    {
        $paginator = IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(perPage: $perPage, page: $page);

        return $paginator->setCollection(
            $paginator->getCollection()
                ->map(fn (IncidentDraft $row): array => $this->toArray($row)),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $allowedIds
     */
    public function savePage(string $draftKey, array $rows, array $allowedIds): void
    {
        $allowedIds = array_map('strval', $allowedIds);

        $rows = collect($rows)
            ->filter(fn (mixed $row): bool => is_array($row))
            ->filter(fn (array $row): bool => in_array((string) ($row['juridical_id'] ?? ''), $allowedIds, true))
            ->values();

        if ($rows->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($draftKey, $rows): void {
            $existing = IncidentDraft::query()
                ->where('draft_key', $draftKey)
                ->get()
                ->keyBy('juridical_id');

            foreach ($rows as $row) {
                $juridicalId = (string) $row['juridical_id'];
                $photos = array_values(array_filter(
                    $row['photos'] ?? [],
                    fn (mixed $photo): bool => is_string($photo) && $photo !== '',
                ));
                $cost = $row['estimated_cost_of_damages'] ?? null;
                $cost = $cost === '' ? null : $cost;

                /** @var IncidentDraft|null $draft */
                $draft = $existing->get($juridicalId);

                if (! $draft instanceof IncidentDraft) {
                    IncidentDraft::create([
                        'draft_key' => $draftKey,
                        'juridical_id' => $juridicalId,
                        'juridical_name' => $row['juridical_name'] ?? $juridicalId,
                        'barangay' => $row['barangay'] ?? null,
                        'estimated_cost_of_damages' => $cost,
                        'photos' => $photos,
                        'sort_order' => 0,
                    ]);

                    continue;
                }

                $removedPhotos = array_diff($draft->photos ?? [], $photos);

                if ($removedPhotos !== []) {
                    $this->deletePhotos(array_values($removedPhotos));
                }

                $draft->update([
                    'juridical_name' => $row['juridical_name'] ?? $draft->juridical_name,
                    'barangay' => $row['barangay'] ?? $draft->barangay,
                    'estimated_cost_of_damages' => $cost,
                    'photos' => $photos,
                ]);
            }
        });
    }

    public function applyCostToAll(string $draftKey, mixed $cost): void
    {
        IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->update([
                'estimated_cost_of_damages' => $cost,
                'updated_at' => now(),
            ]);
    }

    public function deleteRow(string $draftKey, string $juridicalId): void
    {
        $row = IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->where('juridical_id', $juridicalId)
            ->first();

        if (! $row instanceof IncidentDraft) {
            return;
        }

        $this->deletePhotos($row->photos ?? []);

        $row->delete();
    }

    public function clear(string $draftKey): void
    {
        $rows = IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->get();

        $this->deletePhotos(
            $rows->flatMap(fn (IncidentDraft $row): array => $row->photos ?? [])->all(),
        );

        IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->delete();
    }

    /**
     * Remove the draft rows without deleting their photos. Used after the
     * incidents have been created and now own the photo paths.
     */
    public function forget(string $draftKey): void
    {
        IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->delete();
    }

    /**
     * @return Collection<int, IncidentDraft>
     */
    public function all(string $draftKey): Collection
    {
        return IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array{count: int, total: float, with_photos: int}
     */
    public function summary(string $draftKey): array
    {
        $rows = IncidentDraft::query()
            ->where('draft_key', $draftKey)
            ->get(['estimated_cost_of_damages', 'photos']);

        return [
            'count' => $rows->count(),
            'total' => (float) $rows->sum(
                fn (IncidentDraft $row): float => (float) ($row->estimated_cost_of_damages ?? 0),
            ),
            'with_photos' => $rows
                ->filter(fn (IncidentDraft $row): bool => filled($row->photos))
                ->count(),
        ];
    }

    public function pruneOlderThan(CarbonInterface $cutoff, int $limit = 500): int
    {
        $rows = IncidentDraft::query()
            ->where('updated_at', '<', $cutoff)
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return 0;
        }

        $this->deletePhotos(
            $rows->flatMap(fn (IncidentDraft $row): array => $row->photos ?? [])->all(),
        );

        IncidentDraft::query()
            ->whereIn('id', $rows->pluck('id'))
            ->delete();

        return $rows->count();
    }

    /**
     * @return array<string, mixed>
     */
    protected function toArray(IncidentDraft $row): array
    {
        return [
            'juridical_id' => (string) $row->juridical_id,
            'juridical_name' => $row->juridical_name,
            'barangay' => $row->barangay,
            'estimated_cost_of_damages' => $row->estimated_cost_of_damages,
            'photos' => $row->photos ?? [],
        ];
    }

    /**
     * @param  array<int, string>  $photos
     */
    protected function deletePhotos(array $photos): void
    {
        $photos = array_values(array_filter($photos, fn (mixed $photo): bool => is_string($photo) && $photo !== ''));

        if ($photos === []) {
            return;
        }

        Storage::disk('public')->delete($photos);
    }
}
