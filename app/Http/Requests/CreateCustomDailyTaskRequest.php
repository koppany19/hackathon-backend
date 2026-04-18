<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCustomDailyTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                => 'required|string|max:255',
            'description'          => 'required|string',
            'category'             => 'required|in:sport,meal,mental_health',
            'subcategory'          => 'required|in:individual_created,group_created',
            'time'                 => 'nullable|date_format:H:i',
            'location'             => 'nullable|string|max:255',
            'invited_user_ids'     => 'nullable|array',
            'invited_user_ids.*'   => 'integer|exists:users,id',
        ];
    }
}
