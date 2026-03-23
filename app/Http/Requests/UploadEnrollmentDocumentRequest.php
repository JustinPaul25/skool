<?php

namespace App\Http\Requests;

use App\Models\EnrollmentApplication;
use App\Rules\AltchaPayload;
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
            'document' => [
                'required',
                'file',
                'max:10240',
                'mimetypes:image/jpeg,image/png,application/pdf',
            ],
            'altcha' => [
                config('captcha.altcha.enabled') ? 'required' : 'nullable',
                'string',
                'max:4096',
                new AltchaPayload,
            ],
        ];
    }
}
