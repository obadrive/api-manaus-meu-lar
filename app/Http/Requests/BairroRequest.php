<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BairroRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Você pode adicionar lógica de autorização aqui
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'nome' => 'required|string|max:255',
            'geometria' => 'required|array',
            'geometria.type' => 'required|string|in:Polygon',
            'geometria.coordinates' => 'required|array|min:1',
            'geometria.coordinates.*' => 'array|min:3',
            'geometria.coordinates.*.*' => 'numeric',
        ];

        // Para atualização, tornar campos opcionais
        if ($this->isMethod('PATCH')) {
            $rules = [
                'nome' => 'sometimes|string|max:255',
                'geometria' => 'sometimes|array',
                'geometria.type' => 'required_with:geometria|string|in:Polygon',
                'geometria.coordinates' => 'required_with:geometria|array|min:1',
                'geometria.coordinates.*' => 'array|min:3',
                'geometria.coordinates.*.*' => 'numeric',
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do bairro é obrigatório',
            'nome.max' => 'O nome do bairro não pode ter mais de 255 caracteres',
            'geometria.required' => 'A geometria do bairro é obrigatória',
            'geometria.array' => 'A geometria deve ser um array',
            'geometria.type.required' => 'O tipo de geometria é obrigatório',
            'geometria.type.in' => 'O tipo de geometria deve ser Polygon',
            'geometria.coordinates.required' => 'As coordenadas são obrigatórias',
            'geometria.coordinates.min' => 'As coordenadas devem ter pelo menos 1 anel',
            'geometria.coordinates.*.min' => 'Cada anel deve ter pelo menos 3 pontos',
            'geometria.coordinates.*.*.numeric' => 'As coordenadas devem ser numéricas',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
