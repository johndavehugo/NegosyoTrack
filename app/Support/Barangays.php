<?php

namespace App\Support;

final class Barangays
{
    /**
     * @var array<string, string>
     */
    public const OPTIONS = [
        'Bagonbon' => 'Bagonbon',
        'Barangay I' => 'Barangay I (Poblacion)',
        'Barangay II' => 'Barangay II (Poblacion)',
        'Barangay III' => 'Barangay III (Poblacion)',
        'Barangay IV' => 'Barangay IV (Poblacion)',
        'Barangay V' => 'Barangay V (Poblacion)',
        'Barangay VI' => 'Barangay VI (Poblacion)',
        'Buluangan' => 'Buluangan',
        'Codcod' => 'Codcod',
        'Ermita' => 'Ermita',
        'Guadalupe' => 'Guadalupe',
        'Nataban' => 'Nataban',
        'Palampas' => 'Palampas',
        'Prosperidad' => 'Prosperidad',
        'Punao' => 'Punao',
        'Quezon' => 'Quezon',
        'Rizal' => 'Rizal',
        'San Juan' => 'San Juan',
    ];

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::OPTIONS;
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_keys(self::OPTIONS);
    }
}
