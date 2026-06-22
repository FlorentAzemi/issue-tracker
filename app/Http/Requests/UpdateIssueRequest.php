<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', Rule::in(['open', 'in_progress', 'closed'])],
            'priority'    => ['required', Rule::in(['low', 'medium', 'high'])],
            'due_date'    => ['nullable', 'date'],
        ];
    }
}
