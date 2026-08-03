<?php

namespace App\Http\Requests;

use App\Models\TeachingProject;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = $this->route('teachingProject');

        return $this->user() !== null
            && $project instanceof TeachingProject
            && $project->user_id === $this->user()->getAuthIdentifier();
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:pdf,docx', 'max:20480'],
        ];
    }
}
