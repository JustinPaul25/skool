<?php

namespace App\Http\Requests;

use App\Models\EnrollmentApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadEnrollmentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'collection' => [
                'required',
                'string',
                Rule::in([
                    EnrollmentApplication::MEDIA_COLLECTION_PHOTO,
                    EnrollmentApplication::MEDIA_COLLECTION_BIRTH_CERTIFICATE,
                    EnrollmentApplication::MEDIA_COLLECTION_ADDITIONAL,
                ]),
            ],
            'document' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,webp,gif'],
        ];
    }
}
