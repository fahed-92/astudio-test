<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @author Fahed
 * @description Handles validation for updating existing projects with dynamic attributes
 * @package App\Http\Requests\Project
 */
class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only authenticated users can update projects
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     * Rules are similar to store but all fields are optional for partial updates
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', 'string', 'in:active,completed,on_hold,cancelled'],
            'user_ids' => ['sometimes', 'array'],
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
            'name.string' => 'Project name must be a string.',
            'status.in' => 'Invalid project status selected.',
            'user_ids.array' => 'Invalid user selection format.',
            'user_ids.*.exists' => 'One or more selected users do not exist.',
            'attributes.array' => 'Invalid attributes format.',
        ];
    }
}
