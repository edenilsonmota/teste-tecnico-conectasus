<?php

use App\Models\HealthUnit;
use App\Models\Patient;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('lists patients with their health unit and filters by partial name', function () {
    $healthUnit = HealthUnit::create([
        'name' => 'UBS Centro',
        'city' => 'São Luís',
    ]);
    Patient::create([
        'name' => 'Ana Maria',
        'cpf' => '01234567890',
        'birth_date' => '1990-05-10',
        'health_unit_id' => $healthUnit->id,
    ]);
    Patient::create([
        'name' => 'Carlos Souza',
        'cpf' => '12345678901',
        'birth_date' => '1985-08-20',
        'health_unit_id' => $healthUnit->id,
    ]);

    $response = $this->getJson('/api/patients?search=Maria');

    $response
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.name', 'Ana Maria')
        ->assertJsonPath('0.health_unit.id', $healthUnit->id)
        ->assertJsonPath('0.health_unit.name', 'UBS Centro')
        ->assertJsonMissingPath('0.health_unit.city');
});

it('creates a patient and returns 201', function () {
    $healthUnit = HealthUnit::create([
        'name' => 'UBS Cohab',
        'city' => 'São Luís',
    ]);
    $payload = [
        'name' => 'João Silva',
        'cpf' => '00123456789',
        'birth_date' => '2000-01-15',
        'phone' => '(98) 99999-9999',
        'health_unit_id' => $healthUnit->id,
    ];

    $response = $this->postJson('/api/patients', $payload);

    $response
        ->assertCreated()
        ->assertJsonPath('name', 'João Silva')
        ->assertJsonPath('cpf', '00123456789')
        ->assertJsonPath('health_unit.id', $healthUnit->id);
    $this->assertDatabaseHas('patients', [
        'name' => 'João Silva',
        'cpf' => '00123456789',
        'phone' => '(98) 99999-9999',
        'health_unit_id' => $healthUnit->id,
    ]);
    expect(Patient::where('cpf', '00123456789')->firstOrFail()->birth_date->toDateString())
        ->toBe('2000-01-15');
});

it('returns one patient with the health unit', function () {
    $healthUnit = HealthUnit::create([
        'name' => 'UBS Cidade Operária',
        'city' => 'São Luís',
    ]);
    $patient = Patient::create([
        'name' => 'Maria Costa',
        'cpf' => '98765432100',
        'birth_date' => '1995-03-12',
        'health_unit_id' => $healthUnit->id,
    ]);

    $response = $this->getJson("/api/patients/{$patient->id}");

    $response
        ->assertOk()
        ->assertJsonPath('id', $patient->id)
        ->assertJsonPath('health_unit.id', $healthUnit->id)
        ->assertJsonPath('health_unit.name', 'UBS Cidade Operária');
});

it('returns JSON with 404 when the patient does not exist', function () {
    $response = $this->getJson('/api/patients/999999');

    $response
        ->assertNotFound()
        ->assertHeader('content-type', 'application/json');
});

it('updates a patient while keeping their own CPF', function () {
    $healthUnit = HealthUnit::create([
        'name' => 'UBS Centro',
        'city' => 'São Luís',
    ]);
    $patient = Patient::create([
        'name' => 'Nome Original',
        'cpf' => '01234567890',
        'birth_date' => '1992-06-18',
        'health_unit_id' => $healthUnit->id,
    ]);
    $payload = [
        'name' => 'Nome Atualizado',
        'cpf' => '01234567890',
        'birth_date' => '1992-06-18',
        'phone' => '98999999999',
        'health_unit_id' => $healthUnit->id,
    ];

    $response = $this->putJson("/api/patients/{$patient->id}", $payload);

    $response
        ->assertOk()
        ->assertJsonPath('name', 'Nome Atualizado')
        ->assertJsonPath('cpf', '01234567890');
    $this->assertDatabaseHas('patients', [
        'id' => $patient->id,
        'name' => 'Nome Atualizado',
        'cpf' => '01234567890',
        'phone' => '98999999999',
        'health_unit_id' => $healthUnit->id,
    ]);
    expect($patient->fresh()->birth_date->toDateString())->toBe('1992-06-18');
});

it('returns 422 when updating a patient with another patient CPF', function () {
    $healthUnit = HealthUnit::create([
        'name' => 'UBS Centro',
        'city' => 'São Luís',
    ]);
    $patient = Patient::create([
        'name' => 'Primeiro Paciente',
        'cpf' => '01234567890',
        'birth_date' => '1992-06-18',
        'health_unit_id' => $healthUnit->id,
    ]);
    Patient::create([
        'name' => 'Segundo Paciente',
        'cpf' => '98765432100',
        'birth_date' => '1990-01-10',
        'health_unit_id' => $healthUnit->id,
    ]);

    $response = $this->putJson("/api/patients/{$patient->id}", [
        'name' => 'Primeiro Paciente',
        'cpf' => '98765432100',
        'birth_date' => '1992-06-18',
        'health_unit_id' => $healthUnit->id,
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cpf')
        ->assertJsonPath('errors.cpf.0', 'Este CPF já está cadastrado.');
    expect($patient->fresh()->cpf)->toBe('01234567890');
});

it('deletes a patient and returns 204', function () {
    $healthUnit = HealthUnit::create([
        'name' => 'UBS Centro',
        'city' => 'São Luís',
    ]);
    $patient = Patient::create([
        'name' => 'Paciente Removido',
        'cpf' => '12312312312',
        'birth_date' => '1980-11-03',
        'health_unit_id' => $healthUnit->id,
    ]);

    $response = $this->deleteJson("/api/patients/{$patient->id}");

    $response->assertNoContent();
    $this->assertDatabaseMissing('patients', ['id' => $patient->id]);
});

it('returns 422 with Portuguese messages for required fields', function () {
    $response = $this->postJson('/api/patients', []);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'cpf', 'birth_date', 'health_unit_id'])
        ->assertJsonPath('errors.name.0', 'O nome é obrigatório.')
        ->assertJsonPath('errors.cpf.0', 'O CPF é obrigatório.')
        ->assertJsonPath('errors.birth_date.0', 'A data de nascimento é obrigatória.')
        ->assertJsonPath('errors.health_unit_id.0', 'A unidade de saúde é obrigatória.');
});

it('returns 422 for invalid patient data', function () {
    $healthUnit = HealthUnit::create([
        'name' => 'UBS Centro',
        'city' => 'São Luís',
    ]);
    Patient::create([
        'name' => 'CPF Existente',
        'cpf' => '11122233344',
        'birth_date' => '1990-01-01',
        'health_unit_id' => $healthUnit->id,
    ]);

    $response = $this->postJson('/api/patients', [
        'name' => 'Paciente Inválido',
        'cpf' => '11122233344',
        'birth_date' => now()->addDay()->toDateString(),
        'phone' => str_repeat('9', 21),
        'health_unit_id' => 999999,
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cpf', 'birth_date', 'phone', 'health_unit_id'])
        ->assertJsonPath('errors.cpf.0', 'Este CPF já está cadastrado.')
        ->assertJsonPath('errors.birth_date.0', 'A data de nascimento não pode ser posterior à data atual.')
        ->assertJsonPath('errors.phone.0', 'O telefone não pode ter mais de 20 caracteres.')
        ->assertJsonPath('errors.health_unit_id.0', 'A unidade de saúde informada não existe.');
});

it('returns 422 when CPF does not contain exactly 11 numeric digits', function (string $cpf) {
    $healthUnit = HealthUnit::create([
        'name' => 'UBS Centro',
        'city' => 'São Luís',
    ]);

    $response = $this->postJson('/api/patients', [
        'name' => 'Paciente Inválido',
        'cpf' => $cpf,
        'birth_date' => '1990-01-01',
        'health_unit_id' => $healthUnit->id,
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('cpf')
        ->assertJsonPath('errors.cpf.0', 'O CPF deve conter exatamente 11 dígitos numéricos.');
})->with([
    'menos de 11 dígitos' => '1234567890',
    'mais de 11 dígitos' => '123456789012',
    'caracteres não numéricos' => '1234567890a',
]);
