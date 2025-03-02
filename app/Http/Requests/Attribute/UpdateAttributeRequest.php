<?php

namespace App\Http\Requests\Attribute;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @author Fahed
 * @description Handles validation for updating existing dynamic attributes
 * @package App\Http\Requests\Attribute
 */
class UpdateAttributeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only authenticated users can update attributes
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     * Rules are similar to store but name uniqueness check excludes current attribute
     * 
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', 'unique:attributes,name,' . $this->route('attribute')->id],
            'type' => ['sometimes', 'string', 'in:text,date,number,select'],
            'options' => ['required_if:type,select', 'array'],
            'options.*' => ['required_if:type,select', 'string'],
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
            'name.unique' => 'This attribute name already exists.',
            'type.in' => 'Invalid attribute type selected.',
            'options.required_if' => 'Options are required for select type attributes.',
            'options.array' => 'Options must be a list of values.',
            'options.*.required_if' => 'Option values cannot be empty.',
            'options.*.string' => 'Option values must be text.',
        ];
    }
}
