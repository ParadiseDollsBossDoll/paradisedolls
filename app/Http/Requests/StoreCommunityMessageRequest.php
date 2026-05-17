<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreCommunityMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string', 'max:4000', 'required_without:attachment'],
            'reply_to' => ['nullable', 'integer', 'exists:community_messages,id'],
            'attachment' => [
                'nullable',
                File::types(['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'])
                    ->rules([
                        'mimetypes:image/jpeg,image/png,image/gif,image/webp,'
                            . 'application/pdf,'
                            . 'application/msword,'
                            . 'application/vnd.openxmlformats-officedocument.wordprocessingml.document,'
                            . 'application/vnd.ms-excel,'
                            . 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,'
                            . 'text/plain,'
                            . 'application/zip,application/x-zip-compressed',
                    ])
                    ->max(10 * 1024),
            ],
        ];
    }
}
