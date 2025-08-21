<?php

namespace App\Http\Requests\Archive;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            "files" => "required|array",
            "files.*" => "file|mimes:jpg,jpeg,png,pdf,doc,docx,mp3,m4a,wav,zip.rar|max:51200",
            "category_id" => "required|exists:categories,id",
            "folder_id" => "required|exists:folders,id"
        ];

    }
}
