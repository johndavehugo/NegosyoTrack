<?php

use App\Models\CalamityMonitoring\Incident;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicates = DB::table('calamity_incidents')
            ->select('calamity_id', 'juridical_id')
            ->groupBy('calamity_id', 'juridical_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $ids = DB::table('calamity_incidents')
                ->where('calamity_id', $duplicate->calamity_id)
                ->where('juridical_id', $duplicate->juridical_id)
                ->orderByDesc('id')
                ->pluck('id');

            $keep = $ids->shift();

            if ($keep === null) {
                continue;
            }

            Incident::query()
                ->whereIn('id', $ids->all())
                ->get()
                ->each
                ->delete();
        }

        Schema::table('calamity_incidents', function (Blueprint $table) {
            $table->unique(
                ['calamity_id', 'juridical_id'],
                'unique_incident_per_calamity',
            );
        });
    }

    public function down(): void
    {
        Schema::table('calamity_incidents', function (Blueprint $table) {
            $table->dropUnique('unique_incident_per_calamity');
        });
    }
};
