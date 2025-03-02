<?php

namespace App\Http\Requests\Timesheet;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @author Fahed
 * @description Handles validation for updating existing timesheet entries
 * @package App\Http\Requests\Timesheet
 */
class UpdateTimesheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only authenticated users can update timesheet entries
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
            'project_id' => ['sometimes', 'exists:projects,id'],
            'task_name' => ['sometimes', 'string', 'max:255'],
            'date' => ['sometimes', 'date', 'before_or_equal:today'],
            'hours' => ['sometimes', 'numeric', 'min:0.5', 'max:24'],
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
            'project_id.exists' => 'Selected project does not exist.',
            'date.date' => 'Invalid date format.',
            'date.before_or_equal' => 'Cannot log time for future dates.',
            'hours.numeric' => 'Hours must be a number.',
            'hours.min' => 'Minimum time entry is 30 minutes.',
            'hours.max' => 'Maximum time entry is 24 hours per day.',
        ];
    }
}
