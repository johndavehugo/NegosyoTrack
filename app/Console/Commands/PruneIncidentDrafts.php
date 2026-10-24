<?php

namespace App\Console\Commands;

use App\Services\IncidentDraftService;
use Illuminate\Console\Command;

class PruneIncidentDrafts extends Command
{
    protected $signature = 'calamity:prune-incident-drafts {--hours=24}';

    protected $description = 'Delete abandoned incident creation drafts and their photo evidence';

    public function handle(IncidentDraftService $drafts): int
    {
        $cutoff = now()->subHours(max(1, (int) $this->option('hours')));
        $total = 0;

        do {
            $deleted = $drafts->pruneOlderThan($cutoff);
            $total += $deleted;
        } while ($deleted > 0);

        $this->info("Pruned {$total} abandoned incident draft(s).");

        return self::SUCCESS;
    }
}
