<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class HealthUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $healthUnitModel = new class extends Model
        {
            protected $table = 'health_units';

            protected $guarded = [];
        };

        $healthUnits = [
            ['name' => 'UBS Centro', 'city' => 'São Luís'],
            ['name' => 'UBS Cohab', 'city' => 'São Luís'],
            ['name' => 'UBS Cidade Operária', 'city' => 'São Luís'],
        ];

        foreach ($healthUnits as $healthUnit) {
            $healthUnitModel->newQuery()->updateOrCreate(
                [
                    'name' => $healthUnit['name'],
                    'city' => $healthUnit['city'],
                ],
            );
        }
    }
}
