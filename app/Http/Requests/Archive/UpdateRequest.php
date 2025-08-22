<?php

namespace App\Http\Requests\Archive;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            "name"=> "nullable|string|max:255",
            "category_id"=> "required|integer|exists:categories,id",
            "folder_id"=> "required|integer|exists:folders,id",
            "file"=> "nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,mp3,m4a,wav,zip.rar|max:51200",
        ];
    }
}
