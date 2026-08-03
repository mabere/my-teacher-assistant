<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeachingProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $userId = $this->user()?->getAuthIdentifier();

        return [
            'class_profile_id' => [
                'required',
                'integer',
                Rule::exists('class_profiles', 'id')
                    ->where(fn (Builder $query): Builder => $query->where('user_id', $userId)),
            ],
            'title' => ['required', 'string', 'max:160'],
            'topic' => ['required', 'string', 'max:255'],
            'learning_outcomes' => ['nullable', 'string', 'max:10000'],
            'learning_objectives' => ['required', 'string', 'max:10000'],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:10000'],
            'teaching_model' => ['nullable', 'string', 'max:120'],
        ];
    }
}
