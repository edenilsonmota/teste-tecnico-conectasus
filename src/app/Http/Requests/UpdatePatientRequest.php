<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'cpf' => [
                'required',
                'string',
                'regex:/^\d{11}$/',
                Rule::unique('patients', 'cpf')->ignore($this->route('patient')),
            ],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'phone' => ['nullable', 'string', 'max:20'],
            'health_unit_id' => ['required', 'integer', Rule::exists('health_units', 'id')],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'cpf.required' => 'O CPF é obrigatório.',
            'cpf.string' => 'O CPF deve ser informado como texto.',
            'cpf.regex' => 'O CPF deve conter exatamente 11 dígitos numéricos.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'birth_date.required' => 'A data de nascimento é obrigatória.',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',
            'birth_date.before_or_equal' => 'A data de nascimento não pode ser posterior à data atual.',
            'phone.string' => 'O telefone deve ser um texto.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'health_unit_id.required' => 'A unidade de saúde é obrigatória.',
            'health_unit_id.integer' => 'A unidade de saúde informada é inválida.',
            'health_unit_id.exists' => 'A unidade de saúde informada não existe.',
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'cpf' => 'CPF',
            'birth_date' => 'data de nascimento',
            'phone' => 'telefone',
            'health_unit_id' => 'unidade de saúde',
        ];
    }
}
