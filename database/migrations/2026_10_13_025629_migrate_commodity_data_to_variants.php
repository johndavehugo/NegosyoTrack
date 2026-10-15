<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Copy each commodity's existing unit_of_measure + srp into its first variant,
        // and turn its brand_name text into a real brands row.
        $commodities = DB::table('commodities')->get();

        foreach ($commodities as $commodity) {
            $brandId = null;

            if (!empty($commodity->brand_name)) {
                DB::table('brands')->updateOrInsert(
                    ['name' => trim($commodity->brand_name)],
                    ['updated_at' => now(), 'created_at' => now()]
                );
                $brandId = DB::table('brands')->where('name', trim($commodity->brand_name))->value('id');
            }

            DB::table('commodities')->where('id', $commodity->id)->update([
                'brand_id' => $brandId,
            ]);

            DB::table('commodity_variants')->insert([
                'commodity_id'    => $commodity->id,
                'unit_of_measure' => $commodity->unit_of_measure,
                'srp'             => $commodity->srp,
                'is_active'       => $commodity->is_active,
                'created_at'      => $commodity->created_at,
                'updated_at'      => $commodity->updated_at,
            ]);

            if (!empty($commodity->establishments)) {
                echo "Commodity #{$commodity->id} ({$commodity->product_name}) had establishments = \"{$commodity->establishments}\" — map this to a juridical_id by hand afterward.\n";
            }
        }
    }

    public function down(): void
    {
        // Data-only migration; nothing to reverse structurally.
    }
};