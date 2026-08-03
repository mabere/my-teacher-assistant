<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveGeneratedContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'context_verified' => ['accepted'],
            'alignment_reviewed' => ['accepted'],
            'content_reviewed' => ['accepted'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
