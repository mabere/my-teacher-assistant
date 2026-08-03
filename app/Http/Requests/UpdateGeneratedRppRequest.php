<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeneratedRppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'identity_subject' => ['required', 'string', 'max:150'],
            'identity_grade' => ['required', 'string', 'max:50'],
            'identity_topic' => ['required', 'string', 'max:255'],
            'learning_objectives' => ['required', 'string'],
            'opening' => ['required', 'string'],
            'core' => ['required', 'string'],
            'closing' => ['required', 'string'],
            'diagnostic' => ['nullable', 'string'],
            'formative' => ['nullable', 'string'],
            'summative' => ['nullable', 'string'],
            'differentiation' => ['nullable', 'string'],
            'remedial' => ['nullable', 'string'],
            'enrichment' => ['nullable', 'string'],
            'references' => ['nullable', 'string'],
        ];
    }
}
