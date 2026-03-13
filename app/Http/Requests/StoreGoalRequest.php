<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'exercise_id' => 'required|exists:exercises,id',
            'target_weight' => 'required|numeric|min:0',
            'target_reps' => 'nullable|integer|min:1',
            'deadline' => 'nullable|date',
        ];
    }
}
