<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddSetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exercise_id' => 'required|exists:exercises,id',
            'set_number' => 'nullable|integer|min:1',
            'reps' => 'required|integer|min:0',
            'weight' => 'required|numeric|min:0',
        ];
    }
}
