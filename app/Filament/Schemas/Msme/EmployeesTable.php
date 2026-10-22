<?php

namespace App\Filament\Schemas\Msme;

use Filament\Tables\Columns\TextColumn;

class EmployeesTable
{
    public static function make(){
        return [
               TextColumn::make('entity_no'),
               TextColumn::make('employee_fullname'),
               TextColumn::make('work_status'),
               TextColumn::make('work_type'),
               TextColumn::make('employment_id'),
               TextColumn::make('work_id'),
               TextColumn::make('work_category'),
               TextColumn::make('position_title'),
               TextColumn::make('work_nature'),
            ];
    }
}
