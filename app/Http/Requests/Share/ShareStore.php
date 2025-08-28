<?php

namespace App\Http\Requests\Share;

use Illuminate\Foundation\Http\FormRequest;

class ShareStore extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "type" => "required|in:folder,archive",
            "url"=> "required|url",
            "expires_at"=> "nullable",
            "password"=> "nullable|string|min:3",
            "users"=> "required|array",
        ];
    }
}
