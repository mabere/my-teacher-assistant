<?php

namespace App\Http\Requests;

use App\Enums\AbilityProfile;
use App\Enums\EducationLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'education_level' => ['required', Rule::enum(EducationLevel::class)],
            'grade' => ['required', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:100'],
            'student_count' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'ability_profile' => ['required', Rule::enum(AbilityProfile::class)],
            'facilities' => ['nullable', 'string', 'max:2000'],
            'local_context' => ['nullable', 'string', 'max:2000'],
            'differentiation_needs' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
