<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @author Fahed
 * @description Handles validation for creating new dynamic attributes
 * @package App\Http\Requests\Attribute
 */
class StoreAttributeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only authenticated users can create attributes
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
        $rules = [
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:string,number,date,select'],
            'options' => ['required_if:type,select', 'array'],
            'options.*' => ['required_if:type,select', 'string'],
            'value' => ['required']
        ];

        // Add type-specific validation for value
        switch($this->input('type')) {
            case 'number':
                $rules['value'] = ['required', 'numeric'];
                break;
            case 'date':
                $rules['value'] = ['required', 'date'];
                break;
            case 'select':
                $rules['value'] = ['required', 'string', 'in:'.implode(',', (array)$this->input('options', []))];
                break;
            default: // string
                $rules['value'] = ['required', 'string'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'Project ID is required.',
            'project_id.exists' => 'Selected project does not exist.',
            'name.required' => 'Attribute name is required.',
            'name.string' => 'Attribute name must be text.',
            'type.required' => 'Attribute type is required.',
            'type.in' => 'Invalid attribute type selected.',
            'options.required_if' => 'Options are required for select type attributes.',
            'options.array' => 'Options must be a list of values.',
            'options.*.required_if' => 'Option values cannot be empty.',
            'options.*.string' => 'Option values must be text.',
            'value.required' => 'Attribute value is required.',
            'value.numeric' => 'Value must be a number for number type attributes.',
            'value.date' => 'Value must be a valid date for date type attributes.',
            'value.in' => 'Selected value must be one of the provided options.',
        ];
    }
}
