<?php

use App\Models\HealthUnit;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('lists health units ordered by name with only select fields', function () {
    $firstHealthUnit = HealthUnit::create([
        'name' => 'UBS Centro',
        'city' => 'São Luís',
    ]);
    $secondHealthUnit = HealthUnit::create([
        'name' => 'UBS Cidade Operária',
        'city' => 'São Luís',
    ]);

    $response = $this->getJson('/api/health-units');

    $response
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $firstHealthUnit->id,
                'name' => 'UBS Centro',
                'city' => 'São Luís',
            ],
            [
                'id' => $secondHealthUnit->id,
                'name' => 'UBS Cidade Operária',
                'city' => 'São Luís',
            ],
        ]);
});
