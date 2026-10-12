<?php

test('the patient management page is displayed', function () {
    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertViewIs('patients')
        ->assertSee('Cadastro de Pacientes')
        ->assertSee(asset('css/patients.css'), escape: false)
        ->assertSee(asset('js/patients.js'), escape: false);
});
