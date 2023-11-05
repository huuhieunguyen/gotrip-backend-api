<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;

class CreateChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'users' => ['required','array'], // define a validation rule for the 'users' field. It is required and must be an array.
            // defines a validation rule for each element in the 'users' array.
            // 'sometimes' rule means that the validation will only be applied if the users field is present in the request.
            // 'exists' rule specifies that the value of each element in the users array must exist in the id column of the users table.
            'users.*' => ['sometimes','exists:users,id'],
            'isPrivate' => ['required','boolean'],
        ];
    }
}
