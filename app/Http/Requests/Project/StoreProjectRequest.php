<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @author Fahed
 * @description Handles validation for creating new projects with dynamic attributes
 * @package App\Http\Requests\Project
 */
class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only authenticated users can create projects
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:active,completed,on_hold,cancelled'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'attributes' => ['sometimes', 'array'],
            'attributes.*' => ['sometimes', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Project name is required.',
            'status.required' => 'Project status is required.',
            'status.in' => 'Invalid project status selected.',
            'user_ids.required' => 'Please assign at least one user to the project.',
            'user_ids.array' => 'Invalid user selection format.',
            'user_ids.*.exists' => 'One or more selected users do not exist.',
            'attributes.array' => 'Invalid attributes format.',
        ];
    }
}
