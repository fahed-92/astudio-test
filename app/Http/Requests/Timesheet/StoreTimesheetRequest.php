<?php

namespace App\Http\Requests\Timesheet;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @author Fahed
 * @description Handles validation for creating new timesheet entries
 * @package App\Http\Requests\Timesheet
 */
class StoreTimesheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only authenticated users can create timesheet entries
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
            'project_id' => ['required', 'exists:projects,id'],
            'task_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'hours' => ['required', 'numeric', 'min:0.5', 'max:24'],
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
            'project_id.required' => 'Please select a project.',
            'project_id.exists' => 'Selected project does not exist.',
            'task_name.required' => 'Task name is required.',
            'date.required' => 'Date is required.',
            'date.date' => 'Invalid date format.',
            'date.before_or_equal' => 'Cannot log time for future dates.',
            'hours.required' => 'Number of hours is required.',
            'hours.numeric' => 'Hours must be a number.',
            'hours.min' => 'Minimum time entry is 30 minutes.',
            'hours.max' => 'Maximum time entry is 24 hours per day.',
        ];
    }
}
