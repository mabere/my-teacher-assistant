<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeachingProjectWizardExecutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:10000'],
            'teaching_model' => ['nullable', 'string', 'max:120'],
        ];
    }
}
